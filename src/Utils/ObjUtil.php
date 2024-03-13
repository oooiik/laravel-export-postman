<?php

namespace Oooiik\LaravelExportPostman\Utils;

class ObjUtil
{
    /**
     * @param mixed $obj
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public static function object_set(&$obj, string $key, $value): bool
    {
        $parent = &$obj;
        foreach (explode('.', $key) as $pat) {
            if (!is_object($parent) && !is_array($parent)){
                return false;
            }
            if (is_array($parent) && $pat === "") {
                $parent[] = [];
                $parent = &$parent[count($parent)-1];
                continue;
            }
            if (is_array($parent) && filter_var($pat, FILTER_VALIDATE_INT) !== false) {
                $pat = (int)$pat;
            }
            if (!array_key_exists($pat, $parent)) {
                $parent[$pat] = [];
            }
            $parent = &$parent[$pat];
        }
        $parent = $value;

        return true;
    }
}