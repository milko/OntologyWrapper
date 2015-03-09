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
start=$(date +"%s")
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/1-Init_Base.php
finish=$(date +"%s")
difftimelps=$(($finish-$start))
echo "$(($difftimelps / 60)) minutes and $(($difftimelps % 60)) seconds."
echo

########################################################################################
#   Initialise dict                                                                    #
########################################################################################

#
# Init data structures.
#
start=$(date +"%s")
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/2-Init_Dict.php
finish=$(date +"%s")
difftimelps=$(($finish-$start))
echo "$(($difftimelps / 60)) minutes and $(($difftimelps % 60)) seconds."
echo

########################################################################################
#   Initialise data                                                                    #
########################################################################################

#
# Init data collections.
#
start=$(date +"%s")
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/3-Init_Data.php
finish=$(date +"%s")
difftimelps=$(($finish-$start))
echo "$(($difftimelps / 60)) minutes and $(($difftimelps % 60)) seconds."
echo

########################################################################################
#   Initialise institutes                                                              #
########################################################################################

#
# Init institutes.
#
start=$(date +"%s")
php -f /Library/WebServer/Library/OntologyWrapper/Library/batch/4-Init_Institutes.php
finish=$(date +"%s")
difftimelps=$(($finish-$start))
echo
echo "$(($difftimelps / 60)) minutes and $(($difftimelps % 60)) seconds."
echo

exit
