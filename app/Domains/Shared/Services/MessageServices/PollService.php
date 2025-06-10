<?php

namespace App\Domains\Shared\Services\MessageServices;

use App\Models\PollOption;
use App\Models\PollVote;
use App\Domains\Shared\Events\DomainEventPublisher;
use App\Domains\Shared\Events\DomainEvents\Messages\PollVoted;
use Illuminate\Http\Request;

class PollService
{
    public function createPoll($messageId, $options)
    {
        $pollOptions = collect();
        foreach ($options as $optionText) {
            $pollOptions->push(PollOption::create([
                'message_id' => $messageId,
                'option_text' => $optionText,
            ]));
        }
        return $pollOptions;
    }

    public function getSelectedPollOption($message, $currentUser)
    {
        foreach ($message->pollOptions as $option) {
            foreach ($option->votes as $vote) {
                if ($vote->voter_id === $currentUser->id && $vote->voter_type === get_class($currentUser)) {
                    return $option->id;
                }
            }
        }
        return null;
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
}