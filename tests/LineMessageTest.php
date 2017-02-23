<?php

use App\Http\Controllers\GetMessageController;

class LineMessageTest extends TestCase
{
    /** @var  GetMessageController */
    protected $controller;
    
    public function setUp()
    {
        parent::setUp();
        $this->controller = app(GetMessageController::class);
    }
    
    /**
     * @group LineMessageTest
     * @test
     */
    public function requestMessage()
    {
        $input = '{"events":[{"type":"message","replyToken":"ba423901a3f349be801537fbedc78936","source":{"userId":"U167f2467e32c933ba5ba335f376ec1c5","type":"user"},"timestamp":1482219839401,"message":{"type":"text","id":"5376324301443","text":"Hello Word"}}]}';
        
        $this->call('post', route('line.bot.message'),[],[],[],[],$input);
    }
}