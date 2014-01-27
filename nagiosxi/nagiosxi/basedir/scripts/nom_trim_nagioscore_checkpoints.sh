#!/bin/sh
# Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
# $Id: nom_trim_nagioscore_checkpoints.sh 1300 2012-07-17 19:47:23Z swilkerson $

basedir="/usr/local/nagiosxi/nom/checkpoints/nagioscore"
nagiosql_basedir="/usr/local/nagiosxi/nom/checkpoints/nagiosxi"
# TRIM GOOD CHECKPOINTS
function trim_checkpoints {

	if [[ -n $1 ]] ; then
		checkpointdir=$1
	else
		checkpointdir="${basedir}"
	fi
			
	echo "DIR: $checkpointdir"
			
	# Get rid of all but the most recent 10 checkpoints
	numtokeep=11
	lasttokeep=`ls -1tr $checkpointdir/*.gz | tail -$numtokeep | head -1`

	checkpoints=`find $checkpointdir -maxdepth 1 -name "[0-9]*.tar.gz" | wc -l`
	echo "NUMFOUND: $checkpoints"

	if [ $checkpoints -lt $numtokeep ]; then
		echo "KEEPING ALL GOOD CHECKPOINTS"
		return;
	fi

	echo "ALL:"
	ls -1t $checkpointdir


	echo "LAST GOOD CHECKPOINT TO KEEP: $lasttokeep"
	echo "DELETING OLD TEXT FILES...";
	find $checkpointdir -maxdepth 1 -name "[0-9]*.txt" -not -newer "$lasttokeep" -exec rm -f {} \;
	echo "DELETING OLD TARBALL FILES...";
	find $checkpointdir -maxdepth 1 -name "[0-9]*.tar.gz" -not -newer "$lasttokeep" -exec rm -f {} \;
	#find $checkpointdir -name "[0-9]*.tar.gz" -not -newer "$lasttokeep" -exec ls -al {} \;
	}
    
# TRIM NAGIOSQL SNAPSHOTS
function trim_nagiosql_snapshots {
    
	if [[ -n $1 ]] ; then
		checkpointdir=$1
	else
		checkpointdir="${nagiosql_basedir}"
	fi
			
	echo "DIR: $checkpointdir"
			
	# Get rid of all but the most recent 10 checkpoints
	numtokeep=11
	lasttokeep=`ls -1tr $checkpointdir/*.gz | tail -$numtokeep | head -1`

	checkpoints=`find $checkpointdir -maxdepth 1 -name "[0-9]*_nagiosql.sql.gz" | wc -l`
	echo "NUMFOUND: $checkpoints"

	if [ $checkpoints -lt $numtokeep ]; then
		echo "KEEPING ALL SNAPSHOTS"
		return;
	fi

	echo "ALL:"
	ls -1t $checkpointdir


	echo "LAST GOOD SNAPSHOTS TO KEEP: $lasttokeep"
	echo "DELETING OLD SNAPSHOT FILES...";
	find $checkpointdir -maxdepth 1 -name "[0-9]*_nagiosql.sql.gz" -not -newer "$lasttokeep" -exec rm -f {} \;
	}
# TRIM ERROR CHECKPOINTS
function trim_errorpoints {

	if [[ -n $1 ]] ; then
		checkpointdir=$1
	else
		checkpointdir="${basedir}/errors"
	fi
	
	echo "DIR: $checkpointdir"
			
	# Get rid of all but the most recent 10 checkpoints
	numtokeep=11
	lasttokeep=`ls -1tr $checkpointdir/*.gz | tail -$numtokeep | head -1`

	checkpoints=`find $checkpointdir -maxdepth 1 -name "[0-9]*.tar.gz" | wc -l`
	echo "NUMFOUND: $checkpoints"

	if [ $checkpoints -lt $numtokeep ]; then
		echo "KEEPING ALL ERROR CHECKPOINTS"
		return;
	fi

	echo "ALL:"
	ls -1t $checkpointdir


	echo "LAST ERROR CHECKPOINT TO KEEP: $lasttokeep"
	echo "DELETING OLD TEXT FILES...";
	find $checkpointdir -maxdepth 1 -name "[0-9]*.txt" -not -newer "$lasttokeep" -exec rm -f {} \;
	echo "DELETING OLD TARBALL FILES...";
	find $checkpointdir -maxdepth 1 -name "[0-9]*.tar.gz" -not -newer "$lasttokeep" -exec rm -f {} \;
	}

	
trim_checkpoints
trim_errorpoints
trim_nagiosql_snapshots
