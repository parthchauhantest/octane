#!/bin/sh
# Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
# $Id: nagiosql_trim_backups.sh 75 2010-04-01 19:40:08Z egalstad $

# Get rid of backups older than 24 hours
find /etc/nagiosql/backup -mmin +1440 -type f -exec rm -f {} \;


