#!/bin/sh
# Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
# $Id: nagiosql_delete_object.sh 75 2010-04-01 19:40:08Z egalstad $

# What object should we delete?
if [ $# -ne 2 ]; then
    echo "Incorrect arguments"
    exit
fi

otype=$1
shift
id=$1

if [ 'x${otype}' == 'x' ]; then
exit
fi
if [ 'x${id}' == 'x' ]; then
exit
fi

# Login to NagiosQL
/usr/bin/php -q nagiosql_login.php

# Delete the object
cmd="/usr/bin/php -q nagiosql_delete_${otype}.php --id=${id}"
echo $cmd
$cmd


