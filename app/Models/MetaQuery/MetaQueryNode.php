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

			
		if($this->node_type === 'query') {
			$server = app(\App\Services\GQLServerService::class);
			$user = $this->stage->run->metaQuery->user;
			$authorizations = $user->authorizations;


			// If we're trying to resolve a meta query node representing a query 
			$baseQuery = $this->node->getQueryNode(); // base query is the singe query that this node represents

			$queriesToResolve;

			if($this->dependencies->count() > 0) {

				$queriesToResolve = collect([collect([])]);

				$objectDependencies = $this->dependencies->filter(function($dependency){
					return gettype($dependency->output->value) == 'array';
				});

				$queriesToResolve = $objectDependencies->map(function($objectDependency) use ($baseQuery) {
					$path = $objectDependency->input->path;
					$values = json_decode($objectDependencyValues->output->value);

					return collect($values)->map(function($value) use ($path, $baseQuery){
						$q = clone ($baseQuery);
						$q->applyValue($path, $value);
						return $q;
					});
				});

				if($objectDependencies->count() == 0){
					$queriesToResolve = collect([collect([$baseQuery])]); 
				}

				$scalarDependencies = $this->dependencies->filter(function($dependency){
					return gettype($dependency->output->value) != 'array';
				});

				// For each collection of queries
				$queriesToResolve = $queriesToResolve->map(function($querySet) use ($scalarDependencies) {
					// for each query in the collection
					return $querySet->map(function($query) use ($scalarDependencies) {

						// For each scalar dependency of this query node
						return $scalarDependencies->map(function($dependency) use ($query) {		
							$inputPath = $dependency->input->path;
							$values = json_decode($dependency->output->value);

							// For each value of that dependency's output
							return collect($values)->map(function($v) use ($inputPath, $query) {
								$q = clone $query;
								$q->applyValue($inputPath, $v);
								return  $q;
							});
						})->collapse();
					})->collapse();
				});

			} else {
				$queriesToResolve = collect([collect([$baseQuery])]); 
			}

			
				
			$results = $queriesToResolve->map(function($querySet) use ($server, $authorizations){
				return $querySet->map(function($query) use ($server, $authorizations){
					$history = new QueryHistory($server->submitQueryString((string) $query, $authorizations));
					$history->query_structure = json_encode($query);	
					$history->user_id = $this->node->user->id;
					$this->node->history()->save($history);
					return $history;
				});
			});


			$this->outputs->each(function($output) use ($results){



				$values = $results->map(function($queryResultSet) use ($output){
					return $queryResultSet->map(function($result) use ($output){
						return $result->getOutput($output->path);
					});
				})->collapse();


				if($values->count() == 1) {
					$values = $values->collapse();
				}
				$output->value = json_encode($values);
				$output->save();
			});


			$this->resolved = true;
			$this->save();

		} else if ($this->node_type === 'function') {
			
			$values = $this->dependencies->map(function($dependency) {
					return json_decode($dependency->output->value);
			})->collapse()->toArray();


			$this->outputs->each(function($output) use ($values) {
				$output->value = json_encode($this->node->computeOutput($output->path, $values));
				$output->save();
			});


			$this->resolved = true;
			$this->save();
		}		

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

