<?php
namespace App\Models\MetaQuery\Functions;

abstract class AbstractFunction
{
	protected $state;
	public function __construct($state) 
	{
		$this->state = $state;
	}

	public static abstract function getName() : string;
	public static abstract function getInputs(): array;
	public static abstract function getOutputs(): array;
	public abstract function evaluate($values): array;
}
