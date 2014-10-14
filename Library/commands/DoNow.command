#!/bin/bash

#
# This script will perform the current set of operations.
#
# $1: User.
# $2: Pass.
#

########################################################################################
#   Handle Missions                                                                    #
########################################################################################

#
# Load Missions.
#
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/LoadFromSQLArchive.php \
	"MySQLi://$1:$2@localhost/bioversity_archive?socket=/tmp/mysql.sock&persist" \
	"cmdb_mission" \
	"mongodb://localhost:27017/BIOVERSITY"

########################################################################################
#   Handle Collecting Missions                                                         #
########################################################################################

#
# Load Collecting Missions.
#
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/LoadFromSQLArchive.php \
	"MySQLi://$1:$2@localhost/bioversity_archive?socket=/tmp/mysql.sock&persist" \
	"cmdb_collecting" \
	"mongodb://localhost:27017/BIOVERSITY"

########################################################################################
#   Backup and archive database                                                        #
########################################################################################

#
# Backup and archive.
#
rm -R "/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY"
mongodump --directoryperdb \
		  --db 'BIOVERSITY' \
		  --out '/Library/WebServer/Library/OntologyWrapper/Library/backup/data'
rm "/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY.miss.zip"
ditto -c -k --sequesterRsrc --keepParent \
	"/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY" \
	"/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY.miss.zip"
rm -R "/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY"

exit
