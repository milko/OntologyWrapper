#!/bin/bash

#
# This script will perform the current set of operations.
#
# $1: User.
# $2: Pass.
#

########################################################################################
#   Load households from XML archives                                                  #
########################################################################################

#
# Load Households.
#
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/LoadFromSQLArchive.php \
	"MySQLi://$1:$2@localhost/bioversity_archive?socket=/tmp/mysql.sock&persist" \
	"abdh" \
	"mongodb://localhost:27017/MAURICIO"

#
# Backup and archive households.
#
rm -R "/Library/WebServer/Library/OntologyWrapper/Library/backup/data/MAURICIO"
mongodump --directoryperdb \
		  --db 'MAURICIO' \
		  --out '/Library/WebServer/Library/OntologyWrapper/Library/backup/data'
rm "/Library/WebServer/Library/OntologyWrapper/Library/backup/data/MAURICIO.data.zip"
ditto -c -k --sequesterRsrc --keepParent \
	"/Library/WebServer/Library/OntologyWrapper/Library/backup/data/MAURICIO" \
	"/Library/WebServer/Library/OntologyWrapper/Library/backup/data/MAURICIO.data.zip"
rm -R "/Library/WebServer/Library/OntologyWrapper/Library/backup/data/MAURICIO"

exit
