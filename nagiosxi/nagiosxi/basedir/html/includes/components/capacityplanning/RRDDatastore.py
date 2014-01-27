#!/usr/bin/env python2

import numpy as np
import sys

class RRDDatastore( object ):
    
    def __init__( self , host , service ):
        '''USAGE: rrd_datastore( (str)<host> , (str)<service> )
        
        Sets this class's self variables to hold the host and service
        information. Also sets the guessed filenames for the RRD and XML'''
        self.host       = host
        self.service    = service
        
        rrd, xml = RRDDatastore.guess_filename( host , service )
        
        self.rrd = rrd
        self.xml = xml
        
        text = open("/tmp/test.log","a")
        text.write(self.rrd + "\n")
        text.close()
        
        self.minimum = 0
        
        self.list_of_datasets = []
    
    def parse_rrd_file( self , range = '-1w' , end = 'now' ):
        '''USAGE: parse_rrd_file( [(RRD time)<range> (RRD time)<end>] )
        
        Sets information inside of this class about the RRD of choice.'''
        import rrdtool
        
        #~ Record the range for later use
        self.range = int(range[1])
        
        #~ Initialize data by unpacking it from rrdtoolfetch
        rrdinfo , dsinfo , rrddata = rrdtool.fetch( self.rrd , 'AVERAGE' , '-s' , range , '-e' , end )
        
        #~ Change the timeseries organization
        organized_data = zip(*rrddata)
        
        #~ Add each individual dataset to the self.list_of_datasets, also label each
        for dsname,dataset in zip(dsinfo,organized_data):
            tmprrddata = rrdinfo + ( dsname, )
            tmp = Dataset(*tmprrddata)
            tmp.set_dataset( dataset )
            self.list_of_datasets.append(tmp)
        
    def parse_xml_file( self ):
        '''USAGE: parse_xml_file()
        
        Parses XML file for the given RRD and assigns information to the RRDs'''
        from lxml import etree
        
        xml         = etree.parse( self.xml )
        datasources = xml.findall('DATASOURCE')
        for meta,dataset in zip(datasources,self.list_of_datasets):
            dataset.label   = meta.find('LABEL').text
            dataset.unit    = meta.find('UNIT').text
            if dataset.unit == None:
                dataset.unit = '%'
            dataset.warn    = meta.find('WARN').text
            dataset.crit    = meta.find('CRIT').text
            dataset.range   = self.range
            dataset.host    = self.host
            dataset.service = self.service
        
    
    def __str__( self ):
        if not self.list_of_datasets:
            ret = 'Datastore is empty.'
        else:
            ret = ''
            for set in self.list_of_datasets:
                ret += ' [ ' + set.__str__() + ' ] '
        return ret
    
    @staticmethod
    def guess_filename( host , service ):
        '''USAGE: guess_filename( (str)<host> , (str)<service> )
        
        Guesses the filenames for the XML and RRD file given the host and serivce name.
        Uses Nagios LLC defaults for Nagios XI.'''
        
        import re
        
        illegal = r'[:/ ]'
        
        host = re.sub(illegal, '_', host)
        service = re.sub(illegal, '_', service)
        
        base    = "/usr/local/nagios/share/perfdata/%s/%s" % ( host , service )
        
        xml     = base + '.xml'
        rrd     = base + '.rrd'
        
        return rrd, xml


class Dataset( object ):
    
    def __init__( self , start , end , step , ds ):
        '''USAGE: RRDDataset( (int)<start> , (int)<end> , (int)<step> )
        
        Initialize general data about the RRD dataset'''
        
        self.start  = start
        self.end    = end
        self.step   = step
        self.ds     = ds
        
        self.label  = ''
        self.warn   = ''
        self.crit   = ''
        self.unit   = ''
        
        self.period = ''
        
    def set_dataset( self , data ):
        '''USAGE: set_dataset( (list)<data> )
        
        Use this as a mutator for the dataset contained by this class. Makes the necessary
        masked array of the given dataset.'''
        import copy
        tmpArray            = np.ma.masked_array( data )
        tmpArray            = np.ma.masked_equal(tmpArray,None)
        try:
            self.fill_value     = np.ma.mean( tmpArray.compressed() )
        except ZeroDivisionError:
            self.fill_value = 0
        self.dataset        = copy.deepcopy(tmpArray)
        
    def get_dataset( self , safe = True ):
        '''USAGE: get_dataset( [(Boolean)<safe>] )
        
        Returns the dataset. It defaults to returning a safe dataset. However if you want
        the unfilled dataset, simply pass safe = False.'''
        if safe:
            retSet = self.dataset.filled( self.fill_value )
            newSet = np.ma.clip( retSet , 0 , np.Inf )
            #~ return self.dataset.filled( self.fill_value )
            return newSet
        else:
            return self.dataset
            
    def get_independent_variable( self ):
        '''USAGE: get_independent_variable( [(int)<start> (int)<end>] )
        
        Return the x-axis of a timeseries graph to match this dataset'''
        return np.ma.arange( self.start , self.end , self.step )
        
    def calculate_trend( self ):
        
        from scipy.interpolate import UnivariateSpline as spline
        
        spline_func = spline( self.get_independent_variable() , self.get_dataset() )
        
        trend       = np.ma.array(spline_func( self.get_independent_variable() ))
        trend.mask  = self.dataset.mask
        
        self.trend  = trend
    
    def serialize_as_json( self ):
        ret_json = {    
                        "type"          : "area",
                        "name"          : '%s - %s' % ( self.label , self.unit ),
                        "pointStart"    : int(self.start) * 1000.0,
                        "pointInterval" : int(self.step) * 1000.0,
                        "data"          : self.get_dataset().tolist(),
                    }
        return ret_json
        
    
    def set_minimum( self , minimum ):
        
        self.minimum = minimum
        
        
        
    def set_period( self , period='1,w' ):
        '''USAGE: set_period( [(int)period] )
        
        Sets the value of this classes period to number of steps in period'''
        period              = int(period.split(',')[0])
        SECONDS_IN_WEEK     = 604800
        
        self.period             = period * SECONDS_IN_WEEK
        self.steps_in_period    = int( self.period / self.step )
    
    def __str__( self ):
        return self.service + ' on ' + self.host
        
    
    
    
