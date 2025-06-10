<?php

namespace App\Domains\Shared\Services\MessageServices;

use App\Domains\Shared\Services\MessageServices\PollService;
use App\Domains\Shared\Services\MessageServices\TaskListService;
use App\Domains\Shared\Services\MessageServices\MessageFormattingService;
use Hotwired\TurboLaravel\Turbo;

class MessageRenderingService {

    protected $pollService;
    protected $taskListService;
    protected $messageFormattingService;


    public function __construct(PollService $pollService, TaskListService $taskListService, MessageFormattingService $messageFormattingService)
    {
        $this->pollService = $pollService;
        $this->taskListService = $taskListService;
        $this->messageFormattingService = $messageFormattingService;
    }

    public function renderMessageStream($groupMessages, $currentUser)
    {
        $streams = '';
        $senderId = $currentUser->id;
        $senderType = get_class($currentUser);

        foreach ($groupMessages as $dateLabel => $messages) {
            $streams .= turbo_stream()->append('messages', view('Components.messages._date_separator', ['dateLabel' => $dateLabel]));

            foreach ($messages as $message) {
                $displayName = $this->messageFormattingService->getDisplayName($message->sender);
                $profilePicture = $this->messageFormattingService->getProfilePicture($message->sender);
                $selectedOptionId = $this->pollService->getSelectedPollOption($message, $currentUser);
                $taskData = $this->taskListService->getTaskData($message, $currentUser);

                $streams .= turbo_stream()->append(
                    'messages',
                    view('Components.messages._message', [
                        'message' => $message,
                        'displayName' => $displayName,
                        'profilePicture' => $profilePicture,
                        'isSender' => $message->sender_id === $senderId && $message->sender_type === $senderType,
                        'isPoll' => $message->is_poll,
                        'options' => $message->is_poll ? $message->pollOptions : [],
                        'selectedOptionId' => $selectedOptionId,
                        'isTaskList' => $message->is_task_list,
                        'taskList' => $message->is_task_list ? $message->taskList : null,
                        'tasks' => $taskData['tasks'],
                        'taskCompletions' => $taskData['taskCompletions'],
                        'completedTaskIds' => $taskData['completedTaskIds'],
                    ])
                );
                
            }
        }

        return $streams;
    }
}