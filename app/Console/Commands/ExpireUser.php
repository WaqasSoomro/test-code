<?php

namespace App\Console\Commands;

use App\PricingPlan;
use App\TeamSubscription;
use Illuminate\Console\Command;

class ExpireUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'expire:user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire user subscription';

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
     * @return mixed
     */
    public function handle()
    {
        $teamSubscripions = TeamSubscription::whereStatus('1')->get();
        $today = date("Y-m-d");
        foreach ($teamSubscripions as $subscripion){
            $expire = $subscripion->end_date; //from database
            $today_dt = new \DateTime($today);
            $expire_dt = new \DateTime($expire);
            if ($expire_dt < $today_dt) {
                $subscripion->status = '0';
                $subscripion->save();
            }
        }
    }
}
