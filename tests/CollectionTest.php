<?php

use PHPUnit\Framework\TestCase;
use \Gekko\Collections\Collection;

class CollectionTest extends TestCase
{
    public function test_ofMethodShouldReturnACollection()
    {
        $c1 = Collection::of([]);
        $this->assertInstanceOf(\Gekko\Collections\Collection::class, $c1);

        $c2 = Collection::of([ 1 ]);
        $this->assertInstanceOf(\Gekko\Collections\Collection::class, $c2);

        $c3 = Collection::of([ 1, 2 ]);
        $this->assertInstanceOf(\Gekko\Collections\Collection::class, $c3);
    }

    public function test_countMethodReturnsTheNumberOfElementsWithinACollection()
    {
        $c1 = Collection::of([]);
        $this->assertEquals(0, $c1->count());

        $c2 = Collection::of([ 1 ]);
        $this->assertEquals(1, $c2->count());

        $c3 = Collection::of([ 1, 2 ]);
        $this->assertEquals(2, $c3->count());
    }

    public function test_toArrayMethodReturnsACopyOfTheInternalRepresentation()
    {
        $c1 = Collection::of([ 1, 2, 3, 4, 5, 6 ]);
        
        $array = $c1->toArray();
        $this->assertIsArray($array);

        array_pop($array);
        array_pop($array);
        $this->assertEquals(4, count($array));

        $this->assertEquals(6, $c1->count());
    }

    public function test_foreachMethodShouldIterateThroughAllTheElements()
    {
        $c1 = Collection::of([ 1, 2, 3, 4, 5, 6 ]);

        $sum = 0;

        $c1->forEach(function ($element, $index) use (&$sum) {
            $sum += $element;
        });

        $this->assertEquals(21, $sum);
    }

    public function test_selectMethodShouldApplyToAllTheElements()
    {
        $c1 = Collection::of([ 1, 2, 3, 4, 5, 6 ]);
        $doubles = [ 2, 4, 6, 8, 10, 12 ];

        $a = $c1->select(fn($element, $index) => $element * 2)->toArray();

        $this->assertEquals($doubles, $a);
    }

    public function test_selectManyMethodShouldApplyToAllTheElements()
    {
        $c1 = Collection::of([ 1, 3, 5 ]);
        $numbers = [ 1, 2, 3, 4, 5, 6 ];

        $a = $c1->selectMany(fn($element, $index) => [ $element, $element + 1 ])->toArray();
        
        $this->assertEquals($numbers, $a);
    }

    public function test_firstMethodWithoutPredicateReturnsTheFirstElementInTheCollection()
    {
        $c1 = Collection::of([ 1 ]);
        $c2 = Collection::of([ 2, 3 ]);
        $c3 = Collection::of([ 3, 4, 5 ]);
        $c4 = Collection::of([]);

        $this->assertEquals(1, $c1->first());
        $this->assertEquals(2, $c2->first());
        $this->assertEquals(3, $c3->first());
        $this->assertNull($c4->first());
    }

    public function test_firstMethodWithPredicateReturnsTheFirstElementThatSatisfiesThePredicate()
    {
        $c1 = Collection::of([ 1 ]);
        $c2 = Collection::of([ 2, 3 ]);
        $c3 = Collection::of([ 3, 4, 5 ]);
        $c4 = Collection::of([]);

        $this->assertNull($c1->first(fn($element) => $element % 2 === 0));
        
        $this->assertEquals(2, $c2->first(fn($element) => $element % 2 === 0));
        
        $this->assertEquals(4, $c3->first(fn($element) => $element % 2 === 0));
        $this->assertEquals(5, $c3->first(fn($element) => $element > 4));
        
        $this->assertNull($c4->first(fn($element) => $element % 2 === 0));
    }

    public function test_whereMethodShouldFilterElementsWithThePredicate()
    {
        $c1 = Collection::of([ 1, 2, 3, 4, 5, 6 ]);
        $even = [ 2, 4, 6 ];

        $a = $c1->where(fn($element) => $element % 2 == 0)->toArray();

        $this->assertEquals($even, $a);
    }

    public function test_anyMethodShouldReturnTrueIfAtLeastOneElementSatisfiesThePredicate()
    {
        $c1 = Collection::of([ 2, 4, 6, 8 ]);

        $this->assertTrue($c1->any(fn($element) => $element > 3 && $element < 5));
        $this->assertTrue($c1->any(fn($element) => $element == 6));
        $this->assertFalse($c1->any(fn($element) => $element == 5));
        $this->assertFalse($c1->any(fn($element) => $element % 2  !== 0));
    }

    public function test_allMethodShouldReturnTrueIfAllTheElementsSatisfyThePredicate()
    {
        $c1 = Collection::of([ 2, 4, 6, 8 ]);

        $this->assertTrue($c1->all(fn($element) => $element % 2 == 0));
        $this->assertFalse($c1->all(fn($element) => $element % 2  != 0));
    }

    public function test_reduceMethodShouldReturnAnAccumulatedValue()
    {
        $c1 = Collection::of([ 2, 4, 6, 8 ]);

        $accumulator = $c1->reduce(fn($acc, $element) => $acc += $element, 0);
        $this->assertEquals(20, $accumulator);

        $accumulator = $c1->reduce(fn($acc, $element) => $acc += $element, 0);
        $this->assertEquals(20, $accumulator);

        $accumulator = $c1->reduce(fn($acc, $element) => $acc += $element, 10);
        $this->assertEquals(30, $accumulator);

        $accumulator = $c1->reduce(fn($acc, $element) => $acc += $element, -20);
        $this->assertEquals(0, $accumulator);

        $c2 = Collection::of(["Hello", "world"]);
        $result = $c2->reduce(fn($acc, $element) => trim($acc .= " " . $element), "");
        $this->assertEquals("Hello world", $result);

        $c3 = Collection::of(["column1", "column2", "column3", "column4"]);
        $result = $c3->reduce(fn($acc, $element, $index) => $acc .= ($index != 0 ? ", {$element}" : $element), "");
        $this->assertEquals("column1, column2, column3, column4", $result);
    }

    public function test_joinMethodGluesTheElementsWithASeparator()
    {
        $c1 = Collection::of([ 2, 4, 6, 8 ]);
        
        $this->assertEquals("2 4 6 8", $c1->join(" "));

        $c2 = Collection::of(["Hello", "world"]);
        $this->assertEquals("Hello world", $c2->join(" "));

        $c3 = Collection::of(["column1", "column2", "column3", "column4"]);
        $this->assertEquals("column1, column2, column3, column4", $c3->join(", "));
    }
}