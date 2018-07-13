<?php
namespace App\Models\MetaQuery;

use Illuminate\Database\Eloquent\Model;
use App\Models\Query\Query;
use App\Services\GQLServerService;
use App\Models\Query\QueryHistory;

use App\Models\MetaQuery\Resolvers\FunctionResolver;
use App\Models\MetaQuery\Resolvers\QueryResolver;
use App\Models\MetaQuery\Resolvers\MetaQueryResolver;
use App\Models\MetaQuery\Resolvers\ResolvedNodeResolver;

class MetaQueryNode extends Model
{

	private static $resolvers = [
		'query' => QueryResolver::class,
		'function' => FunctionResolver::class, 
		'meta_query' =>  MetaQueryResolver::class,
		'data' =>  ResolvedNodeResolver::class,	
	];


	/*
	 * A MetaQueryNode is polymorphic
	 */
	public function node() {
		return $this->morphTo();
	}

	public function stage() {
		return $this->belongsTo(Stage::class);
	}

	public function inputs() {
		return $this->hasMany(MetaQueryNodeInput::class);
	}

	public function outputs() {
		return $this->hasMany(MetaQueryNodeOutput::class);
	}

	public function dependencies() {
		return $this->hasMany(MetaQueryNodeDependency::class);
	}

	
	public function resolve() {
		$resolver = new MetaQueryNode::$resolvers[$this->node_type]($this);
		$resolver->resolve();
		return;
	}



	public static function fromTopologyNode(Stage $stage, $topologyNode) {
		
		$node = new MetaQueryNode();
		$node->resolved = false;
		$node->topology_id = $topologyNode->id->topology;
		$node->node_type = $topologyNode->id->type; 
		$node->node_id = $topologyNode->id->id;
		$stage->nodes()->save($node);

		collect($topologyNode->inputs)->each(function($input) use ($node) {
			$node->inputs()->save(new MetaQueryNodeInput(['path' => key($input)]));
		});

		collect($topologyNode->outputs)->each(function($output) use ($node) {
			$node->outputs()->save(new MetaQueryNodeOutput(['path' => $output ]));
		});

		return $node;
	}
}

