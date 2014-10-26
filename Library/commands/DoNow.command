#!/bin/bash

#
# This script will perform the current set of operations.
#
# $1: User.
# $2: Pass.
#

########################################################################################
#   Relate Missions                                                                    #
########################################################################################

#
# Relate Missions.
#
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/Bioversity/UpdateMissionRelated.php \
	"mongodb://localhost:27017/BIOVERSITY"

exit
