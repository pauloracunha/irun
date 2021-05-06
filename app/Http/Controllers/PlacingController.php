<?php


namespace App\Http\Controllers;


use App\Models\Race;
use App\Helpers\SearchQuery;
use App\Rules\DateBetween;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

/**
 * Class PlacingController
 * @package App\Http\Controllers
 */
class PlacingController extends Controller
{
    /**
     * @var string
     */
    protected $entity = 'placing';

    /**
     * @var \int[][]
     */
    protected $ageRanges = [
        '18-25' => ['min' => 18, 'max' => 25],
        '25-35' => ['min' => 25, 'max' => 35],
        '35-45' => ['min' => 35, 'max' => 45],
        '45-55' => ['min' => 45, 'max' => 55],
        '55+' => ['min' => 55],
    ];

    /**
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $searchQuery = new SearchQuery(new Race());
            $searchQuery->setParams([
                'id' => [
                    'validation' => ['integer'],
                ],
                'category' => [
                    'validation' => ['in:3,5,10,21,42']
                ],
                'date' => [
                    'validation' => ['date']
                ],
                'date_between' => [
                    'validation' => [new DateBetween],
                    'column' => 'date'
                ]
            ])
                ->setRelations(['competitors']);

            if ($searchQuery->fail()) {
                return response()->json([
                    'entity' => $this->entity,
                    'action' => 'index',
                    'result' => 'failed',
                    'message' => $searchQuery->message()
                ], 400);
            }
            $races = $searchQuery->execute();
            foreach ($races as &$race) {
                if (isset($race['competitors'])) {
                    $this->orderByPosition($race['competitors']);
                }
            }
            return response()->json([
                'entity' => $this->entity,
                'action' => 'index',
                'result' => 'success',
                'pagination' => $searchQuery->getPaging(),
                'content' => $races
            ]);
        } catch (Exception $e) {
            return response()->json([
                'entity' => $this->entity,
                'action' => 'index',
                'result' => 'error',
                'content' => 'Houve um problema. Tente novamente mais tarde.'
            ]);
        }
    }

    /**
     * @return JsonResponse
     */
    public function byAge()
    {
        try {
            $this->validate(request(),
                ['_agerange' => ['regex:/^\d+\,?\d*$/']],
                ['_agerange.regex' => 'Informe _agerange no formato 18,30']
            );
            $searchQuery = new SearchQuery(new Race());
            $searchQuery->setParams([
                'id' => [
                    'validation' => ['integer'],
                ],
                'category' => [
                    'validation' => ['in:3,5,10,21,42']
                ],
                'date' => [
                    'validation' => ['date']
                ],
                'date_between' => [
                    'validation' => [new DateBetween],
                    'column' => 'date'
                ]
            ])
                ->setRelations(['competitors']);

            if ($searchQuery->fail()) {
                return response()->json([
                    'entity' => $this->entity,
                    'action' => 'byage',
                    'result' => 'failed',
                    'message' => $searchQuery->message()
                ], 400);
            }
            $races = $searchQuery->execute();
            foreach ($races as &$race) {
                if (isset($race['competitors'])) {
                    if (request()->has('_agerange')) {
                        $ageRange = [];
                        list($ageRange['min'], $ageRange['max']) = explode(',', request('_agerange'));
                        $this->groupByAge($race['competitors'], $ageRange);
                        $this->orderByPosition($race['competitors']);
                        $race['ageRange'] = ($ageRange['min'] ?? 0) .
                            (!empty($ageRange['max']) ? '-'.$ageRange['max'] : '+');
                    } else {
                        $this->groupByAge($race['competitors']);
                        foreach ($race['competitors'] as &$ragesAge) {
                            $this->orderByPosition($ragesAge);
                        }
                    }
                }
            }
            return response()->json([
                'entity' => $this->entity,
                'action' => 'byage',
                'result' => 'success',
                'pagination' => $searchQuery->getPaging(),
                'content' => $races
            ]);
        } catch (Exception $e) {
            $message = get_class($e) == ValidationException::class ?
                $e->errors() :
                'Houve um problema. Tente novamente mais tarde.';
            return response()->json([
                'entity' => $this->entity,
                'action' => 'byage',
                'result' => 'error',
                'content' => $message
            ]);
        }
    }

    /**
     * @param array $competitors
     */
    protected function orderByPosition(array &$competitors)
    {
        $cmp = function ($a, $b) {
            try {
                $aTime = (new \DateTime($a['started_in']))->diff(new \DateTime($a['ended_in']));
            } catch (Exception $e) {
                return 1;
            }
            try {
                $bTime = (new \DateTime($b['started_in']))->diff(new \DateTime($b['ended_in']));
            } catch (Exception $e) {
                return -1;
            }
            return $aTime<$bTime ? -1 : 1;
        };
        usort($competitors, $cmp);
        foreach ($competitors as $key=>&$competitor) {
            $competitor['position'] = $key+1;
        }
    }

    /**
     * @param array $competitors
     * @param array|null $ageRange
     */
    protected function groupByAge(array &$competitors, array $ageRange = null)
    {
        $groupedCompetitors = [];
        if ($ageRange) {
            $groupedCompetitors = array_filter($competitors, function ($competitor) use ($ageRange) {
                return ($ageRange['min'] ?? 0) <= $competitor['runner']['age']
                    && $competitor['runner']['age'] < ($ageRange['max'] ?? 999);
            });
        } else {
            foreach ($this->ageRanges as $name => $range) {
                $groupedCompetitors[$name] = array_filter($competitors, function ($competitor) use ($range) {
                    return ($range['min'] ?? 0) <= $competitor['runner']['age']
                        && $competitor['runner']['age'] < ($range['max'] ?? 999);
                });
            }
        }
        $competitors = $groupedCompetitors;
    }
}