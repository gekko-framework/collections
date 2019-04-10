<?php
/*
* (c) Leonardo Brugnara
*
* Full copyright and license information in LICENSE file.
*/

namespace Gekko\Collections;

class Collection implements \IteratorAggregate
{
    private $items;

    private function __construct(array $items)
    {
        $this->items = $items;
    }

    public static function of($items) : self
    {
        return new self($items);
    }

    public function getIterator() {
        return new \ArrayIterator($this->items);
    }

    public function toArray() : array
    {
        return $this->items;
    }

    public function forEach(callable $func) : self
    {
        foreach ($this as $i => $item)
            $func($item, $i);

        return $this;
    }

    public function select(callable $predicate) : self
    {
        $items = [];

        foreach ($this as $i => $item)
            $items[] = $predicate($item, $i);

        return new self($items);
    }

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

    public function first(callable $predicate) : ?object
    {
        foreach ($this as $i => $item)
            if ($predicate($item, $i))
                return $item;

        return null;
    }

    public function where(callable $predicate) : self
    {
        $items = [];

        foreach ($this as $i => $item)
            if ($predicate($item, $i))
                $items[] = $item;

        return new self($items);
    }

    public function any(callable $predicate) : bool
    {
        foreach ($this as $i => $item)
            if ($predicate($item, $i))
                return true;
        return false;
    }

    public function all(callable $predicate) : bool
    {
        foreach ($this as $i => $item)
            if (!$predicate($item, $i))
                return false;
        return true;
    }

    public function join(string $glue = "") : string
    {
        return \implode($glue, $this->items);
    }
}
