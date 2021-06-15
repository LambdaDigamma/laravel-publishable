<?php

namespace LaravelPublishable\Tests;

use Illuminate\Support\Carbon;
use Spatie\TestTime\TestTime;

class ExpirableTest extends TestCase
{
    /** @test */
    public function a_model_can_be_expired()
    {
        $model = ExpirableModel::factory()->create();

        $this->assertNull($model->fresh()->expired_at);

        $model->expire();

        $this->assertNotNull($model->fresh()->expired_at);
    }

    /** @test */
    public function a_model_can_be_expired_in_the_future()
    {
        TestTime::freeze('Y-m-d H:i:s', '2021-03-31 20:30:00');

        $model = ExpirableModel::factory()->create();
        
        $this->assertNull($model->fresh()->expired_at);

        $model->expire(Carbon::parse('2021-03-31 21:00:00'));

        $this->assertEquals('2021-03-31 21:00:00', $model->fresh()->expired_at->toDateTimeString());
    }

    /** @test */
    public function a_model_can_be_expired_in_the_future_and_only_be_accessed_before_that()
    {
        TestTime::freeze('Y-m-d H:i:s', '2021-03-31 20:30:00');

        $model = ExpirableModel::factory()->create();
        
        $this->assertNull($model->fresh()->expired_at);

        $model->expireAt(Carbon::parse('2021-03-31 21:00:00'));
        $this->assertEquals('2021-03-31 21:00:00', $model->fresh()->expired_at->toDateTimeString());

        $this->assertCount(1, ExpirableModel::all());

        TestTime::freeze('Y-m-d H:i:s', '2021-03-31 21:10:00');

        $this->assertCount(0, ExpirableModel::all());
    }

    /** @test */
    public function a_model_can_be_unexpired()
    {
        $model = ExpirableModel::factory()->expired()->create();

        $this->assertNotNull($model->fresh()->expired_at);

        $model->unexpire();

        $this->assertNull($model->fresh()->expired_at);
    }

    /** @test */
    public function a_model_cannot_be_queried_normally_when_not_expired()
    {
        ExpirableModel::factory()->expired()->create();
        ExpirableModel::factory()->create();

        $this->assertDatabaseCount('expirable_models', 2);
        $this->assertCount(1, ExpirableModel::all());
    }

    /** @test */
    public function all_models_can_be_found_with_the_withExpired_scope()
    {
        ExpirableModel::factory()->expired()->create();
        ExpirableModel::factory()->create();

        $this->assertCount(1, ExpirableModel::all());
        $this->assertCount(2, ExpirableModel::withExpired()->get());
    }

    /** @test */
    public function only_expired_models_can_be_found_with_the_onlyExpired_scope()
    {
        ExpirableModel::factory()->expired()->create();
        ExpirableModel::factory()->create();

        $this->assertCount(1, ExpirableModel::onlyExpired()->get());
    }

    /** @test */
    public function models_without_the_expirable_trait_are_not_scoped()
    {
        RegularModel::factory()->create();

        $this->assertCount(1, RegularModel::all());
    }
}