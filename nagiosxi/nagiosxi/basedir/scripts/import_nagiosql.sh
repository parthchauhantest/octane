#!/bin/sh
# Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
# $Id: import_nagiosql.sh 787 2011-08-10 17:45:08Z mguthrie $

# Login to NagiosQL
/usr/bin/php -q nagiosql_login.php

#error handling
ret=$?
if [ $ret -gt 0 ]; then
	echo "NAGIOSQL LOGIN FAILED!"
	exit $ret
fi

# Import all data
/usr/bin/php -q nagiosql_importall.php

ret=$?
if [ $ret -gt 0 ]; then
	echo "NAGIOSQL IMPORT FAILED!"
	exit $ret
fi