<?php


namespace App\Repositories;

use Symfony\Component\ClassLoader\ClassMapGenerator;
use App\Repositories\Contracts\MetaQueryFunctionRepositoryInterface; 

// MetaQueryFunctions as represented by classes defined in Models
class FileMetaQueryFunctionRepository implements MetaQueryFunctionRepositoryInterface
{
	protected $functionClasses;

	// Build a collection of id, name, inputs, outputs from functions 
	public function __construct($filePath = '') 
	{
		$functionClassPath = $filePath ? $filePath : app_path('Models/MetaQuery/Functions');		
		$classes = collect(ClassMapGenerator::createMap($functionClassPath));

		$values = $classes->filter(function($_, $className) {
			// Ignore abstract classes
			$instance = new \ReflectionClass($className);
			return !$instance->isAbstract();
		})->map(function($classPath, $className) {

			return (object) ['id' => hash('md5', $classPath), 
							 'name' => $className::getName(),
							 'inputs' => $className::getInputs(),
							 'outputs' => $className::getOutputs(),
							 'className' => $className
							];
		});


		$this->functionClasses = collect(array_values($values->toArray()));
	}

	public function all() {
		return $this->functionClasses;
	}

	public function findById($id) {
		return $this->functionClasses->first(function($class) use ($id) {
			return $class->id === $id;
		});
	}
}	
