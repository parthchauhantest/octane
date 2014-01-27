#!/bin/sh
# Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
# $Id: export_nagiosql.sh 787 2011-08-10 17:45:08Z mguthrie $

# Fix permissions on config files to make sure NagiosQL can write data
./reset_config_perms

#error handling
ret=$?
if [ $ret -gt 0 ]; then
	echo "RESETTING CONFIG PERMS FAILED!\n"
	exit 4
fi

# Login to NagiosQL
/usr/bin/php -q nagiosql_login.php

#error handling
ret=$?
if [ $ret -gt 0 ]; then
	echo "NAGIOSQL LOGIN FAILED!\n"
	exit $ret
fi

# Export all data
/usr/bin/php -q nagiosql_exportall.php

#error handling
ret=$?
if [ $ret -gt 0 ]; then
	echo "NAGIOSQL WRITE CONFIGS FAILED!\n"
	exit $ret
fi