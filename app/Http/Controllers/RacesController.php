<?php


namespace App\Http\Controllers;


use App\Models\Race;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * Class RacesController
 * @package App\Http\Controllers
 */
class RacesController extends Controller
{
    /**
     * @var string
     */
    protected $entity = 'race';

    /**
     * @param string|null $id
     * @return JsonResponse
     */
    public function index(string $id = null): JsonResponse
    {
        try {
            if ($id) {
                $races = DB::table('races')->find($id)->paginate(15);
            } else {
                $races = DB::table('races')->paginate(15);
            }
            $pagination = $races->toArray();
            $races = $races->items();
            unset($pagination['data']);
            return response()->json([
                'entity' => $this->entity,
                'action' => 'search',
                'result' => 'success',
                'pagination' => $pagination,
                'content' => $races
            ]);
        } catch (\Exception $e) {
            Log::error(strtoupper($this->entity)." INDEX: {$e->getMessage()}");
            return response()->json([
                'entity' => $this->entity,
                'action' => 'search',
                'result' => 'error',
                'content' => 'Não foi possível fazer esta consulta. Tente novamente mais tarde!'
            ]);
        }
    }

    /**
     * @return JsonResponse
     * @throws \Throwable
     */
    public function create(): JsonResponse
    {
        try {
            $this->validate(request(),
                [
                    'category' => ['required', 'in:3,5,10,21,42'],
                    'date' => ['required', 'date']
                ],
                [
                    'category.in' => 'Categorias permitidas em km: 3, 5, 10, 21, 42'
                ]
            );
            $race = new Race();
            $race->category = request('category');
            $race->date = request('date');
            $race->saveOrFail();

            return response()->json([
                'entity' => $this->entity,
                'action' => 'create',
                'result' => 'success',
                'content' => $race
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
            ]);
        }
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    public function update(string $id): JsonResponse
    {
        try {
            $this->validate(request(),
                [
                    'category' => ['in:3,5,10,21,42'],
                    'date' => ['date']
                ],
                [
                    'category.in' => 'Categorias permitidas em km: 3, 5, 10, 21, 42'
                ]
            );
            $runner = (new Race())->find($id);
            $runner->update(request()->only(['category', 'date']));

            return response()->json([
                'entity' => $this->entity,
                'action' => 'update',
                'result' => 'success',
                'content' => $runner
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
            ]);
        }
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    public function delete(string $id): JsonResponse
    {
        try {
            Race::find($id)->delete();

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
                'content' => 'Não foi possível excluir os dados desta corrida. Tente novamente mais tarde!'
            ]);
        }
    }
}