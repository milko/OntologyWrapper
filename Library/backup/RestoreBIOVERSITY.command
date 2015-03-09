#!/bin/bash

#
# This script will restore the data.
#

#
# Run command.
#
mongorestore --db=BIOVERSITY \
			 --drop \
			 '/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY/'

exit
