#!/bin/bash

#
# This script will perform the current set of operations.
#
# $1: User.
# $2: Pass.
#

########################################################################################
#   Initialise database                                                                #
########################################################################################

#
# Init data dictionary.
#
#php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/1-Init_Base.php

#
# Backup and archive base dictionary.
#
#mongodump --directoryperdb \
#		  --db 'BIOVERSITY' \
#		  --out '/Library/WebServer/Library/OntologyWrapper/Library/backup/data'
#rm "/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY.1.base.zip"
#ditto -c -k --sequesterRsrc --keepParent \
#	"/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY" \
#	"/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY.1.base.zip"
#rm -R "/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY"

#
# Init main data.
#
#php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/2-Init_Main.php

#
# Backup and archive main dictionary.
#
#mongodump --directoryperdb \
#		  --db 'BIOVERSITY' \
#		  --out '/Library/WebServer/Library/OntologyWrapper/Library/backup/data'
#rm "/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY.2.main.zip"
#ditto -c -k --sequesterRsrc --keepParent \
#	"/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY" \
#	"/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY.2.main.zip"
#rm -R "/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY"

########################################################################################
#   Create XML archives                                                                #
########################################################################################

#
# Archive Households.
#
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/ArchiveHouseholdToSQLDb.php \
	"MySQLi://$1:$2@localhost/bioversity?socket=/tmp/mysql.sock&persist" \
	"MySQLi://$1:$2@localhost/bioversity_archive?socket=/tmp/mysql.sock&persist" \
	"abdh" \
	"mongodb://localhost:27017/BIOVERSITY"

#
# Archive CWR checklist.
#
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/ArchiveCwrCkToSQLDb.php \
	"MySQLi://$1:$2@localhost/bioversity?socket=/tmp/mysql.sock&persist" \
	"MySQLi://$1:$2@localhost/bioversity_archive?socket=/tmp/mysql.sock&persist" \
	"cwr_ck" \
	"mongodb://localhost:27017/BIOVERSITY"

#
# Archive CWR inventory.
#
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/ArchiveCwrInToSQLDb.php \
	"MySQLi://$1:$2@localhost/bioversity?socket=/tmp/mysql.sock&persist" \
	"MySQLi://$1:$2@localhost/bioversity_archive?socket=/tmp/mysql.sock&persist" \
	"cwr_in" \
	"mongodb://localhost:27017/BIOVERSITY"

#
# Archive GRIN CWR inventory.
#
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/ArchiveCwrGrinToSQLDb.php \
	"MySQLi://$1:$2@localhost/bioversity?socket=/tmp/mysql.sock&persist" \
	"MySQLi://$1:$2@localhost/bioversity_archive?socket=/tmp/mysql.sock&persist" \
	"grin_cwr" \
	"mongodb://localhost:27017/BIOVERSITY"

#
# Archive EUFGIS.
#
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/ArchiveEufgisToSQLDb.php \
"MySQLi://$1:$2@localhost/bioversity?socket=/tmp/mysql.sock&persist" \
	"MySQLi://$1:$2@localhost/bioversity_archive?socket=/tmp/mysql.sock&persist" \
	"eufgis" \
	"mongodb://localhost:27017/BIOVERSITY"

#
# Archive GRIN.
#
#php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/ArchiveGrinToSQLDb.php \
#	"MySQLi://$1:$2@localhost/bioversity?socket=/tmp/mysql.sock&persist" \
#	"MySQLi://$1:$2@localhost/bioversity_archive?socket=/tmp/mysql.sock&persist" \
#	"grin" \
#	"mongodb://localhost:27017/BIOVERSITY"

#
# Archive SINGER.
#
#php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/ArchiveSingerToSQLDb.php \
#	"MySQLi://$1:$2@localhost/bioversity?socket=/tmp/mysql.sock&persist" \
#	"MySQLi://$1:$2@localhost/bioversity_archive?socket=/tmp/mysql.sock&persist" \
#	"singer" \
#	"mongodb://localhost:27017/BIOVERSITY"

#
# Archive EURISCO.
#
#php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/ArchiveEuriscoToSQLDb.php \
#	"MySQLi://$1:$2@localhost/EURISCO_ITW?socket=/tmp/mysql.sock&persist" \
#	"MySQLi://$1:$2@localhost/bioversity_archive?socket=/tmp/mysql.sock&persist" \
#	"eurisco" \
#	"mongodb://localhost:27017/BIOVERSITY"

########################################################################################
#   Load households from XML archives                                                  #
########################################################################################

#
# Load Households.
#
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/LoadFromSQLArchive.php \
	"MySQLi://$1:$2@localhost/bioversity_archive?socket=/tmp/mysql.sock&persist" \
	"abdh" \
	"mongodb://localhost:27017/MAURICIO"

#
# Backup and archive households.
#
mongodump --directoryperdb \
		  --db 'MAURICIO' \
		  --out '/Library/WebServer/Library/OntologyWrapper/Library/backup/data'
rm "/Library/WebServer/Library/OntologyWrapper/Library/backup/data/MAURICIO.2.data.zip"
ditto -c -k --sequesterRsrc --keepParent \
	"/Library/WebServer/Library/OntologyWrapper/Library/backup/data/MAURICIO" \
	"/Library/WebServer/Library/OntologyWrapper/Library/backup/data/MAURICIO.2.data.zip"
rm -R "/Library/WebServer/Library/OntologyWrapper/Library/backup/data/MAURICIO"

########################################################################################
#   Load others from XML archives                                                      #
########################################################################################

#
# Load CWR checklist.
#
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/LoadFromSQLArchive.php \
	"MySQLi://$1:$2@localhost/bioversity_archive?socket=/tmp/mysql.sock&persist" \
	"cwr_ck" \
	"mongodb://localhost:27017/BIOVERSITY"

#
# Backup and archive crop wild relatives checklist.
#
mongodump --directoryperdb \
		  --db 'BIOVERSITY' \
		  --out '/Library/WebServer/Library/OntologyWrapper/Library/backup/data'
rm "/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY.3.cwr_ck.zip"
ditto -c -k --sequesterRsrc --keepParent \
	"/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY" \
	"/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY.3.cwr_ck.zip"
rm -R "/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY"

#
# Load EUFGIS.
#
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/LoadFromSQLArchive.php \
	"MySQLi://$1:$2@localhost/bioversity_archive?socket=/tmp/mysql.sock&persist" \
	"eufgis" \
	"mongodb://localhost:27017/BIOVERSITY"

#
# Backup and archive EUFGIS.
#
mongodump --directoryperdb \
		  --db 'BIOVERSITY' \
		  --out '/Library/WebServer/Library/OntologyWrapper/Library/backup/data'
rm "/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY/.4.eufgis.zip"
ditto -c -k --sequesterRsrc --keepParent \
	"/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY" \
	"/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY.4.eufgis.zip"
rm -R "/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY"

#
# Load GRIN.
#
#php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/LoadFromSQLArchive.php \
#	"MySQLi://$1:$2@localhost/bioversity_archive?socket=/tmp/mysql.sock&persist" \
#	"grin" \
#	"mongodb://localhost:27017/BIOVERSITY"

#
# Backup and archive GRIN.
#
#mongodump --directoryperdb \
#		  --db 'BIOVERSITY' \
#		  --out '/Library/WebServer/Library/OntologyWrapper/Library/backup/data'
#rm "/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY/.5.grin.zip"
#ditto -c -k --sequesterRsrc --keepParent \
#	"/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY" \
#	"/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY.5.grin.zip"
#rm -R "/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY"

#
# Load SINGER.
#
#php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/LoadFromSQLArchive.php \
#	"MySQLi://$1:$2@localhost/bioversity_archive?socket=/tmp/mysql.sock&persist" \
#	"singer" \
#	"mongodb://localhost:27017/BIOVERSITY"

#
# Backup and archive SINGER.
#
#mongodump --directoryperdb \
#		  --db 'BIOVERSITY' \
#		  --out '/Library/WebServer/Library/OntologyWrapper/Library/backup/data'
#rm "/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY/.6.singer.zip"
#ditto -c -k --sequesterRsrc --keepParent \
#	"/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY" \
#	"/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY.6.singer.zip"
#rm -R "/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY"

#
# Load EURISCO.
#
#php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/LoadFromSQLArchive.php \
#	"MySQLi://$1:$2@localhost/bioversity_archive?socket=/tmp/mysql.sock&persist" \
#	"eurisco" \
#	"mongodb://localhost:27017/BIOVERSITY"

#
# Backup and archive EURISCO.
#
#mongodump --directoryperdb \
#		  --db 'BIOVERSITY' \
#		  --out '/Library/WebServer/Library/OntologyWrapper/Library/backup/data'
#rm "/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY/.7.eurisco.zip"
#ditto -c -k --sequesterRsrc --keepParent \
#	"/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY" \
#	"/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY.7.eurisco.zip"
#rm -R "/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY"

exit
