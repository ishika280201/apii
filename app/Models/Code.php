<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Code extends Model
{
    use HasFactory;

    protected $table = "code";
    public function isvalidotptime(){
        $timeNow = Carbon::now();
        return $timeNow->diffInMinutes($this->created_at)<10;
    }

    public function sendotp(){
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://console.twilio.com/?frameUrl=/console",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
        ));

        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);
    }
}
