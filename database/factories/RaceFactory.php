<?php


namespace Database\Factories;


use App\Helpers\Generator;
use App\Models\Race;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Class RaceFactory
 * @package Database\Factories
 */
class RaceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Race::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'category' => $this->faker->randomElement(['3', '5', '10', '21', '42']),
            'date' => $this->faker->date('Y-m-d')
        ];
    }
}