<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DatabaseBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Daily database backup in storage/backups/';
    protected $process;

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
        $today = today()->format('Y-m-d');
        if (!is_dir(storage_path('backups'))) mkdir(storage_path('backups'));

        shell_exec(sprintf("mysqldump -u jogo_db_101 -p%s %s > %s",
            escapeshellcmd('ZRkx$SdD5;M%'),
            'jogo_jogoDB',
            storage_path("backups/{$today}.sql")
        ));
    }
}
