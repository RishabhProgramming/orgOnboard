<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_id',
        'name',
        'domain',
        'contact_email',
        'status',
        'processed_at',
        'failed_reason',
    ];
}
