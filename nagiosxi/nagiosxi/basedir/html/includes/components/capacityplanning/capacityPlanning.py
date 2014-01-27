#!/usr/bin/env python

import sys

import warnings
warnings.filterwarnings('ignore')
import logging
import os

mypath= r'/usr/local/nagiosxi/var/components/capacityplanning'
if not os.path.isdir(mypath):
   os.makedirs(mypath)

logging.basicConfig(level=logging.DEBUG,
                    format='%(asctime)s %(levelname)-8s %(message)s',
                    datefmt='%a, %d %b %Y %H:%M:%S',
                    filename='/usr/local/nagiosxi/var/components/capacityplanning/capacityPlanning.log',
                    filemode='w')

def parseArgs():
    '''Wrapper to parse args and make it pretty.'''
    
    def verifyArgs( options ):
        '''Used to verify arguments passed our up to snuff.'''
        
        options.graph = False
        
        if options.host == "" or options.service == "":
            print "\nMust give service and host. Cannot continue without.\n"
            return False
        if not options.graph and not options.xml and not options.check and not options.json:
            print "\nMust specify mode.\n"
            return False
        return True
    
    from optparse import OptionParser, OptionGroup
    
    usage = sys.argv[0] + ' -H <hostname> -S <servicename> -[g/x/c] OPTIONS'
    
    parser = OptionParser(usage)
    #~ Make required option group
    group  = OptionGroup( parser , "Required Options", "The script will bail immediately if these are not specified.")
    group.add_option(  "-H","--host"
                    ,   help="Host to analyze."
                    ,   default=""
                    ,   type="str" )
    group.add_option(  "-S","--service"
                    ,   help="Service on host."
                    ,   default=""
                    ,   type="str" )
    parser.add_option_group(group)
    #~ Make mode option group
    group  = OptionGroup( parser , "Mode", "Determines the mode of the script.")
    group.add_option(   "-x","--xml"
                    ,   help="Sets mode to return XML for data."
                    ,   default=False
                    ,   action="store_true" )
    group.add_option(   "-j","--json"
                    ,   help="Returns the data as a JSON object specifically for use with HighCharts."
                    ,   default=False
                    ,   action="store_true" )
    group.add_option(   "-c","--check"
                    ,   help="Run as Nagios check, returning status and string."
                    ,   default=False
                    ,   action="store_true" )
    parser.add_option_group(group)
    #~ Make extra option group
    group = OptionGroup( parser, "Others" , "Miscellaneous options." )
    group.add_option(   "-r","--range"
                    ,   help="How far back the rrdtool will pull data from. Use only if using graph. [DEFAULT -1w]"
                    ,   default="-2w"
                    ,   type="str" )
    group.add_option(   "-s","--size"
                    ,   help="Size in inches for graph. [DEFAULT 10x3]"
                    ,   default='10x3' )
    group.add_option(   "-m","--smooth"
                    ,   help="Smooth the graph. Boolean flag. [DEFAULT: not present]"
                    ,   default=False
                    ,   action="store_true" )
    group.add_option(   "-t","--threshold"
                    ,   help="Set threshold to be reached. Used for extrapolation. [DEFAULT: 0 (None)]"
                    ,   default=0
                    ,   type="float" )
    group.add_option(   "-e","--extrapolate"
                    ,   help="Extrapolate out this many periods. [DEFAULT: 0 (None)]"
                    ,   default=0
                    ,   type="int" )
    group.add_option(   "-i","--index"
                    ,   help="Pick specific index from the RRD file. [ACCEPTS integer]"
                    ,   default=-1
                    ,   type="int" )
    group.add_option(   "-p","--period"
                    ,   help="Length of period. Usually this value would be something like one week or one month or one year. It is your best guess with the data you have as to what the cycle of the data is. If you are unsure then just leave this blank. [Options: n,w , n,d , n,m , n,y where n is some integer]"
                    ,   default="1,w" )
    group.add_option(   "-z","--method"
                    ,   help='Type of extrapolation used. Currently supported: Holt-Winters[hw], Least Squares[lstsq], Quadratic Fit [dpf], Cubic Fit [tpf]. [DEFAULT: lstsq]'
                    ,   default='lstsq' )
    group.add_option(   "-d","--dates"
                    ,   help='Comma delimited list of dates you want checked. Taken in as time since epoch.'
                    ,   default='')
    group.add_option(   "--dumpgraph"
                    ,   help="Makes the graph PNGs print to /var/www/html/graph1.png"
                    ,   default=False
                    ,   action="store_true" )
    group.add_option(   "-y","--floor"
                    ,   help="Set the smallest value that will be returned by extrapolate. [DEFAULT: 0]"
                    ,   default=0 )
    group.add_option(   "-v","--values"
                    ,   help='Comma delimited list of values you want checked.'
                    ,   default='')
    parser.add_option_group(group)
    (options, args) = parser.parse_args()
    if verifyArgs(options):
        #~ if not options.period: options.period = '1,w'
        return options
    else:
        parser.print_help()
        sys.exit(1)

def main():
    
    options = parseArgs()
    
    errorText = ''
    
    import RRDDatastore,Forecast
    
    currDatastore = RRDDatastore.RRDDatastore(options.host, options.service)
    
    #~ Try to access the files
    try:
        currDatastore.parse_rrd_file(options.range)
        currDatastore.parse_xml_file()
    except Exception, e:
        # raise Exception("Could not open RRD or XML file.")
        logging.exception(e)
        options.index = -1
        print "Could not open RRD file for reading, bad filename."
        sys.exit(1)
        #~ errorText     = "RRD file does not contain enough data to make extrapolations."
        
    
    #~ If the specified an index fetch it, otherwise all    
    datasets = currDatastore.list_of_datasets
    
    if options.index > -1:
        try:
            datasets = [ datasets[options.index] ]
        except:
            raise Exception("Invalid index for RRD file. DS does not exist.")
    
    #~ Determine extrapolation method
    if options.method == 'lstsq':
        exMethod = Forecast.least_squares
    elif options.method == 'hw':
        exMethod = Forecast.holt_winters
    elif options.method == 'dpf':
        exMethod = Forecast.quadratic_fit
    elif options.method == 'tpf':
        exMethod = Forecast.cubic_fit
    else:
        exMethod = None
    
    #~ Now that we have all the info we want, graph it
    if options.graph:
        
        import Visualization
        
        newGraph = Visualization.PythonPlot( options.size )
        
        if errorText:
            newGraph.add_text( errorText )
        else:
            newGraph.set_title( datasets[0] )
            
            for dataset in datasets:
                #~ Set the period for each dataset
                dataset.set_period( options.period )
                #~ Graph both datasets
                newGraph.queue_dataset( dataset )
                #~ If extrapolate is greater than 0, run extrap algorithms
                if options.extrapolate > 0:
                    #~ try:
                    extrapDataset = exMethod( dataset , options.extrapolate )
                    newGraph.queue_dataset( extrapDataset )
                    #~ except:
                        #~ newGraph.add_text('Incomplete data for period in RRD.')
        
        if options.dumpgraph:
            location = '/var/www/html/graph1.png'
        else:
            location = sys.stdout
        
        newGraph.write_graph( location )
    
    if options.json:
        
        try:
            import simplejson as json
        except:
            import json
        
        json_list = []
        
        for dataset in datasets:
            dataset.set_period( options.period )
            json_list.append( dataset.serialize_as_json() )
            if options.extrapolate > 0:
                json_list.append( exMethod( dataset , options.extrapolate ).serialize_as_json() )
        print json.dumps(json_list)
        sys.exit(1)
    
    if options.xml:
        
        import XMLrep
        
        if options.extrapolate == 0:
            print 'No extrapolate time specified.'
            sys.exit(1)
        
        dataset         = datasets[0]
        dataset.set_period( options.period )
        extrapDataset   = exMethod( dataset , options.extrapolate ) 
        
        newXML = XMLrep.XMLextrap( extrapDataset )
        
        if not dataset.warn == None:
            newXML.add_date_of_crit()
            
        if not dataset.crit == None:
            newXML.add_date_of_warn()
        
        if options.dates:
            for date in options.dates.split(','):
                newXML.add_date_to_xml( date )
                
        if options.values:
            for value in options.values.split(','):
                newXML.add_value_to_xml( float(value) )
        
        newXML.sort_xml_by_date()
        newXML.print_xml()
        
if __name__ == '__main__':
    main()
