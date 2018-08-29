import Rete from 'rete';

export default class ComponentBuilder {
	
	
	constructor()  {
		let numberSocket = new Rete.Socket('Number');
		numberSocket.combineWith(numberSocket);

		this.sockets = {
			'Int': numberSocket,
			'Float': numberSocket,
			'String': new Rete.Socket('String'),
			'Boolean': new Rete.Socket('Boolean'),
			'Any': new Rete.Socket('Any')
		}
		
		numberSocket.combineWith(this.sockets.String);
		numberSocket.combineWith(this.sockets.Any);
		this.sockets.Boolean.combineWith(this.sockets.String);
		this.sockets.Boolean.combineWith(this.sockets.Boolean);
		this.sockets.Boolean.combineWith(this.sockets.Any);
		this.sockets.String.combineWith(this.sockets.String);
		this.sockets.String.combineWith(this.sockets.Any);	
		this.sockets.Any.combineWith(this.sockets.String); // Anything can be turned into a string :)	
		
	}

    build(name, inputs, outputs, meta) {
		let sockets = this.sockets;
		let comp = new class extends Rete.Component {

			constructor() {
				super(name)
				this.data = meta;
			}

			builder(node) {
				for(let input of inputs) {
					console.log(input);
					let inputType = input.split(':')[1].split('*')[0]
					let inputPath = input.split(':')[0]
					node.addInput(new Rete.Input(inputPath, inputType + ':' + inputPath, sockets[inputType]))
				}

				for(let output of outputs){
					let outputType = output.split(':')[1].split('*')[0]
					let outputPath = output.split(':')[0]
					node.addOutput( new Rete.Output(outputPath, outputType + ':' + outputPath, sockets[outputType]));
				}

				if(this.data.control) {
					let control = this.data.control;
					
					let value = node.data.value ? node.data.value : control.value; //
					if (control.type === 'Int' || control.type === 'Float') {
						node.addControl(new NumControl(this.editor, 'value', value));
					} else if (control.type === 'String') {
						node.addControl(new StringControl(this.editor, 'value', value));
					} else {
						node.addControl(new BoolControl(this.editor, 'value', value));
					}
				}

				node.data = this.data;
				return node;
			}

		}();

		return comp;
	}

	buildFunctionComponent(func) {
		return this.build(func.name, func.inputs, func.outputs, {
			type_name: 'function', 
			type_id: func.id,
			inputs: func.inputs,
			outputs: func.outputs,
			control: func.control
		});
	}

	buildQueryComponent(query) {
		let queryInputs = this.getInputs(query.structure, '').map( input => {
			return input.path;
		});

		let queryOutputs = this.getOutputs(query.structure, '');
			

		return this.build(query.name, queryInputs, queryOutputs, {
			type_name: 'query', 
			type_id: query.id,
			inputs: queryInputs,
			outputs: queryOutputs
		});	
	}

	buildMetaQueryComponent(meta) {
		return this.build(meta.name, meta.inputs, meta.outputs, {
			type_name: 'meta_query', 
			type_id: meta.id,
			inputs: meta.inputs,
			outputs: meta.outputs
		});
	}


	getInputs(queryStructureRoot, prefix) {
		if(!queryStructureRoot.selected){
			return [];
		}

		prefix = prefix + queryStructureRoot.name + '.';
		let levelInputs = queryStructureRoot.inputs.map((input) => {
			return {
				path: prefix + input.name + ':' + input.inputType.name,
				value: input.value,
				type: input.inputType.name,
			}
		})

		for(let child of queryStructureRoot.children){
			let inputList = this.getInputs(child, prefix);

			if(!inputList.length) 
				continue;

				levelInputs = levelInputs.concat(inputList)
		}
		return levelInputs;
	}

	getOutputs(queryStructureRoot, prefix) {
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
	}
}


class BasicControl extends Rete.Control {
		constructor(emitter, key, value) {
			super();
			this.emitter = emitter;
			this.key = 'value';

			this.scope = {
				change: this.change.bind(this),
				value: value
			}	
		}	

		update() {
			this.putData("value", this.scope.value);
			this._alight.scan();
		}

		mounted() {
	//		this.scope.value = this.getData('value');
			this._alight.scan();
		}

		change(e) {
			this.putData("value", e.target.value)
			this.setValue(e.target.value);
			this.update();
		} 

		setValue(val) {
			this.scope.value = val;
			this._alight.scan()
		}

}

class NumControl extends BasicControl { 
	constructor(emitter, key, value) {
		super();
		this.template = '<input type="number" :value="value" @input="change($event)" @mousedown.stop/>';
	    this.scope.value = value !== null ? value : 0;
	}
}

class StringControl extends BasicControl {
	constructor(emitter, key, value) {
		super();
		this.template = '<input type="text" :value="value" @input="change($event)" @mousedown.stop/>';
		this.scope.value = value !== null ? value : "";
	}
}

class BoolControl extends Rete.Control {
	constructor(emitter, key, value) {
		super();
		this.template = '<input type="checkbox" :checked="value" @input="change($event)" @mousedown.stop/>';
		this.scope.value =  value !== null ? value : false;
	}
}
