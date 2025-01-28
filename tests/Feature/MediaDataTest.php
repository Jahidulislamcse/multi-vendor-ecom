<?php

namespace Tests\Feature;

use App\Data\Resources\MediaData;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Laravel\Sanctum\Sanctum;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Storage;
use Tests\TestCase;

class MediaDataTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_media_data_representation(): void
    {
        Storage::fake('s3');
        $avatarFile = UploadedFile::fake()->image('avatar.jpg');
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        // $user->vendors()->create([
        //     'name' => $this->faker->company(),
        //     'address' => $this->faker->address(),
        //     'description' => $this->faker->paragraph(),
        // ]);

        Vendor::factory()->for($user)->create();

        // uploading profile pic
        $response = $this->postJson(route('uploader.media.store'), [
            'file' => $avatarFile,
            'collection' => 'profile',
        ]);

        $mediaData = MediaData::from(Media::first())->toArray();
        $this->assertArrayHasKey('id', $mediaData);
        $this->assertArrayHasKey('name', $mediaData);
        $this->assertArrayHasKey('file_name', $mediaData);
        $this->assertArrayHasKey('type', $mediaData);
        $this->assertArrayHasKey('mime_type', $mediaData);
        $this->assertArrayHasKey('size', $mediaData);
        $this->assertArrayHasKey('human_readable_size', $mediaData);
    }
}
