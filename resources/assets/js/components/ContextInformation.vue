<template>
	<div>
			<button v-on:click="getContextInfo" class="btn btn-dark"> 
				<i v-bind:class="iconInfo"></i>
			</button>
	</div>
</template>

<script>
export default {
	name: 'contextInfo',
	props: ['iconInfo', 'popupInfo'],
	methods: {
		getContextInfo: function() {
			alert(this.popupInfo);
		}
	}
}

</script>
