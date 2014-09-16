#!/bin/bash

#
# This script will archive SINGER in the SQL archive.
#

#
# Load EUFGIS.
#
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/ArchiveSingerToSQLDb.php \
	"MySQLi://root:Bogomil@localhost/bioversity?socket=/tmp/mysql.sock&persist" \
	"MySQLi://root:Bogomil@localhost/bioversity_archive?socket=/tmp/mysql.sock&persist" \
	"singer" \
	"mongodb://localhost:27017/BIOVERSITY"

exit
