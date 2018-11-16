<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class compareV2Keys extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:check {}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $routes = [
            'library/asset',
            'library/version',
            'library/bookorder',
            'library/book',
            'library/bookname',
            'library/chapter',
            'library/language',
            'library/verseinfo',
            'library/numbers',
            'library/metadata',
            'library/volume',
            'library/volumelanguage',
            'library/volumelanguagefamily',
            'library/volumeorganization',
            'library/volumehistory',
            'library/organization',
            'audio/location',
            'audio/path',
            'audio/versestart',
            'text/font',
            'text/verse',
            'text/search',
            'text/searchgroup',
            'video/location',
            'video/path',
            'country/countrylang',
            'api/apiversion',
            'api/reply'
        ];

        foreach ($routes as $route) {
            $result = $this->fetchAndCompare($route);
        }
    }

    public function fetchAndCompare($route)
    {
        $dbp_new = json_decode(file_get_contents(config('app.api_url')."/$route?key=3e0eed1a69fc6e012fef51b8a28cc6ff&reply=json"));
        $dbp_old = json_decode(file_get_contents("https://dbt.io/library/chapter/$route?key=3e0eed1a69fc6e012fef51b8a28cc6ff&reply=json"));
    }
}
