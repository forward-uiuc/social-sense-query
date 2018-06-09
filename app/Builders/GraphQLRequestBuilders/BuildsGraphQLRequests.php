<?php

namespace App\Builders\GraphQLRequestBuilders;

use App\Models\GraphQLServer;

interface BuildsGraphQLRequests {
	public static function build(GraphQLServer $server, String $queryString);
}
