<?php

use Illuminate\Database\Seeder;
use App\Models\GraphQLServer;

class GraphQLServerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
		$local = new GraphQLServer([
			'name' => 'Social Universe',
			'slug' => 'local',
			'url' => '172.21.0.2:8080',
			'description' => 'Supported querying for Reddit, Youtube, and Twitter'
		]);
		$local->save();
    }
}
