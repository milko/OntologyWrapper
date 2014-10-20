#!/bin/bash

#
# This script will perform the current set of operations.
#
# $1: User.
# $2: Pass.
#

########################################################################################
#   Handle Collecting Samples                                                          #
########################################################################################

#
# Load Collecting Samples.
#
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/LoadFromSQLArchive.php \
	"MySQLi://$1:$2@localhost/bioversity_archive?socket=/tmp/mysql.sock&persist" \
	"cmdb_sample" \
	"mongodb://localhost:27017/BIOVERSITY"

exit
