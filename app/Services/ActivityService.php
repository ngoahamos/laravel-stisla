<?php

namespace App\Services;

use App\Models\Activity;
use Illuminate\Support\Facades\Auth;

class ActivityService extends AbstractService
{
    protected $model;

    protected $relations = ['agent', 'branch'];
    /**
     * @var PDFService
     */
    private $PDFService;

    /**
     * ActivityService constructor.
     * @param Activity $model
     * @param PDFService $PDFService
     */
    public function __construct(Activity $model, PDFService $PDFService)
    {
        $this->model = $model;
        $this->PDFService = $PDFService;
    }

    public function addActivity(array $attributes)
    {
        $validData = $this->validate($attributes,['action' => 'required','company_id' => 'required']);

        if ($validData->status == false) {
            return glue_errors($validData);
        }

        $activity = $this->store($attributes);
    }

    public function activities($search = [])
    {
        $activities = $this->searchActivities($search);

        return prepareResponse(true, $activities);
    }

    public function searchActivities($search)
    {
        return $this->model->with($this->relations)
            ->when(safe_indexing($search, 'user_id') != null, function($query) use($search){
                return $query->where('user_id', $search['user_id']);
            })
            ->when(safe_indexing($search, 'branch_id') != null, function ($query) use($search) {
                return $query->where('branch_id', $search['branch_id']);
            })
            ->when(safe_indexing($search, 'dates') != null, function ($query) use($search) {
                return $query->whereBetween('created_at', $search['dates']);
            })
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * @throws \Exception
     */
    public function activityPDF($search)
    {
        $activities = $this->searchActivities($search);

        $pdfResponse = $this->PDFService->generatePDF(['activities' => $activities, 'options' => true],
            'pdfs.activities.activities');

        if (!$pdfResponse->status)
        {
            throw new \Exception('Failed to Generate PDF') ;
        }

        $user = Auth::user();
        $this->addActivity(['company_id' => $user->company_id, 'branch_id' => $user->branch_id,
            'user_id' => auth_id(),
            'action' => 'Generated Activities history reported']);

        return $pdfResponse->data->download();
    }


}
