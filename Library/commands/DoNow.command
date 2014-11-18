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
#   Handle Missions                                                                    #
########################################################################################

#
# Archive Missions.
#
mysql --host=localhost --user=$1 --password=$2 \
	  --$SOCKET --database=bioversity_archive \
	  --execute="TRUNCATE TABLE cmdb_mission"
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/ArchiveMissionToSQLDb.php \
	"MySQLi://$1:$2@localhost/bioversity?$SOCKET&persist" \
	"MySQLi://$1:$2@localhost/bioversity_archive?$SOCKET&persist" \
	"cmdb_mission" \
	"mongodb://localhost:27017/BIOVERSITY"

#
# Load Missions.
#
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/LoadFromSQLArchive.php \
	"MySQLi://$1:$2@localhost/bioversity_archive?$SOCKET&persist" \
	"cmdb_mission" \
	"mongodb://localhost:27017/BIOVERSITY"

########################################################################################
#   Handle Collecting Missions                                                         #
########################################################################################

#
# Archive Collecting Missions.
#
mysql --host=localhost --user=$1 --password=$2 \
	  --$SOCKET --database=bioversity_archive \
	  --execute="TRUNCATE TABLE cmdb_collecting"
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/ArchiveCollectingMissionToSQLDb.php \
	"MySQLi://$1:$2@localhost/bioversity?$SOCKET&persist" \
	"MySQLi://$1:$2@localhost/bioversity_archive?$SOCKET&persist" \
	"cmdb_collecting" \
	"mongodb://localhost:27017/BIOVERSITY"

#
# Load Collecting Missions.
#
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/LoadFromSQLArchive.php \
	"MySQLi://$1:$2@localhost/bioversity_archive?$SOCKET&persist" \
	"cmdb_collecting" \
	"mongodb://localhost:27017/BIOVERSITY"

########################################################################################
#   Handle Collecting Samples                                                          #
########################################################################################

#
# Archive Collecting Samples.
#
mysql --host=localhost --user=$1 --password=$2 \
	  --$SOCKET --database=bioversity_archive \
	  --execute="TRUNCATE TABLE cmdb_sample"
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/ArchiveCollectingSampleToSQLDb.php \
	"MySQLi://$1:$2@localhost/bioversity?$SOCKET&persist" \
	"MySQLi://$1:$2@localhost/bioversity_archive?$SOCKET&persist" \
	"cmdb_sample" \
	"mongodb://localhost:27017/BIOVERSITY"

#
# Load Collecting Samples.
#
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/LoadFromSQLArchive.php \
	"MySQLi://$1:$2@localhost/bioversity_archive?$SOCKET&persist" \
	"cmdb_sample" \
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
rm "/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY.6.miss.zip"
ditto -c -k --sequesterRsrc --keepParent \
	"/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY" \
	"/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY.6.miss.zip"
rm -R "/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY"

exit
