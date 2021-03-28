<?php

namespace LaravelPublishable\Tests\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use LaravelPublishable\Tests\RegularModel;

class RegularModelFactory extends Factory
{
    protected $model = RegularModel::class;

    public function definition()
    {
        return [];
    }
}