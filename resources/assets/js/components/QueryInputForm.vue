<template>
	<div>
		<div v-for="input in inputs">
			<div class="form-group">
				<label> {{ input.name }} </label>
				<input v-if="input.inputType == 'Float'" step="any" class="form-control" type="number" :name="input.name" v-model="input.value"></input>
				<input v-else-if="input.inputType == 'Boolean'"     class="form-control" type="checkbox" :name="input.name" v-model="input.value"></input>
				<input v-else-if="input.inputType == 'Int'"         class="form-control" type="number" :name="input.name" v-model="input.value"></input>

				<select v-else-if="input.inputType == 'Options'" v-model="input.value">
					<option v-for="option in input.options" v-bind:value="option.value">
							{{ option.text }} 
					</option>
				</select>

				<input v-else                                       class="form-control" type="text"   :name="input.name" v-model="input.value"></input> 
				<small v-if="input.description" class="form-text text-muted"> {{ input.description }} </small> </div>
		</div>
	</div>
</template>

<script>
export default {
	name: 'queryInputForm',
	props: ['inputs']
}

</script>

<style scoped>

</style>
