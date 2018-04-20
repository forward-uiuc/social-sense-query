<?php
namespace App\Models\MetaQuery;

use Illuminate\Database\Eloquent\Model;
use App\Models\Query\Query;
use App\Services\GQLServerService;
use App\Models\Query\QueryHistory;

class MetaQueryNode extends Model
{

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
		if($this->resolved) {
			return false;
		}

		$server = app(\App\Services\GQLServerService::class);
		$user = $this->stage->run->metaQuery->user;
		$authorizations = $user->authorizations;
	
		if($this->node_type === 'query') {
			
			$queriesToResolve = collect([$this->node->getQueryNode()]);	
			$this->dependencies->each(function($dependency) use (&$queriesToResolve) {

				$values = collect(json_decode($dependency->output->value));
				$queriesToResolve = $queriesToResolve->map(function($query) use ($values, $dependency) {

					return $values->map(function($value) use ($query, $dependency, $values) {
						$q = clone $query;
						$successful = $q->applyValue($dependency->input->path, $value); // $successful= were we able to successfully apply that value?
						return $q;
					});

				})->collapse();
				return $queriesToResolve;
			});

			$queryResults = $queriesToResolve->map(function($query) use ($server, $authorizations, $user) {
				$history = new QueryHistory($server->submitQueryString((string) $query, $authorizations));
				$history->user_id = $user->id;
				$history->query_structure = json_encode($query);
				$this->node->history()->save($history);	
				return $history;
			});	
			

			$this->outputs->each(function($output) use ($queryResults) {
				$queryResults->each(function($history) use ($output) {
					$values = $history->getOutput($output->path);
					$output->value = json_encode($values);
					$output->save();
				});
			});

			$this->resolved = true;
			$this->save();
		}		

	}


	public static function fromTopologyNode(Stage $stage, $topologyNode) {
		
		$node = new MetaQueryNode();
		$node->resolved = false;
		$node->topology_id = $topologyNode->id->topology;
		$node->node_type = 'query';
		$node->node_id = $topologyNode->id->query;

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

