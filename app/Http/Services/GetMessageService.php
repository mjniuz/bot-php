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

    public function testMeme(){
        $getHeader = 'haha';
        $getFooter = 'wew';
        $image = 'http://www.techinsights.com/uploadedImages/Public_Website/Content_-_Primary/Teardowncom/Sample_Reports/sample-icon.png';
        $base64 = file_get_contents($image);
        $type = 'jpg';
        $base64Encode = base64_encode($base64);

        $toMemeURL = $this->image->upload($base64Encode);
        return $toMemeURL;
    }

    public function replySend($formData)
    {
        $ev = $formData['events']['0'];
        $userID = $this->userID($ev);
        $replyToken = $ev['replyToken'];
        $this->client = new CurlHTTPClient(env('LINE_BOT_ACCESS_TOKEN'));
        $this->bot = new LINEBot($this->client, ['channelSecret' => env('LINE_BOT_SECRET')]);

        $msgResponse = $this->getMsg($ev);
        if(Cache::get($userID.'meme_ready')){
            Cache::forget($userID . 'meme_ready');
            // image
            $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\ImageMessageBuilder($msgResponse,$msgResponse);
            $response = $this->bot->replyMessage($replyToken, $textMessageBuilder);

            if (!$response->isSucceeded()) {
                $response = $this->bot->replyText($replyToken,  $msgResponse);
            }

            return true;
        }

        Cache::forget($userID.'meme_ready');
        $response = $this->bot->replyText($replyToken,  $msgResponse);
        
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

        if($msgType == 'image'){
            return $this->collectMsg('',$ev);
        }

        return $this->help();
    }

    private function collectMsg($msgUser = '',$ev){
        $userID = $this->userID($ev);


        if(Cache::get($userID.'create_meme')){
            return $this->startMeme($ev);
        }

        if(strtolower($msgUser) == 'help' || $msgUser == ''){
            Cache::flush();
            return $this->help();
        }

        if(strtolower($msgUser) == 'maen meme'){
            Cache::flush();
            return $this->meme($ev);
        }
        // default flush
        Cache::flush();
        return 'Ga jelas lu!';
    }

    private function help(){
        $msg = 'Buatanye Alan.
        Buat lo yang pengen bikin meme, silahkan ketik: 
        maen Meme';

        return $msg;
    }

    private function meme($ev = []){
        $userID = $this->userID($ev);
        $expiresAt = Carbon::now()->addMinutes(2);
        Cache::add($userID.'create_meme', true, $expiresAt);

        $msg = 'Tulis kata untuk menaruh gambar di HEADER';
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

            Cache::add($keyReady, true, $expiresAt);

            return $toMemeURL; //$toMemeURL;
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
        $this->client = new CurlHTTPClient(env('LINE_BOT_ACCESS_TOKEN'));
        $this->bot = new LINEBot($this->client, ['channelSecret' => env('LINE_BOT_SECRET')]);
        $msgID = (int)$ev['message']['id'];
        $response = $this->bot->getMessageContent($msgID);

        if ($response->isSucceeded()) {
            return base64_encode($response->getRawBody()); //$response->getRawBody() || json_encode([$response,$msgID]);
        } else {
            error_log($response->getHTTPStatus() . ' ' . $response->getRawBody());
        }

        return false;

    }
}