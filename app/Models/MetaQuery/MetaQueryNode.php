<?php
namespace App\Models\MetaQuery;

use Illuminate\Database\Eloquent\Model;
use App\Models\Query\Query;
use App\Models\Query\QueryHistory;

use App\Models\MetaQuery\Resolvers\FunctionResolver;
use App\Models\MetaQuery\Resolvers\QueryResolver;
use App\Models\MetaQuery\Resolvers\MetaQueryResolver;
use App\Models\MetaQuery\Resolvers\ResolvedNodeResolver;

class MetaQueryNode extends Model
{

	protected $fillable = [
		'state',
		'topology_id',
		'node_type',
		'node_id',
		'resolved',
		'node_state'
	];

	protected $appends = [
		'node_name'
	];

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

	public function getNodeNameAttribute() {
		if ($this->node_type === 'function') {
			$function = app()->make('App\Repositories\Contracts\MetaQueryFunctionRepositoryInterface')->findById($this->node_id);
			return $function->name;
		} else {
			return $this->node->name;
		}
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


	public static $statuses = [
		'ready', 'not_ready', 'waiting', 'error', 'paused', 'completed', 'running'
	];

	public function setStatus(string $status) {
		if(!in_array($status, MetaQueryNode::$statuses)){
			throw new \Exception("Error, attempting to set node to status of ".$status);
		}
			
		switch ($status) {
			// Check if this node is actually ready
			case 'ready':
				$dependenciesResolved = $this->dependencies()->with('output.node')->get()->reduce(function($carry, $dependency){
					return $carry && ($dependency->output->node->status == 'completed');
				}, true);

				if(!$dependenciesResolved) {
					$this->setStatus('waiting');
					throw new \Exception("Node is waiting for dependencies and is being told to resolve");
				}

				break;

			// Check if this node was actually completed
			case 'completed':
				$nodeHasOutput = $this->outputs->reduce(function($carry, $output) {
					return $carry && ($output->value !== null);	
				}, true);

				if(!$nodeHasOutput){
					$this->setStatus('error');
					throw new \Exception("Error, node is not completed");	
				}
				break;
		}

		$this->status = $status;
		$this->save();
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
