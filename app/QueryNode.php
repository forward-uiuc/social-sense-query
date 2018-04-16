<?php

namespace App;

class QueryNode 
{

	/*
	 * @var $children [QueryNode] Query nodes that are childs of this node
	 */
	protected $children;

	/*
	 * @var $output Output The output of this node
	 */
	protected $output;

	/*
	 * @var $input array The inputs to this node
	 */
	protected $inputs;

	/*
	 * @var $name string The name of this query node
	 */
	protected $name;

	/*
	 * @var $selected boolean Whether this query node has been selected or not
	 */
	protected $selected;

	public function __construct(string $name, array $inputs, Output $output, array $children, bool $selected) 
	{
		$this->name = $name;
		$this->inputs = $inputs;
		$this->output = $output;
		$this->children = $children;
		$this->selected = $selected;
	}

	public function applyValue($path, $value) {
		dd($path, $value, $this);
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
				$queryString .= (string) $child;
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
			return $this->name . ': "' . $this->value . '"';
		case "Boolean":
			return $this->name .': ' . (string) $this->value;
		case "Int":
		case "Float":
			return $this->name .': ' . $this->value;
		default:
			return $this->inputType;
		}
	}

	/*
	 * @var $name string The name of this input type, ex. 'query'
	 */
	protected $name;

	/*
	 * @var $description string The description of the input, ex: 'A query used to search'
	 */
	protected $description;	
	
	/*
	 * @var $inputType string A string representing what type of input this is, ex: 'String', 'Boolean', 'Number'
	 */
	protected $inputType;

	/*
	 * @var $value What value this input has, ex: 5, 'foo', true
	 */
	protected $value;

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
	protected $type;

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

