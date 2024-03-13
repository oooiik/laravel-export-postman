<?php

namespace Oooiik\LaravelExportPostman\Helper;

interface HelperInterface
{
    /** @return string */
    public function collectionName(): string;

    /** @return string[] */
    public function middlewares(): array;

    /** @return string[] */
    public function headers(): array;

    /** @return string */
    public function contentType(): string;

    /** @return string */
    public function contentTypePostman(): string;

    /** @return string */
    public function baseUrl(): string;

    /** @return string */
    public function baseUrlKey(): string;

    /** @return array */
    public function folders(): array;

    /** @return array */
    public function paramsValue(): array;

    /** @return string */
    public function disk(): string;

    /** @return string */
    public function path(): string;
}