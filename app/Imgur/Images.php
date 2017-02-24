<?php

namespace App\Imgur;

class Images
{
    /**
     *
     */

    public function upload($image = ''){

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.imgur.com/3/upload",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "image=".urlencode($image),
            CURLOPT_HTTPHEADER => array(
                "authorization: Client-ID a85f4eb514aa176",
                "cache-control: no-cache",
                "content-type: application/x-www-form-urlencoded"
            ),
        ));

        $response = json_decode(curl_exec($curl));
        $err = curl_error($curl);
        curl_close($curl);


        if($response->success){
            return str_replace('http','https',$response->data->link);
        }
        return $response;
    }

    public function meme($imgURL = '',$headerText = '',$footerText = ''){
        $headerText = str_replace(' ','-',$headerText);
        $footerText = str_replace(' ','-',$footerText);
        $url = 'https://memegen.link/custom/'.$headerText.'/'.$footerText.'.jpg?alt='.$imgURL;
        return $url;
    }
}
