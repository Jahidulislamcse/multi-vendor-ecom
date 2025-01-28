<?php

namespace Database\Factories;

use App\Constants\LivestreamStatuses;
use App\Models\Livestream;
use App\Models\Vendor;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Livestream>
 */
class LivestreamFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->text(),
            'vendor_id' => Vendor::factory(),
            'scheduled_time' => $this->faker->optional()->dateTime(),
        ];
    }

    public function ended(?DateTimeInterface $endedAt = null)
    {
        return $this->state([
            'ended_at' => $endedAt ?? now(),
        ])->afterCreating(function (Livestream $livestream) {
            $livestream->setStatus(LivestreamStatuses::FINISHED->value);
        });
    }
}
