<?php

namespace App\Repositories\Contracts;

interface MetaQueryFunctionRepositoryInterface 
{
	// Return all functions
	public function all();

	// Find function by id
	public function findById($id);
}
