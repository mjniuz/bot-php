<?php

namespace App\Imgur;

class Images
{
    /**
     *
     */

    public function upload($image = ''){
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL,"https://api.imgur.com/3/upload");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Client-ID a85f4eb514aa176',
            'Content-Type: application/x-www-form-urlencoded'
        ));
        curl_setopt($ch, CURLOPT_POSTFIELDS,
            "image=".$image);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $server_output = curl_exec ($ch);

        curl_close ($ch);

        if($server_output['success']){
            return $server_output['data']['link'];
        }
        return false;
    }

    public function meme($imgURL = '',$headerText = '',$footerText = ''){
        $url = 'https://memegen.link/custom/'.$headerText.'/'.$footerText.'.jpg?alt='.$imgURL;
        return $url;
    }
}
