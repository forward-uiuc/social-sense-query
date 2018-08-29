<?php

namespace App\Models\MetaQuery;

use Illuminate\Database\Eloquent\Model;

use App\Models\Query\QueryHistory;
use App\Models\User;
use App\Models\Query\Query;
use App\Models\MetaQuery\MetaQueryFunction;

class MetaQuery extends Model
{

	protected $fillable = ['schedule','canvas','topology','name'];
	protected $table = 'meta_queries';
	protected $appends = ['inputs','outputs'];

	/*
	 * Get the user this MetaQuery belongs to
	 */
	public function user() {
		return $this->belongsTo(User::class);
	}


	/*	
	* Get the runsof this query. T
	*/
	public function runs() {
		return $this->hasMany(Run::class);
	}


	private function getNodeModel ($node) {
		switch ($node->type_name) {
			case 'query':
				return Query::where('id', $node->type_id)->firstOrFail();
			case 'function':
				return \App::make('App\Repositories\Contracts\MetaQueryFunctionRepositoryInterface')->findById($node->type_id);
			case 'meta-query': 
			case 'meta_query':
			case 'metaQuery':
				return MetaQuery::where('id', $node->type_id)->firstOrFail();
		}
	}

	// The inputs of a MetaQuery are all inputs within the nodes
	// that do not have a dependency
	public function getInputsAttribute() {
		$nodes = collect(json_decode($this->topology)->nodes);

		// First, find all the inputs that are part of a dependency
		$inputsToPrune = $nodes->map(function($node) {
			return collect($node->dependencies)->map(function($dependency) {
				return (object) ['path' => $dependency->input_path, 'topology_id' => $dependency->input_topology_node_id];
			});
		})->collapse();

		// next, return all the inputs that are not part of this collection
		$inputsWithoutDependencies = collect($nodes)->map(function($node) use ($inputsToPrune) {
			$toPrune = $inputsToPrune->filter(function($entry) use ($node){
				return $entry->topology_id === $node->topology_id;
			})->pluck('path');

			return collect($node->inputs)->diff($toPrune)->map(function($input) use ($node){
				return '"'. $this->getNodeModel($node)->name .'".' . $node->topology_id . '.' . $input;
			});
		})->collapse();

		return $inputsWithoutDependencies;
	}

	// The outputs of a MetaQuery are all inputs within the nodes
	// that do not have a dependency
	public function getOutputsAttribute() {
		$nodes = collect(json_decode($this->topology)->nodes);

		// First, find all the inputs that are part of a dependency
		$outputsToPrune = $nodes->map(function($node) {
			return collect($node->dependencies)->map(function($dependency) {
				return (object) ['path' => $dependency->output_path, 'topology_id' => $dependency->output_topology_node_id];
			});
		})->collapse();

		// next, return all the outputs that are not part of this collection
		$outputsWithoutDependencies = collect($nodes)->map(function($node) use ($outputsToPrune) {
			$toPrune = $outputsToPrune->filter(function($entry) use ($node){
				return $entry->topology_id === $node->topology_id;
			})->pluck('path');

			return collect($node->outputs)->diff($toPrune)->map(function($output) use ($node){
				return '"'. $this->getNodeModel($node)->name .'".' . $node->topology_id . '.' . $output;
			});
		})->collapse();

		return $outputsWithoutDependencies;
	}
}
