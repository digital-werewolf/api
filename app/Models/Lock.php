<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lock extends Model
{
    use HasFactory;

    protected $fillable = [
        'player_id',
        'reason',
        'expired_at',
    ];

    protected $casts = [
        'expired_at' => 'datetime',
    ];

    public $timestamps = false;

    public function action()
    {
        return $this->belongsTo(LockedAction::class, 'action_id');
    }
}
