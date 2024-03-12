<?php

namespace Oooiik\LaravelExportPostman\Structure;

use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Storage;
use Oooiik\LaravelExportPostman\Helper\HelperInterface;

class Structure
{
    /** @var HelperInterface */
    protected $helper;

    /** @var array */
    public $collection;

    /**
     * @throws BindingResolutionException
     */
    public function __construct()
    {
        $this->helper = Container::getInstance()->make(HelperInterface::class);
        $this->initCollection();
    }

    protected function initCollection()
    {
        $this->collection = [
            'variable' => [],
            'info' => [
                'name' => $this->helper->collectionName(),
                'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json',
            ],
            'item' => [],
            'event' => [],
        ];
    }

    /**
     * @param string $folder # api/dir/inDir
     * @param array $request
     * @return void
     */
    public function write(string $folder, array $request)
    {
        $this->writeFolder($folder);


        $folders = explode('/', $folder);

        $parent = &$this->collection;
        foreach ($folders as $key => $folder) {
            $settingName = implode('/', array_slice($folders, 0, $key + 1));
            $setting = $this->helper->folders()[$settingName] ?? null;

            if (isset($setting['isGlobal'])) {
                $parent = &$this->collection;
            }

            foreach ($parent['item'] as &$item) {
                if ($item['name'] === $folder) {
                    $parent = &$item;
                    break;
                }
            }
        }
        $parent['item'][] = $request;
    }

    protected function writeFolder($uri)
    {
        $folders = explode('/', $uri);
        $parent = &$this->collection;
        foreach ($folders as $key => $folder) {
            $settingName = implode('/', array_slice($folders, 0, $key + 1));
            $setting = $this->helper->folders()[$settingName] ?? null;

            if (isset($setting['isGlobal']) && $setting['isGlobal']) {
                $parent = &$this->collection;
            }

            $hasFolder = false;
            foreach ($parent['item'] as &$item) {
                if ($item['name'] === $folder) {
                    $parent = &$item;
                    $hasFolder = true;
                    break;
                }
            }

            if ($hasFolder) continue;

            $newFolder = [
                'name' => $folder,
                'item' => [],
            ];

            if (in_array($settingName, array_keys($this->helper->folders()))) {
                $setting = $this->helper->folders()[$settingName];
                if (array_key_exists('auth', $setting)) {
                    $newFolder['auth'] = $setting['auth'];
                }
                if (array_key_exists('prerequest', $setting)) {
                    if (!array_key_exists('event', $newFolder)) $newFolder['event'] = [];
                    $newFolder['event'][] = [
                        'listen' => 'prerequest',
                        'script' => [
                            'type' => 'text/javascript',
                            'exec' => [$setting['prerequest']]
                        ]
                    ];
                }
                if (array_key_exists('test', $setting)) {
                    if (!array_key_exists('event', $newFolder)) $newFolder['event'] = [];
                    $newFolder['event'][] = [
                        'listen' => 'test',
                        'script' => [
                            'type' => 'text/javascript',
                            'exec' => [$setting['test']]
                        ]
                    ];
                }
            }

            $parent['item'][] = $newFolder;


            foreach ($parent['item'] as &$item) {
                if ($item['name'] === $folder) {
                    $parent = &$item;
                    break;
                }
            }


        }

    }


    /**
     * @return bool
     */
    public function storeFile(): bool
    {
        return Storage::disk($this->helper->disk())->put($this->helper->path(), json_encode($this->collection));
    }

}