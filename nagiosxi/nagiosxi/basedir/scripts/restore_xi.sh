#!/bin/sh

# Make sure we have the backup file
if [ $# != 1 ]; then
	echo "Usage: $0 <backupfile>"
	echo "This script restores your XI system using a previously made Nagios XI backup file."
	exit 1
fi
backupfile=$1

# MySQL root password
mysqlpass="nagiosxi"

# Must be root
me=`whoami`
if [ $me != "root" ]; then
	echo "You must be root to run this script."
	exit 1
fi

rootdir=/store/backups/nagiosxi

##############################
# MAKE SURE BACKUP FILE EXIST
##############################
if [ ! -f $backupfile ]; then
	echo "Unable to find backup file $backupfile!"
	exit 1
fi

##############################
# MAKE TEMP RESTORE DIRECTORY
##############################
#ts=`echo $backupfile | cut -d . -f 1`
ts=`date +%s`
echo "TS=$ts"
mydir=${rootdir}/${ts}-restore
mkdir -p $mydir
if [ ! -d $mydir ]; then
	echo "Unable to create restore directory $mydir!"
	exit 1
fi


##############################
# UNZIP BACKUP
##############################
echo "Extracting backup to $mydir..."
cd $mydir
tar xzfps $backupfile

# Change to subdirectory
cd `ls`

# Make sure we have some directories here...
backupdir=`pwd`
echo "In $backupdir..."
if [ ! -f nagiosxi.tar.gz ]; then
	echo "Unable to find files to restore in $backupdir"
	exit 1
fi

echo "Backup files look okay.  Preparing to restore..."


##############################
# SHUTDOWN SERVICES
##############################
echo "Shutting down services..."
/etc/init.d/nagios stop
/etc/init.d/nagiosxi stop
/etc/init.d/ndo2db stop
/etc/init.d/npcd stop


##############################
# RESTORE DIRS
##############################
rootdir=/
echo "Restoring directories to ${rootdir}..."

# Nagios Core
echo "Restoring Nagios Core..."
rm -rf /usr/local/nagios
cd $rootdir && tar xzf $backupdir/nagios.tar.gz 

# Nagios xI
echo "Restoring Nagios XI..."
rm -rf /usr/local/nagiosxi
cd $rootdir && tar xzfps $backupdir/nagiosxi.tar.gz 

# NagiosQL
echo "Restoring NagiosQL..."
rm -rf /var/www/html/nagiosql
cd $rootdir && tar xzfps $backupdir/nagiosql.tar.gz 

# NagiosQL etc
echo "Restoring NagiosQL backups..."
rm -rf /etc/nagiosql
cd $rootdir && tar xzfps $backupdir/nagiosql-etc.tar.gz 


cd $backupdir

##############################
# RESTORE DATABASES
##############################

echo "Restoring MySQL databases..."
#mysql -u root --password=$mysqlpass nagios < mysql/nagios.sql
#mysql -u root --password=$mysqlpass nagiosql < mysql/nagiosql.sql
mysql -u root --password=$mysqlpass < mysql/nagios.sql
res=$?
if [ $res != 0 ]; then
	echo "Error restoring MySQL database 'nagios' - check the password in this script!"
	exit;
fi

mysql -u root --password=$mysqlpass < mysql/nagiosql.sql
res=$?
if [ $res != 0 ]; then
	echo "Error restoring MySQL database 'nagiosql' - check the password in this script!"
	exit;
fi

echo "Restoring PostgresQL databases..."
psql -U nagiosxi nagiosxi < pgsql/nagiosxi.sql
res=$?
if [ $res != 0 ]; then
	echo "Error restoring PostgresQL database 'nagiosxi' !"
	exit;
fi

echo "Restarting database servers..."
/etc/init.d/mysqld restart
/etc/init.d/postgresql restart

##############################
# RESTORE CRONJOB ENTRIES
##############################
# Not necessary

##############################
# RESTORE SUDOERS
##############################
# Not necessary

##############################
# RESTORE LOGROTATE
##############################
echo "Restoring logrotate config files..."
cp -rp logrotate/nagiosxi /etc/logrotate.d

##############################
# RESTORE APACHE CONFIG FILES
##############################
echo "Restoring Apache config files..."
cp -rp httpd/*.conf /etc/httpd/conf.d


##############################
# RESTART SERVICES
##############################
/etc/init.d/httpd restart
/etc/init.d/npcd start
/etc/init.d/ndo2db start
/etc/init.d/nagiosxi start
/etc/init.d/nagios start

##############################
# DELETE TEMP RESTORE DIRECTORY
##############################

rm -rf $mydir

echo " "
echo "==============="
echo "RESTORE COMPLETE"
echo "==============="

exit 0;