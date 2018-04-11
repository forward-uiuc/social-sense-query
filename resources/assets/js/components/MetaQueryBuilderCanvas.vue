<template>
	<div id="d3ne" ref="d3ne" class="node-editor" style="width: auto; height: 500px; border:1px solid black"></div>
</template>

<script>
import * as D3NE from 'd3-node-editor';
import 'd3-node-editor/build/d3-node-editor.css';


export default {
	name: 'metaQueryBuilderCanvas',
	data: function() {
		return {
			editor: null,
			components: {},
			sockets: {},
			serializedCanvas: {},
		}
	},
	created: function () { 
		let numberSocket = new D3NE.Socket('number', 'Number', 'This represents a numeric value, either an integer or a floating point value.')
		this.sockets = {
			'Int': numberSocket,
			'Float': numberSocket,
			'String': new D3NE.Socket('string', 'String', 'This represents a string.'),
			'Boolean': new D3NE.Socket('bool', 'Boolean', 'This represents a boolean (true or false).')
		}

		// Numbers and booleans can be coerced into a string
		numberSocket.combineWith(this.sockets.String);
		this.sockets.Boolean.combineWith(this.sockets.String);
	},
	methods: {
		buildComponent: function(query) {

			let queryInputs = this.getInputs(query.structure, '');
			let queryOutputs = this.getOutputs(query.structure, '');

			let sockets = this.sockets;
			let comp = new D3NE.Component(query.name, {
				builder(node) {
					let componentInputs = [];
					let componentOutputs = [];

					for(let input of queryInputs) {
						let inputType = input.split(':')[1]
						let inputPath = input.split(':')[0]
						let d3Input = new D3NE.Input(inputType + ':' + inputPath, sockets[inputType])
						d3Input.meta = input;
						node.addInput(d3Input)
					}
					
					for(let output of queryOutputs){
						let outputType = output.split(':')[1].split('*')[0]
						node.addOutput( new D3NE.Output(output, sockets[outputType]));
					}

					return node;
				},
				worker(node, inputs, outputs){
				}
			});
			return {
				component: comp,
				id: query.id,
				inputs: queryInputs,
				outputs: queryOutputs
			}
		},

		addQuery: function(query) {
			// First, check if we need to build this component
			if (Object.keys(this.components).indexOf(query.name) == -1){
				this.components[query.name] = this.buildComponent(query);
				this.editor.components.push(this.components[query.name].component)
			} 
			let component = this.components[query.name].component;
			let node = component.builder(component.newNode())
			this.editor.addNode(node)
		},

		getInputs: function(queryStructureRoot, prefix) {
			if(!queryStructureRoot.selected){
				return [];
			}

			let levelInputs = queryStructureRoot.inputs.map((input) => {
					return prefix + input.name + ':' + input.inputType	
			})

			for(let child of queryStructureRoot.children){
				let inputList = this.getInputs(child, prefix + queryStructureRoot.name + '.');
				if(!inputList.length)
					continue;
					levelInputs = levelInputs.concat(inputList)
				}
			return levelInputs;
		},

		getOutputs: function(queryStructureRoot, prefix) {
				if(!queryStructureRoot.selected){
					return [];
				}

				let output = queryStructureRoot.output;
				let childPrefix = prefix + queryStructureRoot.name;
				let levelOutput = [];

				if (!output.isObject || output.isObject === 'false'){ // If it's an output
					if (!output.isAList || output.isAList === 'false'){
						levelOutput.push(prefix + queryStructureRoot.name + ':' + output.type.name)
					} else {
						levelOutput.push(prefix + queryStructureRoot.name + ':' + output.type.ofType.name + '*')
					}
				} else {
					if(output.isAList === true || output.isAList === 'true'){
						childPrefix += '*';
					}
				}

			for(let child of queryStructureRoot.children){
				let outputList = this.getOutputs(child, childPrefix + '.');

				if(!outputList.length)
					continue;
				levelOutput = levelOutput.concat(outputList)
			}
			return  levelOutput;
		},


		serializeQuery: function(serializedCanvas) {
			let nodes = this.editor.toJSON().nodes;

			let serializedQuery = [];
			for (let nodeID in nodes) {
				let node = nodes[nodeID]; // nodes is a map between an ID to the actual node >:(
				let nodeInfo = this.components[node.title]; // nodeInfo has the inputs as a list of strings and outputs as a list of strings

				// Next, take the internal connections representation and translate that to one that is better for backend processing

				let logicalInputs = node.inputs.map( (input, index) => {
					let inputPath = nodeInfo.inputs[index]
						
					let logicalInput = {};
					logicalInput[inputPath] = null; // @Refactor: Here we will only preserve the first connection to an input. A more general solution
																				 // will preserve all such inputs and have that resolved in the backend
					if(input.connections.length > 0){
						let connection = input.connections[0]; // connection = { node: (topological ID), output: (output index) }
						let outputNode = this.components[nodes[connection.node].title];
						let outputPath = outputNode.outputs[connection.output];
						logicalInput[inputPath] = {topology_id: connection.node, path: outputPath}
					}
					return logicalInput;
				});

				let logicalOutputs = node.outputs.map( (_, index) => {
					return nodeInfo.outputs[index];
				});


				let serializedQueryNode = {
					id: {
						topology: node.id,
						query: nodeInfo.id
					},
					inputs: logicalInputs,
					outputs: logicalOutputs
				}
				serializedQuery.push(serializedQueryNode)
			}

			return serializedQuery;
		},
		
		getSerliazedCanvas: function() {
			return this.editor.toJSON();
		}, 

		getSerializedQuery: function() {
			return this.serializeQuery(this.getSerliazedCanvas());	
		}
	},
	mounted: function() {
		this.editor = new D3NE.NodeEditor('someName@1.0.0', this.$refs.d3ne, [], null);
		this.engine = new D3NE.Engine('someName@1.0.0', this.editor.components);

		var editor = this.editor;
		var engine = this.engine;

		var serializeQuery = this.serializeQuery;
		var serializedCanvas = this.serializedCanvas;

		this.editor.eventListener.on('change', async () => {
			await engine.abort();
			await engine.process(editor.toJSON());
			serializedCanvas = editor.toJSON();
		});

		this.editor.eventListener.trigger('change');

		this.editor.view.zoomAt(editor.nodes);
		this.editor.view.resize();
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


	.node-editor {
		width: 1500px !important;
		height: 750px !important;
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
