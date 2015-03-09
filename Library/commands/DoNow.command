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
#   Initialise institutes                                                              #
########################################################################################

#
# Init institutes.
#
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/4-Init_Institutes.php

########################################################################################
#   Initialise users                                                                   #
########################################################################################

#
# Init users.
#
php -f /Library/WebServer/Library/OntologyWrapper/Library/settings/ResetUsers.php \
	"mongodb://localhost:27017/BIOVERSITY"

########################################################################################
#   Backup                                                                             #
########################################################################################

#
# Backup and archive main dictionary.
#
rm -R "/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY"
mongodump --directoryperdb \
		  --db 'BIOVERSITY' \
		  --out '/Library/WebServer/Library/OntologyWrapper/Library/backup/data'
rm "/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY.4.inst.zip"
ditto -c -k --sequesterRsrc --keepParent \
	"/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY" \
	"/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY.4.inst.zip"
rm -R "/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY"

########################################################################################
#   Handle checklists                                                                  #
########################################################################################

#
# Load CWR checklist.
#
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/LoadFromSQLArchive.php \
	"MySQLi://$1:$2@localhost/bioversity_archive?$SOCKET&persist" \
	"cwr_ck" \
	"mongodb://localhost:27017/BIOVERSITY"

########################################################################################
#   Handle inventories                                                                 #
########################################################################################

#
# Load CWR inventory.
#
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/LoadFromSQLArchive.php \
	"MySQLi://$1:$2@localhost/bioversity_archive?$SOCKET&persist" \
	"cwr_in" \
	"mongodb://localhost:27017/BIOVERSITY"

########################################################################################
#   Handle GRIN CWR inventory                                                          #
########################################################################################

#
# Load GRIN CWR inventory.
#
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/LoadFromSQLArchive.php \
	"MySQLi://$1:$2@localhost/bioversity_archive?$SOCKET&persist" \
	"grin_cwr" \
	"mongodb://localhost:27017/BIOVERSITY"

########################################################################################
#   Handle EUFGIS                                                                      #
########################################################################################

#
# Load EUFGIS.
#
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/LoadFromSQLArchive.php \
	"MySQLi://$1:$2@localhost/bioversity_archive?$SOCKET&persist" \
	"eufgis" \
	"mongodb://localhost:27017/BIOVERSITY"

########################################################################################
#   Handle QTLs                                                                        #
########################################################################################

#
# Load QTL.
#
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/LoadFromSQLArchive.php \
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

########################################################################################
#   Load templates                                                                     #
########################################################################################

#
# Load templates.
#
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Init_Templates.php

########################################################################################
#   Handle households                                                                  #
########################################################################################

#
# Load Households.
#
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/LoadFromSQLArchive.php \
	"MySQLi://$1:$2@localhost/bioversity_archive?$SOCKET&persist" \
	"abdh" \
	"mongodb://localhost:27017/BIOVERSITY"

########################################################################################
#   Build indexes                                                                      #
########################################################################################

#
# Build indexes.
#
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/BuildIndex.php "mongodb://localhost:27017/BIOVERSITY" ":inventory:dataset" N
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/BuildIndex.php "mongodb://localhost:27017/BIOVERSITY" ":location:country" N
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/BuildIndex.php "mongodb://localhost:27017/BIOVERSITY" "mcpd:INSTCODE" N
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/BuildIndex.php "mongodb://localhost:27017/BIOVERSITY" "mcpd:SAMPSTAT" N
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/BuildIndex.php "mongodb://localhost:27017/BIOVERSITY" "mcpd:COLLSRC" N
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/BuildIndex.php "mongodb://localhost:27017/BIOVERSITY" "mcpd:COLLCODE" N
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/BuildIndex.php "mongodb://localhost:27017/BIOVERSITY" "mcpd:DUPLSITE" N
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/BuildIndex.php "mongodb://localhost:27017/BIOVERSITY" ":location:admin-1" N
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/BuildIndex.php "mongodb://localhost:27017/BIOVERSITY" ":location:admin-2" N
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/BuildIndex.php "mongodb://localhost:27017/BIOVERSITY" ":taxon:epithet" N
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/BuildIndex.php "mongodb://localhost:27017/BIOVERSITY" ":taxon:genus" N
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/BuildIndex.php "mongodb://localhost:27017/BIOVERSITY" ":taxon:species:name" N
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/BuildIndex.php "mongodb://localhost:27017/BIOVERSITY" ":taxon:crop" N
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/BuildIndex.php "mongodb://localhost:27017/BIOVERSITY" ":taxon:crop:category" N
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/BuildIndex.php "mongodb://localhost:27017/BIOVERSITY" ":taxon:crop:group" N

exit
