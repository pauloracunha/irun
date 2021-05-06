<?php

use App\Models\Runner;

class RunnerTest extends TestCase
{
    /**
     * Check if routes is configured for Runners
     *
     * @test
     * @return void
     */
    public function check_if_routes_is_configured()
    {
        $this->assertEquals(url().'/v1/corredores', route('v1.runners.index'));
        $this->assertEquals(url().'/v1/corredores', route('v1.runners.create'));
        $this->assertEquals(url().'/v1/corredores/1', route('v1.runners.update', ['id' => '1']));
        $this->assertEquals(url().'/v1/corredores/1', route('v1.runners.delete', ['id' => '1']));
    }

    /**
     * Check if Runner columns is correct
     *
     * @test
     * @return void
     */
    public function check_if_runners_column_is_correct()
    {
        $runner = new Runner;
        $expected = [
            'name',
            'cpf',
            'birthdate'
        ];

        $this->assertEquals($expected, $runner->getFillable());
    }

    /**
     * Check register Runner
     *
     * @test
     * @return void
     */
    public function check_runner_register()
    {
        $runner = Runner::factory()->make();
        $post = $this->post(route('v1.runners.create'), $runner->toArray());
        $post->assertResponseStatus(201);
        $post->receiveJson();
        $post->seeJsonContains(['result' => 'success']);
        $id = json_decode($post->response->content())->content->id;
        Runner::find($id)->delete();
    }

    /**
     * Check update runner
     *
     * @test
     * @return void
     */
    public function check_runner_update()
    {
        $runner = Runner::factory()->create();
        $runner->name = "José Teste";
        $post = $this->patch(route('v1.runners.update', ['id' => $runner->id]), $runner->toArray());
        $post->assertResponseStatus(200);
        $post->receiveJson();
        $post->seeJsonContains(['result' => 'success']);

        $id = json_decode($post->response->content())->content->id;
        $runnerUpdated = Runner::find($id);
        $this->assertEquals($runner->toArray(), $runnerUpdated->toArray());
        $runnerUpdated->delete();
    }

    /**
     * Check delete runner
     *
     * @test
     * @return void
     */
    public function check_runner_delete()
    {
        $runner = Runner::factory()->create();
        $post = $this->delete(route('v1.runners.delete', ['id' => $runner->id]));
        $post->assertResponseStatus(200);
        $post->receiveJson();
        $post->seeJsonContains(['result' => 'success']);

        $runnerDeleted = Runner::find($runner->id);
        $this->assertNull($runnerDeleted);
    }
}
