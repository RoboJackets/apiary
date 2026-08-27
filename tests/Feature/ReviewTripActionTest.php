<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Travel;
use App\Models\TravelAssignment;
use App\Models\User;
use App\Nova\Actions\ReviewTrip;
use App\Nova\Travel as TravelResource;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Http\Requests\NovaRequest;
use Tests\TestCase;

final class ReviewTripActionTest extends TestCase
{
    public function test_primary_contact_cannot_run_review_trip(): void
    {
        $officer = $this->getTestUser(['officer']);
        $traveler = User::factory()->create();

        $travel = Travel::factory()->create([
            'primary_contact_user_id' => $officer->id,
            'status' => 'draft',
        ]);

        TravelAssignment::factory()->create([
            'travel_id' => $travel->id,
            'user_id' => $traveler->id,
        ]);

        $request = NovaRequest::create('/nova-api/travel/actions', 'GET', [
            'resourceId' => $travel->id,
            'display' => 'detail',
        ]);
        $request->setUserResolver(static fn (): User => $officer);

        $this->assertFalse(ReviewTrip::make()->authorizedToRun($request, $travel));
        $this->assertSame('draft', $travel->fresh()?->status);
    }

    public function test_creator_cannot_run_review_trip(): void
    {
        $officer = $this->getTestUser(['officer']);
        $contact = User::factory()->create();
        $traveler = User::factory()->create();

        $travel = Travel::factory()->create([
            'primary_contact_user_id' => $contact->id,
            'created_by_user_id' => $officer->id,
            'status' => 'draft',
        ]);

        TravelAssignment::factory()->create([
            'travel_id' => $travel->id,
            'user_id' => $traveler->id,
        ]);

        $request = NovaRequest::create('/nova-api/travel/actions', 'GET', [
            'resourceId' => $travel->id,
            'display' => 'detail',
        ]);
        $request->setUserResolver(static fn (): User => $officer);

        $this->assertFalse(ReviewTrip::make()->authorizedToRun($request, $travel));
    }

    public function test_other_officer_can_run_review_trip(): void
    {
        $officer = $this->getTestUser(['officer']);
        $contact = User::factory()->create();
        $creator = User::factory()->create();
        $traveler = User::factory()->create();

        $travel = Travel::factory()->create([
            'primary_contact_user_id' => $contact->id,
            'created_by_user_id' => $creator->id,
            'status' => 'draft',
        ]);

        TravelAssignment::factory()->create([
            'travel_id' => $travel->id,
            'user_id' => $traveler->id,
        ]);

        $request = NovaRequest::create('/nova-api/travel/actions', 'GET', [
            'resourceId' => $travel->id,
            'display' => 'detail',
        ]);
        $request->setUserResolver(static fn (): User => $officer);

        $this->assertTrue(ReviewTrip::make()->authorizedToRun($request, $travel));
    }

    public function test_review_trip_cannot_run_without_assignments(): void
    {
        $officer = $this->getTestUser(['officer']);
        $contact = User::factory()->create();
        $creator = User::factory()->create();

        $travel = Travel::factory()->create([
            'primary_contact_user_id' => $contact->id,
            'created_by_user_id' => $creator->id,
            'status' => 'draft',
        ]);

        $request = NovaRequest::create('/nova-api/travel/actions', 'GET', [
            'resourceId' => $travel->id,
            'display' => 'detail',
        ]);
        $request->setUserResolver(static fn (): User => $officer);

        $this->assertFalse(ReviewTrip::make()->authorizedToRun($request, $travel));
    }

    public function test_actions_list_excludes_review_trip_for_primary_contact(): void
    {
        $officer = $this->getTestUser(['officer']);
        $traveler = User::factory()->create();

        $travel = Travel::factory()->create([
            'primary_contact_user_id' => $officer->id,
            'status' => 'draft',
        ]);

        TravelAssignment::factory()->create([
            'travel_id' => $travel->id,
            'user_id' => $traveler->id,
        ]);

        $request = NovaRequest::create('/nova-api/travel/actions', 'GET', [
            'resourceId' => $travel->id,
            'display' => 'detail',
        ]);
        $request->setUserResolver(static fn (): User => $officer);

        $resource = new TravelResource($travel);
        $actions = $resource->availableActionsOnDetail($request);

        $this->assertFalse(
            $actions->contains(static fn ($action): bool => $action instanceof ReviewTrip),
            'Primary contact should not see the ReviewTrip action.'
        );

        $reviewTripAction = $actions->first(
            static fn ($action): bool => $action instanceof Action && $action->uriKey() === 'review-trip'
        );

        $this->assertNotNull($reviewTripAction);
        $this->assertInstanceOf(Action::class, $reviewTripAction);
    }
}
