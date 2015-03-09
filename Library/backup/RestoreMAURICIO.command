#!/bin/bash

#
# This script will restore the data.
#

#
# Run command.
#
mongorestore --db=MAURICIO \
			 --drop \
			 '/Library/WebServer/Library/OntologyWrapper/Library/backup/data/MAURICIO/'

exit
