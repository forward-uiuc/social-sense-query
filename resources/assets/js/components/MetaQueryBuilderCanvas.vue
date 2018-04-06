<template>
	<div id="canvasMount">
	</div>
</template>

<script>
export default {
	name: 'metaQueryBuilderCanvas',
	props: {
		height: {
			type: Number
		},
		width: {
			type: Number
		},
		resolution: {
			type: Number
		}
	},
	data: () => {
		return {
			queryIndex: 0,
			inputNode: null,
			outputNode: null
		}
	},
	computed: {
		canvas: {
			set: (val) => { this.svg = val },
			get: () => { return this.svg }
		},
		dragBehavior: {
			get: () => {
				return d3.behavior.drag()
					.origin(function(d,i){
						return d3.select(this)[i][0];
					})
					.on('drag', function(d) {
			  		this.x = this.x || 0;//reset if not there
						this.y = this.y || 0;
						this.x += d3.event.dx;
						this.y += d3.event.dy;
						d3.select(this).attr("transform", "translate(" + this.x + "," + this.y + ")");
						let canvas = d3.select(this.parentNode)
						canvas.selectAll('line')
							.attr('x1', (d) => {
									let transformation = d3.transform(d3.select(d.outputNode.parentNode).attr('transform')).translate
									return parseInt(d3.select(d.outputNode).attr('cx')) + transformation[0]
								})
								.attr('x2', (d) => {
									let transformation = d3.transform(d3.select(d.inputNode.parentNode).attr('transform')).translate
									return parseInt(d3.select(d.inputNode).attr('cx')) + transformation[0]
								})
								.attr('y1', (d)=> {
									let transformation = d3.transform(d3.select(d.outputNode.parentNode).attr('transform')).translate
									return parseInt(d3.select(d.outputNode).attr('cy')) + transformation[1]

								})
								.attr('y2', (d) => {
									let transformation = d3.transform(d3.select(d.inputNode.parentNode).attr('transform')).translate
									return parseInt(d3.select(d.inputNode).attr('cy')) + transformation[1]
								})

						})
					}
			},
	},
	methods: {
		addQuery: function(query) {
			let inputs = this.getInputs(query.structure, "")
			let outputs = this.getOutputs(query.structure, "")
			let name = query.name;
			
			let queryID = ++this.queryIndex

			let width = 12.5 * name.length;
			let height =  24 * Math.max(inputs.length, outputs.length);
			let displayedQuery = {
				x: 100,
				y: 100,
				width: width,
				height:height,
				label: name,
				inputs: inputs,
				outputs: outputs
			};

			let canvas = this.canvas;
			var queryGroup = this.canvas.append("g")
			queryGroup.call(this.dragBehavior)
			queryGroup.inputNode = this.inputNode;
			queryGroup.outputNode = this.outputNode;

			// Add the rectangle
			queryGroup.selectAll('rect')
			.data([displayedQuery])
			.enter()
			.append('rect')	
				.attr('x', (d) => { return d.x})
				.attr('y', (d) => { return d.y})
				.attr('width', (d) => { return d.width})
				.attr('height', (d) => { return d.height})

			// Add the name label
			queryGroup.selectAll('label')
			.data([displayedQuery])
			.enter()
			.append('text')
				.attr('x', (d) => { return d.x + d.width/2})
				.attr('y', (d) => { return d.y - 5})
				.attr('font-size', '25')
				.attr('text-anchor', 'middle')
				.text((d) => { return d.label })



		var inputNode = this.inputNode;
		var outputNode = this.outputNode;

		for(let i=0; i < inputs.length; i++){
			let text = queryGroup.selectAll('inputs')
			.data([{label: inputs[i], x: displayedQuery.x - 5, y: displayedQuery.y  +  15 + 25*i}])
			.enter()
			.append('text')
				.attr('x', (d) => {return d.x})
				.attr('y', (d) => {return d.y})
				.attr('text-anchor', 'end')
				.attr('font-size', '15')
				.text((d) => { return d.label })

			let textBBox = text[0][0].getBBox()

			queryGroup.selectAll('inputCicles')
				.data([{label: inputs[i], x: textBBox.x - 15, y: displayedQuery.y  +  15 + 25*i, id: queryID}])
				.enter()
				.append('circle')
					.attr('cx', (d) => {return d.x })
					.attr('cy', (d) => {return d.y - 5})
					.attr('stroke', 'black')
					.attr('stroke-width',1)
					.attr('r', (d) => {return 8})
					.attr('fill', (d) => { return d3.rgb(210, 75, 75, .3)})
					.on('click', function(d){
						this.parentNode.parentNode.inputNode = this;

						if(this.parentNode.parentNode.outputNode){
							var node = this;
							canvas.selectAll('connections')
								.data([{inputNode: this, outputNode:this.parentNode.parentNode.outputNode}])
								.enter()
								.append('line')
								.attr('x1', (d) => {
									let transformation = d3.transform(d3.select(d.outputNode.parentNode).attr('transform')).translate
									return parseInt(d3.select(d.outputNode).attr('cx')) + transformation[0]
								})
								.attr('x2', (d) => {
									let transformation = d3.transform(d3.select(d.inputNode.parentNode).attr('transform')).translate
									return parseInt(d3.select(d.inputNode).attr('cx')) + transformation[0]
								})
								.attr('y1', (d)=> {
									let transformation = d3.transform(d3.select(d.outputNode.parentNode).attr('transform')).translate
									return parseInt(d3.select(d.outputNode).attr('cy')) + transformation[1]

								})
								.attr('y2', (d) => {
									let transformation = d3.transform(d3.select(d.inputNode.parentNode).attr('transform')).translate
									return parseInt(d3.select(d.inputNode).attr('cy')) + transformation[1]
								})
						}
					})
			}
		
			for(let i=0; i < outputs.length; i++){
				let text = queryGroup.selectAll('outputs')
				.data([{label: outputs[i], x: displayedQuery.width + displayedQuery.x + 5, y: displayedQuery.y  +  15 + 25*i}])
				.enter()
				.append('text')
					.attr('x', (d) => {return d.x})
					.attr('y', (d) => {return d.y})
					.attr('text-anchor', 'start')
					.attr('font-size', '15')
					.text((d) => { return d.label })

					let textBBox = text[0][0].getBBox()

				queryGroup.selectAll('outputCircles')
					.data([{label: outputs[i], x: textBBox.x + textBBox.width + 15, y: displayedQuery.y  +  15 + 25*i, id: queryID}])
					.enter()
					.append('circle')
						.attr('cx', (d) => {return d.x })
						.attr('cy', (d) => {return d.y - 5})
						.attr('stroke', 'black')
						.attr('stroke-width',1)
						.attr('r', (d) => {return 8})
						.attr('fill', (d) => { return d3.rgb(17, 137, 6, .3)})
						.on('click', function(d){
							this.parentNode.parentNode.outputNode = this;
						})
			}
		},
		getInputs: function(queryStructureRoot, prefix) {
			if(!queryStructureRoot.selected){
				return [];
			}

			let levelInputs = queryStructureRoot.inputs.map((input) => {
					return prefix + input.name + ":" + input.inputType	
			})

			for(let child of queryStructureRoot.children){
				let inputList = this.getInputs(child, prefix + queryStructureRoot.name + ".");
				if(!inputList.length)
					continue;
					levelInputs = levelInputs.concat(inputList)
				}
			return levelInputs;
		},
		getOutputs: function(queryStructureRoot, prefix) {
				if(!queryStructureRoot.selected){
					return [];
				}

				let output = queryStructureRoot.output;
				let levelOutput = [];

				if (!output.isObject || output.isObject === "false"){
					if (!output.isAList || output.isAList === "false"){
						levelOutput.push(prefix + queryStructureRoot.name + ":" + output.type.name)
					} else {
						levelOutput.push(prefix + queryStructureRoot.name + ":" + output.type.ofType.name + '(*)')
					}
				}

			for(let child of queryStructureRoot.children){
				let outputList = this.getOutputs(child, prefix + queryStructureRoot.name + ".");

				if(!outputList.length)
					continue;
				levelOutput = levelOutput.concat(outputList)
			}
			return  levelOutput;
		},

	},
	mounted () {
		this.canvas = d3.select('#canvasMount').append('svg')
			.attr('width', this.width)
			.attr('height', this.height);
	}
}

</script>

<style scoped>
	>>> svg {
		box-sizing: border-box;
		border: 1px solid rgb(212, 212, 212);
	}

	>>> rect {
		stroke: rgb(95, 176, 228);
		stroke-width: 2;
		fill: rgba(205, 246, 255);
	}

	>>> text {
		font-family:'sans-serif';
		fill: 'black';
	}

	>>> line {
		stroke: rgb(0, 0, 0);
		stroke-width: 5px;
		shape-rendering: crispEdges;
	}
</style>
