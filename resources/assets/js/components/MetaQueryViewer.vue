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
					<d3-network :net-nodes="nodes" :net-links="links" :options="options" v-on:node-click="clickNode" style="border-style:solid">
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

			run.stages.forEach( (stage, index) => {
				this.nodes.push({id: stage.id, name:"Stage" + index, _color: 'red', type:'stage', data: stage, index: index});

				stage.history.forEach( queryHistory => {
					this.nodes.push({id: ++id, name: "Query", _color: 'black', type:'query', data: queryHistory.dataObject });
					this.links.push({sid: id, tid: stage.id}); 
				});
			});

			for(let i=0; i < run.stages.length-1; i++){
				this.links.push({sid: run.stages[i].id, tid: run.stages[i+1].id});
			}

		},
		clickNode: function(event, node) {
			if(node.type == 'stage') {
				this.dataToExamine = {
					stage: node.index,
					queries: node.data.history.map( history => {
						return JSON.parse(history.data);
					})
				}
			} else {
				this.dataToExamine = node.data;
			}
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
	>>> .link {
		marker-end: "arrow"
	}
<style scoped>
</style>
