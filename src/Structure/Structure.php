<?php

namespace Oooiik\LaravelExportPostman\Structure;

use Oooiik\LaravelExportPostman\Helper\HelperInterface;

class Structure
{
    /** @var HelperInterface */
    protected $helper;

    /** @var array */
    public $collection;

    public function __construct(HelperInterface $helper)
    {
        $this->helper = $helper;

        $this->collection = $this->getInit();
    }

    protected function getInit()
    {
        return [
            'variable' => [],
            'info' => [
                'name' => $this->helper->collectionName(),
                'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json',
            ],
            'item' => [],
            'event' => [],
        ];
    }


}