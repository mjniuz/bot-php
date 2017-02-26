<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetMessageRequest;
use App\Http\Services\GetMessageService;
use Symfony\Component\HttpFoundation\Request;
use App\Google\SpeechRepository;

class GetMessageController
{
    /**
     * @var GetMessageService
     */
    private $messageService;
    protected $speech;
    
    /**
     * GetMessageController constructor.
     * @param GetMessageService $messageService
     */
    public function __construct(GetMessageService $messageService,SpeechRepository $speech)
    {
        $this->messageService = $messageService;
        $this->speech = $speech;
    }
    
    public function getMessage(GetMessageRequest $request)
    {
        //logger("request : ", $request->all());
        $this->messageService->replySend($request->json()->all());
    }
    
    public function responseMessage(GetMessageRequest $request){
        //$this->messageService->voiceReply($request->json()->all());
    }
    
    public function test(GetMessageRequest $request){
        //Storage::put('avatars.flac', $audios['source'],'public');
        $voice = $this->speech->convert();
		foreach($voice as $v){
			echo $v['transcript'];
		}
		echo '<hr>';
        echo json_encode($voice); exit;
    }
}