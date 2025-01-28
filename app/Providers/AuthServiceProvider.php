<?php

namespace App\Providers;

use App\Policies\LivestreamPolicy;
use App\Policies\OrderPolicy;
use App\Policies\VendorPolicy;
use App\Policies\VendorProductPolicy;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Gate::define(\App\Constants\GateNames::CREATE_VENDOR->value, [VendorPolicy::class, 'create']);
        Gate::define(\App\Constants\GateNames::UPDATE_VENDOR->value, [VendorPolicy::class, 'update']);
        Gate::define(\App\Constants\GateNames::FOLLOW_VENDOR->value, [VendorPolicy::class, 'follow']);
        Gate::define(\App\Constants\GateNames::UNFOLLOW_VENDOR->value, [VendorPolicy::class, 'unfollow']);
        Gate::define(\App\Constants\GateNames::CREATE_VENDOR_PRODUCT->value, [VendorProductPolicy::class, 'create']);
        Gate::define(\App\Constants\GateNames::UPDATE_VENDOR_PRODUCT->value, [VendorProductPolicy::class, 'update']);
        Gate::define(\App\Constants\GateNames::CREATE_LIVESTREAM->value, [LivestreamPolicy::class, 'create']);
        Gate::define(\App\Constants\GateNames::UPDATE_LIVESTREAM->value, [LivestreamPolicy::class, 'update']);
        Gate::define(\App\Constants\GateNames::GET_LIVESTREAM_PUBLISHER_TOKEN->value, [LivestreamPolicy::class, 'getPublisherToken']);
        Gate::define(\App\Constants\GateNames::GET_LIVESTREAM_SUBSCRIBER_TOKEN->value, [LivestreamPolicy::class, 'getSubscriberToken']);
        Gate::define(\App\Constants\GateNames::ADD_LIVESTREAM_PRODUCTS->value, [LivestreamPolicy::class, 'addProducts']);
        Gate::define(\App\Constants\GateNames::REMOVE_LIVESTREAM_PRODUCTS->value, [LivestreamPolicy::class, 'addProducts']);
        Gate::define(\App\Constants\GateNames::MAKE_ORDER_PICKUP_REQUEST->value, [OrderPolicy::class, 'makePickupRequestToPathao']);

        // VerifyEmail::toMailUsing(function (MustVerifyEmail $notifiable, string $url) {
        //     $otpCode = otp()->make($notifiable->getEmailForVerification());

        //     return (new MailMessage)
        //         ->subject('Verify Email Address')
        //         ->line('You verification code is: '.$otpCode);
        // });
    }
}
