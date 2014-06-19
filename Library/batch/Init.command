#!/bin/bash

#
# This script will initialise and load the database.
#

#
# Init metadata.
#
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Init_All.php

#
# Load EUFGIS.
#
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/LoadEufgisFromSQLDb.php \
	"MySQLi://WEB-SERVICES:webservicereader@localhost/pgrdg?socket=/tmp/mysql.sock&persist" \
	"mongodb://localhost:27017/PGRDG"

#
# Load CWR checklists.
#
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/LoadChecklistsFromSQLDb.php \
	"MySQLi://WEB-SERVICES:webservicereader@localhost/pgrdg?socket=/tmp/mysql.sock&persist" \
	"mongodb://localhost:27017/PGRDG"

#
# Load CWR inventories.
#
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/LoadCWRInventoriesFromSQLDb.php \
	"MySQLi://WEB-SERVICES:webservicereader@localhost/pgrdg?socket=/tmp/mysql.sock&persist" \
	"mongodb://localhost:27017/PGRDG"

#
# Load CWR accessions.
#
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/LoadCWRAccessionsFromSQLDb.php \
	"MySQLi://WEB-SERVICES:webservicereader@localhost/mcpd?socket=/tmp/mysql.sock&persist" \
	"mongodb://localhost:27017/PGRDG"

#
# Load EUFGIS accessions.
#
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/LoadEUFGISAccessionsFromServerSQLDb.php \
	"MySQLi://WEB-SERVICES:webservicereader@localhost/mcpd?socket=/tmp/mysql.sock&persist" \
	"mongodb://localhost:27017/PGRDG"

exit
