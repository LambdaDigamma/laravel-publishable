<?php

namespace LaravelPublishable\Tests;

use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use LaravelPublishable\Expirable;

class ExpirableModel extends Model
{
    use HasFactory;
    use HasTimestamps;
    use Expirable;

    protected $table = 'expirable_models';
    protected $guarded = [];

}