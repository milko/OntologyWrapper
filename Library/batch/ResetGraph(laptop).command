#!/bin/bash

#
# This script will reset the graph database.
#

#
# Stop database.
#
launchctl unload -w "/users/milko/Library/LaunchAgents/org.neo4j.server.plist"

#
# Delete data directory.
#
rm -r /usr/local/opt/neo4j/libexec/data/*

#
# Start database.
#
launchctl load -w "/users/milko/Library/LaunchAgents/org.neo4j.server.plist"

exit
