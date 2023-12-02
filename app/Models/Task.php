<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'assigner_id',
        'assignee_id',
        'status',
        'start_date',
        'end_date',
        'delivery_date'
    ];

    public function assigner()
    {
        return $this->BelongsTo(User::class, 'assigner_id');
    }

    public function assignee()
    {
        return $this->BelongsTo(User::class, 'assignee_id');
    }
}
