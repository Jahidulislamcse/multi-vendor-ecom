<?php

namespace App\Http\Controllers;

use App\Data\Resources\CartData;
use App\Models\User;
use Lunar\Models\Cart;

class GetAuthCartController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(): CartData|array
    {
        /** @var User */
        $user = auth()->user();
        /** @var Cart|null */
        $userCart = Cart::whereUserId($user->getKey())->active()->first();
        if ($userCart) {
            $userCart->load(['lines' => ['purchasable' => ['product', 'prices']], 'user']);
            $userCart->calculate();

            return CartData::from($userCart);
        }

        return CartData::empty();
    }
}
