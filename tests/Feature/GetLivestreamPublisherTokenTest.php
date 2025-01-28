<?php

namespace Tests\Feature;

use App\Constants\LivestreamStatuses;
use App\Facades\Livestream as LivestreamService;
use App\Models\Livestream;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class GetLivestreamPublisherTokenTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_owner_can_only_get_livestream_publisher_token(): void
    {
        $user = User::factory()->create();
        $livestream = Livestream::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson(route('livestreams.get-publisher-token', $livestream->getKey()));

        $response->assertNotFound();
    }

    public function test_owner_can_not_get_token_for_ended_livestream(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $vendor = Vendor::factory()->for($user)->create();
        $livestream = Livestream::factory()->for($vendor)->create([
            'ended_at' => now(),
        ]);
        $livestream->setStatus(LivestreamStatuses::FINISHED->value);

        $response = $this->getJson(route('livestreams.get-publisher-token', $livestream->getKey()));

        $response->assertNotFound();
    }

    public function test_after_getting_the_token_it_will_return_jwt_token(): void
    {
        LivestreamService::shouldReceive('generatePublisherToken')->andReturn($token = $this->faker->randomAscii());
        $user = User::factory()->create();
        $vendor = Vendor::factory()->for($user)->create();
        $livestream = Livestream::factory()->for($vendor)->create();
        Sanctum::actingAs($user);

        $response = $this->getJson(route('livestreams.get-publisher-token', $livestream->getKey()));

        $response->assertOk()->assertJson(['token' => $token]);
    }
}
