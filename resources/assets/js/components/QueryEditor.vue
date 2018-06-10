
<template>
	<div>
		<div class="row" style="margin-top: -20px">
			<div class="offset-md-1">
				<div class="form-group">
					<label> Select Source </label>
					<select class="form-control" v-model="schema">
					  <option v-for="server in servers" v-bind:value="server.schema">
						{{ server.name }}
					  </option>
					</select>
				</div>
			</div>
		</div>
		
		<div class="row" style="margin-bottom: 10px">
					<div class="form-inline offset-md-1">
						<label class="sr-only" for="inlineFormInput">Name</label>
						<input type="text" class="form-control mb-2 mr-sm-2 mb-sm-0" id="inlineFormInput" placeholder="Query Name"  ref="name" name="name">

						<label class="mr-sm-2" for="inlineFormCustomSelect"> Query Schedule</label>
						<select class="custom-select mb-2 mr-sm-2 mb-sm-0" id="inlineFormCustomSelect" ref="schedule"  name="schedule">
							<option value="">Ad hoc</option>
							<option value='* * * * *'> Once a minute </option>
							<option value='0 * * * *'> Once an hour </option>
							<option value='0 0 * * *'> Once a day </option>
							<option value='0 0 * * 0'> Once a week </option>
							<option value='0 0 1 * *'> Once a month </option> 
						</select>

						<button class="btn btn-success" v-on:click="saveQuery($event)"> Save </button>
					</div>
		</div>

		<div class="row border-top" > 
			<div style="margin-left: 5vw">
				<graphql-query-builder ref="queryBuilder" :schema="JSON.parse(schema)" ></graphql-query-builder>
			</div>
		 </div> 

		<input type="hidden" ref="queryStructure" name="structure" ></input>
		<input type="hidden" ref="serverId" name="server_id"></input>
	</div>
</template>

<script>
import GraphQLQueryBuilder from './GraphQLQueryBuilder'
export default {

	name: 'queryEditor',
	props: {
		servers:  {
			type: Array,
			required: true,
			default: [] 
		},
		formId: {
			type: String,
			required: true,
			default: "" 
		},
		initialSchedule: {
			type: String,
			required: false,
			default: ""
		},
		initialName: {
			type: String,
			required: false,
			default: ""
		},
		initialStructure: {
			type: String,
			required: false,
			default: ""
		}	
	},
	components: {
		GraphQLQueryBuilder
	},
	data: function() {
		return {
			schedule: this.initialSchedule,
			name: this.initialName,
			structure: this.initialStructure,
			schema: this.servers[0].schema,
		}	
	},	
	methods: {
		saveQuery: function(e) {
			e.preventDefault();
			if (!this.$refs.queryBuilder.getIsValidQuery()) {
				alert('Select at least one attribute');
				return;
			} else if ( !this.$refs.name) {
				alert('Please give a name to this query')
				return;
			}
			
			let activeServer = this.servers.find( server => {
				return server.schema === this.schema;
			});

			this.$refs.queryStructure.value = JSON.stringify(this.$refs.queryBuilder.getQueryStructure())
			this.$refs.serverId.value = activeServer.id;
			document.getElementById(this.formId).submit()
		}
	},
	mounted () {
		this.$refs.name.value = this.name;
		this.$refs.schedule.value = this.schedule;
		
		if (this.initialStructure) {
			this.$refs.queryBuilder.restoreFromQueryNode(JSON.parse(this.initialStructure));				
		}
	}
}

</script>
