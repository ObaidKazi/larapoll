<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory; 
class Vote extends Model
{
    use HasFactory;
    protected $fillable = ['poll_id', 'poll_option_id', 'user_id', 'ip_address'];

    public function poll()
    {
        return $this->belongsTo(Poll::class);
    }
    public function option()
    {
        return $this->belongsTo(PollOption::class, 'poll_option_id');
    }
}
