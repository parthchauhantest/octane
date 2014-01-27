var mode        = '';
var host        = '';
var service     = '';
var dates       = 0;
var values      = 0;
var baseurl     = THISURL + 'includes/components/capacityplanning/capacityplanning.php';

var templateid  = '#timeframeselect';
var disableid   = '';
var throbber    = '<div style="width:20px;padding:40px;margin:0 auto;"><img id="throbGif" src=' + THISURL + 'images/throbber1.gif' + ' /></div>';

var HashSearch = new function () {
       var params;

       this.set = function (key, value) {
          params[key] = value;
          this.push();
       };

       this.remove = function (key, value) {
          delete params[key];
          this.push();
       };

       this.get = function (key, value) {
           return params[key];
       };

       this.keyExists = function (key) {
           return params.hasOwnProperty(key);
       };

       this.push= function () {
           var hashBuilder = [], key, value;

           for(key in params) if (params.hasOwnProperty(key)) {
               key = escape(key), value = escape(params[key]); // escape(undefined) == "undefined"
               hashBuilder.push(key + ( (value !== "undefined") ? '=' + value : "" ));
           }

           window.location.hash = hashBuilder.join("&");
       };

       (this.load = function () {
           params = {}
           var hashStr = window.location.hash, hashArray, keyVal
           hashStr = hashStr.substring(1, hashStr.length);
           hashArray = hashStr.split('&');

           for(var i = 0; i < hashArray.length; i++) {
               keyVal = hashArray[i].split('=');
               params[unescape(keyVal[0])] = (typeof keyVal[1] != "undefined") ? unescape(keyVal[1]) : keyVal[1];
           }
    })();
}
    
$(document).ready( function() {
    $('#tabs').tabs();

    // Set hostname (from URI hash if availble...)
    if (HashSearch.keyExists("hostname")) {
        host = HashSearch.get("hostname");
    } else {
        host = $('#hostnameselect').val();
    }

    // Set the selected service name (from URI hash if available...)
    if (HashSearch.keyExists("servicename")) {
        service = HashSearch.get("servicename");
    } else {
        service = $('#servicenameselect').val();
    }

    mode = "servicerequest";
    loadDivWith("#serviceList");

    mode = 'timeframerequest';
    loadDivWith('#timeframeList');
    
    if (!HashSearch.keyExists("calculate")) {
        loadNormalGraph();
    }

    $('#hostnameselect').change(function() {
        $('#capacityplanningrenderdiv').fadeOut();
    });
    $('#timeframeselect').blur( function() { loadExtrap(); });
    $('#servicenameselect').blur( function() { redraw(); });
	
	addDate(); 
	addValue();

    // If there is a URI calculate=1 then do an immediate calculation
    if (HashSearch.keyExists("calculate")) {
        if (HashSearch.get('calculate') == 1) {
            doResultSubmit();
        }
    }
});

function newHost() {
    mode = "servicerequest";
    host = $('#hostnameselect').val();
    loadDivWith("#serviceList");
    service = $('#servicenameselect').val();
    mode = 'timeframerequest';
    loadDivWith('#timeframeList');
    loadNormalGraph();
}

function redraw() {
    loadNormalGraph();
}
    
function loadNormalGraph() {
    
    $('#normalGraph').empty();
    $('#normalGraph').append(throbber);
    var urlstring   = '';
    urlstring      += baseurl;
    urlstring      += '?mode=generatehighcharts';
    urlstring      += '&service=' + encodeURI(service);
    urlstring      += '&hostname=' + encodeURI(host);
    urlstring      += '&return=graph';
    $('#normalGraph').load( urlstring );
}
    
function addDate() {
    var currDateFormId = dates++;
    var top = "<span id='date" + currDateFormId + "'><br />";  
	var mid ="<label>Date Selection: </label>";
    mid += "<input type='text' id='dateList" + currDateFormId + "' class='datepicker dateListText' width='60' onmousedown='$(this).datetimepicker();' />";
    var midd= "<input type='button' value='-' id='dateListRemove' onClick='removeDate(" + currDateFormId + ");' /></span>";
        
    $('#dateList').append( top + mid + midd );
}
        
        
function removeDate( listid ) {
    var id = '#date' + listid;
    var ot = '#dateList' + listid;
    $(id).hide().css('display','none').remove();
}
    
function addValue() {
    var currDateFormId = values++;
    var top = "<span id='value" + currDateFormId + "' style='display:none;'><br />";  
	var mid ="<label>Expected Value: </label>";
    mid += "<input type='text' id='valueList" + currDateFormId + "' class='valueListText' width='60' />";
    var midd= "<input type='button' value='-' id='valueListRemove' onClick='removeValue(" + currDateFormId + ");' /></span>";
        
    $('#valueList').append( top + mid + midd );
    $('#value' + currDateFormId).show();
}

function loadExtrap() {
    timeframe = $('#timeframeselect').val();
    numericva = timeframe.split(',')[0];
    var extrapStr = '';
    for(i=1;i<5;i++) {
        extrapStr += '<option value="' + i + '">' + (numericva * i) + ' Weeks</option>';
    }
    $('#extrapselect')
        .children()
        .remove()
        .end()
        .append(extrapStr);
    }
        
function removeValue( listid ) {
    var id = '#value' + listid;
    $(id).hide().css('display','none').remove();
}    
        

function changeVisibility( arg ) {
    for(var i=0; i < arg.length;i++) {
        $( arg[i] ).css("visibility","visible");        
    }
}
    
function changeInvisible( arg ) {
    for(var i=0; i < arg.length;i++) {
        $( arg[i] ).css("visibility","hidden");
    }
}

function hostChange() {
	mode = "servicerequest";
	host = $('#hostnameselect').val();
    HashSearch.set('hostname', host);
	$("#capacitytable").hide();
	loadDivWith("#serviceList");
	serviceChange(); 
	
}

function serviceChange() {
	mode = "timeframerequest";
	service = $('#servicenameselect').val();
    HashSearch.set('servicename', service);
	loadNormalGraph();
	disableid=["#extrapselect","#methodselect"];
	loadDivWith("#timeframeList");
}
    
function loadDivWith( blockid ) {
    var urlstring   = '';
    urlstring      += baseurl;
    urlstring      += '?mode=' + mode;
    urlstring      += '&hostname=' + host;
    urlstring      += '&service=' + service;
    $( blockid ).hide().load( encodeURI(urlstring) , 
        function() {
            var tonka;
            tonka = $( templateid ).attr("disabled");
            for(var i=0; i < disableid.length ; i++){
                if( !tonka )
                    $(disableid[i]).removeAttr("disabled");
                else
                    $(disableid[i]).attr("disabled",tonka);
            }
            $( blockid ).ready( changeVisibility( [ blockid ] ));
        }).show();
}
        
function doResultSubmit() {
    
    // Get values from forms
    var method      = encodeURI($('#methodselect').val());
    var period      = encodeURI($('#timeframeselect').val());
    var extrapolate = encodeURI($('#extrapselect').val());
    var en_service  = encodeURI(service);
    var en_host     = encodeURI(host);

    // Fix for periods
    if (period == "null") {
        period = encodeURI("Not Enough Data");
    }
    
    var urlstring = '';
    urlstring      += baseurl;
    urlstring      += '?mode=generatehighcharts';
    urlstring      += '&method=' + method ;
    urlstring      += '&period=' + period;
    urlstring      += '&extrapolate=' + extrapolate;
    urlstring      += '&service=' + en_service;
    urlstring      += '&hostname=' + en_host;
    urlstring      += '&return=graph';
    
    $('#resulttable').hide();    
    $('#normalGraph').hide().empty().append( throbber ).show().load( urlstring );
    
    var datestring = new Array();
    
    $("#dateList .dateListText").each(
        function() {
            datestring.push(encodeURI($(this).val()));        
        });
        
    var finaldatestring = datestring.join(',');
        
    var valuestring = new Array();
        
    $("#valueList .valueListText").each(function() {
        valuestring.push(encodeURI($(this).val()));
    });
        
    var finalvaluestring = valuestring.join(',');
    
    var urlstring   = '';
    urlstring      += baseurl;
    urlstring      += '?mode=generateresults';
    urlstring      += '&method=' + method ;
    urlstring      += '&period=' + period;
    urlstring      += '&extrapolate=' + extrapolate;
    urlstring      += '&service=' + en_service;
    urlstring      += '&hostname=' + en_host;
    urlstring      += '&dates=' + finaldatestring;
    urlstring      += '&values=' + finalvaluestring;
    urlstring      += '&return=xml';
    $('#capacitytable').load( urlstring );
    $('#capacitytable').show();

    // Set hashtag if calculated once
    HashSearch.set("calculate", 1);
}
