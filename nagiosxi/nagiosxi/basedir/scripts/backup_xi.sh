#!/bin/sh

# MySQL root password
mysqlpass="nagiosxi"

# Must be root
me=`whoami`
if [ $me != "root" ]; then
	echo "You must be root to run this script."
	exit 1
fi

rootdir=/store/backups/nagiosxi

# Make root directory to store backups
mkdir -p $rootdir
cd $rootdir

# Get current Unix timestamp
ts=`date +%s`

# My working directory
mydir=$rootdir/$ts

# Make directory for this specific backup
mkdir -p $mydir

##############################
# BACKUP DIRS
##############################

echo "Backing up Core Config Manager (NagiosQL)..."
#cp -rp /var/www/html/nagiosql $mydir
#cp -rp /etc/nagiosql $mydir/nagiosql-etc
tar czfps $mydir/nagiosql.tar.gz /var/www/html/nagiosql
tar czfps $mydir/nagiosql-etc.tar.gz /etc/nagiosql

echo "Backing up Nagios Core..."
#cp -rp /usr/local/nagios $mydir
tar czfps $mydir/nagios.tar.gz /usr/local/nagios

echo "Backing up Nagios XI..."
#cp -rp /usr/local/nagiosxi $mydir
tar czfps $mydir/nagiosxi.tar.gz /usr/local/nagiosxi

##############################
# BACKUP DATABASES
##############################
echo "Backing up MySQL databases..."
mkdir -p $mydir/mysql
mysqldump -u root --password=$mysqlpass --add-drop-database -B nagios > $mydir/mysql/nagios.sql
res=$?
if [ $res != 0 ]; then
	echo "Error backing up MySQL database 'nagios' - check the password in this script!"
	exit;
fi
mysqldump -u root --password=$mysqlpass --add-drop-database -B nagiosql > $mydir/mysql/nagiosql.sql
res=$?
if [ $res != 0 ]; then
	echo "Error backing up MySQL database 'nagiosql' - check the password in this script!"
	exit;
fi

echo "Backing up PostgresQL databases..."
mkdir -p $mydir/pgsql
pg_dump -c -U nagiosxi nagiosxi > $mydir/pgsql/nagiosxi.sql
res=$?
if [ $res != 0 ]; then
	echo "Error backing up PostgresQL database 'nagiosxi' !"
	exit;
fi

##############################
# BACKUP CRONJOB ENTRIES
##############################
# Not necessary

##############################
# BACKUP SUDOERS
##############################
# Not necessary

##############################
# BACKUP LOGROTATE
##############################
echo "Backing up logrotate config files..."
mkdir -p $mydir/logrotate
cp -rp /etc/logrotate.d/nagiosxi $mydir/logrotate

##############################
# BACKUP APACHE CONFIG FILES
##############################
echo "Backing up Apache config files..."
mkdir -p $mydir/httpd
cp -rp /etc/httpd/conf.d/nagios.conf $mydir/httpd
cp -rp /etc/httpd/conf.d/nagiosxi.conf $mydir/httpd
cp -rp /etc/httpd/conf.d/nagiosql.conf $mydir/httpd

##############################
# ZIP BACKUP
##############################
echo "Compressing backup..."
tar czfps $ts.tar.gz $ts
rm -rf $ts

echo " "
echo "==============="
echo "BACKUP COMPLETE"
echo "==============="
echo "Backup stored in $rootdir/$ts.tar.gz"

exit 0;