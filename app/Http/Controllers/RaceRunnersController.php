<?php


namespace App\Http\Controllers;


use App\Models\Competitor;
use App\Models\Race;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Class RaceRunnersController
 * @package App\Http\Controllers
 */
class RaceRunnersController extends Controller
{
    /**
     * @var string
     */
    protected $entity = 'competitor';

    /**
     * @param string $race_id
     * @param string|null $runnerId
     * @return JsonResponse
     */
    public function index(string $race_id, string $runnerId = null): JsonResponse
    {
        try {
            Validator::validate(
                array_merge(['race_id' => $race_id], request()->all()),
                [
                    'race_id' => ['required', 'exists:races,id'],
                    'runner_id' => ['exists:runners,id']
                ]
            );
            if ($runnerId) {
                $competitors = (new Competitor)->where([
                    'race_id' => $race_id,
                    'runner_id' => $runnerId
                ])->with(['race', 'runner'])->paginate(15);
            } else {
                $competitors = (new Competitor)->where([
                    'race_id' => $race_id
                ])->with(['race', 'runner'])->paginate(15);
            }
            $pagination = $competitors->toArray();
            $competitors = $competitors->items();
            unset($pagination['data']);
            return response()->json([
                'entity' => $this->entity,
                'action' => 'search',
                'result' => 'success',
                'pagination' => $pagination,
                'content' => $competitors
            ]);
        } catch (\Exception $e) {
            $message = get_class($e) == ValidationException::class ?
                $e->errors() :
                'Não foi possível fazer esta consulta. Tente novamente mais tarde!';
            Log::error(strtoupper($this->entity)." INDEX: {$e->getMessage()}");
            return response()->json([
                'entity' => $this->entity,
                'action' => 'search',
                'result' => 'error',
                'content' => $message
            ]);
        }
    }

    /**
     * @param string $race_id
     * @return JsonResponse
     * @throws \Throwable
     */
    public function create(string $race_id): JsonResponse
    {
        try {
            Validator::validate(
                array_merge(['race_id' => $race_id], request()->all()),
                [
                    'race_id' => ['required', 'exists:races,id'],
                    'runner_id' => ['required', 'exists:runners,id'],
                    'started_in' => ['date_format:Y-m-d H:i:s'],
                    'ended_in' => ['date_format:Y-m-d H:i:s', 'after:started_in'],
                ]
            );
            $race = Race::find($race_id);
            $racesInDate = Race::where('date', $race->date)->select(['id'])->pluck('id');
            $competitionInDate = Competitor::whereIn('race_id', $racesInDate)->where('runner_id', request('runner_id'));
            if ($competitionInDate->count() > 0) {
                return response()->json([
                    'entity' => $this->entity,
                    'action' => 'create',
                    'result' => 'error',
                    'content' => 'Este corredor já tem uma corrida marcada para esta data!'
                ], 400);
            }
            $competitor = new Competitor(request()->only([
                'runner_id', 'started_in', 'ended_in'
            ]));
            $competitor->race_id = $race_id;
            $competitor->saveOrFail();

            return response()->json([
                'entity' => $this->entity,
                'action' => 'create',
                'result' => 'success',
                'content' => $competitor
            ], 201);
        } catch (\Exception $e) {
            $message = get_class($e) == ValidationException::class ?
                $e->errors() :
                'Não foi possível salvar os dados. Tente novamente mais tarde!';
            Log::error(strtoupper($this->entity)." CREATE: {$e->getMessage()}");
            return response()->json([
                'entity' => $this->entity,
                'action' => 'create',
                'result' => 'error',
                'content' => $message
            ], 400);
        }
    }

    /**
     * @param string $race_id
     * @param string $id
     * @return JsonResponse
     */
    public function update(string $race_id, string $id): JsonResponse
    {
        try {
            Validator::validate(
                array_merge(['race_id' => $race_id], request()->all()),
                [
                    'race_id' => ['required', 'exists:races,id'],
                    'runner_id' => ['exists:runners,id'],
                    'started_in' => ['date_format:Y-m-d H:i:s'],
                    'ended_in' => ['date_format:Y-m-d H:i:s', 'after:started_in'],
                ]
            );
            $competitor = (new Competitor())->find($id);
            $competitor->update(request()->only(['runner_id', 'started_in', 'ended_in']));

            return response()->json([
                'entity' => $this->entity,
                'action' => 'update',
                'result' => 'success',
                'content' => $competitor
            ]);
        } catch (\Exception $e) {
            $message = get_class($e) == ValidationException::class ?
                $e->errors() :
                'Não foi possível atualizar os dados. Tente novamente mais tarde!';
            Log::error(strtoupper($this->entity)." UPDATE: {$e->getMessage()}");
            return response()->json([
                'entity' => $this->entity,
                'action' => 'update',
                'result' => 'error',
                'content' => $message
            ],400);
        }
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    public function delete(string $id): JsonResponse
    {
        try {
            Competitor::find($id)->delete();

            return response()->json([
                'entity' => $this->entity,
                'action' => 'delete',
                'result' => 'success'
            ]);
        } catch (\Exception $e) {
            Log::error(strtoupper($this->entity)." DELETE: {$e->getMessage()}");
            return response()->json([
                'entity' => $this->entity,
                'action' => 'delete',
                'result' => 'error',
                'content' => 'Não foi possível excluir os dados. Tente novamente mais tarde!'
            ],400);
        }
    }
}