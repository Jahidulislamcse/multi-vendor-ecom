<?php

namespace App\Http\Controllers;

use App\Data\Dto\AddCartProductData;
use App\Data\Dto\RemoveCartProductData;
use App\Data\Dto\UpdateCartProductData;
use App\Data\Dto\UpdateCartProductItemData;
use App\Data\Resources\CartData;
use App\Models\Product;
use Illuminate\Support\Collection;
use Lunar\Models\Cart;
use Lunar\Models\Channel;
use Lunar\Models\Currency;

class CartController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(AddCartProductData $data): CartData
    {
        /** @var Cart */
        $cart = Cart::findOr($data->cartId, fn () => Cart::create([
            'user_id' => auth()->id(),
            'channel_id' => Channel::getDefault()->getKey(),
            'currency_id' => Currency::getDefault()->getKey(),
        ]));

        if (auth()->check() && $cart->user()->isNot(auth()->user())) {
            $cart->associate(auth()->user());
        }

        /** @var Collection<int, Product> */
        $products = Product::whereIn('id', data_get($data->products, '*.id'))->with(['variants'])->get();

        $cart->addLines($products->map(fn (Product $product, int $index) => [
            'purchasable' => $product->variants->first(),
            'quantity' => $data->products[$index]['quantity'],
        ]));

        $cart->load(['lines' => ['purchasable' => ['product', 'prices']], 'user']);
        $cart->calculate();

        return CartData::from($cart);
    }

    /**
     * Display the specified resource.
     */
    public function show(Cart $cart): CartData
    {
        $cart->load(['lines' => ['purchasable' => ['product', 'prices']], 'user']);
        $cart->calculate();

        return CartData::from($cart);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCartProductData $data, Cart $cart): CartData
    {
        /** @var Collection */
        $cartLines = $data
            ->cartlines
            ->toCollection()
            ->map(fn (UpdateCartProductItemData $item) => $item->toArray());

        $cart->updateLines($cartLines);
        $cart->load(['lines' => ['purchasable' => ['product', 'prices']], 'user']);
        $cart->calculate();

        return CartData::from($cart);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cart $cart, RemoveCartProductData $data): CartData
    {
        $cart->remove($data->cartlineId);
        $cart->load(['lines' => ['purchasable' => ['product', 'prices']], 'user']);
        $cart->calculate();

        return CartData::from($cart);
    }
}
