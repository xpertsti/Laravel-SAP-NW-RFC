<?php
namespace App\Traits;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

trait LogService {


    public function createLog($transactionId, $timestamp, $dataHeader, $headerUserid, $endpoint)
    {

        $data = [
            'origin'         => 'serverSapRfc',
            'transactionId'  => $transactionId,
            'timestamp'      => $timestamp,
            'log_level'      => 'info',
            'service_name'   => 'SapConnection',
            'message'        => 'success',
            'correlation_id' => $dataHeader,
            'user_id'        => $headerUserid,
            'endpoint'       => $endpoint,
            'status_code'    => '200',
            'response_time'  => '0'
        ];
        Log::info($data);
        return true;
    }

    public function createLogError($transactionId, $timestamp, $dataHeader,$headerUserid, $endpoint, $e)
    {
        $data = [
            'origin'         => 'serverSapRfc',
            'transactionId'  => $transactionId,
            'timestamp'      => $timestamp,
            'log_level'      => 'error',
            'service_name'   => 'SapConnection',
            'message'        => $e,
            'correlation_id' => $dataHeader,
            'user_id'        => $headerUserid,
            'endpoint'       => $endpoint,
            'status_code'    => '500',
            'response_time'  => '0'
        ];

        Log::error($data);
        return $data;
    }

}