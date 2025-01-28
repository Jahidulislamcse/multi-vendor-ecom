<?php

namespace Tests\Feature;

use App\Constants\FeedbackMessage;
use App\Models\User;
use App\Notifications\OtpNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use Tzsk\Otp\Facades\Otp;

class EmailOtpLoginTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_email_needs_to_be_provided_for_getting_otp(): void
    {
        $response = $this->postJson(route('otp.email'), []);
        $response->assertUnprocessable();
        $response->assertJsonValidationErrorFor('email');
    }

    public function test_rate_limit_of_10_attempts_per_minute_for_corresponding_email(): void
    {
        for ($i = 0; $i <= 11; $i++) {
            $response = $this->postJson(route('otp.email'), []);
        }

        $response->assertTooManyRequests();
    }

    public function test_otp_of_6_digit_will_be_sent_to_the_provided_email(): void
    {
        $otp = (string) $this->faker()->randomNumber(config('otp.digits'));
        $email = $this->faker()->email();
        Otp::shouldReceive('make')->with($email)->andReturn($otp);
        Notification::fake();

        $response = $this->postJson(route('otp.email'), [
            'email' => $email,
        ]);

        $response->assertOk();
        $response->assertJson([
            'message' => FeedbackMessage::OTP_SENT->value,
        ]);
        $this->assertDatabaseMissing(User::getModel()->getTable(), [
            'email' => $email,
        ]);
        Notification::assertSentOnDemand(
            OtpNotification::class, function (OtpNotification $notification, array $channels, object $notifiable) use ($otp, $email) {
                return $notification->code === $otp && $notifiable->routes['mail'] === $email;
            }
        );
        Notification::assertCount(1);
    }

    public function test_to_verify_otp_needs_to_provide_the_code_and_email(): void
    {
        $response = $this->postJson(route('otp-login.email'), []);
        $response->assertUnprocessable()
            ->assertJsonValidationErrorFor('otp')
            ->assertJsonValidationErrorFor('email');
    }

    public function test_will_throw_unauthenticated_if_code_does_not_match(): void
    {
        $email = $this->faker()->email();
        $otp = (string) $this->faker()->randomNumber(config('otp.digits'));
        $wrongOtp = (string) $this->faker()->randomNumber(config('otp.digits'));
        Otp::shouldReceive('make')->with($email)->andReturn($otp);
        Otp::shouldReceive('check')->with($wrongOtp, $email)->andReturnUsing(function ($otpArg, $emailArg) use ($otp, $email) {
            return $otpArg === $otp && $emailArg === $email;
        });

        $this->postJson(route('otp.email'), [
            'email' => $email,
        ]);
        $response = $this->postJson(route('otp-login.email'), [
            'email' => $email,
            'otp' => $wrongOtp,
        ]);

        $response->assertUnauthorized();
    }

    // public function test_will_return_user_data_with_valid_otp_and_email(): void
    // {
    //     $email = $this->faker()->email();
    //     $otp = (string) $this->faker()->randomNumber(config('otp.digits'));
    //     Otp::shouldReceive('make')->with($email)->andReturn($otp);
    //     Otp::shouldReceive('check')->with($otp, $email)->andReturnUsing(function ($otpArg, $emailArg) use ($otp, $email) {
    //         return $otpArg === $otp && $emailArg === $email;
    //     });
    //     Otp::shouldReceive('forget')->with($email)->andReturnTrue();
    //     $this->postJson(route('otp.email'), [
    //         'email' => $email,
    //     ]);
    //     $response = $this->postJson(route('otp-verify.email'), [
    //         'email' => $email,
    //         'otp' => $otp,
    //     ]);
    //     $response->assertOk();
    //     $response->assertJsonStructure([
    //         'user' => [
    //             'email',
    //         ],
    //     ]);
    // }

    public function test_to_get_login_token_user_needs_to_verify_email(): void
    {
        $email = $this->faker()->email();
        $otp = (string) $this->faker()->randomNumber(config('otp.digits'));
        Otp::shouldReceive('make')->with($email)->andReturn($otp);
        Otp::shouldReceive('check')->with($otp, $email)->andReturnUsing(function ($otpArg, $emailArg) use ($otp, $email) {
            return $otpArg === $otp && $emailArg === $email;
        });
        Otp::shouldReceive('forget')->with($email)->andReturnTrue();
        $this->postJson(route('otp.email'), [
            'email' => $email,
        ]);
        $response = $this->postJson(route('otp-login.email'), [
            'email' => $email,
            'otp' => $otp,
        ]);
        $response->assertOk();
        $response->assertJsonPath('token', fn (string $token) => $token !== '');
        $response->assertJsonStructure([
            'user' => [
                'email',
            ],
        ]);
        $this->assertDatabaseHas(User::getModel()->getTable(), [
            'email' => $email,
        ]);
    }
}
