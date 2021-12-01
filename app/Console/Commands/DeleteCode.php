<?php

namespace App\Console\Commands;

use App\Models\Code;
use Illuminate\Console\Command;

class DeleteCode extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete_code';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'delete code for 5 min';

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
        $codes= Code::all();
        foreach ($codes as $code){
            $code['time']+=1;
            if ($code['time']<10){
                $code->save();
            }else{
                $code->delete();
            }

        }
    }
}
