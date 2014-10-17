#!/bin/bash

#
# This script will perform the current set of operations.
#
# $1: User.
# $2: Pass.
#

########################################################################################
#   Handle checklists                                                                  #
########################################################################################

#
# Load CWR checklist.
#
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/LoadFromSQLArchive.php \
	"MySQLi://$1:$2@localhost/bioversity_archive?socket=/tmp/mysql.sock&persist" \
	"cwr_ck" \
	"mongodb://localhost:27017/BIOVERSITY"

########################################################################################
#   Handle inventories                                                                 #
########################################################################################

#
# Load CWR inventory.
#
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/LoadFromSQLArchive.php \
	"MySQLi://$1:$2@localhost/bioversity_archive?socket=/tmp/mysql.sock&persist" \
	"cwr_in" \
	"mongodb://localhost:27017/BIOVERSITY"

########################################################################################
#   Handle GRIN CWR inventory                                                          #
########################################################################################

#
# Load GRIN CWR inventory.
#
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/LoadFromSQLArchive.php \
	"MySQLi://$1:$2@localhost/bioversity_archive?socket=/tmp/mysql.sock&persist" \
	"grin_cwr" \
	"mongodb://localhost:27017/BIOVERSITY"

########################################################################################
#   Handle EUFGIS                                                                      #
########################################################################################

#
# Load EUFGIS.
#
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/LoadFromSQLArchive.php \
	"MySQLi://$1:$2@localhost/bioversity_archive?socket=/tmp/mysql.sock&persist" \
	"eufgis" \
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
rm "/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY.data.zip"
ditto -c -k --sequesterRsrc --keepParent \
	"/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY" \
	"/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY.data.zip"
rm -R "/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY"

########################################################################################
#   Handle Missions                                                                    #
########################################################################################

#
# Archive Missions.
#
mysql --host=localhost --user=$1 --password=$2 \
	  --socket=/tmp/mysql.sock --database=bioversity_archive \
	  --execute="TRUNCATE TABLE cmdb_mission"
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/ArchiveMissionToSQLDb.php \
	"MySQLi://$1:$2@localhost/bioversity?socket=/tmp/mysql.sock&persist" \
	"MySQLi://$1:$2@localhost/bioversity_archive?socket=/tmp/mysql.sock&persist" \
	"cmdb_mission" \
	"mongodb://localhost:27017/BIOVERSITY"

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
# Archive Collecting Missions.
#
mysql --host=localhost --user=$1 --password=$2 \
	  --socket=/tmp/mysql.sock --database=bioversity_archive \
	  --execute="TRUNCATE TABLE cmdb_collecting"
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/ArchiveCollectingMissionToSQLDb.php \
	"MySQLi://$1:$2@localhost/bioversity?socket=/tmp/mysql.sock&persist" \
	"MySQLi://$1:$2@localhost/bioversity_archive?socket=/tmp/mysql.sock&persist" \
	"cmdb_collecting" \
	"mongodb://localhost:27017/BIOVERSITY"

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
rm "/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY.coll.zip"
ditto -c -k --sequesterRsrc --keepParent \
	"/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY" \
	"/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY.coll.zip"
rm -R "/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY"

exit
