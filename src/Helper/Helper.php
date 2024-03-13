<?php

namespace Oooiik\LaravelExportPostman\Helper;

use Illuminate\Container\Container;
use Illuminate\Contracts\Config\Repository;
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
        $headers = $this->config->get('export-postman.headers') ?? [];
        if (!array_key_exists('Accept', $headers)) {
            $headers['Accept'] = "application/json";
        }
        switch ($this->contentType()) {
            case "form-data":
                $headers['Content-Type'] = "multipart/form-data";
                break;
            case "x-www-form-urlencoded":
                $headers['Content-Type'] = "application/x-www-form-urlencoded";
                break;
            case "json":
            default :
            $headers['Content-Type'] = "application/json";
        }

        return array_map(function ($key) use ($headers) {
            return [
                'key' => $key,
                'value' => $headers[$key]
            ];
        }, array_keys($headers));
    }

    public function contentType(): string
    {
        $contentType = $this->config->get('export-postman.content_type');
        if (!in_array($contentType, ["form-data", "x-www-form-urlencoded", "json"])){
            $contentType = "form-data";
        }
        return $contentType;
    }

    public function contentTypePostman(): string
    {
        return [
            "form-data" => "formdata",
            "x-www-form-urlencoded" => "urlencoded",
            "json" => "raw"
        ][$this->contentType()];
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

    public function paramsValue(): array
    {
        return $this->config->get('export-postman.params_value');
    }

    public function disk(): string
    {
        return $this->config->get('export-postman.disk');
    }

    public function path(): string
    {
        return str_replace(
            ['{app}', '{timestamp}'],
            [Str::snake($this->config->get('app.name')), date('Y-m-d_H-i-s')],
            $this->config->get('export-postman.path')
        );
    }
}