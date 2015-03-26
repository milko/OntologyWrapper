#!/bin/bash

#
# This script will restore the data.
#

#
# Run command.
#
mongorestore --db=TEST \
			 --drop \
			 '/Library/WebServer/Library/OntologyWrapper/Library/backup/data/TEST/'

exit
