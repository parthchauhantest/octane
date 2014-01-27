#!/bin/sh
# Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
# $Id: nom_restore_nagioscore_checkpoint_specific.sh 1300 2012-07-17 19:47:23Z swilkerson $
#
# Restores a specific snapshot
# Requires a timestamp of the snapshot that should be restored

cfgdir="/usr/local/nagios/etc"
checkpointdir="/usr/local/nagiosxi/nom/checkpoints/nagioscore"

ts=$1


ss=$checkpointdir/$ts.tar.gz

if [ ! -f $ss ]; then
    echo "NOM SNAPSHOT $ss NOT FOUND!"
    exit 1
fi

# Delete the current Nagios core config files
#find /usr/local/nagios/etc/ -name "*.cfg" -exec ls -al {} \;
find /usr/local/nagios/etc/ -name "*.cfg" -exec rm -f {} \;

# Restore config files from checkpoint file
pushd / 
echo "RESTORING NOM SNAPSHOT : $ss"
tar -pxzf "$ss"
popd

# Fix permissions on config files
./reset_config_perms



