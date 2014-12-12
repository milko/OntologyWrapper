#!/bin/bash

#
# This script will perform the current set of operations.
#
# $1: User.
# $2: Pass.
#

########################################################################################
#   Constants                                                                          #
########################################################################################
SOCKET="socket=/tmp/mysql.sock"

########################################################################################
#   Handle households                                                                  #
########################################################################################

#
# Load Households.
#
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/LoadFromSQLArchive.php \
	"MySQLi://$1:$2@localhost/bioversity_archive?$SOCKET&persist" \
	"abdh" \
	"mongodb://localhost:27017/BIOVERSITY"

#
# Backup and archive households.
#
rm -R "/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY"
mongodump --directoryperdb \
		  --db 'BIOVERSITY' \
		  --out '/Library/WebServer/Library/OntologyWrapper/Library/backup/data'
rm "/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY.test.zip"
ditto -c -k --sequesterRsrc --keepParent \
	"/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY" \
	"/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY.test.zip"
rm -R "/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY"

exit
