export function buildNodesMap (schema) {
		let nodes = {};
		let discardedTypes = new Set(['__Schema', '__Type', '__TypeKind', '__Field', '__InputValue', '__EnumValue', '__Directive', '__DirectiveLocation'])

	schema.types.forEach((type) => {
		if (discardedTypes.has(type.name)) {
			return
		}
		let typeNode = {
			name: type.name,
			data: type // The 'data' for a type is it's graphQL definition
		}
		nodes[typeNode.name] = typeNode
	})
       
	return nodes;
}


function isScalar(field) {
	return field.type.kind === 'SCALAR' || (field.type.kind === 'LIST' && field.type.ofType.kind === 'SCALAR');
}
