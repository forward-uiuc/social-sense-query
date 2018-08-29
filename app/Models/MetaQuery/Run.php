<?php

namespace App\Models\MetaQuery;

use Illuminate\Database\Eloquent\Model;

class Run extends Model
{
	protected $fillable = ['topology'];

	/*
	 * Get which metaQuery this run belongs to
	 */
	public function metaQuery() {
		return $this->belongsTo(MetaQuery::class);
	}

	/*
	 * Get all the stages that this run had
	 */
	public function stages() {
		return $this->hasMany(Stage::class);
	}

	/* 
	* Resolve all the nodes in this run
	*/
	public function resolve() {
		foreach ($this->stages as $stageIndex => $stage) {
			$stage->nodes->each(function($node) {
				$node->resolve();
			});

			if($stageIndex < ($this->stages->count() - 1)) {
				$nextStage = $this->stages[++$stageIndex];
			
				$nextStage->nodes->each(function($node){ 
					$node->setStatus('ready');
				});	
			}
		}
	}
}

