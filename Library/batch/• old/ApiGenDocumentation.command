#!/bin/bash

#
# This script will generate the APIGEN documentation.
#

#
# Run command.
#
apigen --source '/Library/WebServer/Library/OntologyWrapper/Library/OntologyWrapper' \
	   --source '/Library/WebServer/Library/OntologyWrapper/Library/definitions' \
	   --destination '/Library/WebServer/Library/OntologyWrapper/Library/Documentation' \
	   --main 'OntologyWrapper' \
	   --title 'Ontology Wrapper Reference Documentation' \
	   --allowed-html b,i,a,ul,ol,li,p,br,var,samp,kbd,tt,h3,h4

exit
