<template>
		<div class="row"> 
			<div class="col-md-2" style="height:50vh; overflow-y: auto; position:fixed; z-index:1000;">
				<queryInputForm :inputs="inputs"></queryInputForm>
			</div>

			<div class="col-md-10" style="border-color-left:black; margin-left: 10vw">
				<d3-tree-view ref="treeView" :data="tree" v-on:node-clicked="expandQuery" v-on:node-right-clicked="showArguments" style="width:4000px; height:800px"></d3-tree-view>
			</div>
		 </div> 
</template>

<script>
import QueryInputForm from './QueryInputForm';
import {Input, Output, QueryNode} from '../utils/Query';


export default {
	name: 'graphqlQueryBuilder',
	props: ['schema'],
	components: {
		QueryInputForm
	},
	data: function() {
		return {
			types: {},
			inputs: [],
			tree: {text:'hello world', children:[], selected: false}
		}	
	},	
	methods: {
		getQueryStructure: function() {
			return this.map(this.tree)
		},
		getIsValidQuery: function() {
			return QueryNode.isValidQuery(this.getQueryStructure());
		},
		map (treeNode) {
			if(treeNode.selected) {
				return null;
			}


			let name = treeNode.text;
			let selected = !treeNode.selected;
			let output = new Output(treeNode.type)
			let inputs = treeNode.args.map(arg => {
				return new Input(arg.name, arg.description, arg.type, arg.value ? arg.value : arg.defaultValue)
			})
				
			let children = treeNode.children? treeNode.children.map( child => { return this.map(child)  }) : [];

			children = children.filter(child => child != null);
			
			return new QueryNode(name, inputs, output, children, selected);

		},
		logObject (obj) {
			console.log(JSON.parse(JSON.stringify(obj)));
		},
		synchronize(localTree, serializedTree) {
			
			localTree.selected = true;
			this.expandQuery(localTree);
			localTree.children = localTree._children;

			let serializedInputs = {};
			serializedTree.inputs.forEach( input => {
				serializedInputs[input.name] = input;
			});

			localTree.args.forEach( arg => {
				arg.value = serializedInputs[arg.name].value;
			});

			

			if (this.isScalar(localTree.type)){
				localTree.color = 'yellow';
				return;
			}




			let attributes = {}
			serializedTree.children.forEach(child => {
				attributes[child.name] = child;
			});
			
			localTree.children.forEach( child => {
				if(attributes[child.text]){
					this.synchronize(child, attributes[child.text]);
				}
			});
			

		},
		restoreFromQueryNode (queryNode) {
			this.resetSchema();
			this.synchronize(this.tree, queryNode);
			/*let restored = this.transformFromObjectRef(queryNode);
			restored.position = 'left';
			restored.color = 'lightsteelblue'
			restored._children = restored.children;
		  	this.tree = restored;*/
		},
		getFieldTypeName(field) {
			if (field.type.kind === 'LIST') {
				return field.type.ofType.name;	
			}
			return field.type.name
		},
		isScalar (type) {
			return type.kind === 'SCALAR' || (type.kind === 'LIST' && type.ofType.kind === 'SCALAR')
		},
		expandQuery (node) {
			this.inputs = node.args.length > 0 ? node.args : this.inputs;
			this.inputs.forEach( input => {
				let initialValue = input.value ? input.value : input.defaultValue;
				if(input.type.name !== 'String') {
					initialValue = JSON.parse(initialValue)
				}

				input.value = initialValue;
			});
			
			if (node.children && node.children.length > 0){
				return;
			}

			if (this.isScalar(node.type)) {
				if (!node.selected) {
					node.color = 'yellow'
				} else {
					if(node.args.length > 0){
						node.color = 'red'
					} else {
						node.color = 'white'
					}
				}
				return;	
			}

			let children = [];
			node.type.fields.forEach( field => {
				let childType = Object.assign({}, this.types[this.getFieldTypeName(field)]);
				let child = {text: field.name, args: field.args, type: childType, children: [], selected: true}
			
				if (!this.isScalar(childType)) {
					child.color = 'lightsteelblue'	
					child.position = 'left'
				}

				if (child.args.length > 0 )
					child.color = 'red'
	
				children.push(child);
			});
			node._children = children;
		},	
		showArguments (node) {
			this.inputs = node.args;
			this.inputs.forEach( input => {
				input.value = input.value ? input.value : input.defaultValue;
			});

		},
		resetSchema() {
			this.types = [];
			this.tree = {text:'hello world', children:[], selected: false}

			let schema = this.schema.data.__schema;
			let discardedTypes = new Set(['__Schema', '__Type', '__TypeKind', '__Field', '__InputValue', '__EnumValue', '__Directive', '__DirectiveLocation'])
			
			schema.types.forEach( type => {
				if (discardedTypes.has(type.name)) {
					return
				}
				this.types[type.name] = type
			});


			let queryTypeName = schema.queryType.name;

			let baseNode = {
				text: 'query',
				position: 'left',
				selected: false,
				args: [],
				children: [],
				color: 'lightsteelblue',
				type: this.types[queryTypeName]
			}
			
			this.expandQuery(baseNode);	
			this.tree = baseNode;
		},

	},
	watch: {
		schema: function(oldSchema, newSchema){
			this.resetSchema();	
		}
	},
	mounted () {
		this.resetSchema()
	}
}

</script>
