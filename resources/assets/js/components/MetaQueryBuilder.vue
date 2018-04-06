<template>
	<div>
		<div class="form-inline" style="padding-bottom: 20px">
			<div class="form-group">
				<button class="btn btn-info" v-on:click="addQuery"> Add Query </button>
				<div class="input-group" style="padding-left: 20px">
					<select class="form-control" v-model=queryToAdd> 
						<option v-for="query in queries" >
							{{ query.name }}
						</option>
					</select>
				</div>
				</div>
			</div>
		<meta-query-builder-canvas :resolution="20" :width="2000" :height="1000" ref="canvas"></meta-query-builder-canvas>
	</div>
</template>

<script>

import BuilderCanvas from './MetaQueryBuilderCanvas.vue';

export default {
	name: 'metaQueryBuilder',
	props: {
		queries: {
			type: Array 
		}
	},
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
		}
	},
	components: {
		BuilderCanvas // This is registered here for documentation, in app.js this is registered
	},
	mounted () {
		this.queryToAdd = this.queries[0].name;
	}
}

</script>

<style scoped>

</style>
