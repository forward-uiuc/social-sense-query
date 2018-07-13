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
			components: [], 
			sockets: {},
			serializedCanvas: {},
		}
	},
	created: function () { 
		let numberSocket = new D3NE.Socket('number', 'Number', 'This represents a numeric value, either an integer or a floating point value.')
		let anySocket = new D3NE.Socket('any', 'Any', 'This input can take any set of values')

		this.sockets = {
			'Int': numberSocket,
			'Float': numberSocket,
			'String': new D3NE.Socket('string', 'String', 'This represents a string.'),
			'Boolean': new D3NE.Socket('bool', 'Boolean', 'This represents a boolean (true or false).'),
			'Any': anySocket 
		}

		// Numbers and booleans can be coerced into a string
		numberSocket.combineWith(this.sockets.String);
		this.sockets.Boolean.combineWith(this.sockets.String);

		Object.keys(this.sockets).forEach( socketName => {
			let socket = this.sockets[socketName];
			socket.combineWith(this.sockets.Any);
		});

	},
	methods: {
		buildQueryComponent: function(query) {
			console.log(query);
			let queryInputs = this.getInputs(query.structure, '');
			let queryOutputs = this.getOutputs(query.structure, '');
			
			let sockets = this.sockets;
			let comp = new D3NE.Component(query.name, {
				builder(node) {

					for(let input of queryInputs) {
						let inputType = input.split(':')[1].split('*')[0]
						let inputPath = input.split(':')[0]

						let d3Input = new D3NE.Input(inputType + ':' + inputPath, sockets[inputType])
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
				name: query.name,
				id: query.id,
				type: 'query',
				inputs: queryInputs,
				outputs: queryOutputs
			}
		},
		buildFunctionComponent: function(func) {
			let inputs = JSON.parse(func.inputs);
			let outputs = JSON.parse(func.outputs);
			let sockets = this.sockets;

			let comp = new D3NE.Component(func.name, {
				builder(node) {
					for (let input of inputs) {

						let inputType = input.split(':')[1].split('*')[0];
						let inputPath = input.split(':')[0];

						let d3Input = new D3NE.Input(inputType + ':' + inputPath, sockets[inputType]);
						node.addInput(d3Input)
					}

					for (let output of outputs) {
						let outputType = output.split(':')[1].split('*')[0];
						console.log(outputType);
						node.addOutput( new D3NE.Output(output, sockets[outputType]));
					}

					return node;
				},
				worker(node, inputs, outputs) {
				}
			});
			
			return {
				component: comp, 
				id: func.id,
				name: func.name,
				type: 'function',
				inputs: inputs,
				outputs: outputs
			}
		},

		addQuery: function(query) {
			// First, check if we need to build this component
			let componentMetaData= this.components.find(function(comp){
				return comp.id == query.id && comp.type == 'query';
			})

			if (!componentMetaData ){
				componentMetaData = this.buildQueryComponent(query);
				this.components.push(componentMetaData);
				this.editor.components.push(componentMetaData.component);

			} 

			let component = componentMetaData.component;
			let node = component.builder(component.newNode())
			this.editor.addNode(node);
		},

		addFunction: function(func) {
			// First, check if we need to build this component
			let componentMetaData  = this.components.find(function(comp) {
				return comp.id == func.id && comp.type == 'function'
			});

			if ( !componentMetaData ){
				componentMetaData = this.buildFunctionComponent(func);
				this.components.push(componentMetaData);
				this.editor.components.push(componentMetaData.component);
			} 

			let component = componentMetaData.component;
			let node = component.builder(component.newNode())

			
			this.editor.addNode(node)
		},

		getInputs: function(queryStructureRoot, prefix) {
			if(!queryStructureRoot.selected){
				return [];
			}

			prefix = prefix + queryStructureRoot.name + '.';
			let levelInputs = queryStructureRoot.inputs.map((input) => {
					return prefix + input.name + ':' + input.inputType.name
			})

			for(let child of queryStructureRoot.children){
				let inputList = this.getInputs(child, prefix);

				if(!inputList.length) 
					continue;

					levelInputs = levelInputs.concat(inputList)
			}
			console.log(levelInputs);
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

				let nodeInfo = this.components.find( metaData => {
					return metaData.name === node.title;
				});
					
				// Next, take the internal connections representation and translate that to one that is better for backend processing

				let logicalInputs = node.inputs.map( (input, index) => {
					let inputPath = nodeInfo.inputs[index]
						
					let logicalInput = {};
					logicalInput[inputPath] = null; // @Refactor: Here we will only preserve the first connection to an input. A more general solution
																				 // will preserve all such inputs and have that resolved in the backend
					if(input.connections.length > 0){
						let connection = input.connections[0]; // connection = { node: (topological ID), output: (output index) }

						let outputNode = this.components.find( metaData => {
							return metaData.name == nodes[connection.node].title
						});
							
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
						type: nodeInfo.type,
						id: nodeInfo.id
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


>>> .socket.any {
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
