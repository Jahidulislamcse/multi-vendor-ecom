<?php

namespace Tests\Feature;

use App\Constants\LivestreamStatuses;
use App\Models\Livestream;
use App\Models\User;
use App\Models\Vendor;
use DateTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Sanctum\Sanctum;
use Storage;
use Tests\TestCase;

class UpdateLivestreamTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_user_needs_to_be_logged_in_to_update_livestream(): void
    {
        $livestream = Livestream::factory()->create();
        $response = $this->putJson(route('livestreams.update', $livestream->getKey()), []);
        $response->assertUnauthorized();
    }

    public function test_owner_can_update_livestream(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $livestream = Livestream::factory()->create();

        $response = $this->putJson(route('livestreams.update', $livestream->getKey()), []);
        $response->assertNotFound();
    }

    public function test_can_add_thumbnail_for_livestream(): void
    {
        Storage::fake('s3');
        $thumbnailFile = UploadedFile::fake()->image('thumbnail.jpg');

        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $vendor = Vendor::factory()->for($user)->create();
        $livestream = Livestream::factory()->for($vendor)->create();

        $thumbUploadresponse = $this->postJson(route('uploader.media.store'), [
            'file' => $thumbnailFile,
            'collection' => 'thumbnail',
        ]);

        $response = $this->putJson(route('livestreams.update', $livestream->getKey()), [
            'title' => $title = $this->faker->text(),
            'scheduled_time' => $scheduledTime = $this->faker->dateTimeBetween(startDate: '+1 month', endDate: '+2 months')->format(DateTime::ATOM),
            'thumbnail_picture' => $thumbUploadresponse->json('token'),
        ]);

        $response->assertOk()->assertJson([
            'vendor' => [
                'id' => $vendor->getKey(),
                'name' => $vendor->name,
                'address' => $vendor->address,
            ],
            'title' => $title,
            'scheduled_time' => $scheduledTime,
        ]);

        $this->assertDatabaseMissing($livestream->getTable(), [
            'title' => $livestream->title,
        ]);
        $this->assertDatabaseHas($livestream->getTable(), [
            'title' => $title,
        ]);
    }

    public function test_owner_can_start_livestream(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $vendor = Vendor::factory()->for($user)->create();
        $livestream = Livestream::factory()->for($vendor)->create();

        $response = $this->putJson(route('livestreams.update', $livestream->getKey()), [
            'status' => 'test',
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrorFor('status');

        $response = $this->putJson(route('livestreams.update', $livestream->getKey()), [
            'status' => $status = LivestreamStatuses::STARTED->value,
        ]);

        $response->assertOk()->assertJson([
            'id' => $livestream->getKey(),
            'status' => $status,
        ]);

        $response->assertJson(fn (AssertableJson $json) => $json->whereType('started_at', 'string')->etc());

        $this->assertDatabaseCount($livestream->getTable(), 1);
    }

    public function test_owner_can_end_livestream(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $vendor = Vendor::factory()->for($user)->create();
        $livestream = Livestream::factory()->for($vendor)->create([
            'started_at' => now(),
        ]);

        $response = $this->putJson(route('livestreams.update', $livestream->getKey()), [
            'status' => 'test',
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrorFor('status');

        $response = $this->putJson(route('livestreams.update', $livestream->getKey()), [
            'status' => $status = LivestreamStatuses::FINISHED->value,
        ]);

        $response->assertOk()->assertJson([
            'id' => $livestream->getKey(),
            'status' => $status,
        ]);

        $response->assertJson(fn (AssertableJson $json) => $json->whereType('ended_at', 'string')->etc());

        $this->assertDatabaseCount($livestream->getTable(), 1);
    }
}
