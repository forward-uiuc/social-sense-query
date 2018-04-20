<template>
	<div> 
		<div class="row">
			<div class="offset-md-1">
			<tree-view v-if="dataToExamine" :data="dataToExamine" :options="{maxDepth: 1}"></tree-view> 
			</div>
		</div>
		<div class="row">
			<div class="offset-md-1 col-md-3">
				<h1> {{ query.name }} </h1>
				<button v-on:click="submit" class="btn btn-success"> Run Query </button>
				<h3> Runs </h3>
				<ul class="list-group ">
					<li class="list-group-item list-group-item-action" v-for="run in sortedRuns" v-on:click="view(run)" style="cursor: pointer">
						{{ run.created_at }} 
					</li>
				</ul>
			</div>
				<div class="col-md-8">
					<d3-network :net-nodes="nodes" :net-links="links" :options="options" v-on:node-click="clickNode" v-on:link-click="clickLink" style="border-style:solid">
					</d3-network> 
				</div>
		</div>
	</div>
</template>

<script>

import D3Network from "vue-d3-network";

export default {
	name: 'metaQueryBuilder',
	props: ['query'], 
	data: () => {
		return {
      nodes: [
      ],
      links: [
      ],
      options:
      {
        force: 3000,
        nodeSize: 30,
        nodeLabels: true,
				linkLabels: true,
        linkWidth:10,
				size: {
					h: 1000,
					w: 1000
				},
				canvas: false,
      },
			sortedRuns: [],
			dataToExamine:null,
    }
	},
	methods: {
		view: function(run) {
			let id = 0;
			this.nodes = [];
			this.links = [];

			let index = 0;
			let stages = [];
			run.stages.forEach( stage => {


				let stageNode = {id: index, name: 'Stage ' + Math.abs(index), _size: 60, _color: 'red', _labelClass:'btn btn-info', data: stage, type: 'stage'};
				stages.push(stageNode);
				this.nodes.push(stageNode);


				stage.nodes.forEach( node => {
					this.nodes.push({id: node.topology_id, name: node.node.name, data:node, _size: 50, _labelClass:'btn btn-info', type:'node'});
					this.links.push({sid: node.topology_id, tid: index}); // Add a link from this node to its stage
				});
				index--;
			});

			// Add a link from each node to its dependant node
			run.stages.forEach( stage => {
				stage.nodes.forEach( node => {
					node.dependencies.forEach( dependency => {
						this.links.push({sid: dependency.output.node.topology_id, tid: dependency.input.node.topology_id, data:dependency.output.value});
					});
				});
			});

			for(let i=0; i < stages.length - 1; i++){
				let totalOutputs = [];
				
				stages[i].data.nodes.forEach( node => {
					let outputs = node.outputs.forEach( output => {
						totalOutputs = totalOutputs.concat(JSON.parse(output.value));
					});
				});

				this.links.push({sid: -1*i, tid: -1*(i+1), data:totalOutputs});
			}

		},
		clickNode: function(event, node) {
			if(node.type === 'stage') {
				let data = node.data.nodes.map( node => {
					console.log(node.outputs);
					return node.outputs.map( output => {
						return 	{ 
							path: output.path,
							values: JSON.parse(output.value)
						};
					});
				});

				this.dataToExamine = data;
			} else {

				let display = {};
				let outputs = node.data.outputs;
				outputs.forEach( output => {
					display[output.path] = JSON.parse(output.value);
				});

				this.dataToExamine = display;
			}
		},
		clickLink: function(event, link) {
			this.dataToExamine = link.data
		},
		submit: function() {

		}
	},
	computed: {
	},
	components: {
		D3Network
	},
	mounted () {
		this.sortedRuns = this.query.runs.sort((a,b) => {
			return a.created_at < b.created_at;
		});

	}
}

</script>
<style scoped>

</style>
