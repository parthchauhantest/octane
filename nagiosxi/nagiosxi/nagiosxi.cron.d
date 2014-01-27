# /etc/cron.d/nagiosxi: crontab fragment for nagiosxi

# Backup MySQL & PostgreSQL Databases
0   7 * * * root   /root/scripts/automysqlbackup
0   8 * * * root   /root/scripts/autopostgresqlbackup

*   * * * * nagios /usr/bin/php -q /usr/local/nagiosxi/cron/sysstat.php > /usr/local/nagiosxi/var/sysstat.log 2>&1
*   * * * * nagios /usr/bin/php -q /usr/local/nagiosxi/cron/cmdsubsys.php > /usr/local/nagiosxi/var/cmdsubsys.log 2>&1
*   * * * * nagios /usr/bin/php -q /usr/local/nagiosxi/cron/eventman.php > /usr/local/nagiosxi/var/eventman.log 2>&1
*   * * * * nagios /usr/bin/php -q /usr/local/nagiosxi/cron/feedproc.php > /usr/local/nagiosxi/var/feedproc.log 2>&1
*   * * * * nagios /usr/bin/php -q /usr/local/nagiosxi/cron/perfdataproc.php > /usr/local/nagiosxi/var/perfdataproc.log 2>&1
*   * * * * nagios /usr/bin/php -q /usr/local/nagiosxi/cron/nom.php > /usr/local/nagiosxi/var/nom.log 2>&1
*   * * * * nagios /usr/bin/php -q /usr/local/nagiosxi/cron/reportengine.php > /usr/local/nagiosxi/var/reportengine.log 2>&1
*/5 * * * * nagios /usr/bin/php -q /usr/local/nagiosxi/cron/dbmaint.php > /usr/local/nagiosxi/var/dbmaint.log 2>&1
*   * * * * nagios /usr/bin/php -q /usr/local/nagiosxi/cron/cleaner.php > /usr/local/nagiosxi/var/cleaner.log 2>&1
01  * * * * nagios /usr/local/nagiosxi/cron/recurringdowntime.pl > /usr/local/nagiosxi/var/recurringdowntime.log 2>&1

