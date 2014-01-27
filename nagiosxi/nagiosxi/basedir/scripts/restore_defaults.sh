#!/bin/sh

HOST="localhost"
USER="root"
PASSWORD="nagiosxi"
DATABASE="nagiosql"
BACKUP="nagiosql_defaults.sql"

mysqldump -h "$HOST" -u "$USER" -p"$PASSWORD" "$DATABASE" > /usr/local/nagiosxi/var/nagiosql_backup."$(date +%s)".sql

mysql -h "$HOST" -u "$USER" -p"$PASSWORD" "$DATABASE" < "$BACKUP"

cd /usr/local/nagios/etc
rm -f \
commands.cfg \
contactgroups.cfg \
contacts.cfg \
contacttemplates.cfg \
hostdependencies.cfg \
hostescalations.cfg \
hostextinfo.cfg \
hostgroups.cfg \
hosts/* \
hosttemplates.cfg \
servicedependencies.cfg \
serviceescalations.cfg \
serviceextinfo.cfg \
servicegroups.cfg \
services/* \
servicetemplates.cfg \
timeperiods.cfg
cd /usr/local/nagiosxi/scripts

/usr/local/nagiosxi/scripts/reconfigure_nagios.sh

sleep 5
echo ""
echo ""
