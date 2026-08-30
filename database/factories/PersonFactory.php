<?php

namespace Database\Factories;

use App\Modules\People\Domain\Models\Person;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Person> */
class PersonFactory extends Factory
{
    protected $model = Person::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'document_type' => 'CC',
            'document_number' => fake()->unique()->numerify('##########'),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->numerify('3#########'),
            'is_active' => true,
        ];
    }
}
