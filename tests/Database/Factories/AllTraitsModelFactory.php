<?php

namespace LaravelPublishable\Tests\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use LaravelPublishable\Tests\AllTraitsModel;

class AllTraitsModelFactory extends Factory
{
    protected $model = AllTraitsModel::class;

    public function expired()
    {
        return $this->state(function (array $attributes) {
            return [
                'expired_at' => now(),
            ];
        });
    }

    public function published()
    {
        return $this->state(function (array $attributes) {
            return [
                'published_at' => now(),
            ];
        });
    }

    public function definition()
    {
        return [];
    }
}