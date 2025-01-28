<?php

namespace App\Http\Controllers;

use App\Constants\FeedbackMessage;
use App\Data\Dto\GetEmailOtpData;
use App\Data\Dto\VerifyEmailOtpData;
use App\Data\Dto\VerifyPhoneOtpData;
use App\Models\User;
use App\Notifications\OtpNotification;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Notification;

class AuthController extends Controller
{
    public function getEmailOtp(GetEmailOtpData $otpLoginData): JsonResponse
    {
        $email = $otpLoginData->email;

        $otpCode = otp()->make($email);

        Notification::route('mail', $email)
            ->notify(new OtpNotification($otpCode));

        return response()->json([
            'message' => FeedbackMessage::OTP_SENT->value,
        ]);
    }

    public function loginViaEmailOtp(VerifyEmailOtpData $otpVerifyData): JsonResponse
    {
        $otpCode = $otpVerifyData->otp;
        $email = $otpVerifyData->email;

        $isMatch = otp()->check($otpCode, $email);

        throw_if(! $isMatch, AuthenticationException::class);

        /** @var User|null */
        $user = User::whereEmail(
            $email
        )->first();

        if (is_null($user)) {
            /** @var User|null */
            $user = User::create([
                'email' => $email,
            ]);
        }

        $token = $user->createToken('')->plainTextToken;

        otp()->forget($email);

        $user->markEmailAsVerified();

        return response()->json([
            'message' => FeedbackMessage::LOGIN_SUCCESS->value,
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function verifyPhoneOtp(VerifyPhoneOtpData $otpVerifyData): JsonResponse
    {
        $idToken = $otpVerifyData->idToken;

        try {
            $verifiedIdToken = Firebase::auth()->verifyIdToken($idToken);

            $uid = $verifiedIdToken->claims()->get('sub');
            $firebaseUser = Firebase::auth()->getUser($uid);
            /** @var User|null */
            $user = User::wherePhoneNumber($firebaseUser->phoneNumber)->first();

            if (is_null($user)) {
                /** @var User */
                $user = User::create(
                    [
                        'email' => $firebaseUser->email,
                        'phone_number' => $firebaseUser->phoneNumber,
                    ]
                );

                if ($firebaseUser->photoUrl) {
                    $user->addMediaFromUrl($firebaseUser->photoUrl);
                }

                if ($firebaseUser->emailVerified) {
                    $user->markEmailAsVerified();
                }
            }

            $token = $user->createToken('')->plainTextToken;
            Firebase::auth()->deleteUser($firebaseUser->uid);

            return response()->json([
                'message' => FeedbackMessage::LOGIN_SUCCESS->value,
                'token' => $token,
                'user' => $user,
            ]);
        } catch (FailedToVerifyToken $th) {
            // dump($th);
            throw new AuthenticationException();
        }
    }
}
