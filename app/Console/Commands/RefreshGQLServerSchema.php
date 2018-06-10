<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\GraphQLServer;
use App\Models\User;

use Illuminate\Support\Facades\File;

class RefreshGQLServerSchema extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'refresh:gql-server-schema';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh all GraphQL servers';

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
		$introspectionQuery = File::get( resource_path() . '/utils/introspectionQuery.txt');
		$servers = GraphQLServer::all();
		// @REFACTOR Allow for connections to fail
		$servers->filter(function($server) { 
			return $server->schema === null ;
		})->each(function($server) use ($introspectionQuery) {
			$server->schema = $server->buildRequest($introspectionQuery, User::first());
			$server->save();
		});
    }
}
