<?php


namespace Database\Factories;


use App\Helpers\Generator;
use App\Models\Runner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Class RunnerFactory
 * @package Database\Factories
 */
class RunnerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Runner::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'cpf' => Generator::cpf(),
            'birthdate' => $this->faker->date('Y-m-d', '2003-01-01')
        ];
    }
}