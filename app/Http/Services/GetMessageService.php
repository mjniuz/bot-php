<?php

namespace App\Http\Services;

use Illuminate\Support\Facades\Cache;
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

    private function collectMsg($msgUser = '',$ev){
        switch ($msgUser){
            case 'help':
                $response = $this->help();
                break;
            case 'maen meme':
                $response = $this->meme();
                break;
            case strlen($msgUser) > 10:
                $response = $this->startMeme();
                break;
            default:
                $response = 'Ga jelas lu!';
        }

        return $response;
    }

    private function help(){
        $msg = 'Buat lo yang pengen bikin meme, silahkan ketik: \n
                *maen Meme*';

        return $msg;
    }

    private function meme($isHeader = true){
        $msg = 'Tulis kata untuk menaruh gambar di HEADER, lebih dari 10 Karakter yo!! ';
        return $msg;
    }

    private function startMeme($ev = []) {
        $userID = $this->userID($ev);
        $keyHeader = $userID.'meme_header';
        $keyFooter = $userID.'meme_footer';
        $getHeader = Cache::get($keyHeader);
        if(!$getHeader){
            Cache::add($keyHeader, $this->getMsg($ev), 2 /*minutes*/);
            return 'Tulis kata untuk menaruh gambar di FOOTER';
        }

        if($getHeader){
            Cache::add($keyFooter, $this->getMsg($ev), 2 /*minutes*/);
            return 'Sekarang coba upload gambar lo, lebih dari 10 Karakter yo!!';
        }
        return true;
    }

    private function userID($ev = []){
        $userType = !empty($ev['source']) ? $ev['source']['type'] : false;
        if($userType == 'user'){
            return $ev['source']['userId'];
        }

        return false;
    }
}