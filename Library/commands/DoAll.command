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
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/1-Init_Base.php

#
# Backup and archive base dictionary.
#
rm -R "/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY"
mongodump --directoryperdb \
		  --db 'BIOVERSITY' \
		  --out '/Library/WebServer/Library/OntologyWrapper/Library/backup/data'
rm "/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY.1.base.zip"
ditto -c -k --sequesterRsrc --keepParent \
	"/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY" \
	"/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY.1.base.zip"
rm -R "/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY"

########################################################################################
#   Initialise dict                                                                    #
########################################################################################

#
# Init data structures.
#
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/2-Init_Dict.php

#
# Backup and archive main dictionary.
#
rm -R "/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY"
mongodump --directoryperdb \
		  --db 'BIOVERSITY' \
		  --out '/Library/WebServer/Library/OntologyWrapper/Library/backup/data'
rm "/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY.2.dict.zip"
ditto -c -k --sequesterRsrc --keepParent \
	"/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY" \
	"/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY.2.dict.zip"
rm -R "/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY"

########################################################################################
#   Initialise data                                                                    #
########################################################################################

#
# Init data collections.
#
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/3-Init_Data.php

#
# Backup and archive main dictionary.
#
rm -R "/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY"
mongodump --directoryperdb \
		  --db 'BIOVERSITY' \
		  --out '/Library/WebServer/Library/OntologyWrapper/Library/backup/data'
rm "/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY.3.data.zip"
ditto -c -k --sequesterRsrc --keepParent \
	"/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY" \
	"/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY.3.data.zip"
rm -R "/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY"

########################################################################################
#   Handle households                                                                  #
########################################################################################

#
# Archive Households.
#
mysql --host=localhost --user=$1 --password=$2 \
	  --socket=/tmp/mysql.sock --database=bioversity_archive \
	  --execute="TRUNCATE TABLE abdh"
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/ArchiveHouseholdToSQLDb.php \
	"MySQLi://$1:$2@localhost/bioversity?socket=/tmp/mysql.sock&persist" \
	"MySQLi://$1:$2@localhost/bioversity_archive?socket=/tmp/mysql.sock&persist" \
	"abdh" \
	"mongodb://localhost:27017/MAURICIO"

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
rm -R "/Library/WebServer/Library/OntologyWrapper/Library/backup/data/MAURICIO"
mongodump --directoryperdb \
		  --db 'MAURICIO' \
		  --out '/Library/WebServer/Library/OntologyWrapper/Library/backup/data'
rm "/Library/WebServer/Library/OntologyWrapper/Library/backup/data/MAURICIO.data.zip"
ditto -c -k --sequesterRsrc --keepParent \
	"/Library/WebServer/Library/OntologyWrapper/Library/backup/data/MAURICIO" \
	"/Library/WebServer/Library/OntologyWrapper/Library/backup/data/MAURICIO.data.zip"
rm -R "/Library/WebServer/Library/OntologyWrapper/Library/backup/data/MAURICIO"

########################################################################################
#   Handle checklists                                                                  #
########################################################################################

#
# Archive CWR checklist.
#
mysql --host=localhost --user=$1 --password=$2 \
	  --socket=/tmp/mysql.sock --database=bioversity_archive \
	  --execute="TRUNCATE TABLE cwr_ck"
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/ArchiveCwrCkToSQLDb.php \
	"MySQLi://$1:$2@localhost/bioversity?socket=/tmp/mysql.sock&persist" \
	"MySQLi://$1:$2@localhost/bioversity_archive?socket=/tmp/mysql.sock&persist" \
	"cwr_ck" \
	"mongodb://localhost:27017/BIOVERSITY"

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
# Archive CWR inventory.
#
mysql --host=localhost --user=$1 --password=$2 \
	  --socket=/tmp/mysql.sock --database=bioversity_archive \
	  --execute="TRUNCATE TABLE cwr_in"
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/ArchiveCwrInToSQLDb.php \
	"MySQLi://$1:$2@localhost/bioversity?socket=/tmp/mysql.sock&persist" \
	"MySQLi://$1:$2@localhost/bioversity_archive?socket=/tmp/mysql.sock&persist" \
	"cwr_in" \
	"mongodb://localhost:27017/BIOVERSITY"

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
# Archive GRIN CWR inventory.
#
mysql --host=localhost --user=$1 --password=$2 \
	  --socket=/tmp/mysql.sock --database=bioversity_archive \
	  --execute="TRUNCATE TABLE grin_cwr"
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/ArchiveCwrGrinToSQLDb.php \
	"MySQLi://$1:$2@localhost/bioversity?socket=/tmp/mysql.sock&persist" \
	"MySQLi://$1:$2@localhost/bioversity_archive?socket=/tmp/mysql.sock&persist" \
	"grin_cwr" \
	"mongodb://localhost:27017/BIOVERSITY"

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
# Archive EUFGIS.
#
mysql --host=localhost --user=$1 --password=$2 \
	  --socket=/tmp/mysql.sock --database=bioversity_archive \
	  --execute="TRUNCATE TABLE eufgis"
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/ArchiveEufgisToSQLDb.php \
	"MySQLi://$1:$2@localhost/bioversity?socket=/tmp/mysql.sock&persist" \
	"MySQLi://$1:$2@localhost/bioversity_archive?socket=/tmp/mysql.sock&persist" \
	"eufgis" \
	"mongodb://localhost:27017/BIOVERSITY"

#
# Load EUFGIS.
#
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/LoadFromSQLArchive.php \
	"MySQLi://$1:$2@localhost/bioversity_archive?socket=/tmp/mysql.sock&persist" \
	"eufgis" \
	"mongodb://localhost:27017/BIOVERSITY"

########################################################################################
#   Handle QTLs                                                                        #
########################################################################################

#
# Archive QTL.
#
mysql --host=localhost --user=$1 --password=$2 \
	  --socket=/tmp/mysql.sock --database=bioversity_archive \
	  --execute="TRUNCATE TABLE qtl"
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/ArchiveQtlToSQLDb.php \
	"MySQLi://$1:$2@localhost/bioversity?socket=/tmp/mysql.sock&persist" \
	"MySQLi://$1:$2@localhost/bioversity_archive?socket=/tmp/mysql.sock&persist" \
	"qtl" \
	"mongodb://localhost:27017/BIOVERSITY"

#
# Load QTL.
#
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/LoadFromSQLArchive.php \
	"MySQLi://$1:$2@localhost/bioversity_archive?socket=/tmp/mysql.sock&persist" \
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
rm "/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY.4.insitu.zip"
ditto -c -k --sequesterRsrc --keepParent \
	"/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY" \
	"/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY.4.insitu.zip"
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
#   Handle Collecting Samples                                                          #
########################################################################################

#
# Archive Collecting Samples.
#
mysql --host=localhost --user=$1 --password=$2 \
	  --socket=/tmp/mysql.sock --database=bioversity_archive \
	  --execute="TRUNCATE TABLE cmdb_sample"
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/ArchiveCollectingSampleToSQLDb.php \
	"MySQLi://$1:$2@localhost/bioversity?socket=/tmp/mysql.sock&persist" \
	"MySQLi://$1:$2@localhost/bioversity_archive?socket=/tmp/mysql.sock&persist" \
	"cmdb_sample" \
	"mongodb://localhost:27017/BIOVERSITY"

#
# Load Collecting Samples.
#
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/LoadFromSQLArchive.php \
	"MySQLi://$1:$2@localhost/bioversity_archive?socket=/tmp/mysql.sock&persist" \
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
rm "/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY.5.miss.zip"
ditto -c -k --sequesterRsrc --keepParent \
	"/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY" \
	"/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY.5.miss.zip"
rm -R "/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY"

########################################################################################
#   Relate Missions                                                                    #
########################################################################################

#
# Relate Missions.
#
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/UpdateMissionRelated.php \
	"mongodb://localhost:27017/BIOVERSITY"

#
# Relate Collecting Missions.
#
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/UpdateCollectingMissionRelated.php \
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

########################################################################################
#   Archive SINGER                                                                     #
########################################################################################

#
# Archive SINGER.
#
mysql --host=localhost --user=$1 --password=$2 \
	  --socket=/tmp/mysql.sock --database=bioversity_archive \
	  --execute="TRUNCATE TABLE singer"
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/ArchiveSingerToSQLDb.php \
	"MySQLi://$1:$2@localhost/bioversity?socket=/tmp/mysql.sock&persist" \
	"MySQLi://$1:$2@localhost/bioversity_archive?socket=/tmp/mysql.sock&persist" \
	"singer" \
	"mongodb://localhost:27017/BIOVERSITY"

########################################################################################
#   Archive GRIN                                                                       #
########################################################################################

#
# Archive GRIN.
#
mysql --host=localhost --user=$1 --password=$2 \
	  --socket=/tmp/mysql.sock --database=bioversity_archive \
	  --execute="TRUNCATE TABLE grin"
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/ArchiveGrinToSQLDb.php \
	"MySQLi://$1:$2@localhost/bioversity?socket=/tmp/mysql.sock&persist" \
	"MySQLi://$1:$2@localhost/bioversity_archive?socket=/tmp/mysql.sock&persist" \
	"grin" \
	"mongodb://localhost:27017/BIOVERSITY"

########################################################################################
#   Archive EURISCO                                                                    #
########################################################################################

#
# Archive EURISCO.
#
mysql --host=localhost --user=$1 --password=$2 \
	  --socket=/tmp/mysql.sock --database=bioversity_archive \
	  --execute="TRUNCATE TABLE eurisco"
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/ArchiveEuriscoToSQLDb.php \
	"MySQLi://$1:$2@localhost/EURISCO_ITW?socket=/tmp/mysql.sock&persist" \
	"MySQLi://$1:$2@localhost/bioversity_archive?socket=/tmp/mysql.sock&persist" \
	"eurisco" \
	"mongodb://localhost:27017/BIOVERSITY"

########################################################################################
#   Load SINGER from XML archives                                                      #
########################################################################################

#
# Load SINGER.
#
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/LoadFromSQLArchive.php \
	"MySQLi://$1:$2@localhost/bioversity_archive?socket=/tmp/mysql.sock&persist" \
	"singer" \
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
rm "/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY.7.singer.zip"
ditto -c -k --sequesterRsrc --keepParent \
	"/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY" \
	"/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY.7.singer.zip"
rm -R "/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY"

########################################################################################
#   Load GRIN from XML archives                                                       #
########################################################################################

#
# Load GRIN.
#
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/LoadFromSQLArchive.php \
	"MySQLi://$1:$2@localhost/bioversity_archive?socket=/tmp/mysql.sock&persist" \
	"grin" \
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
rm "/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY.8.grin.zip"
ditto -c -k --sequesterRsrc --keepParent \
	"/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY" \
	"/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY.8.grin.zip"
rm -R "/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY"

########################################################################################
#   Load EURISCO from XML archives                                                     #
########################################################################################

#
# Load EURISCO.
#
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/LoadFromSQLArchive.php \
	"MySQLi://$1:$2@localhost/bioversity_archive?socket=/tmp/mysql.sock&persist" \
	"eurisco" \
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
rm "/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY.9.eurisco.zip"
ditto -c -k --sequesterRsrc --keepParent \
	"/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY" \
	"/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY.9.eurisco.zip"
rm -R "/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY"

exit
