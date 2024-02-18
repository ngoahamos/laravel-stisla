<?php

namespace App\Services;

use App\Models\SMS;
use Illuminate\Support\Facades\Http;


class SMSService extends AbstractService
{
    protected $model;
    private CompanyService $companyService;
    private SMSBalanceService $balanceService;

    /**
     * @param SMS $model
     * @param CompanyService $companyService
     * @param SMSBalanceService $balanceService
     */
    public function __construct(SMS $model,CompanyService $companyService, SMSBalanceService $balanceService)
    {
        $this->model = $model;
        $this->companyService = $companyService;
        $this->balanceService = $balanceService;
    }

    public function triggerSend($phone,$message,$company_id=null, $isSecret = false)
    {
        $provider = config('app.sms_provider');
        $senderId = config('app.sms_sender');

        // sms cost
        $cost = $this->getSMSCount($message) * $this->getCountNumbers($phone);

        // check balance
        if (!$this->balanceService->hasEnoughBalance($company_id, $cost)) {
            return prepareResponse(false, ['message' => 'You dont have enough sms balance. please top up']);
        }


        $loggerId = $this->logSMS($phone, $message, null, $cost, 'sending', $isSecret);

        $senderId = $company_id ? $this->companyService->findById($company_id)?->sms_sender_id ?? $senderId : $senderId;

        $phone = $this->cleanContacts($phone);


        $response =  match ($provider) {
            'mnotify' => $this->sendThroughMnotify($phone, $message,$senderId),
            default => prepareResponse(false, ['message' => 'provider not found']),
        };

        if ($response->status == true) {
            $updatedCost = $response->data['cost'];
            $smsLogger = SMS::find($loggerId);
            if ($smsLogger) {
                $smsLogger->cost = $updatedCost;
                $smsLogger->delivery_status =$updatedCost == 0 ? 'not delivered' : 'sent';
                $smsLogger->campaign_id = $response->data['message'];
                $smsLogger->save();
            }
            $response->data =['message' => 'SMS Sent'] ;
            if ($updatedCost == 0) {
                return prepareResponse(false, ['message' => 'SMS Not Delivered']);
            }


            $this->balanceService->reduceBalance($company_id, $updatedCost);
        } else {
            $smsLogger = SMS::find($loggerId);
            if ($smsLogger) {
                $smsLogger->cost = 0;
                $smsLogger->delivery_status = 'failed';
                $smsLogger->save();
            }
        }

        return $response;
    }


    public function logSMS(string $phone,string $message, $receiver_id,int $cost, string $status, bool $isSecret)
    {
        try {
          $log =  $this->model->create([
                'message' => $isSecret ? "xxxxxx" : $message,
                'receiver_id' => $receiver_id,
                'contact' => $phone,
                'cost' => $cost,
                'user_id' => auth_id(),
                'company_id' => auth_company_id(),
                'branch_id' => auth_branch_id(),
                'delivery_status' => $status
            ]);
            return $log->id;
        }catch (\Exception $ex) {
            logger($ex);
        }

        return null;


    }

    public function sendThroughMnotify($phone, $message, $sendId = 'AsoreApp', $smsCost =1)
    {
        $mnotify_key = config('app.mnotify_key');
        try {
          $response =  Http::withHeaders([
              'Content-Type' => 'application/json',
              'api_key' => $mnotify_key
          ])->post('https://api.mnotify.com/api/sms/quick', [
              'key' => $mnotify_key,
              'recipient' => explode( ',', $phone),
              'sender' => $sendId,
              'message' => $message
          ]);

          if ($response->ok()) {
              $responseData = $response->json();

              if ($responseData['status'] == 'success') {
                  return prepareResponse(true, [
                      'message' =>$responseData['summary']['_id'],
                      'cost' => $responseData['summary']['credit_used']
                  ]);
              }
          } else {
              $errorCode = $response->status();
              $errorMessage = $response->body();
              logger('status => ' . $errorCode . ', errorMessage => ' . $errorMessage );

              return prepareResponse(false, ["message"=>"Failed to Send SMS", "cost" => 0]);
          }

        }catch (\Exception $ex) {
            logger($ex);
        }

        return prepareResponse(false,  ['message'=>'Failed to Send','cost' => 0]);
    }

    public function registerMnotifySenderId($senderId, $purpose)
    {
        logger('sender id => ' . $senderId);
        logger('purpose ' . $purpose);

        $mnotify_key = config('app.mnotify_key');
        try {
            $response =  Http::withHeaders([
                'Content-Type' => 'application/json',
                'api_key' => $mnotify_key
            ])->post('https://api.mnotify.com/api/senderid/register', [
                'key' => $mnotify_key,
                'sender_name' => $senderId,
                'purpose' => $purpose,
            ]);
            if ($response->ok()) {
                $responseData = $response->json();
                logger($responseData);
            } else {
                $errorCode = $response->status();
                $errorMessage = $response->body();
                logger('status => ' . $errorCode . ', errorMessage => ' . $errorMessage );
            }
        }catch (\Exception $exception) {
            logger($exception->getMessage());
        }
    }
    function getSMSCount($message) {
        $encoding = mb_detect_encoding($message);
        $length = mb_strlen($message, $encoding);
        $maxChars = $encoding == 'UTF-8' ? 70 : 160;
        return ceil($length / $maxChars);

    }

    function getCountNumbers($contacts) {
        $num = explode(',', $contacts);

        return count($num);
    }

    function cleanContacts($contacts = ''): string
    {

        return implode(',',collect(explode(',', $contacts))->map(function($item){
            return trim($item);
        })->filter(function ($item){
            return !empty($item);
        })
        ->unique()
        ->toArray());
    }




}
