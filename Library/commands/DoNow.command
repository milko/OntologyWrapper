#!/bin/bash

#
# This script will perform the current set of operations.
#
# $1: User.
# $2: Pass.
#

########################################################################################
#   Archive GRIN                                                                       #
########################################################################################

#
# Archive GRIN.
#
mysql --host=localhost --user=$1 --password=$2 \
	  --socket=/tmp/mysql.sock --database=bioversity_archive \
	  --execute="TRUNCATE TABLE grin"
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/ArchiveGrinToSQLDb.php \
	"MySQLi://$1:$2@localhost/bioversity?socket=/tmp/mysql.sock&persist" \
	"MySQLi://$1:$2@localhost/bioversity_archive?socket=/tmp/mysql.sock&persist" \
	"grin" \
	"mongodb://localhost:27017/BIOVERSITY"

exit
