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
#   Load users                                                                         #
########################################################################################

#
# Load users.
#
php -f /Library/WebServer/Library/OntologyWrapper/Library/settings/ResetUsers.php \
	"mongodb://localhost:27017/BIOVERSITY"

########################################################################################
#   Load updates                                                                       #
########################################################################################

#
# Load updates.
#
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/LoadXMLFile.php \
	"/Library/WebServer/Library/OntologyWrapper/Library/standards/UPDATES.xml" \
	"mongodb://localhost:27017/BIOVERSITY"

########################################################################################
#   Load templates                                                                     #
########################################################################################

#
# Load updates.
#
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/Init_Templates.php

exit
