<?php

namespace App\Models\MetaQuery\Resolvers;

use App\Models\MetaQuery\MetaQueryNode;

interface ResolvesMetaQueryNode
{
	public function __construct(MetaQueryNode $node);
	public function resolve(): bool;
	public function pause();
	public function resume();
}
