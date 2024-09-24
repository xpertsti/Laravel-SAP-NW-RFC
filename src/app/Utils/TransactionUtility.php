<?php
namespace App\Utils;

use App\Traits\ResponseService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Str;

class TransactionUtility
{
    public static function getTimestamp()
    {
        try {
            return Carbon::now('UTC')->format('Y-m-d\TH:i:s\Z');
            
        } catch (\Exception $e) {
            return $this->statusHttp(500, $e);
        }

    }

    public static function createTransactionId()
    {
        try {
            return Str::uuid()->toString();
        } catch (\Exception $e) {
            return $this->statusHttp(500, $e);
        }

    }
}
