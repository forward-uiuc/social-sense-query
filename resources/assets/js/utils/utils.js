export function buildNodesMap (schema) {
		let nodes = {};
		let discardedTypes = new Set(['__Schema', '__Type', '__TypeKind', '__Field', '__InputValue', '__EnumValue', '__Directive', '__DirectiveLocation'])

	// First, build a mapping of a typename to it's attributes
	schema.types.forEach((type) => {
		if (/*type.kind === 'SCALAR' || */discardedTypes.has(type.name)) {
			return
		}
		let typeNode = {
			name: type.name,
			data: type // The 'data' for a type is it's graphQL definition
		}
		nodes[typeNode.name] = typeNode
	})
       
	 // Next, Give each type a "children" attribute of types, as well as a "name" attribute
	for(let typeName in nodes) {
		let node = nodes[typeName]
		node.successors = [];

		// If a node has no fields, it doesn't have any children :)
		let fields = node.data.fields;
		if(!fields) {
			continue;
		}

		fields.forEach(function(field){
			if(field.type.kind === "OBJECT") {
				node.successors.push(nodes[field.type.name])
			} else if (field.type.kind === "LIST" && field.type.ofType.kind === "OBJECT"){
				node.successors.push(nodes[field.type.ofType.name])
		   }
	  });
  }
  
	return nodes;
}
