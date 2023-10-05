<?php

namespace PromCMS\Core\Utils;


class ObjectUtils {

  static function objectToArrayRecursive(mixed $value) {
    if(is_object($value) || is_array($value)) {
        $ret = (array) $value;
        foreach ($ret as &$item) {
            $item = static::objectToArrayRecursive($item);
        }
        return $ret;
    } else {
        return $value;
    }
}
}