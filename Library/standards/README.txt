Directory structure
===================

The standards directory is generally divided by namespace, except for the "default" and
"collections" directories, all other directories collect standards belonging to one base
namespace.

Files naming
============

The name of files defines their contents:

- Namespaces:
	These files contain namespace terms and eventual category terms.
	
- Types or TypeXXX:
	These files contain types, in general these cover enumerated values.

- Schemas or SchemaXXX:
	These files contain schema types, these are collection of tags that can be re-used in
	different structures, or full blown structures to be used as types. In general they will
	contain tags specific to the type they define and omit any shared properties.

- Structs or StructXXX:
	These files contain tag structures attached to a root struct tag. These can be used
	to share the same struct tag among different objects. In general they should contain all
	the tags that the root struct needs.

- Structures or StructureXXX:
	These files contain root object structures, they generally are used to define the
	structure of a specific kind of object. In general they will be composed of a collection
	of Struct and schema references.

- Forms or FormXXX:
	These files contain a form structure, these should generally be composed of tags,
	schema nodes and terms that can be used as categories.

- Templates or TemplateXXX:
	These files contain template definitions, the nodes that comprise their element will
	generally be alias nodes, to provide instructions and examples to data providers.

