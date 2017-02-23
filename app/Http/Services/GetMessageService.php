<?php

namespace App\Http\Services;

use Illuminate\Support\Facades\Cache;
use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use Carbon\Carbon;
use App\Imgur\Images;
use LINE\LINEBot\MessageBuilder;

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

    protected $image;

    public function __construct(Images $image)
    {
        $this->image = $image;
    }


    public function replySend($dev,$formData)
    {
        $ev = $formData['events']['0'];
        $userID = $this->userID($ev);
        $replyToken = $ev['replyToken'];
        $this->client = new CurlHTTPClient(env('LINE_BOT_ACCESS_TOKEN'));
        $this->bot = new LINEBot($this->client, ['channelSecret' => env('LINE_BOT_SECRET')]);

        $msgResponse = $this->getMsg($ev);
        if(Cache::get($userID.'meme_ready')){
            // image
            $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\ImageMessageBuilder($msgResponse,$msgResponse);

            if($dev){
                echo json_encode($textMessageBuilder);
                exit;
            }

            $response = $this->bot->replyText($replyToken, $textMessageBuilder);
            Cache::forget($userID.'meme_ready');
            return true;
        }

        if($dev){
            echo json_encode($msgResponse);
            exit;
        }

        Cache::forget($userID.'meme_ready');
        $response = $this->bot->replyText($replyToken,  $msgResponse . ' ' . json_encode($ev));
        
        if ($response->isSucceeded()) {
            logger("reply success!!");
            return true;
        }

        return $response;
    }

    private function getMsg($ev = [],$onlyMsg = false){
        $msgType = !empty($ev['message']) ? $ev['message']['type'] : false;
        if($msgType == 'text'){
            $msgUser = $ev['message']['text'];
            if($onlyMsg){
                return $msgUser;
            }

            return $this->collectMsg($msgUser,$ev);
        }

        return 'no msg';
    }

    private function collectMsg($msgUser = '',$ev){
        $userID = $this->userID($ev);

        if($msgUser == 'help'){
            Cache::flush();
            return $this->help();
        }

        if($msgUser == 'maen meme'){
            Cache::flush();
            return $this->meme($ev);
        }

        if(Cache::get($userID.'create_meme')){
            return $this->startMeme($ev);
        }
        // default flush
        Cache::flush();
        return 'Ga jelas lu!';
    }

    private function help(){
        $msg = 'Buat lo yang pengen bikin meme, silahkan ketik:
                *maen Meme*';

        return $msg;
    }

    private function meme($ev = []){
        $userID = $this->userID($ev);
        $expiresAt = Carbon::now()->addMinutes(2);
        Cache::add($userID.'create_meme', true, $expiresAt);

        $msg = 'Tulis kata untuk menaruh gambar di HEADER, lebih dari 10 Karakter yo!!';
        return $msg;
    }

    private function startMeme($ev = []) {
        $userID = $this->userID($ev);
        $expiresAt = Carbon::now()->addMinutes(2);
        $keyHeader = $userID.'meme_header';
        $keyFooter = $userID.'meme_footer';
        $keyReady = $userID.'meme_ready';
        $getHeader = Cache::get($keyHeader);
        $getFooter = Cache::get($keyFooter);

        if(!$getHeader){
            Cache::add($keyHeader, $this->getMsg($ev,true), $expiresAt);
            return 'Tulis kata untuk menaruh gambar di FOOTER';
        }

        if($getHeader && !$getFooter){
            Cache::add($keyFooter, $this->getMsg($ev,true), $expiresAt /*minutes*/);
            return 'Sekarang coba upload gambar lo';
        }

        if($getHeader && $getFooter){
            // upload image
            $image = $this->getMedia($ev);
            $imgURL = $this->image->upload($image);
            $toMemeURL = $this->image->meme($imgURL,$getHeader,$getFooter);

            Cache::add($keyReady, true, $expiresAt /*minutes*/);

            return $toMemeURL;
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

    private function getMedia($ev = []){
        $msgID = $ev['message']['id'];
        $url = 'https://api.line.me/v2/bot/message/'.$msgID.'/content';
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer ' . env('LINE_BOT_ACCESS_TOKEN',''),
            'Content-Type: application/x-www-form-urlencoded'
        ));

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $server_output = curl_exec ($ch);

        curl_close ($ch);

        return base64_encode($server_output);
    }
}