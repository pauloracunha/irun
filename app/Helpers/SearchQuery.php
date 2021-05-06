<?php


namespace App\Helpers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use stdClass;

/**
 * Class SearchQuery
 * @package App\Helpers
 */
class SearchQuery
{
    /* @var $request Request */
    private $request;

    /* @var $table Model */
    protected $table;

    /* @var $paramsToSearch array */
    protected $paramsToSearch;

    /* @var $_fields array|null */
    protected $_fields;

    /* @var $_where string */
    protected $_where;

    /* @var $_relations array|null */
    protected $_relations;

    /* @var $isAllowWhere bool */
    protected $isAllowWhere;

    /* @var $fail bool */
    protected $fail = false;

    /* @var $message string|null */
    protected $message;

    /** @var $paging \stdClass */
    private $paging;

    /**
     * SearchQuery constructor.
     * @param Model $table
     * @param bool $isAllowWhere
     */
    public function __construct(Model $table, bool $isAllowWhere = true)
    {
        $this->request = request();
        $this->table = $table;
        $this->isAllowWhere = $isAllowWhere;
        $this->initFields();
        $this->initPaginate();
    }

    /**
     * @param string $paramName
     * @param null $validation
     * @param string|array|null $column
     * @param string|array|null $value
     * @return $this|null
     * @throws ValidationException
     */
    public function setParam(string $paramName, $validation = null, $column = null, $value = null): ?SearchQuery
    {
        if (empty($paramName)) {
            $this->fail = true;
            $this->message = 'paramName in APIQuery::setColumn() is empty';
            return null;
        }
        if (!$this->request->has($paramName) && empty($value)) {
            if ((is_array($validation) && in_array('required', $validation)) ||
                (is_string($validation) && strpos($validation, 'required') !== false)) {
                $this->fail = true;
                $this->message = "{$paramName} is required!";
            }
            return null;
        }
        if (!empty($validation)) {
            $values = explode(',', $this->request->input($paramName));
            $validated = [];
            foreach ($values as $item) {
                $validator = Validator::make([$paramName => $item], [$paramName => $validation]);
                if ($validator->fails()) {
                    $this->fail = true;
                    $this->message = $validator->errors()->first();
                    return null;
                }
                $validated[] = $validator->validated()[$paramName];
            }
            if (count($validated) == 1) {
                $validated = null;
            }
        }
        $value = !empty($value) ? explode(',', $value) : ($validated ?? explode(',', $this->request->input($paramName)));
        if (is_array($value)&&count($value)>1) {
            $operator = 'IN';
        } else {
            $operator = '=';
            $value = $value[0];
        }
        if (is_array($validation) && in_array('dateBetween', $validation)) {
            $operator = "BETWEEN";
        }
        $this->paramsToSearch[$paramName] = [
            'column' => !empty($column) ? $column : $paramName,
            'operator' => $operator,
            'value' => $value,
        ];
        return $this;
    }

    /**
     * @param array $params
     * @return $this|null
     * @throws ValidationException
     */
    public function setParams(array $params): ?SearchQuery
    {
        foreach ($params as $key => $item) {
            if (empty($key) || is_int($key)) {
                $this->fail = true;
                $this->message = 'array key undefined';
                return null;
            }
            $this->setParam(
                $key,
                $item['validation'] ?? null,
                $item['column'] ?? null,
                $item['value'] ?? null
            );
        }
        return $this;
    }

    /**
     * @return array|null
     */
    public function getParams(): ?array
    {
        return $this->paramsToSearch ?? null;
    }

    /**
     * @param string $relation
     * @return $this
     */
    public function setRelation(string $relation): SearchQuery
    {
        if ($this->request->has('_include')
            && in_array($relation, explode(',', $this->request->input('_include')))) {
            $this->_relations[] = $relation;
        }
        return $this;
    }

    /**
     * @param array $relations
     * @return $this
     */
    public function setRelations(array $relations): SearchQuery
    {
        foreach ($relations as $relation) {
            $this->setRelation($relation);
        }
        return $this;
    }

    /**
     * @return array|null
     */
    public function execute(): ?array
    {
        $terms = $this->paramsToSearch;
        /* @var $table Builder */
        $table = $this->table->select(!empty($this->_fields) ? $this->_fields : '*');
        if (!empty($this->_relations)) {
            $table = $table->with($this->_relations);
        }
        if (!empty($terms)) {
            $table = $this->where($terms, $table);
        }
        return $this->paginate($table);
    }

    /**
     * @param array $params
     * @param null $table
     * @return mixed|null
     */
    protected function where(array $params, $table = null)
    {
        if (!$table) {
            $table = $this->table;
        }
        foreach ($params as $param) {
            if (is_array($param['column']) && isset($param['column']['table'])) {
                $table = $table->whereExists(function ($query) use ($param) {
                    $query->select($param['column']['localColumn'])
                        ->from($param['column']['table'])
                        ->whereColumn($param['column']['whereColumn'])
                        ->where($param['column']['where'])
                        ->limit(1);
                });
                continue;
            }
            switch ($param['operator']) {
                case in_array($param['operator'], ['=', '>', '>=', '<>', 'like']):
                    $table = $table->where($param['column'], $param['operator'], $param['value']);
                    break;
                case 'IN':
                    $table = $table->whereIn($param['column'], $param['value']);
                    break;
                case 'BETWEEN':
                    $table = $table->whereBetween($param['column'], explode(' AND ', $param['value']));
                    break;
            }
        }
        $this->table = $table;
        return $table;
    }

    /**
     * @param Builder|Model|null $table
     * @return array
     */
    protected function paginate($table = null): array
    {
        if(!$table){
            $table = $this->table;
        }
        $perPage = 15;
        if(isset($this->paging->per_page)){
            $perPage = $this->paging->per_page;
        }
        $paginate = $table->paginate($perPage);
        $paginate->appends($this->request->query());
        $paginate = $paginate->toArray();
        $response = $paginate['data'];
        unset($paginate['data']);
        $this->paging = (object)$paginate;
        return $response;
    }

    /**
     *
     */
    protected function initFields()
    {
        $fields = $this->table->getFillable();
        if ($this->request->has('_fields') && !empty($this->request->input('_fields'))) {
            $this->_fields = explode(',', $this->request->input('_fields'));
            foreach ($this->_fields as $key => $field) {
                if (!in_array($field, $fields)) {
                    unset($this->_fields[$key]);
                }
            }
        } else {
            $this->_fields = $fields;
        }
    }

    /**
     * @param string $field
     * @param string $definition
     * @return $this
     */
    public function addCustomField(string $field, string $definition = ''): SearchQuery
    {
        $field = !empty($definition) ? "{$definition} AS {$field}" : $field;
        $this->_fields = array_merge($this->_fields, [
            DB::raw($field)
        ]);
        return $this;
    }

    /**
     *
     */
    protected function initPaginate()
    {
        $this->paging = new stdClass();
        $this->paging->per_page = 100;
        if ($this->request->has('_per_page')
            && !empty($this->request->input('_per_page'))
            && $this->request->input('_per_page') <= 100
        ) {
            $this->paging->per_page = $this->request->input('_per_page');
        }
    }

    /**
     * @param object $db
     * @return SearchQuery|null
     */
    public function setConnection(object $db): ?SearchQuery
    {
        $config = app('config');
        $config->set('database.connections.external_connection', [
            'driver' => 'mysql',
            'url' => null,
            'host' => $db->dbHost,
            'port' => $db->dbPort,
            'database' => $db->dbName,
            'username' => $db->dbUser,
            'password' => $db->dbPass,
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
        ]);
        $this->table = $this->table->setConnection('external_connection');
        return $this;
    }

    /**
     * @param Model $table
     * @param string|null $foreignKey
     * @param string|null $localColumn
     * @param array $where
     * @return array
     */
    public function externalColumn(Model $table, ?string $foreignKey = null, ?string $localColumn = null, array $where = []): array
    {
        $table = $table->getTable();
        $localModel = strtolower(class_basename($this->table));
        $foreignKey = $foreignKey ? $foreignKey : "{$localModel}_id";
        $localColumn = $localColumn ? $localColumn : 'id';
        return [
            'localColumn' => $localColumn,
            'table' => $table,
            'whereColumn' => [
                ["{$table}.{$foreignKey}", '=', "{$this->table->getTable()}.{$localColumn}"]
            ],
            'where' => $where
        ];
    }

    /**
     * @return Model
     */
    public function getTable(): Model
    {
        return $this->table;
    }

    /**
     * @return \stdClass
     */
    public function getPaging(): \stdClass
    {
        return $this->paging;
    }

    /**
     * @return bool
     */
    public function fail(): bool
    {
        return $this->fail;
    }

    /**
     * @return string|null
     */
    public function message(): ?string
    {
        return $this->message;
    }
}