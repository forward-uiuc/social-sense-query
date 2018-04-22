<template>
	<div>

			<div class="form-group form-inline">
				<div class="btn btn-info"  v-on:click="addQuery" > Add Query </div>
				<div class="input-group col-3">
					<select class="form-control" v-model="queryToAdd"> 
						<option v-for="query in queries" >
							{{ query.name }}
						</option>
					</select>
				</div>
			</div>

			<div class="form-group form-inline" >
				<div class="btn btn-info" v-on:click="addFunction"> Add Function </div>
				<div class="input-group col-3" style="padding-left: 20px">
					<select class="form-control" v-model="functionToAdd">
						<option v-for="f in functions">
							{{ f.name }}
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

		<meta-query-builder-canvas ref="canvas"></meta-query-builder-canvas>
	</div>
</template>

<script>

import BuilderCanvas from './MetaQueryBuilderCanvas.vue';

export default {
	name: 'metaQueryBuilder',
	props: ['formId','queries', 'functions'], 
	data: () => {
		return {
			queryToAdd: null,
			functionToAdd: null
		}
	},
	methods: {
		addQuery: function() {
			let query = this.queries.find((query) => {
				return query.name === this.queryToAdd;
			})
			this.$refs.canvas.addQuery(query)
		},
		addFunction: function() {
			let func = this.functions.find( f => {
				return f.name == this.functionToAdd;
			});

			this.$refs.canvas.addFunction(func);
		},
		saveQuery: function(e){

			e.preventDefault();
			this.$refs.canvasValue.value =JSON.stringify(this.$refs.canvas.getSerliazedCanvas());
			this.$refs.topology.value = JSON.stringify(this.$refs.canvas.getSerializedQuery());
			if(this.$refs.queryName.value) {
				document.getElementById(this.formId).submit();
			} else {
				alert("Please give your meta query a name.");
			}
		}
	},
	components: {
		BuilderCanvas // This is registered here for documentation, in app.js this is registered
	},
	mounted () {
		this.queryToAdd = this.queries[0].name;
		this.functionToAdd = this.functions[this.functions.length - 1].name;
	}
}

</script>

<style scoped>

</style>
