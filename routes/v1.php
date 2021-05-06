<?php
/** @var \Laravel\Lumen\Routing\Router $router */

$router->group(['prefix' => 'corredores', 'as' => 'runners'], function () use ($router) {
    $router->get('', ['as' => 'index', 'uses' => 'RunnersController@index']);
    $router->get('{id}', 'RunnersController@index');
    $router->post('', ['as' => 'create', 'uses' => 'RunnersController@create']);
    $router->patch('{id}', ['as' => 'update', 'uses' => 'RunnersController@update']);
    $router->delete('{id}', ['as' => 'delete', 'uses' => 'RunnersController@delete']);
});
$router->group(['prefix' => 'corridas', 'as' => 'race'], function () use ($router) {
    $router->get('', ['as' => 'index', 'uses' => 'RacesController@index']);
    $router->get('{id}', 'RacesController@index');
    $router->post('', ['as' => 'create', 'uses' => 'RacesController@create']);
    $router->patch('{id}', ['as' => 'update', 'uses' => 'RacesController@update']);
    $router->delete('{id}', ['as' => 'delete', 'uses' => 'RacesController@delete']);
    $router->group(['prefix' => '{race_id}/competidores', 'as' => 'runners'], function () use ($router) {
        $router->get('', ['as' => 'index', 'uses' => 'RaceRunnersController@index']);
        $router->get('{runnerId}', 'RaceRunnersController@index');
        $router->post('', ['as' => 'create', 'uses' => 'RaceRunnersController@create']);
        $router->patch('{id}', ['as' => 'update', 'uses' => 'RaceRunnersController@update']);
        $router->delete('{id}', ['as' => 'delete', 'uses' => 'RaceRunnersController@delete']);
    });
});
$router->group(['prefix' => 'classificacao', 'as' => 'placing'], function () use ($router) {
    $router->get('', ['as' => 'placing.index', 'uses' => 'PlacingController@index']);
    $router->get('/poridade', ['as' => 'placing.byage', 'uses' => 'PlacingController@byAge']);
});