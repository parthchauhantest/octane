#!/bin/sh

echo "Configuring Auto-discovery component permissions..."

basedir=/usr/local/nagiosxi/html/includes/components/autodiscovery

chown -R apache $basedir/*
chown -R .nagios $basedir/*
chmod -R g+w $basedir/jobs
chmod g+ws $basedir/jobs
chmod ugo+x $basedir/scripts/*

/etc/init.d/httpd reload

touch $basedir/setup.done

echo "Setup complete."