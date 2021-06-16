<?php

namespace LaravelPublishable\Tests;

use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use LaravelPublishable\Expirable;
use LaravelPublishable\Publishable;

class AllTraitsModel extends Model
{
    use HasFactory;
    use HasTimestamps;
    use Publishable;
    use Expirable;

    protected $table = 'all_traits_models';
    protected $guarded = [];

}