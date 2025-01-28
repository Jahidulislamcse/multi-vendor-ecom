<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UpdateVendorTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_can_not_update_vendor_data_of_another_user(): void
    {
        $anotherUser = User::factory()->create();
        $createdVendor = $anotherUser->vendors()->create([
            'name' => $this->faker->company(),
            'address' => $this->faker->address(),
            'description' => $this->faker->paragraph(),
            'contact_email' => $this->faker->email(),
            'contact_phone' => $this->faker->phoneNumber(),
        ]);
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $newName = $this->faker->company();
        $response = $this->putJson(route('vendors.update', $createdVendor->getKey()), [
            'name' => $newName,
        ]);

        $response->assertNotFound();
        $this->assertDatabaseMissing(Vendor::getModel()->getTable(), [
            'name' => $newName,
        ]);
    }

    public function test_can_upload_profile_picture_and_thumbnail_for_vendor(): void
    {
        Storage::fake('s3');
        $avatarFile = UploadedFile::fake()->image('avatar.jpg');
        $coverFile = UploadedFile::fake()->image('cover.jpg');
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $createdVendor = $user->vendors()->create([
            'name' => $this->faker->company(),
            'address' => $this->faker->address(),
            'description' => $this->faker->paragraph(),
            'contact_email' => $oldEmail = $this->faker->email(),
            'contact_phone' => $oldPhone = $this->faker->phoneNumber(),
        ]);
        // uploading profile pic
        $profilePicUploadresponse = $this->postJson(route('uploader.media.store'), [
            'file' => $avatarFile,
            'collection' => 'profile',
        ]);
        // uploading cover pic
        $coverPicUploadresponse = $this->postJson(route('uploader.media.store'), [
            'file' => $coverFile,
            'collection' => 'cover',
        ]);
        $profilePicToken = $profilePicUploadresponse->json('token');
        $coverPicToken = $coverPicUploadresponse->json('token');

        $proPicUpdateResposne = $this->putJson(route('vendors.update', $createdVendor->getKey()), [
            'profilePicture' => $profilePicToken,
        ]);
        $coverPicUpdateResposne = $this->putJson(route('vendors.update', $createdVendor->getKey()), [
            'coverPicture' => $coverPicToken,
        ]);

        $proPicUpdateResposne->assertJsonPath('profile_picture', fn (?array $proPicUrl) => ! is_null($proPicUrl));
        $coverPicUpdateResposne->assertJsonPath('cover_picture', fn (?array $coverPicUrl) => ! is_null($coverPicUrl));
    }

    public function test_can_update_vendor_data(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $createdVendor = $user->vendors()->create([
            'name' => $oldName = $this->faker->company(),
            'address' => $address = $this->faker->address(),
            'description' => $desc = $this->faker->paragraph(),
            'contact_email' => $oldEmail = $this->faker->email(),
            'contact_phone' => $oldPhone = $this->faker->phoneNumber(),
        ]);
        $newName = $this->faker->company();
        $newEmail = $this->faker->email();
        $newPhone = $this->faker->phoneNumber();

        $response = $this->putJson(route('vendors.update', $createdVendor->getKey()), [
            'name' => $newName,
            'contact_email' => $newEmail,
            'contact_phone' => $newPhone,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas(Vendor::getModel()->getTable(), [
            'name' => $newName,
            'contact_email' => $newEmail,
            'contact_phone' => $newPhone,
        ]);
        $this->assertDatabaseMissing(Vendor::getModel()->getTable(), [
            'name' => $oldName,
            'contact_email' => $oldEmail,
            'contact_phone' => $oldPhone,
        ]);
        $response->assertJson([
            'id' => $createdVendor->getKey(),
            'name' => $newName,
            'address' => $address,
            'description' => $desc,
            'contact_email' => $newEmail,
            'contact_phone' => $newPhone,
        ]);
    }
}
