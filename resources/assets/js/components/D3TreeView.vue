<template>
	<div class="container-fluid">
		<div class="view" ref='container'>
			<svg ref="svg">
        <g id='treeMount' transform="translate(150,20)">
        </g>
      </svg>
		</div>
	</div>
</template>

<script>

import * as d3 from 'd3';

var i = 0;
export default {
	name: 'd3TreeView',
	props: {
		data: {
			type: Object,
			required: true,
			default: function() { return {} }
		},
		margin: {
			type: Object,
			required: false,
			default: function() {
				return {
					top: 20,
					bottom: 20,
					right: 20,
					left:200 
				}
			}
		},
	},
	data: () => {
		return {
			duration: 750,
			diagonal: d3.svg.diagonal().projection((d) => { return [d.y, d.x];}),
		}
	},
	methods: {
		selectNodeColor: function(d){
			return d.color;
		},
		update: function (source) {
			// Compute the new tree layout.
			let click = this.click;
			let nodes = this.tree.nodes(this.data).reverse();
			let links = this.tree.links(nodes);
			// Normalize for fixed-depth.
			nodes.forEach((d) => { d.y = d.depth * 150; });

			// Update the nodes…
			let node = this.svg.selectAll("g.node").data(nodes, (d) => { return d.id || (d.id = ++i); });

			// Enter any new nodes at the parent's previous position.
			let nodeEnter = node.enter().append("g").attr("class", "node")
				.attr("transform", (d) => { return "translate(" + source.y0 + "," + source.x0 + ")"; })
				.on("click", click);

			nodeEnter.append("circle")
					.attr("r", 1e-6)
					.style("fill", this.selectNodeColor)

			nodeEnter.append("text")
				.attr("x", (d) => { return d.position === 'left' ? -10 : 10; })
				.attr("dy", ".35em")
				.attr("text-anchor", (d) => { return d.position === 'left' ? "end": "start"; }) 
				.text((d) => { return d.text; })
				.style("fill-opacity", 1e-6);

			// Transition nodes to their new position.
			let nodeUpdate = node.transition().duration(this.duration)	
				.attr("transform", (d) => { return "translate(" + d.y + "," + d.x + ")"; });

			nodeUpdate.select("circle")
				.attr("r", 6) // Circle size
				.style("fill", this.selectNodeColor);

			nodeUpdate.select("text")
				.style("fill-opacity", 1);

			// Transition exiting nodes to the parent's new position.
			let nodeExit = node.exit().transition()
				.duration(this.duration)
				.attr("transform", (d) => { return "translate(" + source.y + "," + source.x + ")"; })
				.remove();

			nodeExit.select("circle")
				.attr("r", 1e-6);

			nodeExit.select("text")
				.style("fill-opacity", 1e-6);

			// Update the links…
			let link = this.svg.selectAll("path.link")
				.data(links, (d) => { return d.target.id; });

			// Enter any new links at the parent's previous position.
			let diagonal = this.diagonal;
			link.enter().insert("path", "g")
			.attr("class", "link")
			.attr("d", (d) => {
				let o = {x: source.x0, y: source.y0};
				return diagonal({source: o, target: o});
			});

			// Transition links to their new position.
			link.transition()
				.duration(this.duration)
				.attr("d", diagonal);

			// Transition exiting nodes to the parent's new position.
			link.exit().transition()
				.duration(this.duration)
				.attr("d", (d) => {
					let o = {x: source.x, y: source.y};
					return diagonal({source: o, target: o});
				})
				.remove();

				// Stash the old positions for transition.
			nodes.forEach((d) => { d.x0 = d.x; d.y0 = d.y; });
		},
		click: function(d) {
			d.selected = !d.selected;
			if(d.selected) {
				d._children = d.children;
				d.children = [];
			} else {
				d.children = d._children;
				d._children = [];
			}
			this.update(d);
		},
    getSize () {
      let width = this.$el.clientWidth
      let height = this.$el.clientHeight
      return { width, height }
		}
	},
	computed: {
			svg: () => { return d3.select('#treeMount')},
      tree: function() {
				let size = this.getSize();
				return d3.layout.tree().size([
						size.height - this.margin.top - this.margin.bottom + 2000,
						size.width - this.margin.left - this.margin.right + 2000 
					]);
			},
		width () {
			return this.getSize().width;
		},
		height () {
			return this.getSize().height;
		}
	},
	components: {
	},
	watch: {
		data: function(newData, oldData) {
			this.click(newData);
			this.$refs.container.scrollTop = (this.height + 1250) / 2;
			this.$refs.container.scrollLeft = 0;
		}
	},
	mounted () {
		this.$refs.svg.setAttribute('height', this.height + this.margin.top + this.margin.bottom + 2000)
		this.$refs.svg.setAttribute('width', this.width + this.margin.left + this.margin.right + 2000)
		this.$refs.container.scrollTop = (this.height + 1250) / 2;
	}
}

</script>

<style scoped>
  .view {
		border-color: black;
		border-radius: 25px;
		overflow: scroll;
		height: 80vh;
	}
</style>
