#!/bin/bash

#
# This script will generate the DOXYGEN documentation.
#

#
# Run command.
#
phpdoc --target '/Library/WebServer/Library/OntologyWrapper/Library/Documentation' --directory '/Library/WebServer/Library/OntologyWrapper/Library/OntologyWrapper','/Library/WebServer/Library/OntologyWrapper/Library/definitions'

exit
