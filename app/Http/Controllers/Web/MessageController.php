<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Domains\Shared\Services\MessageServices\MessageService;

class MessageController extends Controller 
{

    protected $messageService;

    public function __construct(MessageService $messageService)
    {
        $this->messageService = $messageService;
    }

    public function store(Request $request)
    {
        return $this->messageService->store($request);
    }

    public function react(Request $request)
    {
        return $this->messageService->react($request);
    }

    public function vote(Request $request)
    {
        return $this->messageService->vote($request);
    }

    public function complete(Request $request)
    {
        return $this->messageService->complete($request);
    }

    public function fetchMessages(Request $request, $eventId)
    {
        return $this->messageService->fetchMessages($request, $eventId);
    }

} 