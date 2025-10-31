<?php

namespace Database\Factories;

use App\Models\Talent;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TalentFactory extends Factory
{
    protected $model = Talent::class;

    public function definition()
    {
        $firstName = $this->faker->firstName;
        $lastName = $this->faker->lastName;
        $usernameBase = Str::slug($firstName . ' ' . $lastName);

        return [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'username' => $usernameBase . '-' . $this->faker->unique()->numberBetween(1, 9999),
            'email' => $this->faker->unique()->safeEmail,
            'synced_at' => $this->faker->optional()->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
