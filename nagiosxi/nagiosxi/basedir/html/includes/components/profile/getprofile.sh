#!/bin/bash


echo "-------------------Fetching Information-------------------"

echo "Please wait......."

tail -100 /usr/local/nagios/var/nagios.log &> /usr/local/nagiosxi/var/components/profile/nagios.txt;

echo "Creating nagios.txt...";

tail -100 /usr/local/nagios/var/perfdata.log &> /usr/local/nagiosxi/var/components/profile/perfdata.txt;

echo "Creating perfdata.txt...";

tail -100 /usr/local/nagios/var/npcd.log &> /usr/local/nagiosxi/var/components/profile/npcd.txt;

echo "Creating npcd.txt...";

tail -100 /usr/local/nagiosxi/var/cmdsubsys.log > /usr/local/nagiosxi/var/components/profile/cmdsubsys.txt;

echo "Creating cmdsubsys.txt...";

tail -100 /usr/local/nagiosxi/var/eventman.log > /usr/local/nagiosxi/var/components/profile/eventman.txt;

echo "Creating eventman.txt...";

sudo /usr/bin/tail -100 /var/log/messages > /usr/local/nagiosxi/var/components/profile/systemlog.txt;

echo "Creating systemlog.txt...";

sudo /usr/bin/tail -100 /var/log/httpd/error_log > /usr/local/nagiosxi/var/components/profile/apacheerrors.txt;

echo "Creating apacheerrors.txt...";

sudo /usr/bin/tail -100 /var/log/mysqld.log > /usr/local/nagiosxi/var/components/profile/mysqllog.txt;

echo "Creating mysqllog.txt...";

ps aux --sort -rss > /usr/local/nagiosxi/var/components/profile/memorybyprocess.txt

df -h > /usr/local/nagiosxi/var/components/profile/filesystem.txt;
df -i >> /usr/local/nagiosxi/var/components/profile/filesystem.txt;

echo "Creating filesystem.txt...";

ps -aef > /usr/local/nagiosxi/var/components/profile/psaef.txt;

echo "Dumping PS - AEF to psaef.txt...";

top -b -n 1 > /usr/local/nagiosxi/var/components/profile/top.txt;

echo "Creating top log...";

FILE=$(ls /usr/local/nagiosxi/nom/checkpoints/nagioscore/ | sort -n -t _ -k 2 | grep .gz | tail -1) 
cp /usr/local/nagiosxi/nom/checkpoints/nagioscore/$FILE /usr/local/nagiosxi/var/components/profile/

echo "Adding latest snapshot to: `pwd`"

## temporarily change to that directory, zip, then leave
(
	cd /usr/local/nagiosxi/var/components/ && zip -r profile.zip profile
)

#Remove directory to avoid duplicate files
rm -rf /usr/local/nagiosxi/var/components/profile/

echo "Zipping logs directory...";

echo "Backup and Zip complete!";
