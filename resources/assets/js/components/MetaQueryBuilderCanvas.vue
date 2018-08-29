<template>
	<div id="canvas" ref="canvas" class="node-editor" style="width: 80%; height: 500px; border:1px solid black"></div>
</template>

<script>
//import 'd3-node-editor/build/d3-node-editor.css';
import Rete from 'rete';
import ConnectionPlugin from 'rete-connection-plugin';
import AlightRenderPlugin from 'rete-alight-render-plugin';
import * as MinimapPlugin from 'rete-minimap-plugin';
import * as ContextMenuPlugin from 'rete-context-menu-plugin';

export default {
	name: 'metaQueryBuilderCanvas',
	props: ['initialState'],
	data: function() {
		return {
			editor: null,
			serializedCanvas: {},
		}
	},
	methods: {
		registerComponent: function(comp) {
			this.editor.register(comp);
			this.engine.register(comp);
		},
		addBuiltComponent: async function(component) {
			let node = await component.createNode().then( node => {
				this.editor.addNode(node);
				return node;
			});
			return node
		},
		connectNodes(node1, node2, outputPath, inputPath) {
			let source = node1.outputs.get(outputPath);
			let destination= node2.inputs.get(inputPath);
			this.editor.connect(source, destination);
		},
		serializeQuery: function(serializedCanvas) {
			let editorState = this.editor.toJSON();
			let nodes = editorState.nodes;

			let serializedQuery = {
				nodes: [],
			};

			for (let nodeID in nodes){
				let topologyNode = nodes[nodeID];
				let metaQueryNode = topologyNode.data;
	
				// First, represent the node as its toplogy, inputs, outputs
				let serializedQueryNode = {
					topology_id: topologyNode.id,
					type_name: metaQueryNode.type_name,
					type_id: metaQueryNode.type_id,
					inputs: metaQueryNode.inputs,
					outputs: metaQueryNode.outputs,
					dependencies: [],
					state: topologyNode.data
				}
				// second, identify the connections that the inputs have
				// as dependencies for the backend
			

				for (let inputPath in topologyNode.inputs) {
				
					let dependency = topologyNode.inputs[inputPath];
					if (dependency.connections.length == 0 ){
						continue;
					}

					let connections = dependency.connections;
					connections.forEach((connection) => {
						serializedQueryNode.dependencies.push({
							output_path: nodes[connection.node].data.outputs.find( path =>  {
								return path.includes(connection.output);
							}),
							input_path: metaQueryNode.inputs.find( path => {
								return path.includes(inputPath)
							}),
							output_topology_node_id: connection.node, //what topology node id does this one depend on
							input_topology_node_id: serializedQueryNode.topology_id // this node's topology id
						});
					});
				}
				serializedQuery.nodes.push(serializedQueryNode);
			}

			return serializedQuery;
		},
		
		getSerliazedCanvas: function() {
			return this.editor.toJSON();
		}, 

		getSerializedQuery: function() {
			return this.serializeQuery(this.getSerliazedCanvas());	
		},

		restoreFromSerializedState: async function() {
			let canvas = JSON.parse(this.initialState.canvas);
			this.editor.fromJSON(canvas)
		}
		
	},
	mounted: function() {
		this.editor = new Rete.NodeEditor('MetaQueryBuilder@1.0.0', this.$refs.canvas);
		this.editor.use(AlightRenderPlugin);
		this.editor.use(ConnectionPlugin, {curvature: 0.4});
		this.editor.use(MinimapPlugin);
		this.editor.use(ContextMenuPlugin);
		this.engine = new Rete.Engine('MetaQueryBuilder@1.0.0');
	}
}
</script>

<style scoped>
>>> .socket.number{
    background: #96b38a
  }

>>> .socket.string{
    background: #96b38a
  }

>>> .socket.bool{
    background: #96b38a
  }


>>> .socket.any {
    background: #96b38a
}


>>> .node.selected {
	background: #E84A27 !important;
	border-color: #13294b !important;
}

>>> .node.selected .title {
	color: white!important;
}

>>> .node {
	background:  	#13294b !important;
	border-color: #E84A27 !important;
}
</style>
