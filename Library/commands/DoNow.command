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
# Load Households.
#
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/LoadFromSQLArchive.php \
	"MySQLi://$1:$2@localhost/bioversity_archive?$SOCKET&persist" \
	"abdh" \
	"mongodb://localhost:27017/BIOVERSITY"

#
# Backup and archive households.
#
rm -R "/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY"
mongodump --host="localhost" \
		  --port="27017" \
		  --db="BIOVERSITY" \
		  --out='/Library/WebServer/Library/OntologyWrapper/Library/backup/data'
rm "/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY.test.base.zip"
ditto -c -k --sequesterRsrc --keepParent \
	"/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY" \
	"/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY.test.base.zip"
rm -R "/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY"

########################################################################################
#   Load templates                                                                     #
########################################################################################

#
# Load templates.
#
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Init_Templates.php

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

########################################################################################
#   Backup and archive database                                                        #
########################################################################################

#
# Backup and archive.
#
rm -R "/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY"
mongodump --host="localhost" \
		  --port="27017" \
		  --db="BIOVERSITY" \
		  --out='/Library/WebServer/Library/OntologyWrapper/Library/backup/data'
rm "/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY.test.zip"
ditto -c -k --sequesterRsrc --keepParent \
	"/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY" \
	"/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY.test.zip"
rm -R "/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY"

exit
