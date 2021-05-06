<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Competitor
 * @package App\Models
 */
class Competitor extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'race_id', 'runner_id', 'started_in', 'ended_in'
    ];

    /**
     * @return BelongsTo
     */
    public function race(): BelongsTo
    {
        return $this->belongsTo(Race::class);
    }

    /**
     * @return BelongsTo
     */
    public function runner(): BelongsTo
    {
        return $this->belongsTo(Runner::class);
    }
}