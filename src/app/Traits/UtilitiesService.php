<?php
namespace App\Traits;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use App\Utils\TransactionUtility;


trait UtilitiesService {

    public function verificaEncabezados($request) {
        $timestamp = TransactionUtility::getTimestamp();
        $transactionId = TransactionUtility::createTransactionId();

        $headerOrigin = $request->header('X-Origin');
        $headerTransactionid = $request->header('X-Transaction-id');
        $headerTimestamp = $request->header('X-Timestamp');
        $headerUserid = $request->header('X-User-id');
        if (empty($headerOrigin) || empty($headerTransactionid) || empty($headerTimestamp) || empty($headerUserid)) {
            $data = [
                'status' => false,
                'transactionId' => $transactionId,
                'timestamp'     => $timestamp,
                'headerUserid'  => $headerUserid,
                'data'   => [
                    'origin'        => 'serverSapRfc',
                    'transactionId' => $transactionId,
                    'timestamp'     => $timestamp,
                    'status'        => false,
                    'data'          => [],
                    'message'       => 'Se requieren encabezados',
                ]
            ];
            return $data;
        } else {
            $data = [
                'status' => true,
                'transactionId' => $transactionId,
                'timestamp'     => $timestamp,
                'headerUserid'  => $headerUserid,
                'data'   => [
                    'originHeader'         => $headerOrigin,
                    'transactionIdHeader'  => $headerTransactionid,
                    'timestampHeader'      => $headerTimestamp,
                ]
            ];
            return $data;

        }

    }

}