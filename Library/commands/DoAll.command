#!/bin/bash

#
# This script will archive and load all data.
#
# $1: User.
# $2: Pass.
#

#
# Init data dictionary.
#
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/1-Init_Base.php

#
# Archive EUFGIS.
#
#php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/ArchiveEufgisToSQLDb.php \
#	"MySQLi://$1:$2@localhost/bioversity?socket=/tmp/mysql.sock&persist" \
#	"MySQLi://$1:$2@localhost/bioversity_archive?socket=/tmp/mysql.sock&persist" \
#	"eufgis" \
#	"mongodb://localhost:27017/BIOVERSITY"

#
# Load EUFGIS.
#
#php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/LoadFromSQLArchive.php \
#	"MySQLi://$1:$2@localhost/bioversity_archive?socket=/tmp/mysql.sock&persist" \
#	"eufgis" \
#	"mongodb://localhost:27017/BIOVERSITY"

#
# Archive GRIN.
#
#php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/ArchiveGrinToSQLDb.php \
#	"MySQLi://$1:$2@localhost/bioversity?socket=/tmp/mysql.sock&persist" \
#	"MySQLi://$1:$2@localhost/bioversity_archive?socket=/tmp/mysql.sock&persist" \
#	"grin" \
#	"mongodb://localhost:27017/BIOVERSITY"

#
# Load GRIN.
#
#php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/LoadFromSQLArchive.php \
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
# Load SINGER.
#
#php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/LoadFromSQLArchive.php \
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

#
# Load EURISCO.
#
#php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/LoadFromSQLArchive.php \
#	"MySQLi://$1:$2@localhost/bioversity_archive?socket=/tmp/mysql.sock&persist" \
#	"eurisco" \
#	"mongodb://localhost:27017/BIOVERSITY"

exit
