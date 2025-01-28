<?php

namespace Tests\Feature;

use App\Models\Livestream;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Laravel\Sanctum\Sanctum;
use Storage;
use Tests\TestCase;

class CreateLivestreamTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_user_needs_to_be_logged_in_to_create_livestream(): void
    {
        $response = $this->postJson(route('livestreams.store'), []);
        $response->assertUnauthorized();
    }

    public function test_user_provides_proper_data_to_create_livestream(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson(route('livestreams.store'), []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'title',
                'vendor_id',
                'thumbnail_picture',
            ]);
    }

    public function test_vendor_owner_can_create_livestream(): void
    {
        Storage::fake('s3');
        $thumbnailFile = UploadedFile::fake()->image('thumbnail.jpg');

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $vendor = Vendor::factory()->create();

        $thumbUploadresponse = $this->postJson(route('uploader.media.store'), [
            'file' => $thumbnailFile,
            'collection' => 'thumbnail',
        ]);

        $response = $this->postJson(route('livestreams.store'), [
            'title' => $title = $this->faker->text(),
            'vendor_id' => $vendor->getKey(),
            'thumbnail_picture' => $thumbUploadresponse->json('token'),
        ]);

        $response->assertNotFound();
    }

    public function test_after_creating_livestream_it_will_be_in_not_started_status(): void
    {
        Storage::fake('s3');
        $thumbnailFile = UploadedFile::fake()->image('thumbnail.jpg');

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $vendor = Vendor::factory()->create([
            'user_id' => $user->getKey(),
        ]);

        $thumbUploadresponse = $this->postJson(route('uploader.media.store'), [
            'file' => $thumbnailFile,
            'collection' => 'thumbnail',
        ]);

        $response = $this->postJson(route('livestreams.store'), [
            'title' => $title = $this->faker->text(),
            'vendor_id' => $vendor->getKey(),
            'thumbnail_picture' => $thumbUploadresponse->json('token'),
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'id',
                'title',
                'vendor' => [
                    'id',
                    'name',
                    'description',
                    'address',
                    'profile_picture',
                    'cover_picture',
                ],
                'thumbnail_picture',
                'status',
                'scheduled_time',
                'total_duration',
                'products',
            ]);

        $this->assertDatabaseCount(Livestream::getModel()->getTable(), 1);
    }
}
