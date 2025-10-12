<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreSponsorRequest;
use App\Http\Requests\UpdateMembersTeamRequest;
use App\Http\Requests\UpdateTeamRequest;
use App\Http\Resources\Team as TeamResource;
use App\Http\Resources\User as UserResource;
use App\Models\Team;
use App\Models\User;
use App\Util\AuthorizeInclude;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class SponsorController implements HasMiddleware
{
    #[\Override]
    public static function middleware(): array
    {
        return [
            new Middleware('permission:read-teams', only: ['index', 'indexWeb', 'show', 'showMembers']),
            new Middleware('permission:create-teams', only: ['store']),
            new Middleware('permission:update-teams', only: ['update']),
            new Middleware('permission:update-teams|update-teams-membership-own', only: ['updateMembers']),
            new Middleware('permission:delete-teams', only: ['destroy']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $include = $request->input('include');
        $sponsorsQ = Sponsor::with(AuthorizeInclude::authorize(Sponsor::class, $include));
        $sponsors = $request->user()->can('read-teams-hidden') ? $sponsorsQ->get() : $sponsorsQ->visible()->get();

        return response()->json(['status' => 'success', 'teams' => TeamResource::collection($teams)]);
    }

    // /**
    //  * Displays the teams page for users.
    //  */
    // public function indexWeb(Request $request)
    // {
    //     $teams = Team::visible()->orderBy('visible_on_kiosk', 'desc')->orderBy('name')->get();

    //     // Send only what's necessary to the front end
    //     $user_id = $request->user()->id;
    //     // Lazy load the teams relationship and send the `id`s over
    //     $user_teams = $request->user()->teams;
    //     $user = [
    //         'id' => $user_id,
    //         'teams' => $user_teams,
    //     ];

    //     return view('teams.index')->with(['teams' => $teams, 'user' => json_encode($user)]);
    // }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSponsorRequest $request): JsonResponse
    {
        $data = $request->validated();

        $sponsor = Sponsor::create([
            'name' => $data['name'],
            'end_date' => $data['end_date'] ?? null,
        ]);

        $sponsor->domainNames()->createMany(array_map(fn ($domainName) => ['domain_name' => $domainName], $data['domain_names'] ?? []));

        if ($sponsor->id !== null) {
            return response()->json(['status' => 'success', 'sponsor' => new SponsorResource($sponsor)], 201);
        }

        return response()->json(['status' => 'error', 'message' => 'unknown_error'], 500);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $include = $request->input('include');
        $sponsor = Sponsor::with(AuthorizeInclude::authorize(Sponsor::class, $include))
            ->where('id', $id)
            ->first();

        if ($sponsor !== null && $sponsor->visible === false && $request->user()->cant('read-teams-hidden')) {
            return response()->json(['status' => 'error', 'message' => 'sponsor_not_found'], 404);
        }

        if ($sponsor !== null) {
            return response()->json(['status' => 'success', 'sponsor' => new SponsorResource($sponsor)]);
        }

        return response()->json(['status' => 'error', 'message' => 'sponsor_not_found'], 404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSponsorRequest $request, string $id): JsonResponse
    {
        $sponsor = Sponsor::where('id', $id)->first();
        if ($sponsor === null || ($sponsor->visible === false && $request->user()->cant('update-sponsors-hidden'))) {
            return response()->json(['status' => 'error', 'message' => 'sponsor_not_found'], 404);
        }
        $data = $request->validated();
        $sponsor->update([
            'name' => $data['name'] ?? $sponsor->name,
            'end_date' => $data['end_date'] ?? $sponsor->end_date,
        ]);

        return response()->json(['status' => 'success', 'sponsor' => new SponsorResource($sponsor)], 201);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $sponsor = Sponsor::where('id', $id)->first();
        if ($sponsor === null) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'sponsor_not_found',
                ],
                422
            );
        }

        if ($team->delete() === true) {
            return response()->json(['status' => 'success', 'message' => 'team_deleted']);
        }

        return response()->json(['status' => 'error'], 500);
    }
}
