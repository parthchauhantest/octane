#!/bin/sh -e

# Set up system variables
./init.sh
. ./xi-sys.cfg

## UNINSTALL NOTICE #########################################

fmt -s -w $(tput cols) <<-EOF
	==================================
	!! DESTRUCTIVE UNINSTALL NOTICE !!
	==================================
	WARNING: This script will uninstall Nagios from this system.
	This action is irreversible and will result in the removal of
	all Nagios databases, configuration files, log files, and services.

EOF

read -p "Are you sure you want to continue? [y/N] " res

if [ "$res" = "y" -o "$res" = "Y" ]; then
	echo "Proceeding with uninstall..."
else
	echo "Uninstall cancelled"
	exit 1
fi

# Stop services
echo "Stopping services..."
service nagios stop
service npcd stop
service ndo2db stop

# Remove sudoers
echo "Removing suduoers..."
rm -f /etc/sudoers.d/nagiosxi

# Remove crontabs
echo "Removing crontabs..."
rm -f /etc/cron.d/nagiosxi
./uninstall-crontab-nagios
./uninstall-crontab-root

# Remove Nagios Core files
echo "Removing Nagios Core files..."
rm -rf /usr/local/nagios

# Remove Nagios XI files
echo "Removing Nagios XI files..."
rm -rf /usr/local/nagiosxi

# Remove NagiosQL files
echo "Removing NagiosQL files..."
rm -f /etc/nagiosql

# Remove MySQL databases
echo "Removing MySQL databases..."
mysql -u root -p"$mysqlpass" -e "DROP DATABASE nagios"
mysql -u root -p"$mysqlpass" -e "DROP DATABASE nagiosql"
service mysqld restart

# Remove Postgres databases
echo "Removing Postgres databases..."
psql -U nagiosxi "$pgsqlpass" -c "DROP DATABASE nagios"
service pgsql restart

# Remove DB backup scripts
echo "Removing database backup scripts..."
rm -f /root/scripts/automysqlbackup
rm -f /root/scripts/autopostgresqlbackup

# Remove Nagios user account and group
echo "Removing user and group accounts..."
eval "$userdelbin" -r "$nagiosuser"
eval "$groupdelbin" -r "$nagiosgroup"

# Remove Apache configs
echo "Removing Apache configs..."
rm -f ${httpdconfdir}/nagios.conf
rm -f ${httpdconfdir}/nagiosxi.conf
rm -f ${httpdconfdir}/nagiosql.conf
rm -f ${httpdconfdir}/nrdp.conf
value service ${httpd} restart

# Remove xinetd configs
echo "Removing xinetd configs..."
rm -f /etc/xinetd.d/nrpe
rm -f /etc/xinetd.d/nsca
rm -f /etc/xinetd.d/nrdp
service xinetd restart


#################################################
# TODO (MAYBE)
#################################################

# Remove automysql/pgsql backup scripts (and backup directory)
# NOTE: Do NOT do this!

# Remove firewall settings
# Don't remove (Fusion or other products may be installed)

# Remove MRTG mods
# Don't remove in case MRTG is used by other apps...

# Remove SELinux mods
# Don't need...

# Remove PHP limits mods
# Don't need this anymore...

# Remove SourceGuardian
# Don't remove (Fusion or other products may be installed)


fmt -s -w $(tput cols) <<-EOF
	====================
	UNINSTALL COMPLETED!
	====================

	NOTE: The following items were left unmodified:
		- Database backup scripts and files
		- Firewall rules 
		- MRTG
		- SourceGuardian Apache loaders
	
EOF

