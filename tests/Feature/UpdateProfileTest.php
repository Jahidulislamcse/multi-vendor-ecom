<?php

namespace Tests\Feature;

use App\Constants\GenderTypes;
use App\Models\User;
use DateTimeImmutable;
use Firebase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Kreait\Firebase\Auth\UserMetaData;
use Kreait\Firebase\Auth\UserRecord;
use Laravel\Sanctum\Sanctum;
use Lcobucci\JWT\Token\DataSet;
use Lcobucci\JWT\UnencryptedToken;
use Mockery\MockInterface;
use Otp;
use Storage;
use Tests\TestCase;

class UpdateProfileTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_user_needs_to_be_authenticated_for_updating_email(): void
    {
        $response = $this->putJson(route('email.update'), []);

        $response->assertUnauthorized();
    }

    public function test_user_needs_to_provide_valid_email_for_updating_info(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->putJson(route('email.update'), [
            'email' => 'dummy',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrorFor('email');
    }

    public function test_after_providing_valid_otp_and_email_user_email_will_be_updated(): void
    {
        $otpCode = $this->faker->randomNumber(config('otp.digits'));
        $email = $this->faker->email;
        $user = User::factory()->create();
        $oldEmail = $user->email;
        Sanctum::actingAs($user);

        Otp::shouldReceive('check')->with($otpCode, $email)->andReturnTrue();
        Otp::shouldReceive('forget')->with($email)->andReturnTrue();

        $response = $this->putJson(route('email.update'), [
            'email' => $email,
            'otp' => (string) $otpCode,
        ]);

        $response->assertOk()->assertJson([
            'email' => $email,
        ]);
        $this->assertDatabaseMissing(User::getModel()->getTable(), [
            'email' => $oldEmail,
        ]);
        $this->assertDatabaseHas(User::getModel()->getTable(), [
            'email' => $email,
        ]);
        $this->assertEquals($user->email, $email);
    }

    public function test_after_providing_valid_idtoken_user_phone_will_be_updated(): void
    {
        $idToken = $this->faker->randomAscii();
        $user = User::factory()->create([
            'phone_number' => $oldPhone = $this->faker->phoneNumber(),
        ]);
        Sanctum::actingAs($user);

        $mockToken = $this->mock(UnencryptedToken::class, function (MockInterface $mock) {
            $mock->shouldReceive('claims')->andReturn(new DataSet([
                'sub' => $this->faker->randomAscii(),
            ], ''));
        });

        $email = $this->faker->email();
        $emailVerified = $this->faker->boolean();
        $newPhoneNumber = $this->faker->phoneNumber();

        $mockAuth = $this->mock(\Kreait\Firebase\Contract\Auth::class, function (MockInterface $mock) use ($idToken, $mockToken, $email, $emailVerified, $newPhoneNumber) {
            $mock->shouldReceive('verifyIdToken')->with($idToken)->andReturn($mockToken);
            $mock->shouldReceive('getUser')->once()->andReturn(new UserRecord(
                uid: $userUid = $this->faker->uuid(),
                email: $email,
                phoneNumber: $newPhoneNumber,
                emailVerified: $emailVerified,
                displayName: $this->faker->name(),
                photoUrl: $this->faker->imageUrl(),
                disabled: $this->faker->boolean(10),
                metadata: new UserMetaData(DateTimeImmutable::createFromMutable($this->faker->dateTime()), null, null, null),
                providerData: [],
                mfaInfo: null,
                passwordHash: null,
                passwordSalt: null,
                customClaims: [],
                tenantId: null,
                tokensValidAfterTime: null,
            ));
            $mock->shouldReceive('deleteUser')->with($userUid)->andReturnNull();
        });

        Firebase::shouldReceive('auth')->andReturn($mockAuth);

        $response = $this->putJson(route('phone.update'), [
            'id_token' => $idToken,
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'phone_number' => $newPhoneNumber,
            ]);

        $this->assertDatabaseHas(User::getModel()->getTable(), [
            'phone_number' => $newPhoneNumber,
        ]);

        $this->assertEquals($user->phone_number, $newPhoneNumber);
    }

    public function test_user_picture_can_be_updated(): void
    {
        Storage::fake('s3');
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $profilePicture = UploadedFile::fake()->image('profile_picture.jpeg');

        $imageUploadRes = $this->postJson(route('uploader.media.store'), [
            'file' => $profilePicture,
            'collection' => 'avatar',
        ]);
        $uploadToken = $imageUploadRes->json('token');

        $response = $this->putJson(route('profile.update'), [
            'profile_picture' => $uploadToken,
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'profile_picture' => [
                    'id',
                    'url',
                ],
            ]);

        $this->assertNotNull($user->getFirstMedia('avatar'));
    }

    public function test_user_update_address_gender_and_name(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $name = $this->faker->name();
        $address = $this->faker->address();
        $gender = GenderTypes::MALE->value;

        $response = $this->putJson(route('profile.update'), [
            'gender' => $gender,
            'address' => $address,
            'name' => $name,
        ]);

        $response->assertOk()
            ->assertJson([
                'name' => $name,
                'gender' => $gender,
                'name' => $name,
            ]);
    }
}
