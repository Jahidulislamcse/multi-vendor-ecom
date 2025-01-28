<?php

namespace App\Providers;

use App\Constants\SupportedPaymentMethods;
use App\Modifiers\FleepnessBaseShippingModifier;
use App\Services\SslCommerzPaymentType;
use Arr;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Lunar\Facades\ModelManifest;
use Lunar\Facades\Payments;
use Lunar\Models\Product;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(\Lunar\Base\ShippingModifiers $shippingModifiers): void
    {
        Request::macro('cacheKey', function () {
            /** @var Request $this */
            $url = $this->url();
            $queryParams = $this->query();

            $queryParams = Arr::sortRecursive($queryParams);

            $queryString = http_build_query($queryParams);

            $fullUrl = "{$url}?{$queryString}";

            $rememberKey = sha1($fullUrl);

            return $rememberKey;
        });

        $models = collect([
            Product::class => \App\Models\Product::class,
        ]);

        ModelManifest::register($models);

        RateLimiter::for('login', function (Request $request) {
            return [
                Limit::perMinute(10)->by($request->input('email')),
            ];
        });

        $shippingModifiers->add(
            FleepnessBaseShippingModifier::class
        );

        Payments::extend(SupportedPaymentMethods::SSLCOMMERZ->value, function ($app) {
            return $app->make(SslCommerzPaymentType::class);
        });
    }
}
