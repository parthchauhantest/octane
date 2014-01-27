#!/usr/bin/perl
# vim:ts=4
#
# CREDITS - This script was written by Steve Shipway (http://www.steveshipway.org/)
# Steve says that unless otherwise specified, his scripts are released under the GPL license.
# Nice job on this Steve! -- Ethan
#
# this should be run regularly from your crontabs, to schedule any outages
# for the forthcoming 24 hours.
#
# $Id: downtime_job.pl 33 2010-05-25 19:20:38Z mmestnik $

# Daily: 
#        crontabs:  01 07 * * * downtime_job.pl > /dev/null 2>&1
# Hourly:
#        crontabs:  01  * * * * downtime_job.pl > /dev/null 2>&1

# WARNING! Only minor verification is made on the config file.  If you give 
#         incorrect hostnames, service descriptions, or hostgrouname then it
#         will not be noticed until Nagios tries to parse the command!

# See companion file for example of structure of schedule.cfg file.
#
# Version 1.1 : fixed for nagios 1.2
# Version 1.2 : trim trailing spaces from parameters, allow smaller increments
# Version 1.3 : allow wildcards in service name, check for already sched
#         1.5 : optimisation
#         1.6 : fix lookahead correctly, big rewrite
#         2.0 : Nagios 2 support
#         2.1 : Fix split regexp to allow spaces as well as commas
#         3.0 : Nagios 3 support

use strict;
use Time::Local;

use constant NAGDIR => '/usr/local/nagios';	# Nagios root directory

my($RETENTIONDAT,$STATUSDAT,$DOWNDAT,$STATUSLOG,$HGCFG,$DOWNLOG)
	= ('','/usr/local/nagios/var/status.dat','','','','');

# Always define these
use constant CFGFILE =>
	'/usr/local/nagios/etc/recurringdowntime.cfg';
		# my configuration file
# Define this for Nagios
use constant NAGCFGFILE => '/usr/local/nagios/etc/nagios.cfg';

use constant FREQUENCY => 1440*7; # how many minutes to look ahead.  Should be at least
                         # 1440 (one day) and <= 1 week.  Only the next outage
                         # is scheduled, not all of them.
use constant MINDUR => 5; # shortest outage allowed in minutes
my($DEBUG) = 1; # set to 1 to produce helpful debugging information
my($rv);

# Nothing more to change after this point!
############################################################################
my $CMDFILE;	# Nagios CMD file
my $OBJECTS;	# Nagios objects file
my(%hostgroups) = ();
my(%hostsvc);
my(%servicegroups) = ();
sub readstatuslog {
    %hostsvc = ();
    return if(! -r $STATUSLOG);
	print "Reading $STATUSLOG...";
    open SL, "<$STATUSLOG" or return;
    while( <SL> ) {
        if( /^\[\d+\]\s+SERVICE;([^;]+);([^;]+);/ ) {
            $hostsvc{$1}{$2} = 1;
        }
    }
    close SL;
	print "Done.\n" if($DEBUG);
}
sub readobjects {
	my($ohost,$osvc,$ohg,$osg) = ('','','','');
	if(! -r $OBJECTS) { 
		readstatuslog; 
		return; 
		}
	print 'Reading '.$OBJECTS.'...';
    %hostsvc = (); %hostgroups = ();
#    %servicegroups;
	open OBJ, '<'.$OBJECTS or return;
	while( <OBJ> ) {
	
		# found a service
		if( /^\s*define service / ) {
			#print "FOUND SVC\n" if($DEBUG);
			$osvc = 1; next;
			} 
		# found a hostgroup
		elsif( /^\s*define hostgroup / ) {
			#print "FOUND HG\n" if($DEBUG);	
			$ohg = 1; next;
			} 
		# found a servicegroup
		elsif( /^\s*define servicegroup / ) {
			#print "FOUND SG\n" if($DEBUG);
			$osg = 1; next;
			}
		# end of definition
		elsif( /^\s*}/ ) {
			#print "FOUND EOD\n" if($DEBUG);
			$ohost = $osvc = $ohg = "";
			} 
		
		# services
		elsif( $osvc ) {
			if( /^\s*host_name\s+(.*\S)/ ) {
				$ohost = $1; 
				} 
			elsif( /^\s*service_description\s+(.*\S)/ ) {
				$hostsvc{$ohost}{$1} = 1;
				$ohost = $osvc = "";
				}
			} 
		
		# host groups
		elsif( $ohg ) {
			if( /^\s*hostgroup_name\s+(.*\S)/ ) {
				$ohg=$1;
				} 
			elsif( /^\s*members\s+(.*\S)/ ) {
				#$hostgroups{$ohg} = [ split /[,\s]+/,$1 ];
				$hostgroups{$ohg} = [ split /[,]+/,$1 ];
				print "HG $ohg = ".(join ":",@{$hostgroups{$ohg}})."\n" if($DEBUG);
				$ohg = "";
				}
			} 
		
		# service groups
		elsif( $osg ) {
		
			if( /^\s*servicegroup_name\s+(.*\S)/ ) {
				$osg=$1;
				} 
			elsif( /^\s*members\s+(.*\S)/ ) {

				#print "SG MEMBERS=$1\n" if($DEBUG);
				#my @servicegroupt =  split /[,\s]+/,$1 ;
				my @servicegroupt =  split /[,]+/,$1 ;
				my $sgt="";
				my $sgh="";
				my $sgs="";
				#print "SGT-B $osg = ".(join ":",@servicegroupt)."\n" if($DEBUG);
				#print join("\n",@servicegroupt),"\n";
				while($sgt=shift(@servicegroupt)){
					#print "$_\n";
					if(! $sgh){
						$sgh=$sgt;
						#print "SGH=$sgh\n";
						}
					else{
						$sgs=$sgt;
						#print "HOST=$sgh, SVC=$sgs\n";
						
						push(@{$servicegroups{$osg}}, $sgh.';'.$sgs);
						
						# clear variables for next service
						$sgh="";
						$sgs="";
						}
					}

#				while (push(@{$servicegroups{$osg}}, shift(@servicegroupt).';'.shift(@servicegroupt))) { };
				print "SG $osg = ".(join ":",@{$servicegroups{$osg}})."\n" if($DEBUG);
				$osg = "";
				}
			
			}
		
	}
	close OBJ;
	print "Done.\n" if($DEBUG);
}

my(%downtime);
sub readdowntime {
    return if(! -r $DOWNLOG);
	print "Reading $DOWNLOG..." if($DEBUG);
    open DL, "<$DOWNLOG" or return;
    while( <DL> ) {
        if( /^\[\d+\]\s+SERVICE_DOWNTIME;\d+;([^;]+);([^;]+);(\d+);/ ) {
            $downtime{"$1:$2:$3"} = 1;
        } elsif( /^\[\d+\]\s+HOST_DOWNTIME;\d+;([^;]+);(\d+);/ ) {
            $downtime{"$1:$2"} = 1;
        } elsif( /^\[\d+\]\s+HOSTGROUP_DOWNTIME;\d+;([^;]+);(\d+);/ ) {
            $downtime{"HG!$1:$2"} = 1;
        }
    }
    close DL;
	print "Done.\n" if($DEBUG);
}
sub readdowntime2 {
	my($hd,$sd,$start,$a);
	my($f) = $DOWNDAT;
	$f = $STATUSDAT if(!$f or ! -r $f);
	$f = $RETENTIONDAT if(!$f or ! -r $f);
	if(!$f or ! -r $f) { readdowntime; return; }
	print "Reading downtime information from $f ..." if($DEBUG);
	$a = 0;
	open DD, "<$f" or return;
	while ( <DD> ) {	
		if( /^\s*hostdowntime/ ) {
			$a = 1; $hd = ""; $start = 0;
		} elsif( /^\s*servicedowntime/ ) {
			$a = 2; $hd = $sd = ""; $start = 0;
		} elsif( $a and /^\s*}/ ) {
			if($a == 1) {
				$downtime{"$hd:$start"} = 1;
				print "Adding $hd:$start\n" if($DEBUG);
			} elsif($a == 2) {
				$downtime{"$hd:$sd:$start"} = 1;
				print "Adding $hd:$sd:$start\n" if($DEBUG);
			}
			$a = 0;
		} elsif( $a ) {
			if( /^\s*host_name\s*=\s*(.*\S)/ ) { $hd = $1; }
			elsif( /^\s*service_description\s*=\s*(.*\S)/ ) { $sd = $1; }
			elsif( /^\s*start_time\s*=\s*(\d+)/ ) { $start = $1; }
		}
	}
	close DD;
	print "Done.\n" if($DEBUG);
}

############################################################################
sub sendcmd($) {
	my($msg) = $_[0];
	my($t) = time;
	print "Sending command '$msg'\n" if($DEBUG);
	open CMD,'>'.$CMDFILE or return 'Error: $!';
	print CMD "[$t] $msg\n";
	close CMD;
	print "$msg\n";
	return 0;
}
sub schedule_host($$$$$$) {
	my($h,$s,$d,$u,$c,$sa) = @_;
	my($rv);
	$u = "Automatic" if(!$u);
	$c = "AUTO: $c" if($c);
	$c = "AUTO: Automatically scheduled for host" if(!$c);
	return "Invalid host $h!" if(!$h or !defined $hostsvc{$h});
	return "Invalid time $s!" if(!$s);
	return "Invalid duration $d!" if(!$DEBUG and ($d < MINDUR));
	print "Scheduling host $h\n" if($DEBUG);
	if( !defined $downtime{"$h:$s"} ) {
#		$rv = sendcmd "SCHEDULE_HOST_DOWNTIME;$h;$s;".($s+($d*60)).";1;0;$u;$c";
		$rv = sendcmd "SCHEDULE_HOST_DOWNTIME;$h;$s;".($s+($d*60)).";1;0;".($d*60).";$u;$c";
	
		if($sa) {
			$rv = sendcmd "SCHEDULE_HOST_SVC_DOWNTIME;$h;$s;".($s+($d*60)).";1;0;".($d*60).";$u;$c" if(!$rv);
		}
	} else { print "Already scheduled\n"; return 0; }
	return $rv;
}
sub schedule_service($$$$$$) {
	my($h,$svc,$s,$d,$u,$c) = @_;
	my($rv);
	$u = "Automatic" if(!$u);
	$c = "AUTO: $c" if($c);
	$c = "AUTO: Automatically scheduled for service" if(!$c);
	return "Invalid host $h!" if(!$h or !defined $hostsvc{$h});
	return "Invalid service!" if(!$svc);
	return "Invalid time $s!" if(!$s);
	return "Invalid duration $d!" if(!$DEBUG and ($d < MINDUR));
	print "Scheduling service $h:$svc\n" if($DEBUG);
	$rv = 0;
	if( $svc =~ /\*/ ) { # wildcarded?
		$svc =~ s/\*/.*/g; # change to regexp
		foreach ( keys %{$hostsvc{$h}} ) {
			if( /^$svc$/ ) {
				if(!defined $downtime{"$h:$_:$s"}) {
#					$rv = sendcmd "SCHEDULE_SVC_DOWNTIME;$h;$_;$s;".($s+($d*60)).";1;0;$u;$c";
					$rv = sendcmd "SCHEDULE_SVC_DOWNTIME;$h;$_;$s;".($s+($d*60)).";1;0;".($d*60).";$u;$c";
				} else { print "Already scheduled!\n"; }
			}
			last if($rv);
		}
	} else {
		return "Invalid service $s on host $h!" if(!defined $hostsvc{$h}{$svc});
		if(!defined $downtime{"$h:$svc:$s"}) {
			#$rv = sendcmd "SCHEDULE_SVC_DOWNTIME;$h;$svc;$s;".($s+($d*60)).";1;0;$u;$c";
			$rv = sendcmd "SCHEDULE_SVC_DOWNTIME;$h;$svc;$s;".($s+($d*60)).";1;0;".($d*60).";$u;$c";
		} else { print "Already scheduled!\n"; }
	}
	return $rv;
}
sub schedule_hostgroup($$$$$$) {
	my($hg,$s,$d,$u,$c,$sa) = @_;
	my($rv,$h);
	$u = "Automatic" if(!$u);
	$c = "AUTO: $c" if($c);
	$c = "AUTO: Automatically scheduled for hostgroup" if(!$c);
	return "Invalid hostgroup $hg!" if(!$hg);
	return "Invalid time $s!" if(!$s);
	return "Invalid duration $d!" if(!$DEBUG and ($d < MINDUR));
	print "Scheduling hostgroup $hg\n" if($DEBUG);
	$rv = 0;
	return "Hostgroup $hg not recognised!" if(!defined $hostgroups{$hg}) ;
	foreach $h ( @{$hostgroups{$hg}} ) {
		if( !defined $downtime{"$h:$s"} ) {
#			$rv = sendcmd "SCHEDULE_HOST_DOWNTIME;$h;$s;".($s+($d*60)).";1;0;$u;$c";
			$rv = sendcmd "SCHEDULE_HOST_DOWNTIME;$h;$s;".($s+($d*60)).";1;0;".($d*60).";$u;$c";
			if($sa) {
#				$rv = sendcmd "SCHEDULE_HOST_SVC_DOWNTIME;$h;$s;".($s+($d*60)).";1;0;$u;$c" if(!$rv);
				$rv = sendcmd "SCHEDULE_HOST_SVC_DOWNTIME;$h;$s;".($s+($d*60)).";1;0;".($d*60).";$u;$c" if(!$rv);
			}
		} else { print "Already scheduled!\n"; }
		last if($rv);
	}
	return $rv;
}
sub schedule_servicegroup($$$$$$) {
	my($sg,$s,$d,$u,$c,$sa) = @_;
	my($rv,$sr);
	$u = "Automatic" if(!$u);
	$c = "AUTO: $c" if($c);
	$c = "AUTO: Automatically scheduled for servicegroup" if(!$c);
	return "Invalid servicegroup $sg!" if(!$sg);
	return "Invalid time $s!" if(!$s);
	return "Invalid duration $d!" if(!$DEBUG and ($d < MINDUR));
	print "Scheduling servicegroup $sg\n" if($DEBUG);
	$rv = 0;
	return "Servicegroup $sg not recognised!" if(!defined $servicegroups{$sg}) ;
	foreach $sr ( @{$servicegroups{$sg}} ) {
		if( !defined $downtime{"$sr:$s"} ) {
#			$rv = sendcmd "SCHEDULE_SVC_DOWNTIME;$sr;$s;".($s+($d*60)).";1;0;$u;$c";
			$rv = sendcmd "SCHEDULE_SVC_DOWNTIME;$sr;$s;".($s+($d*60)).";1;0;".($d*60).";$u;$c";
			if($sa) {
#				$rv = sendcmd "SCHEDULE_SVC_DOWNTIME;$sr;$s;".($s+($d*60)).";1;0;$u;$c" if(!$rv);
				$rv = sendcmd "SCHEDULE_SVC_DOWNTIME;$sr;$s;".($s+($d*60)).";1;0;".($d*60).";$u;$c" if(!$rv);
			}
		} else { print "Already scheduled!\n"; }
		last if($rv);
	}
	return $rv;
}
############################################################################
my( @schedules ) = ();

sub readnagcfg {
	my($line,$k,$v);
	open NAGCFG, '<'.NAGCFGFILE or return 'Error: '.NAGCFGFILE.": $!";
	while( $line=<NAGCFG> ) {
		chomp $line;
		$line =~ s/#.*$//;
		$line =~ s/\r//; # Get rid of DOS-style line endings
		next if(!$line);
		if( $line =~ /^command_file=(.*)\s*$/) {
			$CMDFILE = $1;
		}
		elsif( $line =~ /^object_cache_file=(.*)\s*$/) {
			$OBJECTS = $1;
		}
	}
	close NAGCFG;
	return 0;
}
sub readcfg {
	my(%newsched);
	my($line,$k,$v);
	open CFG, '<'.CFGFILE or return 'Error: '.CFGFILE.": $!";
	while( $line=<CFG> ) {
		chomp $line;
		$line =~ s/#.*$//;
		next if(!$line);
		if( $line =~ /^\s*define\s+schedule\s+{/i ) { %newsched = (); next; }
		if( $line =~ /^\s*}/ ) { 
			push @schedules, { %newsched }
				if(%newsched);; 
			next; 
		}
		if( $line =~ /^\s*(\S+)\s*(\S.*)/ ) { 
			($k,$v)=($1,$2);
			$v =~ s/\s*$//; # trim trailing spaces
			$newsched{$k} = $v; 
		}
	}
	close CFG;
	return 0;
}
sub numerically { $a<=>$b; }
my %dow = ( mon=>1, tue=>2, wed=>3, thu=>4, fri=>5, sat=>6, sun=>0 );
sub parse_days($) {
	my(@rv);

	foreach my $dn ( split /[,\s]+/,$_[0] ) {
		$dn = lc( substr($dn,0,3) );
		push @rv,$dow{$dn} if(defined $dow{$dn});
		push @rv,($1+0) if($dn=~/(\d+)/);
	}
	return ( sort numerically @rv );
}
sub parse_dates($) {
	my(@rv);
	foreach ( split /[,\s]+/,$_[0] ) { push @rv,($_+0); }
	return ( sort numerically @rv );
}

sub checkscheds {
	my($sref);
	my($T) = time();
	my($dow,$h,$min,$d,$m,$y,$next,$nh,$nmin,$nd,$nm,$ny,$rv);
	my(@lt,@nlt,@lst,$f,$t);

	# Identify 'now'.
	@lt = localtime($T);
	($dow,$h,$min,$d,$m,$y) = ($lt[6],$lt[2],$lt[1],$lt[3],$lt[4],$lt[5]);
print "\n now: $dow,$h,$min,$d,$m,$y \n";
	# Loop through all known schedules, find their next due time
	foreach  $sref ( @schedules ) {
		if($DEBUG) {
			if(defined $sref->{comment}) {
				print $sref->{comment} .": ";
			} else {
				print "Next schedule: ";
			}
			print " ".$sref->{host_name} if(defined $sref->{host_name});
			print " ".$sref->{service_description} if(defined $sref->{service_description});
			print "\n";
		}
		$t = $sref->{'time'};
		next if($t !~ /^(\d\d?):(\d\d)/);
		# start with scheduled time, today (may be in the past)
		($nh,$nmin)=($1,$2);
		($nd,$nm,$ny)=($d,$m,$y);
		print "Current candidate: $nh:$nmin on $nd/".($nm+1)."/".($ny+1900)."\n" if($DEBUG);
		# if in the past, advance one day
		if(($h>$nh) or ($h==$nh and $min>$nmin) ) {
			$nd+=1;
        }
        if(($nd>28)and($nm==1)) {$nm+=1;$nd-=28;} # XXX Leap yrs?
        print "\nnd=$nd;nm=$nm;ny=$ny\n";
        #            if(($nd>30)and($nm==9 or $nm==4 or $nm==6 or $nm=11))
        #                    {$nm+=1;$nd-=30;print"\nha!\n";}
        #alex changed the commented lines in  the following, for some reason the 'and' did not apply ?!?!
        # changed these because the months are zero based, they were 4,6,9,11 - SW
        if($nm==3 or $nm==5 or $nm==8 or $nm==10)
        {
        if($nd>30){$nm+=1;$nd-=30;}
        }

        print "\nnd=$nd;nm=$nm;ny=$ny\n";

        if($nd>31) {$nm+=1;$nd-=31;print "increment month:$nm\n";} 
        if($nm>11) {$ny+=1;$nm-=12;}
		
		# now see if we have a filter on dates.  If so, advance until we
		# get a valid date
		if( $sref->{days_of_month} ) {
			@lst = parse_dates($sref->{days_of_month});	# already sorted
			if($#lst>=0) { # any set?
				$f = 0;
				# take the smallest >= our planned time
				foreach ( @lst ) { if( $_ >= $nd ) { $nd=$_; $f = 1; last; } }
				# must be in next month, then
				if(!$f) { $nd = $lst[0]; $nm+=1; if($nm>11){$nm-=12;$ny+=1;} }
			}
		}
		# identify day of week we are looking at
		$next = timelocal( 0,$nmin,$nh,$nd,$nm,$ny );
		@nlt = localtime($next); # to get day of week
		print "Current candidate(dow): $nh:$nmin on $nd/".($nm+1)."/".($ny+1900)."\n" if($DEBUG);
		# is there a day-of-week filter?
		if( $sref->{days_of_week} ) {
			@lst = parse_days($sref->{days_of_week});	
			if($#lst>=0) {
				print "Checking days of week: days (".(join ",",@lst).") are valid\n" if($DEBUG);
				$f = 0;
				# loop through all possible days
				foreach ( @lst ) { 
					if( $_ >= $nlt[6] ) { 
						print "Scheduling for day $_ (today is $dow, looking at scheds for ".$nlt[6]." and later)\n" if($DEBUG); 
						$nd+=($_-$nlt[6]); $f = 1; last; 
					} 
				}
print "\nnd=$nd;nm=$nm;ny=$ny\n";
print "\ndow: $dow\nlst: $lst[0]\n";
#alex changed the following calculation from $nd+=(7-$dow+$lst[0])!
				if(!$f) { $nd +=(7-$nlt[6]+$lst[0]); 
					print "Advancing a week to day ".$lst[0]."\n" if($DEBUG); }
				# if we advanced the day, then make sure the month is right
print "\nnd=$nd;nm=$nm;ny=$ny\n";
				if(($nd>28)and($nm==1)) {$nm+=1;$nd-=28;} # XXX Leap yrs?
                # changed these because the months are zero based, they were 4,6,9,11 - SW
				if($nm==3 or $nm==5 or $nm==8 or $nm==10)
				    {if($nd>30){$nm+=1;$nd-=30;}}
#				if(($nd>30)and($nm==9 or $nm==4 or $nm==6 or $nm=11))
#					 {$nm+=1;$nd-=30;} 
				if($nd>31) {$nm+=1;$nd-=31;} 
				if($nm>11){$nm-=12;$ny+=1; } 
			}
		}

		# convert the planned event to a time_t
		$next = timelocal( 0,$nmin,$nh,$nd,$nm,$ny );
		print "Current candidate: $nh:$nmin on $nd/".($nm+1)."/".($ny+1900)."\n" if($DEBUG);
		# now we know when its next due to run!

		if( $next < $T ) { print "ERROR!  Going back in time?\n"; next; }
		if( ($next-$T) <= (FREQUENCY*60) ) {
			# Schedule it!
			$rv = "";
			if( $sref->{schedule_type} =~ /hostgroup|hg/i ) {
$rv = schedule_hostgroup($sref->{hostgroup_name} ,$next,$sref->{duration},$sref->{user},$sref->{comment},$sref->{svcalso});
			} elsif( $sref->{schedule_type} =~ /host/i ) {
$rv = schedule_host($sref->{host_name} ,$next,$sref->{duration},$sref->{user},$sref->{comment},$sref->{svcalso});
			} elsif( $sref->{schedule_type} =~ /servicegroup|hg/i ) {
$rv = schedule_servicegroup($sref->{servicegroup_name} ,$next,$sref->{duration},$sref->{user},$sref->{comment},$sref->{svcalso});
			} elsif( $sref->{schedule_type} =~ /service|svc/i ) {
$rv = schedule_service($sref->{host_name},$sref->{service_description} ,$next,$sref->{duration},$sref->{user},$sref->{comment});
			} else {
				$rv =  "Unknown schedule type : ".$sref->{schedule_type};	
			}
			if($rv) {
				print "ERROR: $rv\n";
			}
		} else {
			print "Not yet time for this one (wait ".(($next-$T)/3600)."hr)\n" if($DEBUG);
		}
	}
}

############################################################################

$DEBUG = 1 if($ARGV[0] =~ /-d/);

print "Reading Nagios configuration\n";
$rv = readnagcfg;
if($rv) {
	print "ERROR: $rv\n";
	exit 1;
}

print "Reading in configuration\n";
$rv = readcfg;
if($rv) {
	print "ERROR: $rv\n";
	exit 1;
}
print "Reading objects...\n";
if( $OBJECTS and -f $OBJECTS ) { 
	print "   Read object cache file...\n";
	readobjects; 
	} 
else { 
	print "   Reading status log...\n";
	readstatuslog; 
	}
print "Reading in list of already scheduled downtime\n";
readdowntime2; 
print 'Checking for downtime due in next '.FREQUENCY." minutes\n";
checkscheds;

exit 0;
