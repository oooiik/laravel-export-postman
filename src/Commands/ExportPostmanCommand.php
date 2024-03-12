<?php

namespace Oooiik\LaravelExportPostman\Commands;

use Illuminate\Console\Command;
use Oooiik\LaravelExportPostman\Helper\Helper;
use Oooiik\LaravelExportPostman\Services\RouterService;
use Oooiik\LaravelExportPostman\Structure\Structure;

class ExportPostmanCommand extends Command
{
    protected $signature = 'export:postman';

    protected $description = 'Automatically generate a Postman collection based on your API routes';

    /** @var Helper */
    protected $helper;

    /** @var Structure */
    protected $structure;

    public function __construct(Helper $helper, Structure $structure)
    {
        parent::__construct();

        $this->helper = $helper;
        $this->structure = $structure;
    }

    public function handle()
    {
        $structure = new Structure();
        $routerService = new RouterService($structure);
        $routerService->routesToStructure();
        $structure->storeFile();
    }
}