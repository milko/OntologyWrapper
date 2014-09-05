#!/bin/bash

#
# This script will restore the data.
#

#
# Run command.
#
mongorestore --directoryperdb --drop '/Library/WebServer/Library/OntologyWrapper/Library/backup/data/PGRDG/'

exit
