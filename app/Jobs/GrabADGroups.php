<?php

namespace App\Jobs;

use Adldap\AdldapInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GrabADGroups implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Adldap
     */
    protected $ldap;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->ldap = app(AdldapInterface::class);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->ldap->search()->groups();
    }
}
