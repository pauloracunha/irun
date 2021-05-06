<?php


namespace Database\Factories;


use App\Models\Competitor;
use App\Models\Race;
use App\Models\Runner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Class RaceFactory
 * @package Database\Factories
 */
class CompetitorFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Competitor::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        $race = Race::factory()->create();
        $runner = Runner::factory()->create();
        return [
            'race_id' => $race->id,
            'runner_id' => $runner->id,
            'started_in' => $this->faker->dateTimeInInterval('-1 hour', '1 hour')->format('Y-m-d H:i:s'),
            'ended_in' => $this->faker->dateTimeInInterval('+1 hour', '1 hour')->format('Y-m-d H:i:s')
        ];
    }
}