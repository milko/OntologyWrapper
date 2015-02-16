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
#   Initialise institutes                                                              #
########################################################################################

#
# Init institutes.
#
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/4-Init_Institutes.php

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

exit
