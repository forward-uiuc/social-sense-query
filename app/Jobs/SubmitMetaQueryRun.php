<?php

namespace App\Jobs;

use App\Models\MetaQuery\Run;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SubmitMetaQueryRun implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	/**
	 *
	 * The number of times the job may be attempted.
	 *
	 * @var int
	 */ 
	public $tries = 5;

	protected $run;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Run $run)
    {
		$this->run = $run;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
		try {
			$this->run->stages->each(function($stage) {
				$stage->nodes->each(function($node) {
					$node->resolve();
				});
			});
		} catch (\Exception $e) {
			$this->release(60*5); // Try again in 5 minutes
			throw $e; // Throw the exception so that this job can eventually fail;
		}
	}
}
