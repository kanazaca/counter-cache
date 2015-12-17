<?php namespace kanazaca\CounterCache;

trait CounterCache
{
    /**
     * Override save method because we need to increment all counters
     * when is a new one
     *
     * @param array $options
     * @return bool
     */
    public function save(array $options = [])
    {
        if(parent::save($options))
        {
            return $this->incrementAllCounters();
        }

        return false;
    }

    /**
     * Override delete method because we need to decrement all counters
     * when one of them is gone :(
     *
     * @return bool
     */
    public function delete()
    {
        if(parent::delete())
        {
            return $this->decrementAllCounters();
        }

        return false;
    }

    /**
     * Increment all counters in all relations
     *
     * @return bool
     */
    public function incrementAllCounters()
    {
        foreach($this->counterCacheOptions as $method => $field)
        {
            $relation = $this->buildRelation($method);

            $relation->increment($field);
        }

        return true;
    }

    /**
     * Decrement all counters in all relations
     *
     * @return bool
     */
    public function decrementAllCounters()
    {
        foreach($this->counterCacheOptions as $method => $field)
        {
            $relation = $this->buildRelation($method);

            $relation->decrement($field);
        }

        return true;
    }

    /**
     * Builds relation from model name (string)
     *
     * @param $model
     * @return Illuminate\Database\Eloquent\Model
     */
    public function buildRelation($model)
    {
        return $this->$model;
    }
}