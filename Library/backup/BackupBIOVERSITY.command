#!/bin/bash

#
# This script will backup the data.
#

#
# Run command.
#
mongodump --directoryperdb \
		  --db 'BIOVERSITY' \
		  --out '/Library/WebServer/Library/OntologyWrapper/Library/backup/data'

exit
