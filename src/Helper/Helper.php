<?php

namespace Oooiik\LaravelExportPostman\Helper;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Support\Str;

class Helper
{
    /** @var Repository $config */
    protected $config;
    public function __construct(Repository $config)
    {
        $this->config = $config;
    }

    /** @return string */
    public function getFileName()
    {
        return str_replace(
            ['{app}', '{timestamp}'],
            [Str::snake($this->config->get('app.name')), date('Y-m-d_H-i-s')],
            $this->config->get('export-postman.filename')
        );
    }
}