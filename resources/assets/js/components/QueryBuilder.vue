<template>
  <div class='container-fluid' style="background-color:'#afb1b5;">
    <div >
      <svg width="4000" height="2000">
        <g id='queryBuilderMount' transform="translate(120,20)">
        </g>
      </svg>
    </div>

				<b-modal v-b-modal.modal-center id="queryInputModal" ref="queryInputModal" v-bind:title="queryInputModalTitle" ok-title="Assign Arguments" @ok="assign" @hidden="cancel">
				<queryInputForm :inputs="inputs"></queryInputForm>
      </b-modal>

			<!-- The modal for saving a query -->
			<b-modal v-b-modal.modal-center id="saveQueryModal" ref="saveQueryModal" title="Save Query" ok-title="Save"  @ok="postQuery" @hidden="cancel">
				<queryInputForm :inputs="inputs"></queryInputForm>
      </b-modal>

			<!-- The modal for getting arbitrary input for a node -->
			<input type="hidden" ref="name" name="name" id="name"></input>
			<input type="hidden" ref="schedule" name="schedule" id="schedule"></input>
			<input type="hidden" ref="structure" name="structure"></input>
			<input type="hidden" ref="description" name="description"></input>
			<input type="hidden" ref="string" name="string"></input>

      <div class="btn btn-lg btn-primary submitButton" @click="save"> Save Query </div>
  </div>
</template>

<script>
  import  {buildNodesMap} from '../utils/utils.js';
  import schema from './schema.json'
	import QueryInputForm from './QueryInputForm';
	import  {QueryNode, Input, Output, BaseQuery}  from '../utils/Query'
	import BootstrapVue from 'bootstrap-vue'
	import * as d3 from 'd3';

	var i = 0;
  export default {
    name: 'queryBuilder',
		props: [
			'authorizedProviders','formId','serializedQuery', 'queryName','querySchedule'
		],
    data: function() {
      return {
        margin: {
          top: 20,
          bottom: 20,
          right: 20,
          left: 120
        },
        duration: 750,
        diagonal: d3.svg.diagonal().projection((d) => { return [d.y, d.x];}),
        tree: d3.layout.tree().size([2000-20 -20, 4000-120-20]),
        queryInputModalTitle: '',
        inputs: null, // Inputs to the form that we're examining,
				saveQueryInputs: null,
				inputNode: null, 
				query: null,
      }
    },
    components: {
			QueryInputForm
    },
    computed: {
      svg: () => { return d3.select('#queryBuilderMount')},
      svg_width: function() {
        return 4000 - this.margin.right - this.margin.left;
      },
      svg_height: function() {
        return 2000 - this.margin.top - this.margin.bottom;
      },
      root: {
        get: ()    => { return this.root },
        set: (val) => { this.root = val; }
      },
      nodes: {
        get: ()    => { return this.nodes },
        set: (val) => { this.nodes = val; }
      },
      gettingInputPromise: {
        get: ()    => { return this.gettingInputPromise},
        set: (val) => { this.gettingInputPromise = val; }
      },

    },
    methods: {
      isValidQuery(node){
        if(!node.selected){
          // Check to see if we were slected or not
          return false;
        } else if (node.isScalar) {
          // Check to see if we're valid (scalar and selected)
          return true;
        }
        
        // Check to see if a child is valid
        // if so, then we're good
        let returnValue = false;
        node.children.forEach( child => {
          returnValue = returnValue || this.isValidQuery(child);
        });
        return returnValue;
      },
      getInput: function(node) {
				this.inputNode = node;
				node.query.inputs.forEach((input) => {
					if(input.inputType == "String" && input.value != null){
						input.value = input.value.replace(/['"]+/g, '');	
					}
				})
				
        this.inputs = node.query.inputs; 
        this.queryInputModalTitle = node.name 
        this.$refs.queryInputModal.show();
      },
      cancel: function(evt){
				this.inputNode = null;
				this.inputs = [];
      },
      assign: function(evt) {
				this.update(this.inputNode)
				this.inputNode = null;
				this.inputs = [];
    	},
			save: function(event) {
				event.preventDefault();
				if(this.query.isValidGraphQLQuery()){
					console.log(this.$refs.schedule.value)
					console.log(this.$refs.name)
					var schedule = new Input('Query Schedule', 'When you want this query to run','Options', this.querySchedule);
					schedule.options = [
						{value: '', text: 'Ad-Hoc'},
						{value: '* * * * *', text: 'Once a minute'},
						{value: '0 * * * *', text: 'Once an hour'},
						{value: '0 0 * * *', text: 'Once a day'},
						{value: '0 0 * * 0', text: 'Once a week'},
						{value: '0 0 1 * *', text: 'Once a month'}
					]
					this.inputs = [
						new Input('Query Name', 'A personal name that allows you to distinguish this query','String', this.queryName),
						schedule
					]
					this.$refs.saveQueryModal.show()

				} else {
					alert("Please Make sure that you include at least one terminal node in your query")
				}

			},
			postQuery: function() {
				// Serialize the structure
				// Create a description
				this.$refs.name.value = this.inputs[0].value;
				this.$refs.schedule.value = this.inputs[1].value;
				this.$refs.structure.value = JSON.stringify(this.root.query);

				var descriptions = this.flatten(this.getDescription(this.root.query, "", []));
				var reduced =descriptions.reduce((description, currentValue) => {
					return currentValue + "\n" + description;
				});
				
				this.$refs.description.value = reduced;
				this.$refs.string.value = this.query.toGraphQLQueryString();
				document.getElementById(this.formId).submit();
			},
			getDescription: function(queryNode, currentDescription, currentCollection){
				if(!queryNode.selected){
					return "";
				} else if (queryNode.output.isScalar){
					return currentDescription + "." + queryNode.name;
				} else {
					var childCollection = [];
					var parentDescription =  currentDescription != "" ? currentDescription + "." + queryNode.name : queryNode.name;
					queryNode.children.forEach((child) => {
						var childDescription = this.getDescription(child, parentDescription, childCollection);

						if(childDescription != ""){
							childCollection.push(childDescription);
						}
					});

					return childCollection;
				}
			},
			flatten: function(ary) {
					var ret = [];
					ary.forEach((element) => {
						if(Array.isArray(element)){
							ret = ret.concat(this.flatten(element));
						} else {
							ret.push(element);
						}
					});
					return ret;
			},
      selectNodeColor: function(node) {
				if(node.query.inputs.length > 0 && node.query.selected){
					return 'green';
				} else if (node.query.inputs.length > 0){
		      return 'red';
				} else if (node.query.selected && node.query.output.isObject){
					return 'white'
				} else if (node.query.selected && node.query.output.isScalar){
						return 'yellow';
				} else if (!node.query.selected && node.query.output.isScalar){
						return 'Gainsboro';
				} else {
					console.log(JSON.parse(JSON.stringify(node.query)))
						return 'lightsteelblue';
				}
      },
      update: function (source) {
        // Compute the new tree layout.
        let nodes = this.tree.nodes(this.root).reverse();
        let links = this.tree.links(nodes);
        // Normalize for fixed-depth.
        nodes.forEach((d) => { d.y = d.depth * 300; });

        // Update the nodes…
        let node = this.svg.selectAll("g.node")
          .data(nodes, (d) => { return d.id || (d.id = ++i); });

        // Enter any new nodes at the parent's previous position.
        let nodeEnter = node.enter().append("g")
          .attr("class", "node")
          .attr("transform", (d) => { return "translate(" + source.y0 + "," + source.x0 + ")"; })
          .on("click", this.click);

        nodeEnter.append("circle")
          .attr("r", 1e-6)
          .style("fill", this.selectNodeColor);

        nodeEnter.append("text")
          .attr("x", (d) => { return d.query.output.isObject ? -10 : 10; })
          .attr("dy", ".35em")
          .attr("text-anchor", (d) => { return d.query.output.isObject ? "end" : "start"; }) 
          .text((d) => { return d.query.name; })
          .style("fill-opacity", 1e-6);

        // Transition nodes to their new position.
        let nodeUpdate = node.transition()
          .duration(this.duration)
          .attr("transform", (d) => { return "translate(" + d.y + "," + d.x + ")"; });

        nodeUpdate.select("circle")
          .attr("r", 6) // Circle size
          .style("fill", this.selectNodeColor);

        nodeUpdate.select("text")
          .style("fill-opacity", 1);

        // Transition exiting nodes to the parent's new position.
        let nodeExit = node.exit().transition()
          .duration(this.duration)
          .attr("transform", (d) => { return "translate(" + source.y + "," + source.x + ")"; })
          .remove();

        nodeExit.select("circle")
          .attr("r", 1e-6);

        nodeExit.select("text")
          .style("fill-opacity", 1e-6);

        // Update the links…
        let link = this.svg.selectAll("path.link")
          .data(links, (d) => { return d.target.id; });

        // Enter any new links at the parent's previous position.
        let diagonal = this.diagonal;
        link.enter().insert("path", "g")
        .attr("class", "link")
        .attr("d", (d) => {
          let o = {x: source.x0, y: source.y0};
          return diagonal({source: o, target: o});
        });

        // Transition links to their new position.
        link.transition()
          .duration(this.duration)
          .attr("d", diagonal);

        // Transition exiting nodes to the parent's new position.
        link.exit().transition()
          .duration(this.duration)
          .attr("d", (d) => {
            let o = {x: source.x, y: source.y};
            return diagonal({source: o, target: o});
          })
          .remove();

          // Stash the old positions for transition.
        nodes.forEach((d) => { d.x0 = d.x; d.y0 = d.y; });
      },
      click: function(d) {

        let update = this.update; 
        let nodes = this.nodes;
        let getInput = this.getInput; 

				d.query.selected = !d.query.selected; // First, mark that the query node is inverted

				// If a query node was unselected, clear out its attributes and children
				d.children = [];
				if(!d.query.selected){
					d.query.children = [];
				} else if (d.query.selected && d.data.fields) { // If something was selected AND it has fields

					d.data.fields.forEach((field) => {
						let isSupportedFieldType = field.type.kind == 'OBJECT' || field.type.kind == 'SCALAR' ||
																			(field.type.kind == 'LIST' && field.type.ofType.kind == 'OBJECT') ||
																			(field.type.kind == 'LIST' && field.type.ofType.kind == 'SCALAR');

						if(isSupportedFieldType){
							let fieldType = null;
							if(field.type.kind == 'LIST'){
								fieldType = this.nodes[field.type.ofType.name];
							} else {
								fieldType = this.nodes[field.type.name];
							}

							let queryNode = buildChildQueryNode(field);
							d.query.children.push(queryNode)
							let type = Object.assign({}, fieldType)
							type.query = queryNode;
							d.children.push(type);
						} else {
							console.log('Unsupported Field Type', field)
						}
					})

				} else { // If something was selected and doesn't have fields, shrug?
						
				}

				if(d.query.selected && d.query.inputs.length > 0){ 
					getInput(d);
        } else {
          this.update(d);
        }
			},
			convertQueryNodeToDisplayedNode(queryNode){
				var node = {};
				queryNode.output.isAList =   queryNode.output.isAList  == "true" || queryNode.output.isAList === true;
				queryNode.output.isObject =  queryNode.output.isObject == "true" || queryNode.output.isObject === true;
				queryNode.output.isScalar =  queryNode.output.isScalar == "true" || queryNode.output.isScalar === true;

				var temp = []
				node.query = new QueryNode(queryNode.name, queryNode.inputs, queryNode.output, [], !queryNode.selected);

				if(queryNode.output.isAList){
					node = Object.assign(node, this.nodes[queryNode.output.type.ofType.name]);
				} else {
					node = Object.assign(node, this.nodes[queryNode.output.type.name])
				}
				
				node.children = [];
				queryNode.children.forEach((child) => {
					var convertedNode = this.convertQueryNodeToDisplayedNode(child);
					node.children.push(convertedNode);
					node.query.children.push(convertedNode.query)
				})

				return node;
			}
    },
    mounted: function() {
      this.nodes = buildNodesMap(schema.data.__schema);
			if(this.serializedQuery){
				this.root = this.convertQueryNodeToDisplayedNode(this.serializedQuery)
				this.query = new BaseQuery(this.root.query)
				this.root.x0 = this.svg_height / 2;
				this.root.y0 = 0;
				this.update(this.root);
				return;
			}

			var authorizedProviders = JSON.parse(this.authorizedProviders);		
			this.root = this.nodes['Query'];
			let rootOutput = new Output({kind: 'OBJECT', name:'Query'})
			let queryRoot =  new QueryNode('query', [], rootOutput, []);
			
			let toRemove = [];

			this.root.data.fields.forEach((field) => {
				if(!authorizedProviders[field.name] ){
					toRemove.push(field);
					delete this.nodes[field.type.name]; // Remove the object type as a reference in our graph
				}
			});
			toRemove.forEach((fieldToRemove) => {
				this.root.data.fields.splice(this.root.data.fields.indexOf(fieldToRemove), 1);
				// Also remove it as a property of the Query
			});

			this.root.query = queryRoot;
			
			this.query = new BaseQuery(this.root.query)
			this.root.x0 = this.svg_height / 2;
	    this.root.y0 = 0;

      this.update(this.root);
    	window.scrollTo(0, 600);
    },
  }

	// Take the definition of a type in GraphQL relative to another type (e.g. a field) and creates a query
	function buildChildQueryNode(graphQLFieldDefinition){
		// Output constructor: Type 
		let output = new Output(graphQLFieldDefinition.type)

		let inputs = []
		graphQLFieldDefinition.args.forEach(function(arg){
			//Input constructor: name, description, type, defaultValue
				inputs.push(new Input(arg.name, arg.description, arg.type.name, arg.defaultValue))
		})
		let children = [] // The children nodes that we have selected. These should all be objects
		return new QueryNode(graphQLFieldDefinition.name, inputs, output, children)
	}
</script>

<style scoped>
  .container-fluid {
      padding: 60px 50px;
  }

</style>
