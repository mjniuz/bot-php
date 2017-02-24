<?php

namespace App\Http\Services;

use Illuminate\Support\Facades\Cache;
use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use Carbon\Carbon;
use App\Imgur\Images;
use LINE\LINEBot\MessageBuilder;
use App\Line\BotRepository;

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

    public function __construct(Images $image, BotRepository $bot_repo)
    {
        $this->image = $image;
        $this->bot_repo = $bot_repo;
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
        $userID = $this->bot_repo->userID($ev);
        $replyToken = $ev['replyToken'];
        $this->client = new CurlHTTPClient(env('LINE_BOT_ACCESS_TOKEN'));
        $this->bot = new LINEBot($this->client, ['channelSecret' => env('LINE_BOT_SECRET')]);

        $msgResponse = $this->bot_repo->getMsg($ev);
        if(Cache::get($userID.'meme_ready')){
            Cache::forget($userID.'meme_ready');
            Cache::forget($userID.'create_ready');

            $this->bot_repo->forgetCache($userID);
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
}