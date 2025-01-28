<?php

namespace Tests\Feature;

use App\Data\Dto\GenerateSubscriberTokenData;
use App\Data\Resources\UserData;
use App\Facades\Livestream as FacadesLivestream;
use App\Models\Livestream;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class GetLivestreamSubscriberTokenTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_can_not_create_token_for_ended_livestream(): void
    {
        $livestream = Livestream::factory()->ended()->create();
        $response = $this->getJson(route('livestreams.get-subscriber-token', $livestream->getKey()));
        $response->assertNotFound();
    }

    public function test_non_authenticated_user_will_get_only_subscriber_token(): void
    {

        $user = User::factory()->create();
        $vendor = Vendor::factory()->for($user)->create();
        $livestream = Livestream::factory()->for($vendor)->create();

        $ownerId = $user->getKey();
        $vendorId = $vendor->getKey();
        $livestreamId = $livestream->getKey();
        $roomName = "room-{$ownerId}-{$vendorId}-{$livestreamId}";

        $dataToPass = new GenerateSubscriberTokenData(
            roomName: $roomName,
            identity: 'public',
            displayName: 'public',
            isPublic: true,
            userData: null
        );

        $closure = fn (GenerateSubscriberTokenData $data) => $data == $dataToPass;

        FacadesLivestream::shouldReceive('generateSubscriberToken')
            ->withArgs($closure)
            ->andReturn($token = $this->faker->randomAscii());

        $response = $this->getJson(route('livestreams.get-subscriber-token', $livestream->getKey()));

        $response->assertOk()->assertJson([
            'token' => $token,
        ]);
    }

    public function test_authenticated_user_will_get_token_with_subscriber_and_data_publisher_permission(): void
    {
        $user = User::factory()->create();
        $vendor = Vendor::factory()->for($user)->create();
        $livestream = Livestream::factory()->for($vendor)->create();

        $authUser = User::factory()->create();
        Sanctum::actingAs($authUser);
        $ownerId = $user->getKey();
        $vendorId = $vendor->getKey();
        $livestreamId = $livestream->getKey();
        $roomName = "room-{$ownerId}-{$vendorId}-{$livestreamId}";

        $dataToPass = new GenerateSubscriberTokenData(
            roomName: $roomName,
            identity: $authUser->getKey(),
            displayName: $authUser->name,
            isPublic: false,
            userData: UserData::from($authUser)
        );

        $closure = fn (GenerateSubscriberTokenData $data) => $data == $dataToPass;

        FacadesLivestream::shouldReceive('generateSubscriberToken')
            ->withArgs($closure)
            ->andReturn($token = $this->faker->randomAscii());

        $response = $this->getJson(route('livestreams.get-subscriber-token', $livestream->getKey()));

        $response->assertOk()->assertJson([
            'token' => $token,
        ]);
    }
}
