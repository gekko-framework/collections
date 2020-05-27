<?php
/*
* (c) Leonardo Brugnara
*
* Full copyright and license information in LICENSE file.
*/

namespace Gekko\Collections;

class Collection implements \IteratorAggregate, \Countable
{
    /**
     * @var array $items An array of items that are the internal state of the collection
     */
    private $items;

    /**
     * The private constructor that initializes the collection, used
     * by the {@see \Gekko\Collections\Collection::of} method
     * 
     * @param array $items
     *
     */
    private function __construct(array $items)
    {
        $this->items = $items;
    }

    /**
     * Creates a new collection object
     * 
     * @param array $items Array of objects that represent the collection
     * @return \Gekko\Collections\Collection Collection object
     * 
     */
    public static function of(array $items) : self
    {
        return new self($items);
    }

    /**
     * Returns the number of elements in the collection
     * 
     * @return int Number of elements
     * 
     */
    public function count() : int
    {
        return \count($this->items);
    }

    /**
     * @see \IteratorAggregate\getIterator
     */
    public function getIterator() : \Traversable
    {
        return new \ArrayIterator($this->items);
    }

    /**
     * Returns an array with the elements that are part of the collection
     * 
     * @return array An array with the elements
     */
    public function toArray() : array
    {
        return $this->items;
    }

    /**
     * Iterates through all the elements calling the callable object with
     * each element as the first argument and the element's index as the second
     * argument.
     * 
     * @param callable $func A callable object
     * @return \Gekko\Collections\Collection The same collection refrence 
     */
    public function forEach(callable $func) : self
    {
        foreach ($this as $i => $item)
            $func($item, $i);

        return $this;
    }

    /**
     * Iterates through all the elements calling the callable object with
     * each element as the first argument and the element's index as the second
     * argument. The object returned by the predicate function is an element
     * to be included in a new collection object. 
     * 
     * @param callable $predicate A callable object to apply to each element
     * @return \Gekko\Collections\Collection A new collection with the elements returned by the predicate function
     */
    public function select(callable $predicate) : self
    {
        $items = [];

        foreach ($this as $i => $item)
            $items[] = $predicate($item, $i);

        return new self($items);
    }

    /**
     * Iterates through all the elements calling the callable object with
     * each element as the first argument and the element's index as the second
     * argument. The array returned by the predicate function is a group of elements
     * to be included in a new collection object. 
     * 
     * @param callable $predicate A callable object to apply to each element
     * @return \Gekko\Collections\Collection A new collection with the elements returned by the predicate function
     */
    public function selectMany(callable $predicate) : self
    {
        $items = [];

        foreach ($this as $i => $item)
        {
            $array = $predicate($item, $i);
            $items = array_merge($items, $array instanceof self ? $array->toArray() : $array);
        }

        return new self($items);
    }

    /**
     * Iterates through the elements calling the callable object with
     * each element as the first argument and the element's index as the second
     * argument. The function returns the first element that makes the predicate function 
     * return true or null in case no element satisfies the predicate.
     * If the predicate object is null, this function returns the first element in the
     * collection
     * 
     * @param callable|null $predicate A callable object that returns a boolean value or null
     * @return null|mixed The first item that satisfies the predicate or null if the predicate
     * is provided, if it is not, this function returns the first element in the collection.
     */
    public function first(?callable $predicate = null)
    {
        if ($predicate === null)
            return isset($this->items[0]) ? $this->items[0] : null;

        foreach ($this as $i => $item)
            if ($predicate($item, $i) === true)
                return $item;

        return null;
    }

    /**
     * Iterates through all the elements calling the callable object with
     * each element as the first argument and the element's index as the second
     * argument. If the element does not satisfy the predicate, it is ignored, otherwise 
     * it is included in a new collection.
     * 
     * @param callable $predicate A callable object to apply to each element
     * @return \Gekko\Collections\Collection A new collection with the elements that satisfy the predicate
     */
    public function where(callable $predicate) : self
    {
        $items = [];

        foreach ($this as $i => $item)
            if ($predicate($item, $i) === true)
                $items[] = $item;

        return new self($items);
    }

    /**
     * Iterates through the elements calling the callable object with
     * each element as the first argument and the element's index as the second
     * argument. The function returns true if any element within the collection satisfies
     * the predicate, otherwise it returns false.
     * 
     * @param callable $predicate A callable object that returns a boolean value
     * @return bool True if at least one element satisfies the predicate, otherwise the function returns false.
     */
    public function any(callable $predicate) : bool
    {
        foreach ($this as $i => $item)
            if ($predicate($item, $i) === true)
                return true;
        return false;
    }

    /**
     * Iterates through the elements calling the callable object with
     * each element as the first argument and the element's index as the second
     * argument. The function returns true if all the elements within the collection satisfy
     * the predicate, otherwise it returns false.
     * 
     * @param callable $predicate A callable object that returns a boolean value
     * @return bool True if all the elements satisfy the predicate, otherwise the function returns false.
     */
    public function all(callable $predicate) : bool
    {
        foreach ($this as $i => $item)
            if ($predicate($item, $i) === false)
                return false;
        return true;
    }

    /**
     * The reducer function is applied to each element in the collection resulting
     * in a single output value.
     * 
     * @param callable $reducer A callable object that expects 3 arguments: The accumulator
     * that is remembered between each reducer call, an item, and the item's index in the collection.
     * @param mixed $accumulator The initial value of the accumulator
     * @return mixed The function returns the value of the accumulator after applying the reducer function
     * to all the elements within the collection
     */
    public function reduce(callable $reducer, $accumulator)
    {
        foreach ($this as $i => $item)
            $accumulator = $reducer($accumulator, $item, $i);

        return $accumulator;
    }

    /**
     * This function stringifies the elements and glues the elements using the
     * provided separator.
     * 
     * @param string $glue The separator
     * @return string The string representation of the elements glued with the separator
     */
    public function join(string $glue = "") : string
    {
        return \implode($glue, $this->items);
    }
}
