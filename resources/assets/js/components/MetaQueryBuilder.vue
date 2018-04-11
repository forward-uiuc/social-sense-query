<template>
	<div>
		<div class="form-inline" style="padding-bottom: 20px">
			<div class="form-group">
				<div class="btn btn-info" v-on:click="addQuery"> Add Query </div>
				<div class="input-group" style="padding-left: 20px">
					<select class="form-control" v-model=queryToAdd> 
						<option v-for="query in queries" >
							{{ query.name }}
						</option>
					</select>
				</div>
			</div>
			<div class="offset-md-3">
				<input ref="canvasValue" type="hidden" name="canvas" value="foobar"> </input>
				<input ref="topology" type="hidden" name="topology"> </input>
				<input ref="schedule" type="hidden" name="schedule"> </input>
				<input type="submit" class="btn btn-success" v-on:click="saveQuery($event)" ></input>
			</div>
		</div>
		<meta-query-builder-canvas ref="canvas"></meta-query-builder-canvas>
	</div>
</template>

<script>

import BuilderCanvas from './MetaQueryBuilderCanvas.vue';

export default {
	name: 'metaQueryBuilder',
	props: ['formId','queries'], 
	data: () => {
		return {
			queryToAdd: null
		}
	},
	methods: {
		addQuery: function() {
			let query = this.queries.find((query) => {
				return query.name === this.queryToAdd;
			})
			this.$refs.canvas.addQuery(query)
		},
		saveQuery: function(e){
			e.preventDefault();
			this.$refs.canvasValue.value =JSON.stringify(this.$refs.canvas.getSerliazedCanvas());
			this.$refs.topology.value = JSON.stringify(this.$refs.canvas.getSerializedQuery());
			document.getElementById(this.formId).submit();
		}
	},
	components: {
		BuilderCanvas // This is registered here for documentation, in app.js this is registered
	},
	mounted () {
		console.log(this.formID)
		this.queryToAdd = this.queries[0].name;
	}
}

</script>

<style scoped>

</style>
