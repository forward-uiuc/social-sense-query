<?php

namespace App\Models\MetaQuery\Resolvers;

abstract class ResolverStatus
{
	const WAITING_FOR_DEPENDENCIES = 0;
	const READY = 1;
	const COMPLETED = 2;
	const PAUSED = 3;
	const ERROR = 4;	
	const RUNNING = 5;	
}
