#!/bin/sh -e
#get nagiosQL credentials
CONFIG=/var/www/html/nagiosql/config/settings.php
valid_fields=(server database username password install )
while read line; do
    if [[ ! "$line" =~ ^[^\#\;]*= ]]; then
        continue
    fi
    if [[ "${valid_fields[@]}" =~ `echo $line | cut -d'=' -f 1` ]];then
        eval "${line//[[:space:]]}"
    else
        continue
    fi
done < "$CONFIG"

rootdir=/usr/local/nagiosxi/nom/checkpoints/nagiosxi
if [ ! -d "$rootdir" ];then
    mkdir -p "$rootdir"
    chown nagios:nagios "$rootdir"
fi
if [[ $# == 0 || "$1" == "-h" || "$1" == "-help" ]]; then
        echo "Usage: $0 <snapshot_timestamp> [restore]"
        echo "This script restores your XI system using a previously made Nagios XI snapshot file."
        exit 1
fi
ts=$1
restore=$2
if [ ! -d $rootdir ]; then
 mkdir -p $rootdir
fi

if [[ "$restore" != "restore" ]]; then
  echo "taking snapshot"
 mysqldump -h $server -u $username -p$password $database | gzip > $rootdir/${ts}_nagiosql.sql.gz
  res=${PIPESTATUS[0]}
  if [ $res != 0 ]; then
	echo "Error creating MySQL snapshot 'nagiosql' - check the password in /var/www/html/nagiosql/config/settings.php"
	exit 1;
  else
   chown nagios:nagios $rootdir/${ts}_nagiosql.sql.gz
  fi

echo "Backup Complete."
else
  ##############################
  # RESTORE SNAPSHOT
  ##############################
  echo "Restoring NagiosQL snapshot"
  ##############################
  # MAKE SURE SNAPSHOT FILE EXIST
  ##############################
  if [[ ! -f $rootdir/${ts}_nagiosql.sql.gz ]]; then
	echo "Unable to find required snapshot files!"
	exit 1
  fi
    
  echo "Removing old files from /usr/local/nagios/etc"
  (
    cd /usr/local/nagiosxi/scripts
    ./nom_restore_nagioscore_checkpoint_specific.sh ${ts}
  )
  echo "Restoring NagiosQL databases..."
  gunzip < $rootdir/${ts}_nagiosql.sql.gz | mysql -h $server -u $username -p$password nagiosql
  res=$?
  if [ "$res" != 0 ]; then
	echo "Error restoring MySQL database 'nagiosql' - check the password in /var/www/html/nagiosql/config/settings.php"
	exit 1;
  fi

  echo "Restore Complete."
  
fi
exit 0
