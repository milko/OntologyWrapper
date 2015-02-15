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
#   Load templates                                                                     #
########################################################################################

#
# Load templates.
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
#   Build indexes                                                                      #
########################################################################################

#
# Build indexes.
#
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/BuildIndex.php "mongodb://localhost:27017/BIOVERSITY" ":inventory:dataset" N
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/BuildIndex.php "mongodb://localhost:27017/BIOVERSITY" ":location:country" N
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/BuildIndex.php "mongodb://localhost:27017/BIOVERSITY" "mcpd:INSTCODE" N
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/BuildIndex.php "mongodb://localhost:27017/BIOVERSITY" "mcpd:SAMPSTAT" N
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/BuildIndex.php "mongodb://localhost:27017/BIOVERSITY" "mcpd:COLLSRC" N
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/BuildIndex.php "mongodb://localhost:27017/BIOVERSITY" "mcpd:COLLCODE" N
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/BuildIndex.php "mongodb://localhost:27017/BIOVERSITY" "mcpd:DUPLSITE" N
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/BuildIndex.php "mongodb://localhost:27017/BIOVERSITY" ":location:admin-1" N
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/BuildIndex.php "mongodb://localhost:27017/BIOVERSITY" ":location:admin-2" N
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/BuildIndex.php "mongodb://localhost:27017/BIOVERSITY" ":taxon:epithet" N
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/BuildIndex.php "mongodb://localhost:27017/BIOVERSITY" ":taxon:genus" N
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/BuildIndex.php "mongodb://localhost:27017/BIOVERSITY" ":taxon:species:name" N
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/BuildIndex.php "mongodb://localhost:27017/BIOVERSITY" ":taxon:crop" N
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/BuildIndex.php "mongodb://localhost:27017/BIOVERSITY" ":taxon:crop:category" N
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/BuildIndex.php "mongodb://localhost:27017/BIOVERSITY" ":taxon:crop:group" N


exit
