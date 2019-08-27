<?php

namespace Gekko\Collections;

final class Predicate
{
    public static function isNull() {
        return function ($item, $index) {
            return is_null($item);
        };
    }

    public static function isEmpty() {
        return function ($item, $index) {
            return empty($item);
        };
    }
}