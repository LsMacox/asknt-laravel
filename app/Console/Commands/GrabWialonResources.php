<?php

namespace App\Console\Commands;

use App\Models\Wialon\WialonResources;
use Illuminate\Console\Command;

class GrabWialonResources extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'grab:wialon-resources';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retrieves and saves resources from all wialon accounts';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $wResources = \WialonResource::firstResource();

        $wResources->each(function ($resource, $hostId) {
            if ($resource) {
                WialonResources::updateOrCreate(
                    ['w_id' => $resource->id, 'w_conn_id' => $hostId],
                    ['name' => $resource->nm]
                );
            }
        });
    }
}
