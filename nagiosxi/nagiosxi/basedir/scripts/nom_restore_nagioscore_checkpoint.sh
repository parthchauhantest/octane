#!/bin/sh
# Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
# $Id: nom_restore_nagioscore_checkpoint.sh 75 2010-04-01 19:40:08Z egalstad $

cfgdir="/usr/local/nagios/etc"
checkpointdir="/usr/local/nagiosxi/nom/checkpoints/nagioscore"


# Find latest snapshot
latest=`ls -1r $checkpointdir/*.gz | head --lines=1`

if [ "x$latest" = "x" ]; then
    echo "NO NOM SNAPSHOT FOUND!"
    exit 1
fi

echo "LATEST NOM SNAPSHOT: $latest"

# Delete the current Nagios core config files
#find /usr/local/nagios/etc/ -name "*.cfg" -exec ls -al {} \;

# Restore config files from checkpoint file
pushd / 
echo "RESTORING NOM SNAPSHOT : $latest"
#tar -p -s -xzf "$checkpointdir/$latest"
tar -p -s -xzf "$latest"
popd

# Fix permissions on config files
./reset_config_perms



