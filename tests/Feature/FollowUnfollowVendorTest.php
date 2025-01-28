<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FollowUnfollowVendorTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_user_needs_to_be_authenticated_to_follow_any_vendor(): void
    {
        $vendor = Vendor::factory()->create();
        $response = $this->postJson(route('vendor-follows.store', $vendor->getKey()));

        $response->assertUnauthorized();
    }

    public function test_user_can_not_follow_his_own_vendor(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $vendor = Vendor::factory()->for($user)->create();

        $response = $this->postJson(route('vendor-follows.store', $vendor->getKey()));

        $response->assertForbidden();
    }

    public function test_user_can_not_follow_twice(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $vendor = Vendor::factory()->create();
        $vendor->followers()->attach($user->getKey());

        $response = $this->postJson(route('vendor-follows.store', $vendor->getKey()));

        $response->assertForbidden();
    }

    public function test_user_can_follow_other_vendor(): void
    {
        $vendor = Vendor::factory()->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson(route('vendor-follows.store', $vendor->getKey()));

        $response->assertCreated()
            ->assertJson([
                'followers_count' => 1,
            ]);
        $vendor->loadCount('followers');
        $this->assertEquals($vendor->followers_count, 1);
    }

    public function test_user_can_not_unfollow_vendor_which_is_not_following(): void
    {
        $vendor = Vendor::factory()->create();
        $follower = User::factory()->create();
        $vendor->followers()->attach($follower->getKey());

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->deleteJson(route('vendor-follows.destroy', $vendor->getKey()));

        $response->assertForbidden();
        $vendor->loadCount('followers');
        $this->assertEquals($vendor->followers_count, 1);
    }

    public function test_user_can_unfollow_following_vendor(): void
    {
        $vendor = Vendor::factory()->create();
        $follower = User::factory()->create();
        $vendor->followers()->attach($follower->getKey());
        $user = User::factory()->create();
        $vendor->followers()->attach($user->getKey());
        Sanctum::actingAs($user);

        $response = $this->deleteJson(route('vendor-follows.destroy', $vendor->getKey()));

        $response->assertOk()
            ->assertJson([
                'followers_count' => 1,
            ]);
        $vendor->loadCount('followers');
        $this->assertEquals($vendor->followers_count, 1);
    }
}
