<template>
	<div>

			<div class="form-group form-inline">
				<div class="btn btn-info"  v-on:click="addQuery" > Add Query </div>
				<div class="input-group col-3">
					<select class="form-control" v-model="queryToAdd"> 
						<option v-for="query in queries.sort((a,b) => a.name > b.name)" >
							{{ query.name }}
						</option>
					</select>
				</div>
			</div>

			<div class="form-group form-inline" >
				<div class="btn btn-info" v-on:click="addFunction"> Add Function </div>
				<div class="input-group col-3" style="padding-left: 20px">
					<select class="form-control" v-model="functionToAdd">
						<option v-for="f in functions.sort((a,b) => a.name > b.name)">
							{{ f.name }}
						</option>
					</select>
				</div>
			</div>

			<div class="form-group form-inline" >
				<div class="btn btn-info" v-on:click="addMetaQuery"> Add Meta Query</div>
				<div class="input-group col-3" style="padding-left: 20px">
					<select class="form-control" v-model="metaQueryToAdd">
						<option v-for="m in metaQueries.sort((a,b) => a.name > b.name)">
							{{ m.name }}
						</option>
					</select>
				</div>
			</div>

		<div class="form-group ">
				<input ref="canvasValue" type="hidden" name="canvas" value="foobar"> </input>

				<div class="form-group col-5 form-inline">
					<label for="name" class="col-md-2" > Meta Query Name</label>
					<input ref="queryName" name="name" type="text" class="form-control" required> </input>
				</div>

				<input ref="topology" type="hidden" name="topology"> </input>
				<input ref="schedule" type="hidden" name="schedule"> </input>
				<input type="submit" class="btn btn-success pull-right" v-on:click="saveQuery($event)" value="Save Query"></input>
		</div>

		<meta-query-builder-canvas ref="canvas" :initial-state="initialState"></meta-query-builder-canvas>
	</div>
</template>

<script>

import BuilderCanvas from './MetaQueryBuilderCanvas.vue';
import ComponentBuilder from '../utils/ComponentBuilder.js';

export default {
	name: 'metaQueryBuilder',
	props: ['formId','queries', 'functions', 'metaQueries', 'initialState'], 
	data: () => {
		return {
			queryToAdd: null,
			functionToAdd: null,
			metaQueryToAdd: null,
			builder: new ComponentBuilder()
		}
	},
	methods: {
		addQuery: async function() {
			let query = this.queries.find((query) => {
				return query.name === this.queryToAdd;
			})


			// first, build the component
			let comp = this.builder.buildQueryComponent(query);
			this.$refs.canvas.registerComponent(comp);
			let destination = await this.$refs.canvas.addBuiltComponent(comp);
			destination.position = [200, 0];
			// second, add the components values for their inputs
			let inputs = this.builder.getInputs(query.structure, '');
			inputs.forEach( async (input) => {
				if (!input.value) {
					return;
				}

				let valueFunction = this.functions.find( f => {
					return f.name == input.type + ' Value'; // Hard coded :(
				});

				if( !valueFunction) {
					console.error('Error adding value function for type ' + input.type);
				}
				
				valueFunction.control = {
					type: input.type,
					value: input.value
				}

				let val = this.builder.buildFunctionComponent(valueFunction);
				this.$refs.canvas.registerComponent(val);
				let sourceNode = await this.$refs.canvas.addBuiltComponent(val);

				let inputKey = input.path.split(':')[0]; // The component stores the input as just the string without the :TYPE
														 // Thus we need to remove the type

				this.$refs.canvas.connectNodes(sourceNode, destination, 'value', inputKey);
				
			});
			
			
		},
		addFunction: function() {
			let func = this.functions.find( f => {
				return f.name == this.functionToAdd;
			});

			if(func.name.match(/Value/g) != null) {
				func.control = {
					type: func.name.split(" ")[0],
					value: null
				}
			}

			let comp = this.builder.buildFunctionComponent(func);
			this.$refs.canvas.registerComponent(comp);
			this.$refs.canvas.addBuiltComponent(comp);
		},
		addMetaQuery: function() {
			let metaQuery= this.metaQueries.find( m => {
				return m.name == this.metaQueryToAdd;
			});

			let comp = this.builder.buildMetaQueryComponent(metaQuery);
			this.$refs.canvas.registerComponent(comp);
			this.$refs.canvas.addBuiltComponent(comp);
		},

		saveQuery: function(e){

			e.preventDefault();
			this.$refs.canvasValue.value =JSON.stringify(this.$refs.canvas.getSerliazedCanvas());
			this.$refs.topology.value = JSON.stringify(this.$refs.canvas.getSerializedQuery());
		
			window.structure = JSON.parse(JSON.stringify(this.$refs.canvas.getSerializedQuery()));
			if(this.$refs.queryName.value) {
				document.getElementById(this.formId).submit();
			} else {
				alert("Please give your meta query a name.");
			}
		},

		restoreState: function() {
			this.queries.forEach((query) => {
				let comp = this.builder.buildQueryComponent(query);
				this.$refs.canvas.registerComponent(comp);
			});

			this.functions.forEach((func) => {

				// handle assigning a control to the function
				if( func.name.match(/[Vv]alue/g)) {
					func.control = {
						type: func.name.split(" ")[0]
					}
				}	

				let comp = this.builder.buildFunctionComponent(func);
				this.$refs.canvas.registerComponent(comp);

			});


			this.metaQueries.forEach((meta) => {
				let comp = this.builder.buildMetaQueryComponent(meta);
				this.$refs.canvas.registerComponent(comp);
			});
			this.$refs.canvas.restoreFromSerializedState();
		}
	},
	components: {
		BuilderCanvas // This is registered here for documentation, in app.js this is registered
	},
	mounted () {
		this.queryToAdd = this.queries[0].name;
		this.functionToAdd = this.functions[0].name;
		this.metaQueryToAdd = this.metaQueries[0].name;


		if(this.initialState) {
			this.$refs.queryName.value = this.initialState.name;
			this.restoreState();
		}
	}
}

</script>

<style scoped>

</style>
