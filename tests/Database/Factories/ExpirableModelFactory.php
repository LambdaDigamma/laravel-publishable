<?php

namespace LaravelPublishable\Tests\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use LaravelPublishable\Tests\ExpirableModel;

class ExpirableModelFactory extends Factory
{
    protected $model = ExpirableModel::class;

    public function expired()
    {
        return $this->state(function (array $attributes) {
            return [
                'expired_at' => now(),
            ];
        });
    }

    public function definition()
    {
        return [];
    }
}