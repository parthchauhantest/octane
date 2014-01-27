#!/bin/sh

# This script repairs one (or all) tables in a specific Nagios XI mysql database
# Usage:
# repairmysql.sh [database] [table]

if [ $# -lt 1 ]; then
	echo "Usage: $0 <database> [table]"
	echo ""
	echo "This script repairs one or more tables in a specific Nagios XI MySQL database."
	echo "Valid database names include:"
	echo "   nagios";
	echo "   nagiosql";
	echo ""
	echo "If the [table] option is omitted, all tables in the database will be repaired."
	echo ""
	echo "Example Usage:"
	echo "   $0 nagios nagios_logentries"
	echo ""
	exit 1
fi

db=$1
table="";
if [ $# -eq 2 ]; then
	table=$2
fi

echo "DATABASE: $db"
echo "TABLE:    $table"

cmd="/usr/bin/myisamchk -r -f"

if [ "x$table" == "x" ]; then
	t="*.MYI"
else
	t=$table;
fi

dest="/var/lib/mysql/$db"
pushd $dest
ret=$?
if [ $ret -eq 0 ]; then
	/sbin/service mysqld stop
	$cmd $t
	/sbin/service mysqld start
	popd
else
	echo "ERROR: Could not change to dir: $dest"
	exit 1
fi

echo " "
echo "==============="
echo "REPAIR COMPLETE"
echo "==============="

exit 0