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
        //TODO: too many indentation level, change this in the near future
        foreach($this->counterCacheOptions as $method => $counter)
        {
            if(isset($counter['filter']))
            {
                if (!$this->runFilter($counter['filter']))
                {
                    continue;
                }
            }

            $this->updateCounterField($method, 'increment', $counter['field']);
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
            $this->updateCounterField($method, 'decrement', $counter['field']);
        }

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