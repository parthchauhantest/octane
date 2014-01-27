#!/bin/sh

echo "Configuring Auto-discovery component permissions..."

basedir=/usr/local/nagiosxi/html/includes/components/autodiscovery

# Required, but causes install to fail - this has to be done manually elsewhere...
#chown -R apache $basedir/*
chown -R .nagios $basedir/*
chmod -R g+w $basedir/jobs
chmod g+ws $basedir/jobs
chmod ugo+x $basedir/scripts/*

# Required, but causes install to fail - this has to be done manually elsewhere...
#/etc/init.d/httpd reload

touch $basedir/setup.done

echo "Setup complete."