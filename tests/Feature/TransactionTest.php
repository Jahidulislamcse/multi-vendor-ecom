<?php

namespace Tests\Feature;

use App\Constants\SupportedPaymentMethods;
use Database\Seeders\ApplicationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Lunar\DataTypes\Price;
use Lunar\Models\Order;
use Lunar\Models\Transaction;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ApplicationSeeder::class);
    }

    public function test_transaction_creates(): void
    {
        $order = Order::factory()->create();

        $transaction = Transaction::create([
            'type' => 'intent',
            'success' => false,
            'status' => 'pending',
            'amount' => $order->total,
            'order_id' => $order->getKey(),
            'reference' => $order->reference,
            'driver' => SupportedPaymentMethods::SSLCOMMERZ->value,
            'card_type' => '',
        ]);

        /** @var Price */
        $orderTotal = $order->total;
        $this->assertEquals($transaction->amount->decimal(), $orderTotal->decimal());
    }
}
