<?php

namespace App\Builders\GraphQLRequestBuilders;

use App\Models\GraphQLServer;
use App\Models\User;

interface BuildsGraphQLRequests {
	public static function build(GraphQLServer $server, User $user, String $queryString);
}
