#!/bin/sh -e

. ../xi-sys.cfg

fmt -s -w $(tput cols) <<-EOF
    ========================
    Nagios XI Core 4 Upgrade
    ========================

    This script will upgrade a Nagios XI server to Nagios Core 4.

    IMPORTANT: 
    THIS SHOULD ONLY BE DONE IN A TEST ENVIRONMENT
    EXPECT THINGS TO NOT WORK AFTER PERFORMING THIS UPGRADE
    
EOF
read -p "Do you want to continue? [Y/n] " res

case "$res" in
    Y | y | "")
        echo "Proceeding with upgrade..."
        ;;
    *)
        echo "Installation cancelled"
        exit 0
esac

#stop services
echo "Stopping services"

service nagios stop
service ndo2db stop

######################################
#   Core 4 Section
######################################
(

    coreversion="nagios-4.0.0-beta1"

    echo "Getting $coreversion..."

    wget http://sourceforge.net/projects/nagios/files/nagios-4.x/nagios-4.0.0/$coreversion.tar.gz/download
    tar xzf $coreversion.tar.gz
    
    #compile core
    cd nagios
    ./configure --with-command-group=nagcmd
    make all
    make install
    
    #comment out deprecated config items
    sed -i 's/^old/#new/g' /usr/local/nagios/etc/nagios.cfg
    sed -i 's/use_embedded_perl_implicitly/#use_embedded_perl_implicitly/g' /usr/local/nagios/etc/nagios.cfg
    sed -i 's/^sleep_time/#sleep_time/g' /usr/local/nagios/etc/nagios.cfg
    sed -i 's/^p1_file/#p1_file/g' /usr/local/nagios/etc/nagios.cfg
    sed -i 's/^external_command_buffer_slots/#external_command_buffer_slots/g' /usr/local/nagios/etc/nagios.cfg
    sed -i 's/^enable_embedded_perl/#enable_embedded_perl/g' /usr/local/nagios/etc/nagios.cfg
    sed -i 's/^command_check_interval/#command_check_interval/g' /usr/local/nagios/etc/nagios.cfg
)

######################################
#   Ndoutil 2 Section
######################################

(
    # we need subversion until we package this into a tarball
    yum install -y git
    
    # get ndo svn
    echo "Getting ndoutils 2.0 from version control..."
    git clone git://git.code.sf.net/p/nagios/ndoutils ndoutils
    
    #compile ndo
    cd ndoutils
    git checkout -b ndoutils-2-0 origin/ndoutils-2-0
    ./configure; make; make install
    cp -f src/ndomod-4x.o /usr/local/nagios/bin/ndomod.o
    # Copy 4.x daemon
    cp -f src/ndo2db-4x /usr/local/nagios/bin/ndo2db
    # Copy utilities
    cp -f src/file2sock /usr/local/nagios/bin/
    cp -f src/log2ndo /usr/local/nagios/bin/
    cp -f src/sockdebug /usr/local/nagios/bin/

    ##parse values in case mysql is offloaded 
    ndopass=$(sed -n '/^db_pass=/ s///p' /usr/local/nagios/etc/ndo2db.cfg)
    ndohost=$(sed -n '/^db_host=/ s///p' /usr/local/nagios/etc/ndo2db.cfg)
    ndouser=$(sed -n '/^db_user=/ s///p' /usr/local/nagios/etc/ndo2db.cfg)

    ./db/upgradedb -u "$ndouser" -p "$ndopass" -h "$ndohost" -d nagios
    
)


#start services
service ndo2db start
service nagios start

fmt -s -w $(tput cols) <<-EOF
    ========================
    Nagios XI Core 4 Upgrade
    ========================

    Nagios XI server has been sucessfully upgraded to Nagios Core 4
    and Ndo2db 2.0

    IMPORTANT: 
    THIS SHOULD ONLY BE DONE IN A TEST ENVIRONMENT
    EXPECT THINGS TO NOT WORK AFTER PERFORMING THIS UPGRADE
    
    Please post bug report to the Nagios XI Forum
    
    Thanks
    
EOF

