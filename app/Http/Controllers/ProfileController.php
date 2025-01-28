<?php

namespace App\Http\Controllers;

use App\Constants\FeedbackMessage;
use App\Data\Resources\UserData;
use App\Data\UpdateEmailData;
use App\Data\UpdatePhoneData;
use App\Data\UpdateProfileData;
use App\Models\User;
use Firebase;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ProfileController extends Controller
{
    public function show(): UserData
    {
        $user = auth()->user();

        return UserData::from($user);
    }

    public function updateEmail(UpdateEmailData $data): UserData
    {
        /** @var User */
        $user = auth()->user();
        $otpCode = $data->otp;
        $email = $data->email;

        $isMatch = otp()->check($otpCode, $email);

        throw_if(! $isMatch, BadRequestHttpException::class, FeedbackMessage::OTP_MISMATCH->value);

        $user->update([
            'email' => $data->email,
        ]);

        otp()->forget($email);

        return UserData::from($user);
    }

    public function updatePhone(UpdatePhoneData $data): UserData
    {
        /** @var User */
        $user = auth()->user();

        $idToken = $data->idToken;

        $verifiedIdToken = Firebase::auth()->verifyIdToken($idToken);

        $uid = $verifiedIdToken->claims()->get('sub');
        $firebaseUser = Firebase::auth()->getUser($uid);

        $user->update([
            'phone_number' => $firebaseUser->phoneNumber,
        ]);

        Firebase::auth()->deleteUser($firebaseUser->uid);

        return UserData::from($user);
    }

    public function update(UpdateProfileData $data): UserData
    {
        /** @var User */
        $user = auth()->user();

        $user->update($data->toArray());

        if (is_string($data->profilePicture)) {
            $user->addAllMediaFromTokens($data->profilePicture, 'avatar');
        }

        return UserData::from($user);
    }
}
