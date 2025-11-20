<?php

namespace Database\Factories;

use App\Models\Coupon;
use App\Models\CouponValidation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CouponValidation>
 */
class CouponValidationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'coupon_id' => Coupon::factory(),
            'validated_by' => User::factory(),
            'validated_at' => now(),
            'action' => $this->faker->randomElement(['used', 'reversed']),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    /**
     * Indicate that the validation is for using a coupon.
     */
    public function used(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'used',
            'notes' => null,
        ]);
    }

    /**
     * Indicate that the validation is for reversing a coupon.
     */
    public function reversed(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'reversed',
            'notes' => $this->faker->sentence(),
        ]);
    }
}
