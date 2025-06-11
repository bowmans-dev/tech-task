<?php

namespace App\Domains\Shared\Services\MessageServices;

use Illuminate\Http\Request;
use App\Models\{Message, User, Admin};
use App\Domains\Shared\Services\MessageServices\{PollService, TaskListService, MessageReactionService, MessageFormattingService, MessageRenderingService};
use App\Domains\Shared\Repositories\MessageRepository;
use App\Domains\Shared\Events\DomainEventPublisher;
use App\Domains\Shared\Events\DomainEvents\Messages\MessageSent;

class MessageService {

    public function __construct(
        PollService $pollService, 
        TaskListService $taskListService,
        MessageReactionService $messageReactionService, 
        MessageFormattingService $messageFormattingService, 
        MessageRenderingService $messageRenderingService,
        MessageRepository $messageRepository, 
    )
    {
        $this->pollService = $pollService;
        $this->taskListService = $taskListService;
        $this->messageReactionService = $messageReactionService;
        $this->messageFormattingService = $messageFormattingService;
        $this->messageRenderingService = $messageRenderingService;
        $this->messageRepository = $messageRepository;
    }


    public function store(Request $request)
    {
        $message = $this->messageRepository->createMessage($request);
        $eventName = $request->input('event_name');

        $pollOptions = $message->is_poll && $request->has('options') ? $this->pollService->createPoll($message->id, $request->input('options')) : collect();
        $tasks = $message->is_task_list && $request->has('tasks') ? $this->taskListService->createTaskList($message->id, $message->content, $request->input('tasks'))['tasks'] : collect();

        DomainEventPublisher::publish(new MessageSent($message, $eventName, $pollOptions, $tasks));
    }


    public function react(Request $request)
    {
       return $this->messageReactionService->react($request);
    }


    public function vote(Request $request)
    {
        return $this->pollService->vote($request);
    }


    public function complete(Request $request)
    {
        return $this->taskListService->complete($request);
    }

    
    public function fetchMessages(Request $request, $eventId)
    {
        $currentUser = auth('admin')->check() ? auth('admin')->user() : auth('web')->user();

        $messages = $this->messageRepository->getMessagesByEvent($eventId);

        $groupedMessages = $this->messageFormattingService->groupMessagesByDate($messages);

        $streams = $this->messageRenderingService->renderMessageStream($groupedMessages, $currentUser);

        return response($streams, 200, ['Content-Type' => 'text/vnd.turbo-stream.html']);
    }
}