<?php

use App\Models\Race;

class RaceTest extends TestCase
{
    /**
     * Check if routes is configured for races
     *
     * @test
     * @return void
     */
    public function check_if_routes_is_configured()
    {
        $this->assertEquals(url().'/v1/corridas', route('v1.race.index'));
        $this->assertEquals(url().'/v1/corridas', route('v1.race.create'));
        $this->assertEquals(url().'/v1/corridas/1', route('v1.race.update', ['id' => '1']));
        $this->assertEquals(url().'/v1/corridas/1', route('v1.race.delete', ['id' => '1']));
    }

    /**
     * Check if race columns is correct
     *
     * @test
     * @return void
     */
    public function check_if_races_column_is_correct()
    {
        $race = new Race;
        $expected = [
            'id',
            'category',
            'date'
        ];

        $this->assertEquals($expected, $race->getFillable());
    }

    /**
     * Check register race
     *
     * @test
     * @return void
     */
    public function check_race_register()
    {
        $race = Race::factory()->make();
        $post = $this->post(route('v1.race.create'), $race->toArray());
        $post->assertResponseStatus(201);
        $post->receiveJson();
        $post->seeJsonContains(['result' => 'success']);
        $id = json_decode($post->response->content())->content->id;
        Race::find($id)->delete();
    }

    /**
     * Check update race
     *
     * @test
     * @return void
     */
    public function check_race_update()
    {
        $race = Race::factory()->create([
            'category' => '10'
        ]);
        $race->category = '42';
        $post = $this->patch(route('v1.race.update', ['id' => $race->id]), $race->toArray());
        $post->assertResponseStatus(200);
        $post->receiveJson();
        $post->seeJsonContains(['result' => 'success']);

        $id = json_decode($post->response->content())->content->id;
        $raceUpdated = Race::find($id);
        $this->assertEquals($race->toArray(), $raceUpdated->toArray());
        $raceUpdated->delete();
    }

    /**
     * Check delete race
     *
     * @test
     * @return void
     */
    public function check_race_delete()
    {
        $race = Race::factory()->create();
        $post = $this->delete(route('v1.race.delete', ['id' => $race->id]));
        $post->assertResponseStatus(200);
        $post->receiveJson();
        $post->seeJsonContains(['result' => 'success']);

        $raceDeleted = Race::find($race->id);
        $this->assertNull($raceDeleted);
    }
}
