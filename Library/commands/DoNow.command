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
#   Restore database dump                                                              #
########################################################################################

#
# Restore BIOVERSITY.
#
/Library/WebServer/Library/OntologyWrapper/Library/backup/RestoreBIOVERSITY.command

########################################################################################
#   Load users                                                                         #
########################################################################################

#
# Load users.
#
php -f /Library/WebServer/Library/OntologyWrapper/Library/settings/ResetUsers.php \
	"mongodb://localhost:27017/BIOVERSITY"

########################################################################################
#   Load updates                                                                       #
########################################################################################

#
# Load updates.
#
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/LoadXMLFile.php \
	"/Library/WebServer/Library/OntologyWrapper/Library/standards/UPDATES.xml" \
	"mongodb://localhost:27017/BIOVERSITY"

########################################################################################
#   Load templates                                                                     #
########################################################################################

#
# Load updates.
#
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/Init_Templates.php

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

########################################################################################
#   Backup                                                                             #
########################################################################################

#
# Backup and archive.
#
rm -R "/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY"
mongodump --directoryperdb \
		  --db 'BIOVERSITY' \
		  --out '/Library/WebServer/Library/OntologyWrapper/Library/backup/data'
rm "/Library/WebServer/Library/OntologyWrapper/Library/backup/data/TEST.zip"
ditto -c -k --sequesterRsrc --keepParent \
	"/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY" \
	"/Library/WebServer/Library/OntologyWrapper/Library/backup/data/TEST.zip"
rm -R "/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY"

exit
