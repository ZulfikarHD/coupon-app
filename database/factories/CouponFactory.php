<?php

namespace Database\Factories;

use App\Models\Coupon;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Coupon>
 */
class CouponFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Generate code in ABC-1234-XYZ format
        $part1 = strtoupper(fake()->lexify('???'));
        $part2 = str_pad((string) fake()->numberBetween(0, 9999), 4, '0', STR_PAD_LEFT);
        $part3 = strtoupper(fake()->lexify('???'));
        $code = "{$part1}-{$part2}-{$part3}";

        $firstName = fake()->firstName();
        $lastName = fake()->lastName();

        return [
            'code' => $code,
            'type' => fake()->randomElement(['Gratis 1 Kopi', 'Diskon 20%', 'Buy 1 Get 1', 'Diskon 10%']),
            'description' => fake()->sentence(),
            'customer_name' => $firstName . ' ' . $lastName,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'customer_phone' => '628' . fake()->numerify('##########'),
            'customer_email' => fake()->optional()->safeEmail(),
            'customer_social_media' => fake()->optional()->userName(),
            'expires_at' => fake()->optional()->dateTimeBetween('now', '+30 days'),
            'status' => fake()->randomElement([Coupon::STATUS_ACTIVE, Coupon::STATUS_USED, Coupon::STATUS_EXPIRED]),
            'created_by' => User::factory(),
        ];
    }

    /**
     * Indicate that the coupon is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Coupon::STATUS_ACTIVE,
        ]);
    }

    /**
     * Indicate that the coupon is used.
     */
    public function used(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Coupon::STATUS_USED,
        ]);
    }

    /**
     * Indicate that the coupon is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Coupon::STATUS_EXPIRED,
        ]);
    }
}
