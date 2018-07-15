<template>
	<div>

		<div class="row" style="margin-left: 10px; margin-top: -20px">
			<h1> {{ query.name }}  </h1>
		</div>

		<div class="row">
			<div class="col-md-4" style="margin-left: 10px; margin-bottom: 10px">
				<!-- <button class="btn btn-warning" v-on:click="editQuery"> Edit Query </button> -->
				<!-- <button class="btn btn-danger" v-on:click="deleteQuery"> Delete Query </button> -->
				<button class="btn btn-success right" v-on:click="submitQuery"> Submit Meta Query </button>
			</div>
		</div>

		<div class="row">
			<div class="col-md-3" style="height:80vh; overflow-y: scroll; margin-left: 10px">
				<table class="table table-striped table-dark">
					<thead>
						<tr>
							<th scope="col"> Time </th>
							<th scope="col"> Data </th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="(run, index) in runs">
							<td>
									{{ run.created_at }}
							</td>
							<td> 
								<div class="btn btn-secondary" v-on:click="refreshRun(run)"> Show data </div>
							</td>
						</tr>
					</tbody>
			</table>
			</div>

			<div class="col-md-8">
				<d3-tree-view ref="treeView" :data="visibleData" v-on:node-clicked="paginateChildren" v-on:node-right-clicked="updateNode"style="width:4000px; height:800px"></d3-tree-view>
			</div>
			
		</div>
	</div>
</template>

<script>
import Vue from 'vue';

export default {
	name: 'queryViewer',
	props: ['query', 'deleteFormId'], 
	data: () => {
		return {
			visibleData: {text:'result', children: []},
			run: null,
			refreshIntervalId: -1,
			intervalPeriod: 1000
		}
	},
	methods: {
		isScalar (data) {
			return typeof(data) !== 'object' && !Array.isArray(data);
		},
		convertOutputValueToTree: function(value) {

			if (this.isScalar(value) || value === null) {
				let node = {
					text: value ? value : 'null',
					position: 'right',
					color: 'yellow'	
				}
				return node;
			} 			

			if(!Array.isArray(value)){
				console.log(value);
				throw("Value is not scalar or array");
			}	
			
			let node = {
				text: 'values',
				position: 'left',
				color: 'Goldenrod',
				selected: false,
				children: []
			}	

			for(let index in value) {
				let childNode = this.convertOutputValueToTree(value[index]);
				childNode.text = index + ": " + childNode.text ;
				node.children.push(childNode);
			}	

			return node;
		},
		convertStageToTree: function(stage) {
			let displayedStage = {color: 'white', position: 'right', children: [], source:stage, type:'stage'};
			if (stage.nodes.length == 0 ) {
				return  displayedStage;
			}	

			displayedStage.color = 'black';

			displayedStage.children = stage.nodes.map( queryNode => {
				return this.convertMetaQueryNodeIntoDisplayedTreeNode(queryNode);
			});

			return displayedStage;
		},
		convertMetaQueryNodeIntoDisplayedTreeNode (metaQueryNode) {
			
				// First, prepare the node
				let displayedNode = {
					position: 'left',
					text:  metaQueryNode.node.name + ' [Status: ' + metaQueryNode.status + ']',
					source: metaQueryNode,
					type: 'node'
				}
				
				if(metaQueryNode.status === 'error'){
					displayedNode.color = 'red';
				} else {
					displayedNode.color = 'lightsteelblue';
				}
				
				let dependentIDs= metaQueryNode.dependencies.map( dependency => {
					return dependency.output.node.topology_id
				});
				
				if (dependentIDs.length != 0 ){
					displayedNode.text += ' *(' + dependentIDs.join(', ') + ')'
				}	
					

				// next, prepare its children
				displayedNode.children = metaQueryNode.outputs.map( output => {
					let outputNode = { // Create the node
						text: output.path.split('.').pop(),
						position: 'left',
						color: 'grey',
						selected: true,
						_children: []
					}

					
					let outputValue = JSON.parse(output.value);
					for(let childIndex in outputValue){ // add their values
						let valueNode = this.convertOutputValueToTree(outputValue[childIndex]);
						valueNode.text = childIndex + ": " + valueNode.text;
						outputNode._children.push(valueNode)
					}
					
					return outputNode;
				});	
				
				return displayedNode;
		},
		paginate: function(tree, paginationSize) {
			// base case
			let childrenAttrName = tree.children ? 'children' : '_children';
			let childArray = tree[childrenAttrName];

			if (!childArray || childArray.length ==0) {
				return tree;
			}

			// case to not apply pagination
			if(childArray.length <= paginationSize) {
				childArray.forEach( child => {
					this.paginate(child, paginationSize);
				});
				return tree;
			}


			// case to apply pagination
			tree.pages = [];

			while (childArray.length > 0) {
				tree.pages.push(childArray.splice(0, paginationSize));	
			}

			tree.pages.forEach( (pageOfChildren, pageIndex) => {
				pageOfChildren.forEach( child => {
					child.parent = tree;
					this.paginate(child, paginationSize);
				});

				let previousPageIndex = (pageIndex - 1) < 0 ? tree.pages.length - 1 : pageIndex - 1;
				let nextPageIndex = (pageIndex + 1) % tree.pages.length;
				pageOfChildren.unshift({
					text: 'view page ' + previousPageIndex,
					color: 'red',
					changeIndex: previousPageIndex,
					position: 'right',
					role: 'paginatePrevious'
				});
			
				pageOfChildren.push({
					text: 'view page ' + nextPageIndex,
					color: 'red',
					changeIndex: nextPageIndex,
					position: 'right',
					role: 'paginateNext'	
				});
			});	
		
			// last, but not least, set the children of this tree to the first page
			tree[childrenAttrName] = tree.pages[0];
			return tree;
		},
		paginateChildren (childNode) {
			if( !childNode.role) {
				return;	
			}
			childNode.parent.children = childNode.parent.pages[childNode.changeIndex];
		},
		show: function(run) {
			this.run = run;
			this.visibleData =  {
				text: run.created_at,
				source: run,
				type: 'run',
				position: 'left',
				color: 'lightsteelblue',
				selected: false,
				children: [],
			}

			if(run.stages.length == 0) {
				return;
			}	
			
			var stages = run.stages.map( stage => {
				return this.convertStageToTree(stage);
			});
		
			for(let stageIndex in stages) {
				stages[stageIndex].text = 'Stage ' + stageIndex;
				stages[stageIndex].position = 'left';	
				if(stageIndex == 0 ) {
					this.visibleData.children.push(stages[stageIndex]);
				} else {
					stages[stageIndex-1].children.push(stages[stageIndex]);
				}
			}	
			
			this.visibleData = this.paginate(this.visibleData, 10);	
		},
		deleteQuery () {
			document.getElementById(this.deleteFormId).submit();
		},
		submitQuery () {
			location.replace('/meta-queries/' + this.query.id + '/submit')
		},
		editQuery () {
			location.replace('/queries/' + this.query.id + '/edit')
		},
		refreshRun (run) {
			Vue.$http.get('/api/runs/' + run.id).then( response => {
				this.show(response.data);
			}, error => {
				console.log(error);
			});
		},
		updateNode (displayedNode) {
			if (displayedNode.type == 'node') {
				console.log(displayedNode.source.id);
				Vue.$http.get('/api/meta_query_nodes/' + displayedNode.source.id + '/resolve').then( response => {
					displayedNode = this.convertMetaQueryNodeIntoDisplayedTreeNode(response.data);
				}, error => {
					console.log(error);
				});
			} 
		}
	},
	computed: {
		runs() {
			return this.query.runs.sort((a,b) => {
				return new Date(b.created_at) - new Date(a.created_at);
			});
		}
	},
	mounted () {
		this.show(this.runs[0]);
	},
}

</script>

<style scoped>

</style>
