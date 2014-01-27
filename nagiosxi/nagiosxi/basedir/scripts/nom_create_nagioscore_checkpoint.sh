#!/bin/sh
# Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
# $Id: nom_create_nagioscore_checkpoint.sh 1300 2012-07-17 19:47:23Z swilkerson $

cfgdir="/usr/local/nagios/etc"
checkpointdir="/usr/local/nagiosxi/nom/checkpoints/nagioscore"

# Fix permissions on config files
./reset_config_perms

pushd $checkpointdir

# What timestamp should we use for this files?
stamp=`date +%s`

# Get Nagios verification output
output=`/usr/local/nagios/bin/nagios -v /usr/local/nagios/etc/nagios.cfg > $stamp.txt`

# Create a tarball backup of the configuration directory
tar czfp $stamp.tar.gz $cfgdir

# Fix perms (if script run by root)
chown nagios:nagios $stamp.txt
chown nagios:nagios $stamp.tar.gz

popd

# Create NagiosQL restore point
restore_point=`/usr/local/nagiosxi/scripts/nagiosql_snapshot.sh $stamp`
