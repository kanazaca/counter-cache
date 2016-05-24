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
     * Override update method because we need to listen for relation changes
     *
     * @param array $attributes
     * @param array $options
     * @return bool
     */
    public function update(array $attributes = [], array $options = [])
    {
        foreach($this->counterCacheOptions as $method => $counter)
        {
            $this->decrementCounter($method, $counter); // decrement 1 in the old relation - xit happens bro

            $updated = parent::update($attributes);

            if (!$updated)
            {
                $this->incrementCounter($method, $counter);
            }
        }

        return true;
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
        foreach($this->counterCacheOptions as $method => $counter)
        {
            if(!$this->incrementCounter($method, $counter))
            {
                continue;
            }
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
        foreach($this->counterCacheOptions as $method => $counter)
        {
            $this->decrementCounter($method, $counter);
        }

        return true;
    }

    /**
     * Increment one counter
     *
     * @param $method
     * @param $counter
     * @return bool
     */
    public function incrementCounter($method, $counter, $removeCache = false)
    {
        if(isset($counter['filter']))
        {
            if (!$this->runFilter($counter['filter']))
            {
                return false;
            }
        }

        $this->updateCounterField($method, 'increment', $counter['field']);

        return true;
    }

    /**
     * Decrement one counter
     *
     * @param $method
     * @param $counter
     * @return bool
     */
    public function decrementCounter($method, $counter, $removeCache = false)
    {
        $this->updateCounterField($method, 'decrement', $counter['field']);

        return true;
    }

    /**
     * Update the field (increment or decrement)
     *
     * @param $method
     * @param $type
     * @param $field
     */
    public function updateCounterField($method, $type, $field)
    {
        $this->clearRelationCache($method);

        $relation = $this->buildRelation($method);

        $relation->$type($field);
    }

    /**
     * Run filter
     *
     * @param $filterName
     * @return bool
     */
    public function runFilter($filterName)
    {
        return $this->$filterName();
    }

    /**
     * Clears relation cache, by reloading it
     *
     * @param $model
     */
    public function clearRelationCache($model)
    {
        $this->load($model);
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
