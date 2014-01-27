#!/bin/sh

# $Id: reset_config_perms.sh 1331 2012-08-20 18:58:01Z swilkerson $
#
# NOTE: This script is called by the reset_config_perms compiled executable 
# to bypass setuid root restrictions on some distros

# Fix permissions on config files
echo "RESETTING PERMS"
/bin/chown -R apache:nagios /usr/local/nagios/etc/
/bin/chmod -R ug+rw /usr/local/nagios/etc/
/bin/chmod -R 775 /usr/local/nagios/share/perfdata/
#added this for rename component 2012 -MG
/bin/chown -R nagios.nagios /usr/local/nagios/share/perfdata
/bin/chmod 775 /usr/local/nagios/libexec
#added this for NagiosQL restore script -SW
/bin/chown nagios:nagios /usr/local/nagiosxi/nom/checkpoints/nagiosxi

#chmod ug+rw /usr/local/nagios/etc/*.cfg
#chmod -R ug+rw /usr/local/nagios/etc/hosts/
#chmod -R ug+rw /usr/local/nagios/etc/services/