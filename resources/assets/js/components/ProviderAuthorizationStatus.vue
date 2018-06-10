<template>
	<i v-bind:class="style" v-bind:style="{ color: this.active ? this.display[this.providerSlug].activeColor : '#686868' }" ></i>
</template>

<script>
    export default {
		name: 'providerAuthorizationStatus',
		props: ['providerSlug', 'iconSize', 'active'],
		data: function() {
				return {
					display: {
						reddit: {
							icon: 'fa-reddit-alien',
							activeColor: '#FF5700'
						},
						youtube: {
							icon: 'fa-youtube',
							activeColor:'#cc181e'
						},
						twitter: {
							icon: 'fa-twitter',
							activeColor:'#00aced'
						}
					}
				}	
		},	
		computed: {
			style: function () {
				return 'fab ' + this.display[this.providerSlug].icon + ' fa-' + this.iconSize + 'x';
			}
		},
    }
</script>
<style scoped>
</style>
