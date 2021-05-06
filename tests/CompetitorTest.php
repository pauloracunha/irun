<?php

use App\Models\Competitor;
use App\Models\Race;
use App\Models\Runner;

class CompetitorTest extends TestCase
{
    /**
     * Check if routes is configured for competitors
     *
     * @test
     * @return void
     */
    public function check_if_routes_is_configured()
    {
        $this->assertEquals(
            url().'/v1/corridas/1/competidores',
            route('v1.race.runners.index', ['race_id' => '1'])
        );
        $this->assertEquals(
            url().'/v1/corridas/1/competidores',
            route('v1.race.runners.create', ['race_id' => '1'])
        );
        $this->assertEquals(
            url().'/v1/corridas/1/competidores/2',
            route('v1.race.runners.update', ['race_id' => '1', 'id' => '2'])
        );
        $this->assertEquals(
            url().'/v1/corridas/1/competidores/2',
            route('v1.race.runners.delete', ['race_id' => '1', 'id' => '2'])
        );
    }

    /**
     * Check if competitor columns is correct
     *
     * @test
     * @return void
     */
    public function check_if_competitors_column_is_correct()
    {
        $competitor = new Competitor;
        $expected = [
            'race_id',
            'runner_id',
            'started_in',
            'ended_in'
        ];

        $this->assertEquals($expected, $competitor->getFillable());
    }

    /**
     * Check register competitor
     *
     * @test
     * @return void
     */
    public function check_competitor_register()
    {
        $competitor = Competitor::factory()->make();
        $post = $this->post(route('v1.race.runners.create', ['race_id' => $competitor->race_id]), $competitor->toArray());
        $post->assertResponseStatus(201);
        $post->receiveJson();
        $post->seeJsonContains(['result' => 'success']);
        $competitorCreated = json_decode($post->response->content())->content;
        Competitor::find($competitorCreated->id)->delete();
        Race::find($competitorCreated->race_id)->delete();
        Runner::find($competitorCreated->runner_id)->delete();
    }

    /**
     * Check update competitor
     *
     * @test
     * @return void
     */
    public function check_competitor_update()
    {
        $competitor = Competitor::factory()->create();
        $competitor->started_in = date('Y-m-d H:i:s');
        $post = $this->patch(
            route('v1.race.runners.update', ['race_id' => $competitor->race_id, 'id' => $competitor->id]),
            $competitor->toArray()
        );
        $post->assertResponseStatus(200);
        $post->receiveJson();
        $post->seeJsonContains(['result' => 'success']);

        $id = json_decode($post->response->content())->content->id;
        $competitorUpdated = Competitor::find($id);
        $this->assertEquals($competitor->toArray(), $competitorUpdated->toArray());
        $competitorUpdated->delete();
    }

    /**
     * Check delete Competitor
     *
     * @test
     * @return void
     */
    public function check_race_delete()
    {
        $competitor = Competitor::factory()->create();
        $post = $this->delete(
            route('v1.race.runners.delete', ['race_id' => $competitor->race_id, 'id' => $competitor->id])
        );
        $post->assertResponseStatus(200);
        $post->receiveJson();
        $post->seeJsonContains(['result' => 'success']);

        $competitorDeleted = Competitor::find($competitor->id);
        $this->assertNull($competitorDeleted);
    }
}
