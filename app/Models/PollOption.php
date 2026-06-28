<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory; 
class PollOption extends Model
{
    use HasFactory;
    protected $fillable = ['poll_id', 'label', 'votes_count', 'sort_order'];

    public function poll()
    {
        return $this->belongsTo(Poll::class);
    }

    public function getPercentageAttribute(): float
    {
        $total = $this->poll->total_votes;
        return $total > 0 ? round(($this->votes_count / $total) * 100, 1) : 0;
    }
}
