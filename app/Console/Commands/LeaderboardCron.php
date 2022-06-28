<?php

namespace App\Console\Commands;

use App\Leaderboard;
use App\League;
use App\PlayerScore;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class LeaderboardCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leaderboard:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch data from player scores and update or create to leaderboard table';

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
        $ps = PlayerScore::select(DB::raw("(SUM(score)) as total_score"), "user_id")->groupBy('user_id')
            ->latest('total_score')->get();

        if(count($ps) > 0){
            Leaderboard::truncate();

            $data = array();
            foreach ($ps as $key => $item){

                $tmp = array();
                $tmp['user_id'] = $item->user_id;
                $tmp['total_score'] = $item->total_score;
                $tmp['position'] = $key + 1;

                $league_icon = '';
                if ($item->total_score <= 2000) {
                    $league_icon = 'https://www.kindpng.com/picc/m/183-1838998_trophy2-awards-and-achievements-logo-hd-png-download.png';
                }
                else if ($item->total_score > 2000 && $item->total_score <= 3000) {
                    $league_icon = 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSI3TQ_25G5CqAYH8RRCXOz7L2XtwXyLp1U4vemceqyFV8iaMw&s';
                }
                else if ($item->total_score > 3000 && $item->total_score <= 4000) {
                    $league_icon = 'https://www.pinclipart.com/picdir/middle/160-1609821_recent-student-achievements-you-winner-clipart.png';
                }
                else{
                    $league_icon = 'https://img1.cgtrader.com/items/75207/54574752ad/winner-cup-7-3d-model-max.jpg';
                }

                $league = League::where('league_icon', $league_icon)->first();

                $tmp['league_id'] = $league->id ?? null;
                array_push($data,$tmp);
            }

            Leaderboard::insert($data);

            activity()->log('Data updated in leaderboards table using leaderboard cron');
        }
    }
}
