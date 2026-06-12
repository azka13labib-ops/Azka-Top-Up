<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'key', 'value'
])]
class SiteSetting extends Model
{
    // No special casts, values can be parsed individually in the repository
}
