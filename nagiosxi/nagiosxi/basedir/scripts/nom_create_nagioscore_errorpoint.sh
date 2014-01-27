#!/bin/sh
# Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
# $Id: nom_create_nagioscore_errorpoint.sh 749 2011-07-28 20:11:10Z egalstad $

cfgdir="/usr/local/nagios/etc"
checkpointdir="/usr/local/nagiosxi/nom/checkpoints/nagioscore/errors"

pushd $checkpointdir

# What timestamp should we use for this files?
stamp=`date +%s`

# Get Nagios verification output
output=`/usr/local/nagios/bin/nagios -v /usr/local/nagios/etc/nagios.cfg > $stamp.txt`

# Create the tarball
tar czf $stamp.tar.gz $cfgdir

# Fix perms (if script run by root)
chown nagios:nagios $stamp.txt
chown nagios:nagios $stamp.tar.gz

popd
