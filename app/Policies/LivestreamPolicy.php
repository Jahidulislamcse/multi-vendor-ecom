<?php

namespace App\Policies;

use App\Constants\LivestreamStatuses;
use App\Models\Livestream;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Auth\Access\Response;

class LivestreamPolicy
{
    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, ?Vendor $vendor): Response|bool
    {
        if (is_null($vendor) || $vendor->user()->isNot($user)) {
            return Response::denyAsNotFound();
        }

        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Livestream $livestream): Response|bool
    {
        $userVendor = $user->vendors->first();

        $canSee = $livestream->vendor()->is($userVendor) && $livestream->status !== LivestreamStatuses::FINISHED->value && is_null($livestream->ended_at);

        if (! $canSee) {
            return Response::denyAsNotFound();
        }

        return true;
    }

    public function getPublisherToken(User $user, Livestream $livestream): Response|bool
    {
        return $this->update($user, $livestream);
    }

    public function getSubscriberToken(?User $user, Livestream $livestream): Response|bool
    {
        $canSee = $livestream->status !== LivestreamStatuses::FINISHED->value && is_null($livestream->ended_at);

        if (! $canSee) {
            return Response::denyAsNotFound();
        }

        return true;
    }

    public function addProducts(User $user, Livestream $livestream): Response|bool
    {
        return $this->update($user, $livestream);
    }

    public function removeProducts(User $user, Livestream $livestream): Response|bool
    {
        return $this->update($user, $livestream);
    }
}
