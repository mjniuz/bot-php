<?php

namespace App\Http\Services;

use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;

class GetMessageService
{
    /**
     * @var LINEBot
     */
    private $bot;
    /**
     * @var HTTPClient
     */
    private $client;
    
    
    public function replySend($formData)
    {
        $ev = $formData['events']['0'];

        $replyToken = $ev['replyToken'];
        $this->client = new CurlHTTPClient(env('LINE_BOT_ACCESS_TOKEN'));
        $this->bot = new LINEBot($this->client, ['channelSecret' => env('LINE_BOT_SECRET')]);

        $msgResponse = $this->getMsg($ev);
        $response = $this->bot->replyText($replyToken,  $msgResponse . ' ' . json_encode($ev));
        
        if ($response->isSucceeded()) {
            logger("reply success!!");
            return;
        }
    }

    private function getMsg($ev = []){
        $msgType = !empty($ev['message']) ? $ev['message']['type'] : false;
        if($msgType == 'text'){
            $msgUser = $ev['message']['text'];

            return $this->collectMsg($msgUser);
        }

        return 'no msg';
    }

    private function collectMsg($msgUser = ''){
        switch ($msgUser){
            case 'help':
                $response = $this->help();
                break;
            case 'maen meme':
                $response = $this->meme();
                break;
            default:
                $response = $this->help();
        }

        return $response;
    }

    private function help(){
        $msg = 'Buat lo yang pengen bikin meme, silahkan ketik: \n
                *maen Meme*';

        return $msg;
    }

    private function meme(){
        $msg = 'Tulis kata untuk menaruh gambar diatas';
        return $msg;
    }
}