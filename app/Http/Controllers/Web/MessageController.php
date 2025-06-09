<?php

namespace App\Http\Controllers\Web;

use App\Models\Message;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Hotwired\TurboLaravel\Turbo;
use App\Domains\Shared\Events\DomainEventPublisher;
use App\Domains\Shared\Events\DomainEvents\Messages\MessageSent;
use App\Domains\Shared\Events\DomainEvents\Messages\MessageReacted;
use App\Domains\Shared\Events\DomainEvents\Messages\PollVoted;
use App\Domains\Shared\Events\DomainEvents\Messages\TaskCompleted;
use App\Models\Admin;
use App\Models\PollOption;
use App\Models\PollVote;
use App\Models\TaskList;
use App\Models\Task;
use App\Models\TaskCompletion;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\MessageReaction;



class MessageController extends Controller 
{
    private function formatMessageDate($createdAt)
    {
        $date = Carbon::parse($createdAt);
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $startOfWeek = Carbon::now()->startOfWeek(); // Monday
        $lastWeekStart = $startOfWeek->copy()->subWeek();

        if ($date->isToday()) {
            return 'Today';
        } elseif ($date->isYesterday()) {
            return 'Yesterday';
        } elseif ($date->greaterThanOrEqualTo($lastWeekStart) && $date->lessThan($today)) {
            return $date->format('l'); // day of the week
        } else {
            return $date->format('d M Y'); // e.g. 12 May 2025
        }
    }

    public function store(Request $request)
    {
        $auth = auth('admin')->check() ? auth('admin') : auth('web');

        if (!$auth->check()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $sender = $auth->user();

        $eventName = $request->input('event_name');

        $isPoll = $request->boolean('is_poll', false);
        $isTaskList = $request->boolean('is_task_list', false);

        $message = Message::create([
            'sender_id' => $sender->id,
            'sender_type' => get_class($sender),
            'content' => $request->input('content'),
            'event_id' => $request->input('event_id'),
            'is_poll' => $isPoll,
            'is_task_list' => $isTaskList,
        ]);

        // If this is a poll, create the poll options here
        $pollOptions = collect();
        if ($message->is_poll && $request->has('options')) {
            foreach ($request->input('options') as $optionText) {
                $pollOptions->push(PollOption::create([
                    'message_id' => $message->id,
                    'option_text' => $optionText,
                ]));
            }
        }

        // Handle Task List Logic
        $taskList = null;
        $tasks = collect();
        if ($message->is_task_list && $request->has('tasks') && !empty($request->input('tasks'))) {
            $taskList = TaskList::create([
                'message_id' => $message->id,
                'topic' => $request->input('content'),
            ]);

            foreach ($request->input('tasks') as $taskText) {
                $tasks->push(Task::create([
                    'task_list_id' => $taskList->id,
                    'task_text' => $taskText,
                ]));
            }
        }


        $selectedOptionId = null;

        if ($message->is_poll && $pollOptions->isNotEmpty()) {
            $selectedOptionId = $pollOptions->first()->id; // assume user just submitted the first option
        }

        // Dispatch domain event with poll options (if any)
        DomainEventPublisher::publish(new MessageSent($message, $eventName, $pollOptions, $tasks));

    }



    public function react(Request $request)
    {
        $request->validate([
            'message_id' => 'required|exists:messages,id',
            'emoji' => 'required|string|max:20'
        ]);

        $auth = auth('admin')->check() ? auth('admin') : auth('web');

        if (!$auth->check()) { 
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $user = $auth->user();

        // Now apply the reaction logic with proper authentication
        $reaction = MessageReaction::updateOrCreate(
            [
                'message_id' => $request->message_id, 
                'user_id' => $user->id, 
                'user_type' => get_class($user)
            ],
            ['emoji' => $request->emoji]
        );


        $reaction->load('user', 'message');

        DomainEventPublisher::publish(new MessageReacted($reaction));

        return response()->json(['success' => true]);
    }

    public function vote(Request $request)
    {
        $auth = auth('admin')->check() ? auth('admin') : auth('web');

        if (!$auth->check()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $voter = $auth->user();

        $validated = $request->validate([
            'message_id' => 'required|exists:messages,id',
            'option_id' => 'required|exists:poll_options,id',
        ]);

        $alreadyVoted = PollVote::where([
            'message_id' => $validated['message_id'],
            'voter_type' => get_class($voter),
            'voter_id' => $voter->id,
        ])->exists();

        if ($alreadyVoted) {
            return response()->json(['error' => 'You have already voted.'], 409);
        }

        $vote = PollVote::create([
            'poll_option_id' => $validated['option_id'],
            'message_id' => $validated['message_id'],
            'voter_type' => get_class($voter),
            'voter_id' => $voter->id,
        ]);

        DomainEventPublisher::publish(new PollVoted($vote));

        return response()->json(['success' => true, 'vote' => $vote]);
    }

    public function complete(Request $request)
    {
        $auth = auth('admin')->check() ? auth('admin') : auth('web');

        if (!$auth->check()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $worker = $auth->user();
        $taskId = $request->input('task_id');

        $existingCompletion = TaskCompletion::where([
            'task_id' => $taskId,
            'worker_id' => $worker->id,
            'worker_type' => get_class($worker),
        ])->first();

        if ($existingCompletion) {
            return response()->json(['message' => 'Task already completed.'], 200);
        }

        $completion = TaskCompletion::create([
            'task_id' => $taskId,
            'worker_id' => $worker->id,
            'worker_type' => get_class($worker),
        ]);

        DomainEventPublisher::publish(new TaskCompleted($completion));

        return response()->json(['message' => 'Task marked as completed.']);
    }


    public function fetchMessages(Request $request, $eventId)
    {
        $currentUser = auth('admin')->check() ? auth('admin')->user() : auth('web')->user();
        $currentUserClass = get_class($currentUser);
        $currentUserId = $currentUser->id;

        $messages = Message::where('event_id', $eventId)
            ->with(['sender', 'reactions.user', 'pollOptions.votes.voter','taskList.tasks.taskCompletions.worker'])
            ->orderBy('created_at', 'asc')
            ->get();


        $streams = '';

        $currentSenderId = auth('web')->check() ? auth('web')->id() : auth('admin')->id();
        $currentSenderType = auth('web')->check() ? \App\Models\User::class : \App\Models\Admin::class;

        
        $groupedMessages = [];

        foreach ($messages as $message) {
            $dateGroup = $this->formatMessageDate($message->created_at);
            $groupedMessages[$dateGroup][] = $message;
        }

        foreach ($groupedMessages as $dateLabel => $groupMessages) {

            // Insert date label as a Turbo Stream partial
            $streams .= turbo_stream()->append(
                'messages',
                view('Components.messages._date_separator', ['dateLabel' => $dateLabel])
            );

            foreach ($groupMessages as $message) {
                $sender = $message->sender;
                $isAdmin = $sender instanceof \App\Models\Admin;

                $displayName = $isAdmin 
                    ? '(Admin) ' . $sender->name 
                    : $sender->first_name . ' ' . $sender->last_name;

                $profilePicture = $sender->profile_picture
                    ? asset('storage/' . $sender->profile_picture)
                    : asset('storage/default_profile_image.webp');

                $isSender = $message->sender_id === $currentSenderId && $message->sender_type === $currentSenderType;

                $selectedOptionId = null;
                if ($message->is_poll) {
                    foreach ($message->pollOptions as $option) {
                        foreach ($option->votes as $vote) {
                            if (
                                $vote->voter_id === $currentUserId &&
                                $vote->voter_type === $currentUserClass
                            ) {
                                $selectedOptionId = $option->id;
                                break 2;
                            }
                        }
                    }
                }

                // Task Handling
                $tasks = [];
                $taskCompletions = [];
                $completedTaskIds = [];

                if ($message->is_task_list && $message->taskList) {
                    foreach ($message->taskList->tasks as $task) {
                        // Store task details
                        $tasks[] = [
                            'id' => $task->id,
                            'text' => $task->task_text,
                        ];

                        // Store task completions
                        $taskCompletions[$task->id] = [];
                        foreach ($task->taskCompletions as $completion) {
                            $worker = $completion->worker;
                            $taskCompletions[$task->id][] = [
                                'worker_id' => $worker->id,
                                'worker_type' => class_basename(get_class($worker)),
                                'profile_picture' => $worker->profile_picture
                                    ? asset('storage/' . $worker->profile_picture)
                                    : asset('storage/default_profile_image.webp'),
                                'name' => $worker instanceof \App\Models\Admin
                                    ? '(Admin) ' . $worker->name
                                    : $worker->first_name . ' ' . $worker->last_name,
                            ];

                            // Track completed tasks for the current user
                            if ($completion->worker_id === $currentUserId && $completion->worker_type === $currentUserClass) {
                                $completedTaskIds[] = $task->id;
                            }
                        }
                    }
                }


                $streams .= turbo_stream()->append(
                    'messages',
                    view('Components.messages._message', [
                        'message' => $message,
                        'displayName' => $displayName,
                        'profilePicture' => $profilePicture,
                        'isSender' => $isSender,
                        'isPoll' => $message->is_poll,
                        'options' => $message->is_poll ? $message->pollOptions : [],
                        'selectedOptionId' => $selectedOptionId,
                        'isTaskList' => $message->is_task_list,
                        'taskList' => $message->is_task_list ? $message->taskList : null,
                        'tasks' => $tasks,
                        'taskCompletions' => $taskCompletions,
                        'completedTaskIds' => $completedTaskIds,
                    ])
                );
            }
        }


        return response($streams, 200, ['Content-Type' => 'text/vnd.turbo-stream.html']);
    }

} 