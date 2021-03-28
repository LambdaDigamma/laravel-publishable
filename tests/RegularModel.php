<?php

namespace LaravelPublishable\Tests;

use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegularModel extends Model
{
    use HasTimestamps;
    use HasFactory;

    protected $table = 'regular_models';
    protected $guarded = [];

}