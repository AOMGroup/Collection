<?php


namespace AOM\Lib\Collection;

use AOM\Lib\Collection\Exception\ElementAlreadyExistsException;
use AOM\Lib\Collection\Exception\ElementNotFoundException;
use AOM\Lib\Collection\Exception\NotStackableElementException;

/**
 * Class Collection
 * @package AOM\Lib\Collection
 */
class Collection
{
    /**
     * @var array
     */
    private $collector = [];

    /**
     * @var array
     */
    private $types = [];

    /**
     * @var Stackable[]|array
     */
    private $stackedElements = [];


    /**
     * Collection constructor.
     * @param array|null $array
     */
    public function __construct(?array $array = null)
    {
        if (null !== $array) {
            $this->attach($array);
        }
    }

    /**
     * @param array $array
     */
    private function attach(array $array): void
    {
        foreach ($array as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function set($key, $value): self
    {
        $this->collector[$this->getType($value)][$key] = $value;
        return $this;
    }

    /**
     * @param $element
     * @return $this
     * @throws ElementAlreadyExistsException
     */
    public function insert($element): self
    {
        if (false !== $this->contains($element)) {
            throw new ElementAlreadyExistsException();
        }
        $this->collector[$this->getType($element)][] = $element;
        return $this;
    }

    /**
     * @param $element
     * @param $value
     * @return $this
     * @throws ElementNotFoundException
     */
    public function update($element, $value): self
    {
        if (false !== ($key = $this->contains($element))) {
            $this->collector[$this->getType($element)][$key][] = $value;
        }
        throw  new ElementNotFoundException();
    }

    /**
     * @param $element
     * @return string
     */
    private function getType($element): string
    {
        $type = gettype($element);
        if ('object' === $type) {
            $type = get_class($element);
        }
        if (!in_array($type, $this->types)) {
            $this->types[] = $type;
        }
        return $type;
    }

    /**
     * @param $element
     * @return bool|mixed
     */
    public function contains($element)
    {
        $key = array_search($element, $this->collector[$this->getType($element)], true);
        if (false === $key) {
            return false;
        }
        return $key;
    }

    /**
     * @param callable $expression
     * @return Collection
     */
    public function filter(callable $expression): Collection
    {
        $result = [];
        $filteredValues = [];
        foreach ($this->types as $type) {
            $filteredValues = array_filter($this->collector[$type], $expression, ARRAY_FILTER_USE_BOTH);
        }
        $result = array_merge($result, $filteredValues);
        return new static($result);
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->collector);
    }

    /**
     * @param string $varType
     * @param callable $expression
     * @return Collection
     */
    public function applyToAllOfType(string $varType, callable $expression): self
    {
        foreach ($this->collector[$varType] as $key => $item) {
            $this->collector[$varType][$key] = $expression($item);
        }
        return $this;
    }


    /**
     * @param callable $expression
     * @return Collection
     */
    public function applyToAll(callable $expression): self
    {
        foreach ($this->types as $type) {
            $this->applyToAllOfType($type, $expression);
        }
        return $this;
    }

    /**
     * @param $key
     * @return mixed
     * @throws ElementNotFoundException
     */
    public function getFirstElementByKey($key)
    {
        foreach ($this->types as $type) {
            $key = array_key_exists($key, $this->collector[$type]);
            return $this->collector[$type][$key];
        }
        throw new ElementNotFoundException();
    }

    /**
     * @param $key
     * @param string|null $type
     * @return mixed
     * @throws ElementNotFoundException
     */
    public function getElementByKey($key, ?string $type = null)
    {
        if (null === $type) {
            return $this->getFirstElementByKey($key);
        }
        if (array_key_exists($key, $this->collector[$type])) {
            return $this->collector[$type][$key];
        }
        throw new ElementNotFoundException();
    }

    /**
     * @param $element
     * @return Collection
     * @throws ElementNotFoundException
     */
    public function removeElement($element): self
    {
        $type = $this->getType($element);
        $key = $this->contains($element);
        if (false === $key) {
            throw new ElementNotFoundException();
        }
        unset($this->collector[$type][$key]);
        $this->updateTypes($type);
        return $this;
    }

    /**
     * @param $element
     * @return Collection
     * @throws ElementNotFoundException
     */
    public function makeElementStackable($element): self
    {
        $key = $this->contains($element);
        if (false === $key) {
            throw new ElementNotFoundException();
        }
        $type = $this->getType($element);
        $this->stackedElements[$type][$key] = (new Stackable())->setCount(1)->setValue($element);
        return $this;
    }

    /**
     * @param $element
     * @return bool
     */
    public function isStackable($element): bool
    {
        $type = $this->getType($element);
        $key = $this->contains($element);
        return !empty($this->stackedElements[$type][$key]);
    }

    /**
     * @param $element
     * @return Stackable
     * @throws NotStackableElementException
     */
    public function selectStackable($element): Stackable
    {
        if (!$this->isStackable($element)) {
            throw new NotStackableElementException();
        }
        $type = $this->getType($element);
        $key = $this->contains($element);
        return $this->stackedElements[$type][$key];
    }

    /**
     * @param string $type
     */
    private function updateTypes(string $type): void
    {
        if (empty($this->collector[$type])) {
            if (false !== ($key = array_search($type, $this->types))) {
                unset($this->types[$key]);
            }
        }
    }
}
