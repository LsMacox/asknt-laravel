<?php

namespace App\Console\Commands;

use App\Models\Wialon\WialonObjects;
use Illuminate\Console\Command;

class GrabWialonObjects extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'grab:wialon-objects';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retrieves and saves objects from all wialon accounts';

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
        $wObjects = \WialonResource::getObjectsWithRegPlate();

        $wObjects->each(function ($host, $hostId) {
            $host->each(function ($object) use ($hostId) {
                $regPlate = (string) \Str::of(
                    optional(collect($object->pflds)
                        ->where('n', 'registration_plate')
                        ->first())
                        ->v
                )->upper()->trim()->replaceMatches('/\s+/', '');

                WialonObjects::updateOrCreate(
                    ['w_id' => $object->id, 'w_conn_id' => $hostId],
                    ['name' => $object->nm, 'registration_plate' => $regPlate]
                );
            });
        });
    }
}
