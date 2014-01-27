#!/usr/bin/env python

import rrdtool, sys

try:
    hostname = sys.argv[1]
    service  = sys.argv[2]
except:
    print 'Usage: %s hostname servicename' % sys.argv[0]
    sys.exit(1)

hostname = hostname.replace(':','_').replace(' ','_')
service  = service.replace(':','_').replace(' ','_')

rrdfile = '/usr/local/nagios/share/perfdata/%s/%s.rrd' % ( hostname , service )

def valid_timeframe( dataset ):
    
    # Split the RRD data into lists that contain data for only one dataset
    segregated  = zip(*dataset)
    length      = len(segregated[0])
    totalNone   = 0
    
    for data in segregated[0]:
        
        if data == None:
            totalNone += 1
    
    return float(totalNone)/length < .2
        
def main():
    
    weeks_power = 8
    
    while( weeks_power > 0):
        data = rrdtool.fetch( rrdfile , 'AVERAGE' , '-s' , '-%dw' % (2**weeks_power) , '-e' , '-%dw' % (2**(weeks_power-1))  )
        if valid_timeframe( data[2] ):
            
            # Create a usable list of time frames. If this current time frame is valid
            # then all the rest of the time frames below it will be valid. All time frames
            # will be a number of weeks that is a power of two.
            usable_list = [ '%dw' % (2**x) for x in range(0,weeks_power) ]
            # Make a comma-delimited list of the usable time frames.
            print ','.join(usable_list)
            return
            
        weeks_power -= 1
    
    print 'None'
    return 0

try:
    main()
except Exception, e:
    print 'DNE'
