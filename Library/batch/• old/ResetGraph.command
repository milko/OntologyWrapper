#!/bin/bash

#
# This script will reset the graph database.
#

#
# Stop database.
#
launchctl unload -w "/Users/milko/Library/LaunchAgents/org.neo4j.server.plist"

#
# Delete data directory.
#
rm -r /Volumes/Data/Neo4j/*

#
# Start database.
#
launchctl load -w "/Users/milko/Library/LaunchAgents/org.neo4j.server.plist"

exit
