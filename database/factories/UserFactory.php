<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= bcrypt('password'),
            'remember_token' => \Illuminate\Support\Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * User ko 'admin' role assign karo — apna poll create/edit/delete kar sakta hai.
     */
    public function admin(): static
    {
        return $this->afterCreating(function (User $user) {
            if (!\Spatie\Permission\Models\Role::where('name', 'admin')->where('guard_name', 'web')->exists()) {
                \Spatie\Permission\Models\Role::create(['name' => 'admin', 'guard_name' => 'web']);
            }
            $user->assignRole('admin');
        });
    }

    /**
     * User ko 'super-admin' role assign karo — sab kuch access kar sakta hai.
     */
    public function superAdmin(): static
    {
        return $this->afterCreating(function (User $user) {
            if (!\Spatie\Permission\Models\Role::where('name', 'super_admin')->where('guard_name', 'web')->exists()) {
                \Spatie\Permission\Models\Role::create(['name' => 'super_admin', 'guard_name' => 'web']);
            }
            $user->assignRole('super_admin');
        });
    }
}