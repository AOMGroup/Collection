<?php

namespace AOM\Lib\Collection;

/**
 * Class Countable
 * @package AOM\Lib\Collection
 */
class Stackable
{
    private $value;
    private $count = 0;

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     * @return Stackable
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * @param mixed $count
     * @return Stackable
     */
    public function setCount($count)
    {
        $this->count = $count;
        return $this;
    }

    /**
     * Resets the counter to 0
     */
    public function reset()
    {
        $this->count = 0;
    }

    public function increment()
    {
        $this->count++;
    }

    public function decrement()
    {
        $this->count++;
    }

    public function increaseBy(int $value)
    {
        $this->count += $value;
    }

    public function decreaseBy(int $value)
    {
        $this->count -= $value;
    }
}