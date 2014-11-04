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

########################################################################################
#   Handle checklists                                                                  #
########################################################################################

#
# Archive CWR checklist.
#
mysql --host=localhost --user=$1 --password=$2 \
	  --$SOCKET --database=bioversity_archive \
	  --execute="TRUNCATE TABLE cwr_ck"
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/ArchiveCwrCkToSQLDb.php \
	"MySQLi://$1:$2@localhost/bioversity?$SOCKET&persist" \
	"MySQLi://$1:$2@localhost/bioversity_archive?$SOCKET&persist" \
	"cwr_ck" \
	"mongodb://localhost:27017/BIOVERSITY"

#
# Load CWR checklist.
#
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/LoadFromSQLArchive.php \
	"MySQLi://$1:$2@localhost/bioversity_archive?$SOCKET&persist" \
	"cwr_ck" \
	"mongodb://localhost:27017/BIOVERSITY"

########################################################################################
#   Handle inventories                                                                 #
########################################################################################

#
# Archive CWR inventory.
#
mysql --host=localhost --user=$1 --password=$2 \
	  --$SOCKET --database=bioversity_archive \
	  --execute="TRUNCATE TABLE cwr_in"
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/ArchiveCwrInToSQLDb.php \
	"MySQLi://$1:$2@localhost/bioversity?$SOCKET&persist" \
	"MySQLi://$1:$2@localhost/bioversity_archive?$SOCKET&persist" \
	"cwr_in" \
	"mongodb://localhost:27017/BIOVERSITY"

#
# Load CWR inventory.
#
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/LoadFromSQLArchive.php \
	"MySQLi://$1:$2@localhost/bioversity_archive?$SOCKET&persist" \
	"cwr_in" \
	"mongodb://localhost:27017/BIOVERSITY"

########################################################################################
#   Handle GRIN CWR inventory                                                          #
########################################################################################

#
# Archive GRIN CWR inventory.
#
mysql --host=localhost --user=$1 --password=$2 \
	  --$SOCKET --database=bioversity_archive \
	  --execute="TRUNCATE TABLE grin_cwr"
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/ArchiveCwrGrinToSQLDb.php \
	"MySQLi://$1:$2@localhost/bioversity?$SOCKET&persist" \
	"MySQLi://$1:$2@localhost/bioversity_archive?$SOCKET&persist" \
	"grin_cwr" \
	"mongodb://localhost:27017/BIOVERSITY"

#
# Load GRIN CWR inventory.
#
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/LoadFromSQLArchive.php \
	"MySQLi://$1:$2@localhost/bioversity_archive?$SOCKET&persist" \
	"grin_cwr" \
	"mongodb://localhost:27017/BIOVERSITY"

########################################################################################
#   Handle EUFGIS                                                                      #
########################################################################################

#
# Archive EUFGIS.
#
mysql --host=localhost --user=$1 --password=$2 \
	  --$SOCKET --database=bioversity_archive \
	  --execute="TRUNCATE TABLE eufgis"
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/ArchiveEufgisToSQLDb.php \
	"MySQLi://$1:$2@localhost/bioversity?$SOCKET&persist" \
	"MySQLi://$1:$2@localhost/bioversity_archive?$SOCKET&persist" \
	"eufgis" \
	"mongodb://localhost:27017/BIOVERSITY"

#
# Load EUFGIS.
#
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/LoadFromSQLArchive.php \
	"MySQLi://$1:$2@localhost/bioversity_archive?$SOCKET&persist" \
	"eufgis" \
	"mongodb://localhost:27017/BIOVERSITY"

########################################################################################
#   Handle QTLs                                                                        #
########################################################################################

#
# Archive QTL.
#
mysql --host=localhost --user=$1 --password=$2 \
	  --$SOCKET --database=bioversity_archive \
	  --execute="TRUNCATE TABLE qtl"
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/ArchiveQtlToSQLDb.php \
	"MySQLi://$1:$2@localhost/bioversity?$SOCKET&persist" \
	"MySQLi://$1:$2@localhost/bioversity_archive?$SOCKET&persist" \
	"qtl" \
	"mongodb://localhost:27017/BIOVERSITY"

#
# Load QTL.
#
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/LoadFromSQLArchive.php \
	"MySQLi://$1:$2@localhost/bioversity_archive?$SOCKET&persist" \
	"qtl" \
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
rm "/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY.5.insitu.zip"
ditto -c -k --sequesterRsrc --keepParent \
	"/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY" \
	"/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY.5.insitu.zip"
rm -R "/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY"

exit
