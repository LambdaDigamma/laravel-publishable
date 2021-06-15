<?php

namespace LaravelPublishable;

use Exception;
use Illuminate\Support\Carbon;
use LaravelPublishable\Scopes\ExpirableScope;

/**
 * @method static static|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder withNotExpired()
 * @method static static|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder onlyNotExpired()
 * @method static static|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder withoutExpired()
 */
trait Expirable
{
    /**
     * Indicates if the model should use expires.
     *
     * @var bool
     */
    public $expires = true;

    /**
     * Boot the expiring trait for a model.
     *
     * @return void
     */
    public static function bootExpirable()
    {
        static::addGlobalScope(new ExpirableScope);
    }

    /**
     * Initialize the soft deleting trait for an instance.
     *
     * @return void
     */
    public function initializeExpirable()
    {
        if (! isset($this->casts[$this->getExpiredAtColumn()])) {
            $this->casts[$this->getExpiredAtColumn()] = 'datetime';
        }
    }

    /**
     * Set the model expired.
     * 
     * @param Carbon|null $expiredAt Specify the expiring date or set null to set expired now.
     *
     * @return bool|null
     *
     * @throws Exception
     */
    public function expire(?Carbon $expiredAt = null)
    {
        $this->mergeAttributesFromClassCasts();

        if (is_null($this->getKeyName())) {
            throw new Exception('No primary key defined on model.');
        }

        // If the model doesn't exist, there is nothing to set expired.
        if (! $this->exists) {
            return;
        }

        // If the expiring event doesn't return false, we'll continue
        // with the operation.
        if ($this->fireModelEvent('expiring') === false) {
            return false;
        }

        // Update the timestamps for each of the models owners. Breaking any caching
        // on the parents
        $this->touchOwners();

        $this->runExpire($expiredAt);

        // Fire expired event to allow hooking into the post-expired operations.
        $this->fireModelEvent('expired', false);

        // Return true as the expiration is presumably successful.
        return true;
    }

    /**
     * Expire the model at a give date.
     * 
     * @param Carbon $expireAt Specify the expiration date.
     *
     * @return bool|null
     *
     * @throws Exception
     */
    public function expireAt(Carbon $expireAt)
    {
        return $this->expire($expireAt);
    }

    /**
     * Schedule the model to be expired on a given date.
     * 
     * @param Carbon $expireAt Specify the expiration date.
     *
     * @return bool|null
     *
     * @throws Exception
     */
    public function expireFrom(Carbon $expiredAt)
    {
        return $this->expire($expiredAt);
    }

    /**
     * Perform the actual expiration query on this model instance.
     *
     * @return void
     */
    public function runExpire(?Carbon $expiredAt)
    {
        $query = $this->setKeysForSaveQuery($this->newModelQuery());

        $time = $this->freshTimestamp();
        $expirationTime = $expiredAt ?? $time;

        $columns = [$this->getExpiredAtColumn() => $this->fromDateTime($expirationTime)];

        $this->{$this->getExpiredAtColumn()} = $time;

        if ($this->usesTimestamps() && ! is_null($this->getUpdatedAtColumn())) {
            $this->{$this->getUpdatedAtColumn()} = $time;

            $columns[$this->getUpdatedAtColumn()] = $this->fromDateTime($time);
        }

        $query->update($columns);

        $this->syncOriginalAttributes(array_keys($columns));
    }

    public function unexpire()
    {
        // If the unexpiring event return false, we will exit the operation.
        // Otherwise, we will clear the expired at timestamp and continue
        // with the operation
        if ($this->fireModelEvent('unexpiring') === false) {
            return false;
        }

        $this->{$this->getExpiredAtColumn()} = null;

        $this->exists = true;

        $result = $this->save();

        $this->fireModelEvent('unexpiring', false);

        return $result;
    }

    /**
     * Determine if the model instance has been expired.
     *
     * @return bool
     */
    public function isExpired()
    {
        return ! is_null($this->{$this->getExpiredAtColumn()});
    }

    /**
     * Register a "unexpiring" model event callback with the dispatcher.
     *
     * @param  \Closure|string  $callback
     * @return void
     */
    public static function unexpiring($callback)
    {
        static::registerModelEvent('unexpiring', $callback);
    }

    /**
     * Register a "expired" model event callback with the dispatcher.
     *
     * @param  \Closure|string  $callback
     * @return void
     */
    public static function expired($callback)
    {
        static::registerModelEvent('expired', $callback);
    }

    /**
     * Register a "expiring" model event callback with the dispatcher.
     *
     * @param  \Closure|string  $callback
     * @return void
     */
    public static function expiring($callback)
    {
        static::registerModelEvent('expiring', $callback);
    }

    /**
     * Register a "unexpired" model event callback with the dispatcher.
     *
     * @param  \Closure|string  $callback
     * @return void
     */
    public static function unexpired($callback)
    {
        static::registerModelEvent('unexpired', $callback);
    }

    /**
     * Get the name of the "expired at" column.
     *
     * @return string
     */
    public function getExpiredAtColumn()
    {
        return defined('static::EXPIRED_AT') ? static::EXPIRED_AT : 'expired_at';
    }

    /**
     * Get the fully qualified "expired at" column.
     *
     * @return string
     */
    public function getQualifiedExpiredAtColumn()
    {
        return $this->qualifyColumn($this->getExpiredAtColumn());
    }
}
