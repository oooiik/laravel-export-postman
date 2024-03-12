<?php

namespace Oooiik\LaravelExportPostman\Helper;

use Illuminate\Container\Container;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Helper implements HelperInterface
{
    /** @var Repository $config */
    protected $config;
    public function __construct()
    {
        $this->config = Container::getInstance()->make(Repository::class);
    }

    public function collectionName(): string
    {
        return str_replace(
            ['{app}'],
            [Str::snake($this->config->get('app.name'))],
            $this->config->get('export-postman.collection_name')
        );
    }

    public function middlewares(): array
    {
        return $this->config->get('export-postman.include_middleware');
    }

    public function headers(): array
    {
        return $this->config->get('export-postman.headers');
    }

    public function baseUrl(): string
    {
        return $this->config->get('export-postman.base_url');
    }

    public function baseUrlKey(): string
    {
        return $this->config->get('export-postman.base_url_key');
    }

    public function folders(): array
    {
        return $this->config->get('export-postman.folders');
    }

    public function formData(): array
    {
        return $this->config->get('export-postman.formdata');
    }

    public function path(): string
    {
        $path = str_replace(
            ['{app}', '{timestamp}'],
            [Str::snake($this->config->get('app.name')), date('Y-m-d_H-i-s')],
            $this->config->get('export-postman.path')
        );
        return Storage::disk($this->config->get('export-postman.disk'))
            ->path($path);
    }
}