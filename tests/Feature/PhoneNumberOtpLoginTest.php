<?php

namespace Tests\Feature;

use App\Models\User;
use DateTimeImmutable;
use Http;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Kreait\Firebase\Auth\UserMetaData;
use Kreait\Firebase\Auth\UserRecord;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Lcobucci\JWT\Token\DataSet;
use Lcobucci\JWT\UnencryptedToken;
use Mockery\MockInterface;
use Storage;
use Tests\TestCase;

class PhoneNumberOtpLoginTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_user_needs_to_provide_id_token_from_firebase_to_verify(): void
    {
        $response = $this->postJson(route('otp-login.phone'), []);

        $response->assertUnprocessable()->assertJsonValidationErrorFor('id_token');

        $this->assertDatabaseCount(User::getModel()->getTable(), 0);
    }

    public function test_after_successful_verification_response_will_be_a_token_and_user_data(): void
    {
        Storage::fake('s3');
        $idToken = $this->faker->randomAscii();

        $mockToken = $this->mock(UnencryptedToken::class, function (MockInterface $mock) {
            $mock->shouldReceive('claims')->andReturn(new DataSet([
                'sub' => $this->faker->randomAscii(),
            ], ''));
        });

        $email = $this->faker->email();
        $emailVerified = $this->faker->boolean();
        $photoUrl = $this->faker->imageUrl();

        Http::fake([
            $photoUrl => Http::response('::file::'),
        ]);

        $mockAuth = $this->mock(\Kreait\Firebase\Contract\Auth::class, function (MockInterface $mock) use ($idToken, $mockToken, $email, $emailVerified, $photoUrl) {
            $mock->shouldReceive('verifyIdToken')->with($idToken)->andReturn($mockToken);
            $mock->shouldReceive('getUser')->once()->andReturn(new UserRecord(
                uid: $userUid = $this->faker->uuid(),
                email: $email,
                emailVerified: $emailVerified,
                displayName: $this->faker->name(),
                phoneNumber: $this->faker->phoneNumber(),
                photoUrl: $photoUrl,
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

        $response = $this->postJson(route('otp-login.phone'), [
            'id_token' => $idToken,
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure([
                'token',
                'user' => [
                    'id',
                    'email',
                    'phone_number',
                ],
            ]);

        $this->assertDatabaseHas(User::getModel()->getTable(), [
            'email' => $email,
        ]);

        if ($emailVerified) {
            $this->assertNotNull(User::first()->email_verified_at);
        } else {
            $this->assertNull(User::first()->email_verified_at);
        }
    }
}
