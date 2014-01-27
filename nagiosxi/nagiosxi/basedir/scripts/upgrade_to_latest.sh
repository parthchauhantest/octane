#!/bin/sh -e
#backup XI before upgrade
#(
#	cd /usr/local/nagiosxi/scripts
#	./backup_xi.sh
#)

# Perform upgrade
(
	cd /usr/local/nagiosxi/tmp
	rm -rf nagiosxi xi-latest.tar.gz
	wget http://assets.nagios.com/downloads/nagiosxi/xi-latest.tar.gz
	tar xzf xi-latest.tar.gz
	cd /usr/local/nagiosxi/tmp/nagiosxi
	./upgrade
)
