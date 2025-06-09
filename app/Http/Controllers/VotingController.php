<?php

namespace App\Http\Controllers;

use App\Models\Carriages;
use App\Models\Voting;
use App\Models\VotingOption;
use App\Models\UserVote;
use App\Models\Content;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon; // Ensure Carbon is imported
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class VotingController extends Controller
{
    /**
     * Get voting for a specific carriage.
     * This function checks if there is an active voting for the carriage.
     * If not, it checks if there is a live content in the carriage.
     * If there is live content, it creates a new voting with the remaining time of the live content.
     * If there is no live content, it creates a new voting with a default duration.
     */
    public function getVotingForCarriage($carriageId)
    {
        $carriage = Carriages::with([
            'contents' => function ($query) {
                $query->where('status', 'published');
            }
        ])->findOrFail($carriageId);

        $activeVoting = Voting::with(['options.content'])
            ->where('carriages_id', $carriageId)
            ->where('is_active', true)
            ->where('end_time', '>', now())
            ->first();

        // Check if there is no active voting
        if (!$activeVoting) {
            // Check if there is any live content in the carriage
            $liveContent = Content::where('is_live', true)
                ->whereHas('carriages', function ($query) use ($carriageId) {
                    $query->where('carriages.id', $carriageId);
                })
                ->where('airing_time_end', '>', now()) // Pastikan stream belum berakhir
                ->first();

            // If there is live content
            if ($liveContent) {
                $remainingSeconds = now()->diffInSeconds($liveContent->airing_time_end, false);

                if ($carriage->contents->count() >= 2) {
                    $newVoting = $this->createVotingForCarriageInternal($carriage, $remainingSeconds);
                    if ($newVoting) {
                        $activeVoting = $newVoting->load(['options.content']);
                    }
                }
            }
            // If there is no live content or no valid remaining time
            else {
                // Only create if there are at least 2 contents in the carriage
                if ($carriage->contents->count() >= 2) {
                    $newVoting = $this->createVotingForCarriageInternal($carriage);
                    if ($newVoting) {
                        $activeVoting = $newVoting->load(['options.content']);
                    }
                }
            }
        }

        $userIdentifier = $this->getUserIdentifier(request());
        $hasVoted = false;
        if ($activeVoting) {
            $hasVoted = UserVote::where('voting_id', $activeVoting->id)
                ->where('user_identifier', $userIdentifier)
                ->exists();
        }

        return response()->json([
            'carriage' => [
                'id' => $carriage->id,
                'name' => $carriage->name,
                'contents' => $carriage->contents->map(function ($content) {
                    return [
                        'id' => $content->id,
                        'title' => $content->title,
                        'thumbnail' => url(Storage::url($content->thumbnail_path)),
                        'description' => $content->description,
                        'duration_seconds' => $content->duration_seconds,
                    ];
                })
            ],
            'voting' => $this->formatVotingResponse($activeVoting, $hasVoted)
        ]);
    }

    /**
     * (HELPER) Create a new voting for a carriage.
     * This function is used internally to create a voting for a carriage.
     * It accepts a Carriages model and an optional duration in seconds.
     * If the duration is not provided, it defaults to 60 seconds.
     * It creates a new Voting model and VotingOption models for each content in the carriage.
     * It returns the created Voting model.
     */
    private function createVotingForCarriageInternal(Carriages $carriage, ?int $durationSeconds = null)
    {
        $voting = null;
        DB::transaction(function () use ($carriage, $durationSeconds, &$voting) {
            // Duration in seconds, default to 60 seconds if not provided
            $actualDuration = $durationSeconds ?? 60;

            // Do not create voting if duration is less than or equal to 0
            if ($actualDuration <= 0) {
                return;
            }

            $voting = Voting::create([
                'carriages_id' => $carriage->id,
                'title' => 'Vote for the next content in ' . $carriage->name . '!',
                'description' => 'Choose your favorite content to be played next.',
                'is_active' => true,
                'start_time' => now(),
                'end_time' => now()->addSeconds($actualDuration)
            ]);

            foreach ($carriage->contents as $content) {
                VotingOption::create([
                    'voting_id' => $voting->id,
                    'content_id' => $content->id,
                ]);
            }
        });

        if ($voting) {
            Log::info("Auto-created new voting poll ID: {$voting->id} for carriage '{$carriage->name}'.");
        }

        return $voting;
    }

    /**
     * Format the voting response for API output.
     * This function formats the voting data into a structured array for API responses.
     * It includes the voting title, description, end time, total votes, and options with their vote counts and percentages.
     */
    private function formatVotingResponse($voting, $hasVoted = false)
    {
        if (!$voting) {
            return null;
        }

        $totalVotes = $voting->options->sum('vote_count');

        return [
            'id' => $voting->id,
            'title' => $voting->title,
            'description' => $voting->description,
            'end_time' => $voting->end_time->toIso8601String(),
            'total_votes' => $totalVotes,
            'has_voted' => $hasVoted,
            'options' => $voting->options->map(function ($option) use ($totalVotes) {
                $percentage = $totalVotes > 0 ? round(($option->vote_count / $totalVotes) * 100, 2) : 0;

                $contentData = [
                    'id' => $option->content->id,
                    'title' => $option->content->title,
                    'thumbnail' => url(Storage::url($option->content->thumbnail_path)),
                    'description' => $option->content->description,
                    'duration_seconds' => $option->content->duration_seconds
                ];

                return [
                    'id' => $option->id,
                    'content' => $contentData,
                    'vote_count' => $option->vote_count,
                    'vote_percentage' => $percentage
                ];
            })
        ];
    }

    /**
     * Create a new voting poll for all content within a specific carriage.
     * This is an internal function to be called by the stream manager.
     */
    public function createVotingForCarriage(Carriages $carriage)
    {
        // Check if there is already an active voting
        if (Voting::where('is_active', true)->exists()) {
            Log::warning("Attempted to create a new voting for carriage {$carriage->id}, but another voting is already active.");
            return null; // Exit if a voting is already running
        }

        $eligibleContents = $carriage->contents()
            ->where('status', 'published')
            ->get();

        if ($eligibleContents->count() < 2) {
            Log::error("Cannot create voting for carriage '{$carriage->name}'. It needs at least 2 published contents.");

            return null;
        }

        $voting = null;
        DB::transaction(function () use ($carriage, $eligibleContents, &$voting) {
            // Default duration for voting in minutes
            $durationMinutes = 1;

            $voting = Voting::create([
                'title' => 'Vote for the next content in ' . $carriage->name . '!',
                'description' => 'The winning content will play immediately after this poll ends.',
                'is_active' => true,
                'start_time' => now(),
                'end_time' => now()->addMinutes($durationMinutes)
            ]);

            foreach ($eligibleContents as $content) {
                VotingOption::create([
                    'voting_id' => $voting->id,
                    'content_id' => $content->id,
                ]);
            }
        });

        Log::info("Successfully created new voting poll ID: {$voting->id} for carriage '{$carriage->name}'.");
        return $voting;
    }

    /**
     * Ends voting, determines a winner, and IMMEDIATELY starts the winner's stream.
     * Called by the system scheduler via StreamController.
     */
    public function endVotingAndStartWinner($votingId)
    {
        $voting = Voting::with('options.content')->findOrFail($votingId);

        // Check if voting is already inactive
        if (!$voting->is_active) {
            Log::info("Voting ID {$votingId} is already inactive. Skipping.");
            return response()->json(['message' => 'Voting already processed.']);
        }

        $voting->update(['is_active' => false]);

        $winnerOption = $voting->options()
            ->orderByDesc('vote_count')
            ->orderBy('created_at', 'asc') // Tie-breaker: opsi yang lebih dulu dibuat menang
            ->first();

        // If no votes were cast, pick a random content as a fallback
        // This is to ensure that we always have a content to stream, even if no one voted
        if (!$winnerOption || $winnerOption->vote_count === 0) {
            Log::warning("Voting ID {$votingId} ended with no votes. Picking a random content as a fallback.");

            $winnerOption = $voting->options()->inRandomOrder()->first();

            if (!$winnerOption) {
                Log::error("Voting ID {$votingId} has no options. Cannot start any stream.");
                return response()->json(['message' => 'Voting ended with no options.'], 422);
            }
        }

        $winnerContent = $winnerOption->content;

        Log::info("Voting ID {$votingId} ended. Winner is '{$winnerContent->title}'. Preparing to start stream immediately.");

        $streamController = new StreamController();
        return $streamController->startStream($winnerContent);
    }

    /**
     * Get active voting with options
     */
    public function getActiveVoting(Request $request)
    {
        $voting = Voting::with([
            'options.content' => function ($query) {
                $query->select('id', 'title', 'thumbnail_path', 'description', 'duration_seconds');
            }
        ])
            ->where('is_active', true)
            ->where('start_time', '<=', now())
            ->where('end_time', '>=', now())
            ->first();

        if (!$voting) {
            return response()->json([
                'message' => 'No active voting available',
                'voting' => null
            ]);
        }

        // Check if user has voted
        $userIdentifier = $this->getUserIdentifier($request);
        $hasVoted = UserVote::where('voting_id', $voting->id)
            ->where('user_identifier', $userIdentifier)
            ->exists();

        // Calculate total votes for percentage calculation (if not already an accessor)
        $totalVotes = $voting->options->sum('vote_count');

        return response()->json([
            'voting' => [
                'id' => $voting->id,
                'title' => $voting->title,
                'description' => $voting->description,
                'end_time' => $voting->end_time->toIso8601String(),
                'total_votes' => $totalVotes,
                'has_voted' => $hasVoted,
                'options' => $voting->options->map(function ($option) use ($totalVotes) {
                    $percentage = $totalVotes > 0 ? round(($option->vote_count / $totalVotes) * 100, 2) : 0;
                    return [
                        'id' => $option->id,
                        'content' => [
                            'id' => $option->content->id,
                            'title' => $option->content->title,
                            'thumbnail' => url(Storage::url($option->content->thumbnail_path)),
                            'description' => $option->content->description,
                            'duration_seconds' => $option->content->duration_seconds
                        ],
                        'vote_count' => $option->vote_count,
                        'vote_percentage' => $percentage
                    ];
                })
            ]
        ]);
    }

    /**
     * Submit vote
     */
    public function submitVote(Request $request)
    {
        $request->validate([
            'voting_option_id' => 'required|exists:voting_options,id'
        ]);

        $option = VotingOption::with('voting')->findOrFail($request->voting_option_id);
        $voting = $option->voting;

        // Check if voting is active
        if (!$voting->is_active || $voting->end_time < now()) {
            return response()->json([
                'message' => 'Voting is no longer active or has ended.'
            ], 422);
        }

        $userIdentifier = $this->getUserIdentifier($request);

        // Check if user already voted
        $existingVote = UserVote::where('voting_id', $voting->id)
            ->where('user_identifier', $userIdentifier)
            ->first();

        if ($existingVote) {
            return response()->json([
                'message' => 'You have already voted in this poll.'
            ], 422);
        }

        DB::transaction(function () use ($option, $userIdentifier) {
            UserVote::create([
                'voting_id' => $option->voting_id,
                'voting_option_id' => $option->id,
                'user_identifier' => $userIdentifier
            ]);

            // Increment vote count
            $option->increment('vote_count');
        });

        // Recalculate percentages for all options in this voting
        $totalVotes = $voting->fresh()->options->sum('vote_count');
        $updatedOptions = $voting->fresh()->options->map(function ($opt) use ($totalVotes) {
            return [
                'id' => $opt->id,
                'vote_count' => $opt->vote_count,
                'vote_percentage' => $totalVotes > 0 ? round(($opt->vote_count / $totalVotes) * 100, 2) : 0
            ];
        });


        return response()->json([
            'message' => 'Vote submitted successfully',
            'option_id' => $option->id,
            'new_vote_count' => $option->fresh()->vote_count,
            'updated_voting_options' => $updatedOptions
        ]);
    }

    /**
     * Get voting results
     */
    public function getResults($votingId)
    {
        $voting = Voting::with([
            'options.content' => function ($query) {
                $query->select('id', 'title', 'thumbnail_path', 'duration_seconds');
            }
        ])
            ->findOrFail($votingId);

        $totalVotes = $voting->options->sum('vote_count');

        $results = $voting->options
            ->sortByDesc('vote_count')
            ->map(function ($option) use ($totalVotes) {
                $percentage = $totalVotes > 0 ? round(($option->vote_count / $totalVotes) * 100, 2) : 0;
                return [
                    'content' => [
                        'id' => $option->content->id,
                        'title' => $option->content->title,
                        'thumbnail' => url(Storage::url($option->content->thumbnail_path)),
                        'duration_seconds' => $option->content->duration_seconds
                    ],
                    'vote_count' => $option->vote_count,
                    'vote_percentage' => $percentage
                ];
            });

        return response()->json([
            'voting' => [
                'id' => $voting->id,
                'title' => $voting->title,
                'total_votes' => $totalVotes,
                'is_active' => $voting->is_active && $voting->end_time >= now(),
                'end_time' => $voting->end_time->toIso8601String()
            ],
            'results' => $results
        ]);
    }

    /**
     * Create new voting (admin function)
     */
    public function createVoting(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'content_ids' => 'required|array|min:2|max:5',
            'content_ids.*' => 'exists:contents,id',
            'duration_minutes' => 'required|integer|min:5|max:1440'
        ]);

        // Validate contents are eligible for voting (not currently live, published)
        $contents = Content::whereIn('id', $request->content_ids)
            ->where('status', 'published')
            ->where('is_live', false)
            ->get();

        if ($contents->count() !== count($request->content_ids)) {
            return response()->json([
                'message' => 'Some selected contents are not eligible for voting (e.g., not published or already live).'
            ], 422);
        }

        // Check if there is an overlapping active voting
        $overlappingVoting = Voting::where('is_active', true)
            ->where('end_time', '>=', now())
            ->first();

        if ($overlappingVoting) {
            return response()->json([
                'message' => 'An active voting is already running. Please wait until it ends.',
                'active_voting_id' => $overlappingVoting->id
            ], 422);
        }


        DB::transaction(function () use ($request, $contents) {
            // Create voting
            $voting = Voting::create([
                'title' => $request->title,
                'description' => $request->description,
                'is_active' => true,
                'start_time' => now(),
                'end_time' => now()->addMinutes($request->duration_minutes)
            ]);

            // Create voting options
            foreach ($contents as $content) {
                VotingOption::create([
                    'voting_id' => $voting->id,
                    'content_id' => $content->id,
                    'vote_count' => 0
                ]);
            }
        });

        return response()->json([
            'message' => 'Voting created successfully',
            'voting_id' => $voting->id,
            'end_time' => $voting->end_time->toIso8601String()
        ]);
    }

    /**
     * Get winner and prepare for next stream.
     * This function should primarily be called by a background job/scheduler
     * when a voting period ends.
     */
    public function endVotingAndScheduleWinner($votingId)
    {
        $voting = Voting::with(['options.content'])
            ->findOrFail($votingId);

        if ($voting->is_active) {
            // If the voting is still active, forcibly end it
            $voting->update(['is_active' => false, 'end_time' => now()]);
            Log::info("Forcibly ended active voting ID: {$votingId}");
        }

        $winner = $voting->options()
            ->with('content')
            ->orderByDesc('vote_count')
            ->first();

        if (!$winner || $winner->vote_count === 0) {
            Log::warning("Voting ID: {$votingId} ended with no votes or no clear winner. No content scheduled.");
            return response()->json([
                'message' => 'Voting ended, but no votes received or no clear winner. No content scheduled.'
            ], 200);
        }

        // Determine when the current stream ends to schedule the winner right after it.
        $currentLiveContent = Content::where('is_live', true)->first();
        $nextStreamTime = now()->addMinutes(5); // Default to 5 minutes from now if no current stream

        if ($currentLiveContent && $currentLiveContent->airing_time_end > now()) {
            $nextStreamTime = $currentLiveContent->airing_time_end->addMinutes();
            Log::info("Scheduling winner '{$winner->content->title}' after current stream ends at {$currentLiveContent->airing_time_end->toIso8601String()}. New start: {$nextStreamTime->toIso8601String()}");
        } else {
            Log::info("No current stream. Scheduling winner '{$winner->content->title}' to start at {$nextStreamTime->toIso8601String()}.");
        }

        // Schedule the winner for next stream
        // Update winner content's airing_time_start and airing_time_end
        // Make sure duration_seconds is available for accurate end_time
        if (!$winner->content->duration_seconds) {
            Log::error("Winner content ID: {$winner->content->id} has no duration_seconds set. Cannot schedule accurately.");
            return response()->json([
                'message' => 'Winner content duration not set. Cannot schedule next stream.',
            ], 422);
        }

        $winner->content->update([
            'is_live' => false,
            'airing_time_start' => $nextStreamTime,
            'airing_time_end' => $nextStreamTime->copy()->addSeconds($winner->content->duration_seconds)
        ]);

        // Deactivate the voting process
        $voting->update(['is_active' => false]);
        Log::info("Voting ID: {$votingId} ended. Winner '{$winner->content->title}' scheduled.");

        return response()->json([
            'message' => 'Voting ended and winner scheduled for next stream.',
            'winner' => [
                'content_id' => $winner->content->id,
                'title' => $winner->content->title,
                'thumbnail' => url(Storage::url($winner->content->thumbnail_path)),
                'vote_count' => $winner->vote_count,
                'vote_percentage' => $voting->options->sum('vote_count') > 0 ?
                    round(($winner->vote_count / $voting->options->sum('vote_count')) * 100, 2) : 0
            ],
            'scheduled_time' => $nextStreamTime->toIso8601String(),
            'scheduled_end_time' => $winner->content->airing_time_end->toIso8601String(),
            'content_duration_seconds' => $winner->content->duration_seconds
        ]);
    }

    /**
     * Get user identifier (IP address for now, can be user ID if auth exists)
     */
    private function getUserIdentifier(Request $request): string
    {
        return $request->ip();
    }
}