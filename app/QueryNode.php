<?php

namespace App;

class QueryNode 
{

	/*
	 * @var $children [QueryNode] Query nodes that are childs of this node
	 */
	public $children;

	/*
	 * @var $output Output The output of this node
	 */
	public $output;

	/*
	 * @var $input array The inputs to this node
	 */
	public $inputs;

	/*
	 * @var $name string The name of this query node
	 */
	public $name;

	/*
	 * @var $selected boolean Whether this query node has been selected or not
	 */
	public $selected;

	public function __construct(string $name, array $inputs, Output $output, array $children, bool $selected) 
	{
		$this->name = $name;
		$this->inputs = $inputs;
		$this->output = $output;
		$this->children = $children;
		$this->selected = $selected;
	}


	// If this class is cloned, clone its subclasses as well
	public function __clone()
	{
		$this->inputs = collect($this->inputs)->map(function($input){
			return clone $input;	
		})->toArray();
		$this->output = clone $this->output;
		$this->children = collect($this->children)->map(function($child) {
			return clone $child;
		})->toArray();
	}


	public function applyValue($path, $value) {
		if(!$this->selected){
			return false;
		}


		$path = explode('.', $path);
		$name = array_shift($path);

		if($this->name != $name) {
			return false;
		}


		$toReturn = false;

		if(count($path) == 1 ) {
			$inputToReplace = $path[0];

			foreach($this->inputs as $input) {
				if($input->name == $inputToReplace) {
					$input->value = $value;
					$toReturn = true;
				}
			}
		} else {
			foreach($this->children as $child) {
				$toReturn |= $child->applyValue(implode('.', $path), $value);
			}			
		}

		return $toReturn;


	
		
		
	}


	public function __toString() 
	{
		if(!$this->selected) {
			return "";
		}

		$queryString = $this->name;

		if (count($this->inputs) > 0)
		{
			$queryString .= "(" . collect($this->inputs)->map(function($input) {
				return (string) $input;
			})->implode(", ") . ")";			
		}

		if(! $this->output->isScalar) {
			$queryString .= "{ ";

			foreach($this->children as $child) {
				if((string) $child != "") {
					$queryString .= (string) $child . " ";
				}
			}

			$queryString .= "} "	;
		}

		return $queryString;
	}



	/*
	 * Returns a QueryNode representation of a query structure
	 * @param $structure a json_decoded instances of a query's structure
	 */
	public static function deserialize($structure) 
	{
		if(!$structure) {
			return null;
		}

		$inputs = [];
		foreach($structure->inputs as $input) {
			array_push($inputs, new Input($input->name, $input->description, $input->inputType, $input->value));
		}

		$output = new Output($structure->output);

		$children = [];
		foreach($structure->children as $child) {
			array_push($children, QueryNode::deserialize($child));
		}

		return new QueryNode($structure->name, $inputs, $output, $children, $structure->selected);
	}
}



class Input 
{

	public function __toString() 
	{
		switch($this->inputType) {
		case "String":

			return $this->name . ': "' . $this->cleanInput($this->value ? $this->value : '') . '"';
		case "Boolean":
			return $this->name .': ' . (string) $this->value;
		case "Int":
		case "Float":
			return $this->name .': ' . $this->value;
		default:
			return $this->inputType;
		}
	}

	private function cleanInput(string $input) {
		$value = str_replace('"', '\\"', $input); // First, escale double quoates
		$value = str_replace("\n", '', $value);  // Second, escape end line carriages and carriage returns
		$value = str_replace("\r", '', $value);
		$value = str_replace("\t", '', $value); // Last, replace tabs 
		return $value;
	}

	/*
	 * @var $name string The name of this input type, ex. 'query'
	 */
	public $name;

	/*
	 * @var $description string The description of the input, ex: 'A query used to search'
	 */
	public $description;	
	
	/*
	 * @var $inputType string A string representing what type of input this is, ex: 'String', 'Boolean', 'Number'
	 */
	public $inputType;

	/*
	 * @var $value What value this input has, ex: 5, 'foo', true
	 */
	public $value;


	public function __construct(string $name, $description, string $inputType, $value) {
		$this->name = $name;
		$this->description = $description;
		$this->inputType = $inputType;
		$this->value = $value;
	}
}


class Output
{
	/*
	 * @var $type an instance of a GraphQL ouput type
	 */
	public $type;

	/*
	 * @var $isScalar boolean is the output type of this node a scalar 
	 */
	public $isScalar = false;

	/*
	 * @var $isList  boolean is the output of this node a list
	 */
	public $isList = false;

	/*
	 * @var $isObject boolean is the output of this node an object
	 */
	public $isObject = false;

	public function __construct($output) 
	{
		$this->type = $output->type;
		$this->isList = $this->type->kind === 'LIST';

		if($this->isList) {
			$this->isScalar = $this->type->ofType->kind === 'SCALAR';
			$this->isObject = $this->type->ofType->kind === 'OBJECT';
		} else {
			$this->isScalar = $this->type->kind === 'SCALAR';
			$this->isObject = $this->type->kind === 'OBJECT';
		}
	}
}

