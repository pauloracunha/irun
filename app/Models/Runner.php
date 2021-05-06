<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Runner
 * @package App\Models
 */
class Runner extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'cpf', 'birthdate'
    ];

    /**
     * @var string[]
     */
    protected $appends = ['age'];

    /**
     * @return int
     * @throws \Exception
     */
    public function getAgeAttribute()
    {
        return (new \DateTime($this->birthdate))->diff(new \DateTime())->y;
    }

    /**
     * @return HasMany
     */
    public function races(): HasMany
    {
        return $this->hasMany(Competitor::class)->with('race');
    }
}