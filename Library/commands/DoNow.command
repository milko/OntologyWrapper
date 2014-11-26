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
# Archive Households.
#
mysql --host=localhost --user=$1 --password=$2 \
	  --$SOCKET --database=bioversity_archive \
	  --execute="TRUNCATE TABLE abdh"
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/ArchiveHouseholdToSQLDb.php \
	"MySQLi://$1:$2@localhost/bioversity?$SOCKET&persist" \
	"MySQLi://$1:$2@localhost/bioversity_archive?$SOCKET&persist" \
	"abdh" \
	"mongodb://localhost:27017/BIOVERSITY"

#
# Load Households.
#
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/LoadFromSQLArchive.php \
	"MySQLi://$1:$2@localhost/bioversity_archive?$SOCKET&persist" \
	"abdh" \
	"mongodb://localhost:27017/BIOVERSITY"

exit
