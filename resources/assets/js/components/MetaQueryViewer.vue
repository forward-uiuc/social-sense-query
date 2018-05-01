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
								<div class="btn btn-secondary" v-on:click="show(run)"> Show data </div>
							</td>
						</tr>
					</tbody>
			</table>
			</div>

			<div class="col-md-8">
				<d3-tree-view ref="treeView" :data="visibleData"></d3-tree-view>
			</div>
			
		</div>
	</div>
</template>

<script>

export default {
	name: 'queryViewer',
	props: ['query', 'deleteFormId'], 
	data: () => {
		return {
			visibleData: {text:'result', children: []}
		}
	},
	methods: {
		isScalar (data) {
			return typeof(data) !== 'object' && !Array.isArray(data);
		},
		convertOutputValueToTree: function(value) {
			if (this.isScalar(value)) {
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
				selected: true,
				_children: []
			}	

			for(let index in value) {
				let childNode = this.convertOutputValueToTree(value[index]);
				childNode.text = index + ": " + childNode.text ;
				node._children.push(childNode);
			}	

			return node;
		},
		convertStageToTree: function(stage) {
			let node = {color: 'white', position: 'right', children: []};
			if (stage.nodes.length == 0 ) {
				return  node;
			}	

			node.color = 'black';

			node.children = stage.nodes.map( queryNode => {
				let childNode = {
					position: 'left',
					text: '(' + queryNode.topology_id + ') ' + queryNode.node.name
				}

				if (childNode.node_type === 'function') {
					childNode.color = 'red'
				} else {
					childNode.color = 'lightsteelblue';
				}
				
				let childNodeDependencyIds = queryNode.dependencies.map( dependency => {
					return dependency.output.node.topology_id
				});
				
				if (childNodeDependencyIds.length != 0 ){
					childNode.text += ' *(' + childNodeDependencyIds.join(', ') + ')'
				}	
					
				childNode.children = queryNode.outputs.map( output => {
					let outputNode = {
						text: output.path.split('.').pop(),
						position: 'left',
						color: 'grey',
						selected: true,
						_children: []
					}

					let outputValue = JSON.parse(output.value);
					for(let childIndex in outputValue){
						let valueNode = this.convertOutputValueToTree(outputValue[childIndex]);
						valueNode.text = childIndex + ": " + valueNode.text;
						outputNode._children.push(valueNode)
					}
					
					return outputNode;
				});	
				
				return childNode;
			});

			return node;
		},
		show: function(run) {
			this.visibleData =  {
				text: run.created_at,
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
				
		},
		deleteQuery () {
			document.getElementById(this.deleteFormId).submit();
		},
		submitQuery () {
			location.replace('/meta-queries/' + this.query.id + '/submit')
		},
		editQuery () {
			location.replace('/queries/' + this.query.id + '/edit')
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
	}
}

</script>

<style scoped>

</style>
