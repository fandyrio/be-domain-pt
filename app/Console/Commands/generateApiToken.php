<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class generateApiToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-api-token';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate api token for Ampuh-ZI';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $token=Str::random(60);

        DB::table('api_token')->insert([
            'token'=>hash('sha256', $token),
            'env'=>'local'
        ]);
        $this->info($token);
        return 0;
    }
}
