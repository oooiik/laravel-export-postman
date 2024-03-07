<?php

namespace Oooiik\LaravelExportPostman\Helper;

interface HelperInterface
{
    /** @return string */
    public function filename();


    /** @return string */
    public function collectionName();
}