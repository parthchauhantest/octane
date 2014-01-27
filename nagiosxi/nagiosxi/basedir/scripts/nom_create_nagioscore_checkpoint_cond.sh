#!/bin/sh
# Create a conditional NOM checkpoint
# Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
# $Id: nom_create_nagioscore_checkpoint_cond.sh 75 2010-04-01 19:40:08Z egalstad $

scriptsdir=/usr/local/nagiosxi/scripts

/etc/init.d/nagios checkconfig
ret=$?


if [ $ret -eq 0 ]; then
    pushd $scriptsdir
    ./nom_create_nagioscore_checkpoint.sh
    popd
    echo "Config test passed.  Checkpoint created."
    exit 0
else
    echo "Config test failed.  Checkpoint aborted."
    exit 1
fi