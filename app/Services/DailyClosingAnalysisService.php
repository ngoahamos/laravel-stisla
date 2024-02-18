<?php

namespace App\Services;

use App\Contracts\TransactionType;
use App\Models\DailyClosing;
use Illuminate\Support\Facades\Auth;

class DailyClosingAnalysisService extends AbstractService
{
    protected $model;
    /**
     * @var DailyClosingStatedAsService
     */
    private DailyClosingStatedAsService $closingStatedAsService;
    /**
     * @var DailyClosingAgentService
     */
    private DailyClosingAgentService $closingAgentService;
    /**
     * @var DailyClosingCashAtHandService
     */
    private DailyClosingCashAtHandService $closingCashAtHandService;
    /**
     * @var TransactionService
     */
    private TransactionService $transactionService;
    /**
     * @var BranchService
     */
    private BranchService $branchService;
    /**
     * @var UserService
     */
    private UserService $userService;
    /**
     * @var BankAccountService
     */
    private BankAccountService $bankAccountService;
    /**
     * @var DenominationService
     */
    private DenominationService $denominationService;
    /**
     * @var PDFService
     */
    private PDFService $PDFService;
    /**
     * @var ActivityService
     */
    private ActivityService $activityService;

    /**
     * DailyClosingAnalysisService constructor.
     * @param DailyClosing $model
     * @param DailyClosingStatedAsService $closingStatedAsService
     * @param DailyClosingAgentService $closingAgentService
     * @param DailyClosingCashAtHandService $closingCashAtHandService
     * @param TransactionService $transactionService
     * @param BranchService $branchService
     * @param UserService $userService
     * @param BankAccountService $bankAccountService
     * @param DenominationService $denominationService
     * @param PDFService $PDFService
     * @param ActivityService $activityService
     */
    public function __construct(DailyClosing $model,
                                DailyClosingStatedAsService $closingStatedAsService,
                                DailyClosingAgentService $closingAgentService,
                                DailyClosingCashAtHandService $closingCashAtHandService,
                                TransactionService $transactionService,
                                BranchService $branchService,
                                UserService $userService,
                                BankAccountService $bankAccountService,
                                DenominationService $denominationService,
                                PDFService $PDFService,
                                ActivityService $activityService)
    {
        $this->model = $model;
        $this->closingStatedAsService = $closingStatedAsService;
        $this->closingAgentService = $closingAgentService;
        $this->closingCashAtHandService = $closingCashAtHandService;
        $this->transactionService = $transactionService;
        $this->branchService = $branchService;
        $this->userService = $userService;
        $this->bankAccountService = $bankAccountService;
        $this->denominationService = $denominationService;
        $this->PDFService = $PDFService;
        $this->activityService = $activityService;
    }

    public function dailyClosings($search = [])
    {
        $analysis = $this->search($search);

        return prepareResponse(true, $analysis);
    }

    public function search($search)
    {
        return $this->model->with(
            ['agents.agent', 'cashAtHand.denomination', 'statedAs.bankAccount', 'branch']
        )
            ->when(safe_indexing($search, 'id'), function ($query) use($search){
                return $query->where('id', $search['id']);
            })
            ->when(safe_indexing($search, 'dates'), function ($query) use($search) {
                return  $query->whereBetween('date', $search['dates']);
            })->get();
    }

    public function dailyClosing($id)
    {
        $analysis = $this->getById($id, ['agents.agent', 'cashAtHand.denomination', 'statedAs.bankAccount', 'branch']);

        if ($analysis == null) {
            return $this->notFound('Analysis Not Found');
        }

        return prepareResponse(true, $analysis);
    }

    public function addAnalysis(array $attributes)
    {
//        $validData = $this->validate($attributes,
//            ['date' => 'required', 'user_id' => 'required', 'cashAtHand' => 'required', 'statedAs' => 'required',
//                'agents' => 'required']);
//
//        if ($validData->status == false) {
//            return glue_errors($validData);
//        }

        $analysis = $this->store($attributes);

        if ($analysis == null) {
            return $this->storeFailed("Failed to Add Analysis");
        }

        foreach ($attributes['agents'] as $agent) {
            $agent['daily_closing_id'] = $analysis->id;
            $this->closingAgentService->addDailyAgent($agent);
        }

        foreach ($attributes['cashAtHand'] as $cash)
        {
            $cash['daily_closing_id'] = $analysis->id;
            $this->closingCashAtHandService->addCashAtHand($cash);
        }

        foreach ($attributes['statedAs'] as $stated)
        {

            $stated['daily_closing_id'] = $analysis->id;
            $this->closingStatedAsService->addStatedAs($stated);
        }

        return $this->dailyClosing($analysis->id);

    }

    public function analysisData($date, $branch_id)
    {
        $branch = $this->branchService->getById($branch_id);

        if ($branch == null) {
            return $this->notFound("Branch Not Found");
        }
        // remove later
        $gross = 0;

        $_agents = $this->userService->all();

        $agents = [];

        foreach ($_agents as $agent) {

            $amount = $this->transactionService->asAt(TransactionType::$DEPOSIT, $date, $agent->id);

            if ($agent->status != 'Active' && $amount <= 0) {
                continue;
            }

            $agents[] = [
                'user_id' => $agent->id,
                'name' => $agent->name,
                'amount' => $this->transactionService->asAt(TransactionType::$DEPOSIT, $date, $agent->id)
            ];
        }

        $total_collected = $this->transactionService->asAtByBranch(TransactionType::$DEPOSIT, $date, $branch_id);
        $total_withdrawal = $this->transactionService->asAtByBranch(TransactionType::$WITHDRAWAL, $date, $branch_id);

        $_previous_collected = $this->transactionService->model()
            ->whereDate('date', '<', $date)
            ->where('type', TransactionType::$DEPOSIT)
            ->sum('amount');
        $_previous_withdrawals = $this->transactionService->model()
            ->whereDate('date', '<', $date)
            ->where('type', TransactionType::$WITHDRAWAL)
            ->sum('amount');

        $previous = $_previous_collected - $_previous_withdrawals;

        $bankAccounts = $this->bankAccountService->all();
        $denominations = $this->denominationService->all();


        return prepareResponse(true, [
            'branch' => $branch,
            'gross' => $gross,
            'agents' => $agents,
            'previous' => $previous,
            'total_deposit' => $total_collected,
            'total_withdrawal' => $total_withdrawal,
            'denominations' => $denominations,
            'bankAccounts' => $bankAccounts
        ]);


    }

    public function generateAnalysisPDF($search)
    {
        $data['analyses'] = $this->search($search);
        $data['options'] = true;
        $pdfResponse = $this->PDFService->generatePDF($data, 'pdfs.analysis.closing-analysis');
        if (!$pdfResponse->status) {
            if (!request()->expectsJson()) {
                return redirect()->back()->with('error_message', $pdfResponse->data['message']);
            }
            return helper_response($pdfResponse);
        }
        $this->activityService->addActivity(['company_id' => Auth::user()->company_id,
            'branch_id' => Auth::user()->branch_id,
            'user_id' => auth_id(),
            'action' => 'Generated account Daily Analysis Report']);

        return $pdfResponse->data->download();
    }


}
