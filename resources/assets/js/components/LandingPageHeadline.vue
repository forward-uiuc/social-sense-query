<template>
	<div>
		<div class="title m-b-md">
			{{ primary }} 
		</div>
		<p style="margin-top: -2em">  
			Listen to	{{ currentOption }} 
		</p>
	</div>
</template>

<script>
    export default {
		name: 'landingPageHeadline',
		props: {
			options: {
				type: Array,
				required: true,
				default () {
					return []
				}
			},
			primary: {
				type: String,
				required: true,
				default: ''
			},
			intervalTiming: {
				type: Number,
				required: false,
				default: 800
			}
		},
		data () {
			return {
				intervalID: '',
				optionIndex: 0
			}
		},
		computed: {
			currentOption () {
				return this.options.length > 0 ? this.options[this.optionIndex] : 'No Option Set'
			}
		},
        mounted() {
			this.intervalID = setInterval(() => {
				if (this.options.length	 > 0){
					this.optionIndex = (this.optionIndex + 1) % this.options.length;	
				}
			}, this.intervalTiming);
        },
		beforeDestroy () {
			clearInterval(this.intervalID);
		}
	
    }
</script>

