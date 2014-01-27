#!/usr/bin/env python2

from sys import exit
from RRDDatastore import Dataset
import numpy as np

def least_squares( dataset , extrap ):
    '''USAGE: least_squares( (Dataset)dataset , (int)extrap )
    
    Uses the dataset's period attribute to extrapolate the dataset out
    extrap amount of periods. 
    
    Returns a Dataset object holding only exrapolated values using least squares.'''
    #~ Extract values from the given dataset
    xpoints = dataset.get_independent_variable()
    ypoints = dataset.get_dataset()
    
    #~ Declare new Dataset that will hold extrapolations, setting the 
    #~ start and end of the this new dataset to the proper bounds
    extrapDataset = get_extrap_dataset( dataset , extrap )
    
    #~ Use the least squares method
    A       = np.vstack([xpoints, np.ones(xpoints.size)]).T
    m, c    = np.linalg.lstsq(A,ypoints)[0]
    func    = np.vectorize(lambda x: m*x + c)
    xextrap = extrapDataset.get_independent_variable()
    extrapDataset.set_dataset( func(xextrap) )
    extrapDataset.label += ' LstSq'
    
    return extrapDataset
    
def quadratic_fit( dataset , extrap ):
    '''USAGE: quadratic_fit( (Dataset)dataset , (int)extrap )
    
    Use polyfit to extrapolate future values.'''

    import warnings
    warnings.simplefilter('ignore', np.RankWarning)
    
    xpoints         = dataset.get_independent_variable()
    ypoints         = dataset.get_dataset()
    
    extrapDataset   = get_extrap_dataset( dataset, extrap )
    xextrap         = extrapDataset.get_independent_variable()
    
    #~ if not xpoints.size == ypoints.size:
        #~ print 'Dimension mismatch, attempting to guess.'
        #~ xpoints = np.resize(xpoints,ypoints.size)
        #~ if not xpoints.size == ypoints.size:
            #~ return np.zeros(xpoints.size)
    coefficients    = np.polyfit( xpoints , ypoints , 2 )
    coeff_func      = np.poly1d( coefficients )
    extrapDataset.set_dataset( coeff_func( xextrap ) )
    extrapDataset.label += ' QuadFit'
    
    return extrapDataset
    
def cubic_fit( dataset , extrap ):
    '''USAGE: quadratic_fit( (Dataset)dataset , (int)extrap )
    
    Use polyfit to extrapolate future values.'''

    import warnings
    warnings.simplefilter('ignore', np.RankWarning)
    
    xpoints         = dataset.get_independent_variable()
    ypoints         = dataset.get_dataset()
    
    extrapDataset   = get_extrap_dataset( dataset, extrap )
    xextrap         = extrapDataset.get_independent_variable()
    
    #~ if not xpoints.size == ypoints.size:
        #~ print 'Dimension mismatch, attempting to guess.'
        #~ xpoints = np.resize(xpoints,ypoints.size)
        #~ if not xpoints.size == ypoints.size:
            #~ return np.zeros(xpoints.size)
    coefficients    = np.polyfit( xpoints , ypoints , 3 )
    coeff_func      = np.poly1d( coefficients )
    extrapDataset.set_dataset( coeff_func( xextrap ) )
    extrapDataset.label += ' CubeFit'
    
    return extrapDataset
    
def holt_winters( dataset , extrap ):
    '''USAGE: least_squares( (Dataset)dataset , (int)extrap )
    
    Extrapolate using the Holt-Winters Algorithm'''
    
    extrapDataset = get_extrap_dataset( dataset , extrap )
    
    alpha=.11
    beta=.001
    gamma=.002
    debug = False;
    
    ypoints             = dataset.get_dataset()
    cycle_length        = dataset.steps_in_period
    extrapolate_cycles  = extrap
    
    ylen = ypoints.size
    
    if not ylen % cycle_length == 0:
        ypoints = np.append(ypoints,[np.mean(ypoints)])
        ylen = ypoints.size
        
    
    if not ylen % cycle_length == 0:
        #~ print "ypoints must be a multiple of cycle_length."
        #~ print "Truncating ypoints."
        reversed    = ypoints[::-1]
        cycles      = ylen / cycle_length
        reversed    = np.resize(reversed, cycles * cycle_length)
        ypoints     = reversed[::-1]
        ylen = ypoints.size
    
    fc = float(cycle_length)
    c  = cycle_length
    #~ Get the average of the second cycle of time series data.
    ybar2 = np.mean(ypoints[c:(2*c)])
    #~ Get the average of the first cycle of of time series data.
    ybar1 = np.mean(ypoints[:c])
    #~ Calcute initial b value from averages
    b_not = (ybar2 - ybar1) / fc
    if debug: print "b_not: ",b_not
    #~ Create a set of t points to go along with the ypoints
    tset = np.arange(1,c+1)
    tbar = np.mean(tset)
    #~ Use tbar to get initial alpha value
    a_not = ybar1 - b_not * tbar
    if debug: print "a_not: ",a_not
    
    #~ Construct Trend array
    #~ TODO: Find a more numpy approach to this problem
    I = np.asarray([ ypoints[i] / ( a_not + (i+1) * b_not ) for i in range(0,ylen)])
    if debug: print "Initial indices: ", I
    #~ Create empty array for season data
    S = (I[:c] + I[c:(2*c)]) / 2.0
    S = np.resize(S,ylen+(extrapolate_cycles*c))
    tS = c / np.sum(S[:c])
    S = S * tS
    if debug: print 'S:',S
    #~ Enter... the dragon
    
    F = np.zeros(ylen+c)
    
    E = np.zeros(c*extrapolate_cycles)
    A_t = a_not
    B_t = b_not
    for i in range(ylen):
        A_tm1   = A_t
        B_tm1   = B_t
        A_t     = alpha * ypoints[i] / S[i] + ( 1.0 - alpha ) * ( A_tm1 + B_tm1 )
        B_t     = beta  * ( A_t - A_tm1 ) + ( 1 - beta ) * B_tm1
        S[i+c]  = gamma * ypoints[i] / A_t + ( 1.0 - gamma ) * S[i]
        F[i]    = ( a_not + b_not * (i+1)) * S[i]
        #~ print "i=", i+1, "y=", y[i], "S=", S[i], "Atm1=", A_tm1, "Btm1=",B_tm1, "At=", A_t, "Bt=", B_t, "S[i+c]=", S[i+c], "F=", F[i]
    for m in np.arange(extrapolate_cycles*c):
        E[m] = ( A_t + B_t * (m+1)) * S[ylen + m]
        #~ print "Forecast: ", ( A_t + B_t * (m+1)) * S[ylen + m]
    
    extrapDataset.set_dataset( E )
    extrapDataset.label += ' HoltWinters'
    
    return extrapDataset
    
def get_extrap_dataset( dataset , extrap ):
    '''USAGE: get_extrap_dataset( (Dataset)dataset , (int)extrap )
    
    Find the set of values that will be the x points of the extrapolated data.
    
    Returns Dataset object with proper start end end filled in.'''
    start                   = dataset.end
    end                     = start + ( extrap * dataset.period )
    
    #~ Copy over values from old dataset to extrapDataset
    
    extrapDataset           = Dataset( start , end , dataset.step , 'DS Extrap' )
    extrapDataset.label     = dataset.label
    extrapDataset.unit      = dataset.unit
    extrapDataset.warn      = dataset.warn
    extrapDataset.crit      = dataset.crit
    extrapDataset.host      = dataset.host
    extrapDataset.service   = dataset.service
    return extrapDataset
    

