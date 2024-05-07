<?php

namespace Oooiik\LaravelExportPostman\Helper;

use Illuminate\Support\Str;

class RouteHelper
{
    public static function containsSerializedClosure(array $action)
    {
        return is_string($action['uses']) && Str::startsWith($action['uses'], [
                'C:32:"Opis\\Closure\\SerializableClosure',
                'O:47:"Laravel\\SerializableClosure\\SerializableClosure',
                'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure',
            ]) !== false;
    }
}