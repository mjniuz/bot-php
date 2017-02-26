<?php

namespace App\Http\Services;

use Illuminate\Support\Facades\Cache;
use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use Carbon\Carbon;
use App\Imgur\Images;
use LINE\LINEBot\MessageBuilder;
use App\Line\BotRepository;
use App\Google\SpeechRepository;

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
    protected $bot_repo;
    protected $speech;

    public function __construct(Images $image, BotRepository $bot_repo,SpeechRepository $speech)
    {
        $this->image = $image;
        $this->bot_repo = $bot_repo;
        $this->speech = $speech;
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
        $replyToken = $ev['replyToken'];
        $userID = $this->bot_repo->userID($ev);

        $msgResponse = $this->bot_repo->getMsg($ev);
        if(Cache::get($userID.'meme_ready')){
            $this->bot_repo->forgetCache($userID);
            return $this->bot_repo->replyMsg($replyToken,$msgResponse, true);
        }
        $this->bot_repo->forgetCache($userID,['meme_ready']);
        
        if(Cache::get($userID.'voice_ready')){			
            $dir = storage_path().'/app/'.$msgResponse;
            $voiceTexts = $this->speech->convert($dir);
			$transcript = '';
			if(!empty($voiceTexts)){
				foreach($voiceTexts as $voiceText){
					$transcript .= $voiceText['transcript'];
				}
			}
		
            $result = ($transcript != '') ? $transcript : "Lagi ada masalah nih. Coba diulang pake command *voice*";
			
			Cache::forget($userID.'voice');
            Cache::forget($userID.'voice_ready');

            $response = $this->bot_repo->replyMsg($replyToken,$result);
            return $response;
        }
        
		// default
        $response = $this->bot_repo->replyMsg($replyToken,$msgResponse);
        return $response;
    }
    
    /*public function voiceReply($formData = []){
        $ev = $formData['events']['0'];
        $replyToken = $ev['replyToken'];
        $userID = $this->bot_repo->userID($ev);

        $voiceMsg = $this->bot_repo->getVoiceMessage($ev);

        $voiceText = $this->speech->convert(storage_path('public').'/'.$voiceMsg);
        $transcript = isset($voiceText['transcript']) ? $voiceText['transcript'] : false;

        return $this->bot_repo->voiceProcess($replyToken,$transcript);
    }*/
}