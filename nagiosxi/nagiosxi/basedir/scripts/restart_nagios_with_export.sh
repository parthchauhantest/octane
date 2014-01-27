#!/bin/sh
# Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
# $Id: restart_nagios_with_export.sh 787 2011-08-10 17:45:08Z mguthrie $

# Export configuration from NagiosQL
./export_nagiosql.sh
ret=$?
if [ $ret -gt 0 ]; then
	exit $ret
fi



# Verify Nagios configuration
output=`/usr/local/nagios/bin/nagios -v /usr/local/nagios/etc/nagios.cfg`

ret=$?
echo "OUTPUT: $output"
echo "RET: $ret"

#exit 1

# Config was okay, so restart
if [ $ret -eq 0 ]; then

    # Restart Nagios
    /etc/init.d/nagios restart
	ret=$?
	if [ $ret -gt 0 ]; then
		exit 6
	fi

    # Make a new NOM checkpoint
    ./nom_create_nagioscore_checkpoint.sh &

    exit 0

# There was a problem with the config, so restore older config from last NOM checkpoint
else
    # Make a new NOM error checkpoint
    ./nom_create_nagioscore_errorpoint.sh

	# Restore the last known good checkpoint
    ./nom_restore_nagioscore_checkpoint.sh

    exit 1
fi


