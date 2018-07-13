<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/runs/{id}', function($id) {
		$withProperties = [
			'stages.nodes.inputs',
			'stages.nodes.outputs',
			'stages.nodes.node',
			'stages.nodes.dependencies',
			'stages.nodes.dependencies.input.node',
			'stages.nodes.dependencies.output.node',
		];

	return App\Models\MetaQuery\Run::with($withProperties)->findOrFail($id);
});

Route::get('/meta_query_nodes/{id}', function($id) {
	$withProperties = ['inputs','outputs','dependencies', 'node', 'dependencies.input.node', 'dependencies.output.node'];
	return App\Models\MetaQuery\MetaQueryNode::with($withProperties)->findOrFail($id);
});

Route::get('/meta_query_nodes/{id}/resolve', function($id) {
	$withProperties = ['inputs','outputs','dependencies', 'node', 'dependencies.input.node', 'dependencies.output.node'];
	$node = App\Models\MetaQuery\MetaQueryNode::with($withProperties)->findOrFail($id);
	try { 
		$node->resolve();
	} catch (\Exception $e) {
		report($e);
	}
	return $node;
});
