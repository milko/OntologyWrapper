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
	"MySQLi://WEB-SERVICES:webservicereader@192.168.181.190/pgrdg?socket=/var/mysql/mysql.sock&persist" \
	"mongodb://localhost:27017/PGRDG"

#
# Load CWR checklists.
#
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/LoadChecklistsFromSQLDb.php \
	"MySQLi://WEB-SERVICES:webservicereader@192.168.181.190/pgrdg?socket=/var/mysql/mysql.sock&persist" \
	"mongodb://localhost:27017/PGRDG"

#
# Load CWR inventories.
#
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/LoadCWRInventoriesFromSQLDb.php \
	"MySQLi://WEB-SERVICES:webservicereader@192.168.181.190/pgrdg?socket=/var/mysql/mysql.sock&persist" \
	"mongodb://localhost:27017/PGRDG"

#
# Load CWR accessions.
#
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/LoadCWRAccessionsFromServerSQLDb.php \
	"MySQLi://WEB-SERVICES:webservicereader@192.168.181.190/MCPD?socket=/var/mysql/mysql.sock&persist" \
	"mongodb://localhost:27017/PGRDG"

#
# Load EUFGIS accessions.
#
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/LoadEUFGISAccessionsFromServerSQLDb.php \
	"MySQLi://WEB-SERVICES:webservicereader@192.168.181.190/MCPD?socket=/var/mysql/mysql.sock&persist" \
	"mongodb://localhost:27017/PGRDG"

exit
