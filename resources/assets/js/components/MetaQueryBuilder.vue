<template>
	<div>
		<div class="row" style="padding-bottom:20px;">
			<div class="form-inline" >
				<div class="form-group">
					<div class="btn btn-info" v-on:click="addQuery"> Add Query </div>
					<div class="input-group" style="padding-left: 20px">
						<select class="form-control" v-model="queryToAdd"> 
							<option v-for="query in queries" >
								{{ query.name }}
							</option>
						</select>
					</div>
				</div>
				<div style="padding-left: 20px;">
					<input ref="canvasValue" type="hidden" name="canvas" value="foobar"> </input>
					<input ref="topology" type="hidden" name="topology"> </input>
					<input ref="schedule" type="hidden" name="schedule"> </input>
					<input type="submit" class="btn btn-success" v-on:click="saveQuery($event)" value="Save Query"></input>
				</div>
			</div>
		</div>
		<div class="row" style="padding-bottom:20px;">
			<div class="form-inline">
				<div class="btn btn-info" v-on:click="addFunction"> Add Function </div>
				<div class="input-group" style="padding-left: 20px">
					<select class="form-control" v-model="functionToAdd">
						<option v-for="f in functions">
							{{ f.name }}
						</option>
					</select>
				</div>
			</div>
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
			console.log(this.$refs.canvas.getSerializedQuery());
			//document.getElementById(this.formId).submit();
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
