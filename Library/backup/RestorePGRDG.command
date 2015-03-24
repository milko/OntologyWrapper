#!/bin/bash

#
# This script will restore the data.
#

#
# Run command.
#
mongorestore --db="PGRDG" \
			 --drop \
			 '/Library/WebServer/Library/OntologyWrapper/Library/backup/data/PGRDG/'

exit
