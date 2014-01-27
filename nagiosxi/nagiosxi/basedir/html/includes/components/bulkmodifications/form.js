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
	.css('height', '345px')
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
	var ids	  = []; //array of id's 
	
	var input = '#'+inputDiv;
	$(input+' :selected').each(function(i, selected){ 
	  titles[i] = $(selected).text(); 
	  values[i] = $(selected).val();
	  ids[i] 	= $(selected).attr('id'); //capture item id  
	});
	
	for(i=0;i < ids.length; i++)
	{
		//create data id that ties to option
		unique++; 
		var thisID = ids[i];	
		//input string for group or service selections 
		var string = '<tr class="trOption" oldid="'+ids[i]+'" id="tr'+unique+'"><td><div class="outputTableData">'+titles[i]+'</div></td>';
		string += "<input class='hiddenList' type='hidden' name='"+postArray+"[]' value='"+values[i]+"' />";
		//string += '<td><input type="checkbox" name="critical[]" value="'+values[i]+'" /></td>';				
		string += '<td><div class="remove"><a  class="xBox" href="javascript:void(0)" onclick="remove_row(\'tr'+unique+'\', \''+postArray+'\',\''+values[i]+'\');">X</a></div></td></tr>';
		
		//write output to new table/form	
		var output = '#'+outputDiv;
		
		//prevent duplicate entries 
		var itemID = '#'+thisID;
		$(itemID).hide(); 
		$(itemID).attr('disabled','disabled');		
		$(itemID).attr('selected',''); 
		
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

	delete uniqueIDs[id]; 
	//uniqueIDs[id]=false;
	$(oldID).show(); 	
	$(oldID).attr('disabled','');
	$(oldID).attr('selected','selected'); 
	
 
	
}//end function remove() 

function removeAll(tbl)
{
	$('#'+tbl+' tr.trOption').each(function() {

		var oldid = $(this).attr('oldid'); 
		$('#'+oldid).show();
		$(this).remove(); 

	}); 

}




function bulkWizard1(id) {

	switch(id) {
	
	case 'changeConfig':
		$('#bulkCmd').val('change'); //update post 
		$('#addContact').fadeOut('fast'); //hide options 
		$('#removeContact').fadeOut('fast');
		$('#addContactGroup').fadeOut('fast'); //hide options 
		$('#removeContactGroup').fadeOut('fast');		
		$('#bulk_change_option').fadeIn('fast'); //show available options 
	break;
	
	case 'addContact':
		$('#bulkCmd').val('add'); //update POST 
		$('#changeConfig').fadeOut('fast');
		$('#removeContact').fadeOut('fast');
		$('#addContactGroup').fadeOut('fast'); //hide options 
		$('#removeContactGroup').fadeOut('fast');		
		$('#contact_edit').fadeIn('fast');
		$('#overlayOptions').fadeIn('fast'); 
	break;
	
	case 'removeContact':
		$('#bulkCmd').val('remove');
		$('#changeConfig').fadeOut('fast');
		$('#addContact').fadeOut('fast');
		$('#addContactGroup').fadeOut('fast'); //hide options 
		$('#removeContactGroup').fadeOut('fast');		
		$('#findRelationships').fadeIn('fast'); 
		$('#contact_edit').fadeIn('fast');
	break; 
	
	case 'addContactGroup':
		$('#bulkCmd').val('addcg'); //update POST 
		$('#changeConfig').fadeOut('fast');
		$('#addContact').fadeOut('fast');
		$('#removeContact').fadeOut('fast'); //hide options 		
		$('#removeContactGroup').fadeOut('fast');
		$('#contactgroup_edit').fadeIn('fast');
		$('#overlayOptionsCg').fadeIn('fast'); 
	break;
	
	case 'removeContactGroup':
		$('#bulkCmd').val('removecg');
		$('#changeConfig').fadeOut('fast');
		$('#addContact').fadeOut('fast');
		$('#removeContact').fadeOut('fast'); //hide options 		
		$('#addContactGroup').fadeOut('fast');
		$('#findCgRelationships').fadeIn('fast'); 
		$('#contactgroup_edit').fadeIn('fast');
	break; 
	
	}

}


/* form functions for bulk modification tool */
function updateBulkForm() {
	var bools = new Array('active_checks_enabled','passive_checks_enabled','check_freshness','obsess_over_host','event_handler_enabled','flap_detection_enabled','retain_status_information','retain_nonstatus_information','process_perf_data','notifications_enabled'); 
	var intForm = '<input type="radio" value="1" id="rad1" name="intForm" /> <label for="rad1">on&nbsp;</label>';
	intForm    += '<input type="radio" value="0" id="rad0" name="intForm" /> <label for="rad0">off&nbsp;</label>';
	intForm    += '<input type="radio" value="2" id="rad2" name="intForm" /> <label for="rad2">skip&nbsp;</label>';
	intForm    += '<input type="radio" value="3" id="rad3" name="intForm" /> <label for="rad3">null&nbsp;</label>';
	var txtForm = '<label for="txtForm">Value: </label><input type="text" size="2" value="" name="txtForm" id="txtForm" />';

	var selected = $('#option_list').val(); 
	if($.inArray(selected,bools) != -1)
		$('#inner_config_option').html(intForm);
	else
		$('#inner_config_option').html(txtForm);	
		
	$('#saveButton').css('display','inline');
}	


	//XI's hijacked write config function 
function apply_config()
{
	window.location = "/nagiosxi/includes/components/nagioscorecfg/applyconfig.php";
}		



function getContactRelationships() {  

	//alert('hi');
	var id = $('#contact').val();
	//var bulkType = $('input:radio.bulkType:checked').val(); 
	var contact = encodeURI($("#contact option:selected").text());


	url = 'bulkmodifications.php?cmd=getcontacts&contact='+contact+'&id='+id; 
	//alert(url);
	$('#relationships').load(url); 
	$('#saveButton').css('display','inline'); 
}

function getContactGroupRelationships() {  

	//alert('hi');
	var id = $('#contactgroups').val();
	var bulkType = $('input:radio.bulkType:checked').val(); 
	var contactgroup = encodeURI($("#contactgroups option:selected").text());


	url = 'bulkmodifications.php?cmd=getcontactgroups&contactgroup='+contactgroup+'&id='+id; 
	//alert(url);
	$('#relationships').load(url); 
	$('#saveButton').css('display','inline'); 
}
var allChecked=false;
var allCheckedServices=false;
var allCheckedHosts=false

//check all items on page, i = number of results on page  
function checkAll() {

	if(allChecked) {
	       $('input:checkbox').each(function() {
         		this.checked = '';
        	});
		$('#checkAll').text('Check All');
		allChecked = false;		
	}
	else {
	    //var checked_status = this.checked;
	    $('input:checkbox').each(function() { 
	     	this.checked = 'checked';	  
	    });
	    $('#checkAll').text('Deselect All');
	    allChecked = true;
	}
}

//bad hack for bulk modification tool 
function checkAllType(type) {

	bool = (type=='host') ? allCheckedHosts : allCheckedServices;
	if(bool) {
	       $('input.'+type).each(function() {
         		this.checked = '';
        	});
		$('#checkAll'+type).text('Check All');
		if(type=='host')
			allCheckedHosts=false;
		else
			allCheckedServices=false;			
	}
	else {
	    //var checked_status = this.checked;
	    $('input.'+type).each(function() { 
	     	this.checked = 'checked';	  
	    });
	    $('#checkAll'+type).text('Deselect All');
		if(type=='host')
			allCheckedHosts=true;
		else
			allCheckedServices=true;
	}
}