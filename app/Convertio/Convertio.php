<?php

namespace App\Convertio;


class Convertio
{
    public function __construct() {
        
    }

    public function convertToFlac($source = ''){
        $source = base64_encode(file_get_contents($source));
        $ch = curl_init();
        $apiKey = env('CONVERTIO_API_KEY');

        $data = [
            'apikey'    => $apiKey,
            'input'     => 'base64',
            'file'      => $source,
            'outputformat' => 'flac',
            'filename'  => md5(date('Y-m-d H:i:s'))
        ];
        curl_setopt($ch, CURLOPT_URL, "https://api.convertio.co/convert");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = json_decode(curl_exec($ch));
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close ($ch);


        $getID = isset($result->data->id) ? $result->data->id : false;
        if($getID){
            $status = $this->getStatusConvert($getID);

            $step = $status->data->step;
            while($step != 'finish'){
                $status = $this->getStatusConvert($getID);
                $step = $status->data->step;
            }

            $result = $status->data->output->url;
        }

        
        return $result;
    }
    
    public function getStatusConvert($id = ''){
        $status = @file_get_contents('https://api.convertio.co/convert/'.$id.'/status');
        return json_decode($status);
    }
}