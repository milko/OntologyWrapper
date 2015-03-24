#!/bin/bash

#
# This script will backup the data.
#

#
# Run command.
#
mongodump --db 'MAURICIO' \
		  --out '/Library/WebServer/Library/OntologyWrapper/Library/backup/data'

exit
