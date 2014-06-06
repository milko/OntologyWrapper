#!/bin/bash

#
# This script will restore the data.
#

#
# Run command.
#
mongorestore --host 192.168.181.1 --port 27017 '/Library/WebServer/Library/OntologyWrapper/Library/backup/data/PGRDG/'

exit
