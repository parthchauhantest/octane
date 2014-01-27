/* javascript include for CCM forms */ 
/* code by Mike Guthrie, Nagios Enterprises */ 


//default is to load "Common Settings" 
//load preselected items
$(document).ready(function() {
  // hide all but tab 1    
  	showHideTab('1');
  
  //select any preloaded items 
  //clear cached selections and restore to DB entries 
 	document.forms[0].reset(); 

	//hosts
	transferMembers('selHosts', 'tblHosts', 'hosts');		
	//parents
	transferMembers('selParents', 'tblParents', 'parents'); 
	//hostgroups
	transferMembers('selHostgroups', 'tblHostgroups', 'hostgroups');
	//servicegroups
	transferMembers('selServicegroups', 'tblServicegroups', 'servicegroups');
	//templates
	transferMembers('selTemplates', 'tblTemplates', 'templates');
	//contacts
	transferMembers('selContacts', 'tblContacts', 'contacts');
	//services
	transferMembers('selHostservices', 'tblHostservices', 'hostservices');
	//timeperiods
	transferMembers('selExcludes', 'tblExcludes', 'excludes');
	//contactgroups
	transferMembers('selContactgroups','tblContactgroups','contactgroups');
	//contacttemplates
	transferMembers('selContacttemplates','tblContacttemplates','contacttemplates'); 
	//notificationcommands
	transferMembers('selHostcommands','tblHostcommands','hostcommands'); 
	transferMembers('selServicecommands','tblServicecommands','servicecommands'); 
	//services (service escalations and dependency page );
	transferMembers('selServices','tblServices','services');
	//dependencies
	transferMembers('selHostdependencys','tblHostdependencys','hostdependencys');
	transferMembers('selHostgroupdependencys','tblHostgroupdependencys','hostgroupdependencys');	
	transferMembers('selServicedependencys','tblServicedependencys','servicedependencys');  
	  
	  //Free variables 

	//////////////////////////Validate and Submit//////////////////////
	$('#subForm1').click(function() {
		var valid = true; 
		$('input.required').each(function() {
			if($(this).val() == '')
				valid = false;					
		});
		if(valid==false) { 
			alert('Missing required fields'); 
			return; 
		}
		//sanity check for user password //make sure passwords are the same
		if($('#type').val() == 'user') {		 
			if( $('#password').val() != $('#confirm').val() ) {
				alert('Passwords do not match!');
				return;
			}	
			//bypass main routing function for this 
			$('#cmd').val('admin');		
		}	
		
		//object name character checks 		
		if( $('#type').val() == 'service'  ) {
			if( $('#tfServiceDescription').val().match(/[^a-zA-Z0-9 .\/@:_-]/) ) {
				alert('Illegal characters in service description!'); 
				return false;
			}	
		}	
		//object names 
		if ( ($('#tfName').val() != undefined) && $('#tfName').val().match(/[^a-zA-Z0-9 .\:@_-]/)) {
			alert('Illegal characters in object name!'); 
			return false;
		}	

		
		//sanity checks passed 
		$('#mainCcmForm').submit(); 		
	}); 	
	//check for valid fields

	////////////////dynamic width for option lists//////////
	// lists width = 275px 
	//$('.lists option').hover(function() { //mousein
	//	var width = $(this).width(); 
		//var text = $(this).text(); 
		//$(this).text(width); 
	
	//},function(){ //mouseout
		//$(this).text(width);
	//}); 
	
	//show command test??
	toggle_command_test();

    ///////////////////command test///////////////// 	
	$('#command_test').click(function() {
		 
	//TODO: add logic to detect selected hosts and ask which one to check against 
	
	var address = $('#tfAddress').val();
	var cid = $('#selHostCommand').val();
	var arg1 = encode($('#tfArg1').val()); 
	var arg2 = encode($('#tfArg2').val());
	var arg3 = encode($('#tfArg3').val());
	var arg4 = encode($('#tfArg4').val());
	var arg5 = encode($('#tfArg5').val()); 
	var arg6 = encode($('#tfArg6').val()); 
	var arg7 = encode($('#tfArg7').val()); 
	var arg8 = encode($('#tfArg8').val()); 		
	var fullcommand = $('#fullcommand').html();
	var token = $('#token').val(); 

	    //bail without a check command id 
	    if(cid=='null' || cid=='0' || cid==undefined) {
		alert('You must select a check command to test');  
		return false;
	    }
	  
	    //check if we need a hostaddress for this command
	    var hostmacro =new RegExp(/HOSTADDRESS/); 
            var bool = hostmacro.test(fullcommand); 

//		alert(address); 
//		alert(bool); 

      if((address==undefined || address=='undefined' || address=='') && bool==true) {
//      	alert('address is undefined'); 
			address = prompt("Please enter a host address: ", "");
			if(address == null) return; 
      } 
	    
	    //dump output to overlay div 
	    overlay('commandOutputBox'); 
            var url = "command_test.php?cmd=test&token="+token+"&mode=test&address="+address+"&cid="+cid+"&arg1="+arg1+"&arg2="+arg2+"&arg3="+arg3;
            url += "&arg4="+arg4+"&arg5="+arg5+"&arg6="+arg6+"&arg7="+arg7+"&arg8="+arg8;

	    //TODO: add a session protector in the URL arguments so only authenticated calls can be processed  
	    //alert(url);
         $('#command_output').html('<img src="images/throbber1.gif">').load(url);
	     //~ $('#command_output').load(url);            
        }); 

}); 
 
 
function toggle_command_test() {
	//hide command test if no check command selected
	var cid = $('#selHostCommand').val();
	if(cid=='null' || cid=='0' || cid==undefined)
		$('#command_test_box').hide(); 
	else
		$('#command_test_box').show(); 

} 
 
function encode(arg)
{
	if(arg=='' || arg==undefined || arg=='undefined') return ''; 
	return encodeURI(arg); 
}
 
 
 //Tabular display for CCM forms 
function showHideTab(id)
{

	var inpID = "#tab"+id;			
	for(i=1;i < 5;i++)
	{
		if(i != id)
		{
			var tab = "#tab"+i;	
			$(tab).hide()
		}	
	}	
	$(inpID).show();
	   
}
 
//////////////tab click bind ////////////////
$(document).ready(function() {	

	$('#commonSettings').toggleClass('selectedTab'); 
	

	$('.navLink').click(function() {
		$('.navLink').parent().each(function() {
			$(this).removeClass('selectedTab'); 
		}); 
		$(this).parent().toggleClass('selectedTab');
	}); 
}); 

function abort(type)
{
	if(type=='user')
		window.location='index.php?cmd=admin&type='+type;
	else
		window.location='index.php?cmd=view&type='+type; 
}


function removeAll(tbl)
{
	$('#'+tbl+' tr.trOption').each(function() {

		var oldid = $(this).attr('oldid'); 
		$('#'+oldid).prop('disabled',false);		
		$('#'+oldid).prop('selected','selected'); 
		$('#'+oldid).show();
		$(this).remove(); 

	}); 

}



//load and split command args into fields upon page load.  DO NOT CHANGE when select list changes 

function reveal_command(id)
{
	//var args = new Array();
	//args = command_list[id].split(" ", 9);
	$('#fullcommand').empty(); 
	$('#fullcommand').append(command_list[id]);	

	toggle_command_test();
}	



function get_plugin_help(token)
{
	var input_plugin = $('#selPlugins').val(); 
	
	$('#pluginhelp').load('command_test.php?&cmd=help&mode=help&plugin='+input_plugin+'&token='+token); 
}

/* **********************OVERLAY ********* */

function overlay(div)
{	
	var ID = '#'+div;
	//sanity check 
	if($(ID).html() == null) {
		alert('Undefined overlay');
		return;
	}	
	//fade out rest of page 
	$('#visibleDivs').css('opacity','0.2').css('z-index','2');	
	$('div.navDiv').css('opacity','0.2').css('z-index','2');
	$('h1.title').css('opacity','0.2').css('z-index','2');
	
	//display the overlay 	
	$(ID)
	.css('visibility', 'visible')
	.css('opacity','1.0')
	.css('position','fixed')
	.css('background', '#FFF')
	.css('border', '5px solid #000')
	.css('height', '360px')
	.css('padding', '10px'); 

}

function killOverlay(id)
{
	$('#visibleDivs').css('opacity','1.0').css('z-index','0');
	$('div.navDiv').css('opacity','1.0').css('z-index','0');	
	$('h1.title').css('opacity','1.0').css('z-index','0');
	
	ID = '#'+id;
	$(ID).css('visibility', 'hidden')
	.css('height', '0')
	.css('padding', '10px')
	;

}


/* **************Transfer Memberships and form auto population **** */

/*this function toggles the grids and configuration tables */
function showHide(id, td_id)
{
	//alert(id);
	//change background color of 'this' td 
	var tdID = "#"+td_id;	
	$(tdID).toggleClass('groupexpand')
	var divID = "#"+id;
	$(divID).slideToggle("fast");
	   
}


/*this function hides all lists that can be toggled*/
function hide()
{
	//alert('this is a functional alert');
	$(".hidden").hide();
}

//unique identifier for added <tr> rows
var unique = 0;

//control array for transferMembers() 
var uniqueIDs = []; 

///////////////////// transferMembers()  ///////////////////////////////////////// 
//used to select parents, hostgroups, and other items that may have multiple values
//inputDiv - the ID of the select list to pull selected items from 
//outputDiv - the table that the selected items are being added to ->(hidden inputs will be added for the values)
//postArray - the php $_POST array value that these items will be added to 
function transferMembers(inputDiv,outputDiv, postArray, afterLoad)
{
	//unique;
	var titles = []; //display titles for select options
	var values = []; //option values 
    var orderids = []; // order ids
	var ids	  = []; //array of id's 
	
	var input = '#'+inputDiv;
	$(input+' :selected').each(function(i, selected){ 
	  titles[i] = $(selected).text(); 
	  values[i] = $(selected).val();
      orderids[i] 	= [i,$(selected).attr('orderid')]; //capture order item id  
	  ids[i] 	= $(selected).attr('id'); //capture item id  
	});
	
    function cmp(a, b) {
        return a[1].localeCompare(b[1]);
    }

if (typeof orderids[0] !== 'undefined' && typeof orderids[0][1] !== 'undefined')
    orderids.sort(cmp);

	for(i=0;i < ids.length; i++)
	{
		//create data id that ties to option
		unique++; 
        if (orderids.length > 0)
            index_id = orderids[i][0];
        else
            index_id = ids[i]
		var thisID = ids[index_id];	
		//input string for group or service selections 
		var string = '<tr class="trOption" oldid="'+ids[index_id]+'" id="tr'+unique+'"><td><div class="outputTableData">'+titles[index_id]+'</div></td>';
		string += "<input class='hiddenList' type='hidden' name='"+postArray+"[]' value='"+values[index_id]+"' />";
		//string += '<td><input type="checkbox" name="critical[]" value="'+values[i]+'" /></td>';				
		string += '<td><div class="remove"><a  class="xBox" href="javascript:void(0)" onclick="remove_row(\'tr'+unique+'\', \''+postArray+'\',\''+values[index_id]+'\');">X</a></div></td></tr>';
		
		//write output to new table/form	
		var output = '#'+outputDiv;
		
		//prevent duplicate entries 
		var itemID = '#'+thisID;
		$(itemID).hide(); 
		$(itemID).prop('disabled','disabled');		
		$(itemID).prop('selected',false); 
		
		//old dupliate prevention 
		var duplicate = false; 
		
		/*  SOLVED DUPLICATE ISSUE BY HIDING SELECTABLE ITEM 
		if(uniqueIDs[thisID] == undefined || uniqueIDs[thisID] == false || $.inArray(thisID,uniqueIDs)== -1 )   
			uniqueIDs[thisID] = thisID;  
		else {
			alert('Duplicate: '+uniqueIDs[thisID]);
			duplicate=true; 
		}
		
		*/ 
		//if new value is not already there, insert it			
		if(duplicate==false)
			$(output).append(string); 		

	}
	
} //end memberTransfer()

function insertDefinition(varName,varDef)
{
	//grab text fields if nothing has been passed
	if(varName==false && varDef==false)
	{
		varName = $('#txtVariablename').val();
		varDef = $('#txtVariablevalue').val();
	}
	
	if(varName =='' || varDef=='') {
		alert('Invalid entry, please enter both a variable name and a variable value.');
        $('#txtVariablename')
		return;
	}

    if(varName.match(/[^a-zA-Z0-9 .\:_-]/)) {
        alert('Illegal characters in variable name.');
        return;
    }
	
    if(varDef.match(/[`~$&|<>]/)) {
        alert('Illegal characters in variable definition.');
        return;
    }	
	
	varDef = htmlEntities(varDef);  
	//alert(varname);	
	
		//create data id that ties to option
		unique++; 
				
		//input string for group or service selections 
		var string = '<tr class="trOption" id="tr'+unique+'"><td>'+varName+'</td><td>'+varDef+'</td>';
		string += "<input class='hiddenList' type='hidden' name='variables[]' value='"+varName+"' />";
		string += "<input class='hiddenList' type='hidden' name='variabledefs[]' value='"+varDef+"' />";			
		string += '<td><a href="javascript:void(0)" onclick="remove_row(\'tr'+unique+'\', \'\',\'\')">X</a></td></tr>';
		
		//write output to new table/form	
		$('#tblVariables').append(string); 
	
}	


function htmlEntities(str) {
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g,'&apos;');
}


function insertTimeperiod(varName,varDef) 
{
	//grab text fields if nothing has been passed
	if(varName==false && varDef==false)
	{
		varName = $('#txtTimedefinition').val();
		varDef = $('#txtTimerange').val();
	}
	//alert(varname);	
	if(varName =='' || varDef=='') {
		alert('Invalid entry, please enter both a time definition and a time range');
		return;
	}
		//create data id that ties to option
		unique++; 
				
		//input string for group or service selections 
		var string = '<tr class="trOption" id="tr'+unique+'"><td>'+varName+'</td><td>'+varDef+'</td>';
		string += "<input class='hiddenList' type='hidden' name='timedefinitions[]' value='"+varName+"' />";
		string += "<input class='hiddenList' type='hidden' name='timeranges[]' value='"+varDef+"' />";			
		string += '<td><a href="javascript:void(0)" onclick="remove_row(\'tr'+unique+'\', \'\',\'\')">X</a></td></tr>';
		
		//write output to new table/form	
		$('#tblTimeperiods').append(string); 
		//clear fields 
		$('#txtTimedefinition').val('');
		$('#txtTimerange').val('');
	
}	




/////////////////////// remove() ////////////////////////////
//removes item from output table and arrays of selected items
//id - removes the <tr> by unique id
//arrayType - tells which array to remove the selected item from
//value - tells what value to erase from the selected array 
function remove_row(id, arrayType, value)
{
	var ID = '#'+id;
	var oldID = '#'+$(ID).attr('oldid'); 
	
	//alert("ID: "+ID+" oldID: "+oldID);
	$(ID).remove();

	uniqueIDs[id]=null;  
	delete uniqueIDs[id]; 
	//uniqueIDs[id]=false;
	$(oldID).show(); 	
	$(oldID).prop('disabled',false);
	$(oldID).prop('selected','selected'); 
	
 
	
}//end function remove() 


function getHelpOverlay(type) {

	var opt = $('#helpList').val(); 
	var token = $('#token').val(); 
	if(opt == '')
		return; 
		
	var url='ajax.php?cmd=getinfo&type='+type+'&opt='+opt+'&token='+token; 
	$('#documentation').load(url,function() {
		overlay('documentation');
		//alert('loaded!');
		
	});	

}





