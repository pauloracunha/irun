<?php


namespace App\Http\Controllers;


use App\Helpers\SearchQuery;
use App\Models\Runner;
use App\Rules\Cpf;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * Class RunnersController
 * @package App\Http\Controllers
 */
class RunnersController extends Controller
{
    /**
     * @var string
     */
    protected $entity = 'runner';

    /**
     * @var false|string
     */
    protected $birthDateMax;

    /**
     * RunnersController constructor.
     */
    public function __construct()
    {
        $this->birthDateMax = date("Y-m-d", strtotime("-18 years"));
    }

    /**
     * @param string|null $id
     * @return JsonResponse
     */
    public function index(string $id = null): JsonResponse
    {
        try {
            $runnersTable = new SearchQuery(new Runner);
            $runnersTable->setParams([
                'id' => [
                    'validation' => ['integer'],
                    'value' => $id
                ],
                'cpf' => [
                    'validation' => [new Cpf],
                ],
                'birthdate' => [
                    'validation' => ['date'],
                ]
            ]);
            $runnersTable->setRelations(['races']);
            $runners = $runnersTable->execute();
            return response()->json([
                'entity' => $this->entity,
                'action' => 'search',
                'result' => 'success',
                'pagination' => $runnersTable->getPaging(),
                'content' => $runners
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
                    'name' => ['required', 'string'],
                    'cpf' => ['required', new Cpf, 'unique:runners,cpf'],
                    'birthdate' => ['required', 'date', "before:{$this->birthDateMax}"]
                ],
                [
                    'birthdate.before' => 'Menores de idade não são permitidos!',
                    'cpf.unique' => "O CPF já existe no ".env('APP_NAME')."!"
                ]
            );
            $runner = new Runner();
            $runner->name = request('name');
            $runner->cpf = request('cpf');
            $runner->birthdate = request('birthdate');
            $runner->saveOrFail();

            return response()->json([
                'entity' => $this->entity,
                'action' => 'create',
                'result' => 'success',
                'content' => $runner
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
     * @param string $id
     * @return JsonResponse
     */
    public function update(string $id): JsonResponse
    {
        try {
            $this->validate(request(),
                [
                    'name' => ['string'],
                    'cpf' => [new Cpf],
                    'birthdate' => ['date', "before:{$this->birthDateMax}"]
                ],
                ['birthdate.before' => 'Menores de idade não são permitidos!']
            );
            $runner = (new Runner())->find($id);
            $runner->update(request()->only(['name', 'cpf', 'birthdate']));

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
            ], 400);
        }
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    public function delete(string $id): JsonResponse
    {
        try {
            Runner::find($id)->delete();

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
                'content' => 'Não foi possível excluir os dados deste corredor. Tente novamente mais tarde!'
            ], 400);
        }
    }
}