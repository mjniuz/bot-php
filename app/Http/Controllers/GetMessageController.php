<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetMessageRequest;
use App\Http\Services\GetMessageService;

class GetMessageController
{
    /**
     * @var GetMessageService
     */
    private $messageService;
    
    /**
     * GetMessageController constructor.
     * @param GetMessageService $messageService
     */
    public function __construct(GetMessageService $messageService)
    {
        $this->messageService = $messageService;
    }
    
    public function getMessage(GetMessageRequest $request)
    {
        //logger("request : ", $request->all());
        $this->messageService->replySend($request->json()->all());
    }
}