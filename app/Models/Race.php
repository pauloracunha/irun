<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Race
 * @package App\Models
 */
class Race extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'category', 'date'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function competitors(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Competitor::class)->with('runner');
    }
}