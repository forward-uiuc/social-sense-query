<?php 

namespace App;

use App\Query;
use App\QueryNode;
use App\Services\GQLServerService;

class MetaQueryNode
{

	// $id is an integer that represents this node's unique identifier in a meta query topology
	public $id;

	// @var $query \App\QueryNode a query node that represents this query's structure
	public $query;

	// $resolved is a boolean representing if this node has had it's queried resolved (allowing us to get the data)
	public $resolved = false;

	// $dependencies is a collection of objects with an id attribute (the identifier of a MetaQueryNode) and an output attribute (a path that is in that output)
	public $dependencies;

	// $inputs is an array of strings that denote where in an input query we have to provide some parameters
	public $inputs;

	// $outputs is an array of strings that denote the paths of output values of this query node
	public $outputs;

	// @var $data App\QueryHistory the resulting data from submitting this query, expected to be null if this hasn't been resolved
	public $data;

	// @var $queries a collection of query nodes that this node must resolve
	private $queries;

	/**
	 * @constructor
	 * @param $toplogyNode an object being stored in an App\MetaQuery toplogy. The topology will typically have many of these
	 */
	public function __construct($topologyNode){	
		$this->id = $topologyNode->id->topology;

		$query = QueryNode::deserialize(json_decode(Query::findOrFail($topologyNode->id->query)->first()->structure));
		$this->queries = collect([$query, $query]);

		$this->outputs = collect($topologyNode->outputs);

		// This dependencies is an object with an id property corresponding to a topology ID
		// and an output property, corresponding to an output on that MetaQueryNode
		$this->dependencies = collect($topologyNode->inputs)->filter(function($input) {
			$path = key($input);
			return $input->$path != null;
		})->map(function($input){
			$path = key($input); // PATH: The input path of where we need to apply this value
			//    'id' => the topology id that we depend on,   'output' => The output path that we depend on
			//          'input' => Where to apply that value
			dd($input);
			$input = (object) ['id' => $input->$path->topology_id, 'output' => $input->$path->path, 'input' => $path];
			return $input;
		});

		// This inputs is an array of paths 
		$this->inputs = collect($topologyNode->inputs)->map(function($input){
			return key($input);
		});
	}

	public function resolve($authorizations) {
		$server = app(\App\Services\GQLServerService::class);

		$this->data = $this->queries->map(function($query) use ($authorizations, $server){
			$response = (object) $server->submitQueryString( (string) $query, $authorizations);
			$response->data  = json_decode($response->data)->data;
			return $response;
		});
		// I am so sorry
		// $this->data is an object that is the result of submitting this query 
		//   (ie has a duration, data response, and 'has_error' property showcasing if
		//    this query has an error)
		// 
		// $this->data->data is the actual response, which also happens to have
		// a data attribute. To make things simpler, we're just grabbing that attribute
		// here

		$this->resolved = true;
	}

	/*
	 * Collect all of the output of a variable given an output path of this node.
	 */
	public function getOutput($path) {
		if (!$this->resolved) {
			throw new Exception("Cannot get the output path ${$path} of an unresolved query");
		} else if (!in_array($path, $this->outputs->toArray())) {
			throw new Exception("Output path ${$path} is not an output of this node");
		}	

		// Example of a $path:
		// query.reddit.searchSubredditNames:String*
		//  '*' can be at any level (ex query.reddit*.searchSubredditNames:String*)
		//  a '*' denotes that output at that level can be a list
		$attributes = explode('.', $path);	

		$data = $this->data->map(function($response) {
			return $response->data;
		});

		foreach($attributes as $currentLevel) {
			// Remove the first attribute, always guarenteed to be 'query'
			if(array_search($currentLevel, $attributes) === 0) {
				continue;
			} 
			
		
			// The last attribute will have a ':', which we don't want and we have to handle as a special case
			if (array_search($currentLevel, $attributes) === count($attributes) -1) {
				$currentLevel = explode(':', $currentLevel)[0];
			}

			if ( MetaQueryNode::getEndOfString($currentLevel) == '*'){
				$currentLevel = explode('*', $currentLevel)[0];
			}
			
			$data = $data->map(function($value) use ($currentLevel) {
				return $value->$currentLevel;
			});
		}

		return $data;
	}

	private static function getEndOfString($string) {
		return substr($string, strlen($string)-1, strlen($string));
	}



	/*
	 * Apply the output of a MetaQueryNode to the input of this MetaQueryNode
	 */
	public function apply(MetaQueryNode $input) {
		if(!$input->resolved) {
			throw new Exception("Cannot apply the input of a MetaQueryNode if it has not been resolved");
		} else if ($input->data == null) {
			throw new Exception("Cannot apply the input of a MetaQueryNode if it has no data");
		}

		// First, find the dependency that this query node represents
		$this->dependencies->filter(function($dependency) use ($input){
			return $dependency->id == $input->id;

		})->each(function($dependency) use ($input) {
			$values = $input->getOutput($dependency->output)->flatten();

			$this->queries = $this->queries->map(function($query) use ($values, $dependency) {
				$path = explode(":", $dependency->input)[0];
				return $values->map(function($value) use ($query, $path) {
					$newQuery = clone $query;
					$newQuery->applyValue($path, $value);
					return $newQuery;
				});
			})->flatten();
			
			dd($this->queries);
		});
	}
}
