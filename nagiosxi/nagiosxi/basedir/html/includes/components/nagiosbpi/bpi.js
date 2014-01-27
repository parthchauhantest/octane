/*this function toggles the grids and configuration tables */

/*
// Nagios BPI (Business Process Intelligence) 
// Copyright (c) 2010 Nagios Enterprises, LLC.
// Written by Mike Guthrie <mguthrie@nagios.com>
//
// LICENSE:
//
// This work is made available to you under the terms of Version 2 of
// the GNU General Public License. A copy of that license should have
// been provided with this software, but in any event can be obtained
// from http://www.fsf.org.
// 
// This work is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
// General Public License for more details.
// 
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
// 02110-1301 or visit their web page on the internet at
// http://www.fsf.org.
//
//
// CONTRIBUTION POLICY:
//
// (The following paragraph is not intended to limit the rights granted
// to you to modify and distribute this software under the terms of
// licenses that may apply to the software.)
//
// Contributions to this software are subject to your understanding and acceptance of
// the terms and conditions of the Nagios Contributor Agreement, which can be found 
// online at:
//
// http://www.nagios.com/legal/contributoragreement/
//
//
// DISCLAIMER:
//
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
// INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A 
// PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT 
// HOLDERS BE LIABLE FOR ANY CLAIM FOR DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
// OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE 
// GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) OR OTHER
// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, STRICT LIABILITY, TORT (INCLUDING 
// NEGLIGENCE OR OTHERWISE) OR OTHER ACTION, ARISING FROM, OUT OF OR IN CONNECTION 
// WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/


hiddenTds = new Array();
hiddenDivs = new Array(); 
hiddenSorts = new Array(); 
var serializedDivs = false;
var serializedTds = false; 
var serializedSorts = false;  

function showHide(id, td_id)
{
    var tdID = "#"+td_id;
    $(tdID).toggleClass('groupexpand');
    hiddenTds.push(td_id); 
    
    var divID = "#"+id;
    $(divID).slideToggle("fast");
    hiddenDivs.push(id);
    
    //for some reason this preserves the styles on the AJAX load, no idea why
    serializedDivs = serialize(hiddenDivs);
    serializedTds = serialize(hiddenTds); 
}

var d = 0;
function bpi_load()
{
        args = '?divs='+serializedDivs+'&tds='+serializedTds+'&sorts='+serializedSorts; //pass along JS to next page load 
        //alert(args); 
       
       	//$('#bpiContent').empty(); 
        //var contents =  $.load('bpi_display.php'+args); 
        $('#bpiContent').load('bpi_display.php'+args, function() {
       		var d = new Date(); 
         	$('#lastUpdate').empty();
        	$('#lastUpdate').append(('Last Update: '+ d.toString() ));       	
       });
		//alert(contents);                  
        setTimeout(bpi_load,(MULTIPLIER*1000));  //have this multiplier as a config option 
}

function reShowHide(id, td_id)
{
    var tdID = "#"+td_id;
    $(tdID).toggleClass('groupexpand');

    //hiddenDivs.push(id);
    var divID = "#"+id;
    //do this manually with an if/else

    $(divID).slideToggle(0);
    //for some reason this preserves the styles on the AJAX load, no idea why
    //serializedData = serialize(hiddenDivs);

}


/*this function hides all lists that can be toggled*/
function hide()
{
	//alert('this is a functional alert');
	$(".hidden").hide();
}

var unique = 0;

/* function for selectable bpi group items in the add/edit page. */ 
function dostuff()
{
	//unique;
	var titles = []; //display titles for select options
	var values = []; //option values 
	$('#multiple :selected').each(function(i, selected){ 
	  titles[i] = $(selected).text(); 
	  values[i] = $(selected).val();
	  $(selected).prop('disabled','disabled');
	  $(selected).prop('selected',null); 
	});
	
	for(i=0;i < titles.length; i++)
	{
		//create data id that ties to option
		unique++; 
				
		//input string for group or service selections 
		var string = '<tr class="trOption" id="tr'+unique+'"><td class="memberTitle">'+titles[i]+'</td>';
		string += "<input type='hidden' name='members[]' value='"+values[i]+"' />";
		string += '<td><input type="checkbox" name="critical[]" value="'+values[i]+'" /></td>';				
		string += '<td><a href="javascript:void(0)" onclick="bpi_remove(\'tr'+unique+'\',\''+values[i]+'\')">X</a></td></tr>';
		//write output to new table/form	
		$('#selectoutput').append(string); 
		
	}
	
}

/* function for preloading form values on 'edit' page */ 
function preload(title, value, opt)
{
			//create data id that ties to option
		unique++; 
				
		//input string for group or service selections 
		if(opt=='|')
		{ var checked = 'checked="checked"'; }
		else { checked = ''; }		
		
		var string = '<tr class="trOption" id="tr'+unique+'"><td class="memberTitle">'+title+'</td>';
		string += "<input type='hidden' name='members[]' value='"+value+"' />";
		string += '<td><input type="checkbox" '+checked+' name="critical[]" value="'+value+'" /></td>';				
		string += '<td><a href="javascript:void(0)" onclick="bpi_remove(\'tr'+unique+'\',\''+value+'\')">X</a></td></tr>';
		//write output to new table/form	
		$('#selectoutput').append(string); 
		
		//find option with this value and disable it
		// XXX  TODO: This is terribly inefficient, needs a redo!
		$('#multiple option').each(function(i, option){ 
			if($(option).val() == value) {
				$(option).prop('disabled','disabled'); 
				return; 
			}	
		});
		

}



function submitForm()
{
	//validate required fields
	var id = $('#groupIdInput').val();
	var title = $('#groupTitleInput').val();
	var warn = parseInt($('#groupCrit').val());
	var crit = parseInt($('#groupWarn').val());
	
	if( id == '' || title == '')
	{	
		//required fields met
		alert('Please fill in all required fields');
		return; 
	}
	else if ( (warn < 0 || warn > 100) || (crit < 0 || crit > 100)   ){
		alert('Invalid entry for thresholds, please use an integer (0-100)');
		return; 
	}
	
	else 
		$('#outputform').submit(); 
}

//removes item from output table
function bpi_remove(id,textval)
{
	var ID = '#'+id;
	$(ID).remove();
				
	//find option with this value and disable it
	// XXX  TODO: This is terribly inefficient, needs a redo!
	$('#multiple option').each(function(i, option){ 
		if($(option).val() == textval) {
			//alert("match!"); 
			$(option).prop('disabled',false);  
			return; 
		}	
	});
	
	
}

//expecting delete url as the argument 
function deleteGroup(arg)
{
	var conf = confirm('Are you sure you want to delete this group?\nThis will permanently delete this group and all associated memberships.\nThis action cannot be undone.');
	if(conf===true)
	{
		//forward to delete page
		location.href=arg;
	}
	

}

function clearMembers()
{
	var table = $('#selectoutput');
	//alert(table);
	var members = table.childNodes;
	for(i=0; i<=unique; i++)
	{
		idMask = 'tr'+i;
		if(document.getElementById(idMask))
		{
			textval = $('#'+idMask+' td input').val();
			//alert(textval);
			bpi_remove(idMask,textval);
		}
	}
}

    
function sortchildren(id,preload)
{
	//service and group listings 
	var count = 0;
//	$(id).each(function() {
//		count++; 
//	}); 	
//	alert('ID: '+id+' COUNT: '+count); 
	
	shuffle_items(id,'sortOk');
	shuffle_items(id,'sortUp');
	//warnings
	shuffle_items(id,'sortWarning');
	//unknown/unreachable
	shuffle_items(id,'sortUnknown'); 
	shuffle_items(id,'sortUnreachable');
	//critical/down 
	shuffle_items(id,'sortCritical'); //Critical 
	shuffle_items(id,'sortDown'); //Down hosts 	
	
	if(preload==false) {
		
		if($.inArray(id,hiddenSorts)==-1) {
			hiddenSorts.push(id); 	
			serializedSorts = serialize(hiddenSorts); 
			//alert('sort added'); 
		}	
	}
}


function shuffle_items(id,cssclass)
{
	//grab all child list items for this group 
	$('li.'+id+'.'+cssclass).each(function() {	

		var listClass = $(this).attr('class');
		var string = "<li class='"+listClass+"'>"+$(this).html()+"</li>"; 
		//fetch parent class and ID's 
		var parentID = $(this).parent().attr('id');
		var parentClass = $(this).parent().attr('class');
		  
		///////////////TOP LEVEL GROUPS ///////////////
		if(parentID =='') {   //top level class/id is one more level up 
			parentID = $(this).parent().parent().attr('id');				
			parentClass = $(this).parent().parent().attr('class');  //returns "hidden" for top level group 
		}
					 
		//pop element to the top of the list 
		$('#'+parentID).prepend( string );
		$(this).remove();  
	}); 
}


function infobox(helpItem)

{
	//alert(helpItem)
	msg = ''; 
	switch(helpItem)
	{
		case 'groupid':
		msg = '<strong>Group ID:</strong> The Group ID is a unique identifier used internally by Nagios BPI and the check plugin. Only alpha-numeric characters are allowed.  Spaces are not allowed.'; 
		break; 
		
		case 'displayname':
		msg = '<strong>Display Name:</strong> The group name that will be displayed to the end-user in the BPI Interface.'; 
		break;
		
		case 'primary':
		msg = '<strong>Primary Group:</strong> Primary Groups are visible on the top level of the tree. Non-primary groups must be added as a child member to a visible group in order to be displayed in the tree';
		break;
		
		case 'priority':
		msg = '<strong>Priority:</strong> The priority tab the group will be displayed in. Defaults to "None" for hostgroups, servicegroups, and dependencies.';
		break;
		
		case 'warning':
		msg = '<strong>Warning Threshold:</strong> If the health percentage of the group drops below the Warning Threshold, the group state changes to "Warning."';
		break;
		
		case 'critical':
		msg = '<strong>Critical Threshold:</strong> If the health percentage of the group drops below the Critical Threshold, the group state changes to "Critical."';
		break;
		
		case 'authusers':
		msg = '<strong>Authorized Users:</strong> A list of non-administrative users who can view this group.  Non-administrative users will only see hosts and services within the groups that they are authorized for, and the group state will be calculated based on the "visible" group members. Admin-level users can automatically see and modify all groups.';
		break;
		
		case 'groupmembers':
		msg = '<strong>Group Members:</strong> Group Members can be hosts, services, or other groups. "Essential" members can decide the entire group\'s state. If an essential member\'s state is in a problem state the parent group is listed as "Critical." If all essential members are in a non-problem state, the group\'s state is then determined by the threshold settings.';
		break; 
		
		default:
		return; 
	
	}
	
	msg = "<div id='msg'>"+msg+"</div><br /><br /><div id='closeBox'><a href='javascript:closeinfobox();'>Close</a></div>"; 
	//set css for $('#infoBox')
	
	
	$('#container').css('opacity','0.2')
					.css('z-index','2');
	
//	$('div.navDiv').css('opacity','0.2').css('z-index','2');
//	$('h1.title').css('opacity','0.2').css('z-index','2');
		
	$('#helpBox').html(msg);
	$('#helpBox').css('visibility', 'visible')
				 .css('z-index','1000'); 


//	$('#helpBox').show(); 
	//populate infobox with appropriate text 

	

}

function closeinfobox()
{

	$('#container').css('opacity','1.0').css('z-index','0');
//	$('div.navDiv').css('opacity','1.0').css('z-index','0');	
//	$('h1.title').css('opacity','1.0').css('z-index','0');
	
	$('#helpBox').css('visibility', 'hidden')
//	.css('height', '0')
//	.css('padding', '10px')
				.css('z-index','-1000')

	;

}

//selected tab
$(document).ready(function() {
	$('#'+tab).parent().addClass('selectedTab'); 

}); 




function serialize (mixed_value) {
    // http://kevin.vanzonneveld.net
    // +   original by: Arpad Ray (mailto:arpad@php.net)
    // +   improved by: Dino
    // +   bugfixed by: Andrej Pavlovic
    // +   bugfixed by: Garagoth
    // +      input by: DtTvB (http://dt.in.th/2008-09-16.string-length-in-bytes.html)
    // +   bugfixed by: Russell Walker (http://www.nbill.co.uk/)
    // +   bugfixed by: Jamie Beck (http://www.terabit.ca/)
    // +      input by: Martin (http://www.erlenwiese.de/)
    // +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net/)
    // +   improved by: Le Torbi (http://www.letorbi.de/)
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net/)
    // +   bugfixed by: Ben (http://benblume.co.uk/)
    // -    depends on: utf8_encode
    // %          note: We feel the main purpose of this function should be to ease the transport of data between php & js
    // %          note: Aiming for PHP-compatibility, we have to translate objects to arrays
    // *     example 1: serialize(['Kevin', 'van', 'Zonneveld']);
    // *     returns 1: 'a:3:{i:0;s:5:"Kevin";i:1;s:3:"van";i:2;s:9:"Zonneveld";}'
    // *     example 2: serialize({firstName: 'Kevin', midName: 'van', surName: 'Zonneveld'});
    // *     returns 2: 'a:3:{s:9:"firstName";s:5:"Kevin";s:7:"midName";s:3:"van";s:7:"surName";s:9:"Zonneveld";}'
    var _utf8Size = function (str) {
        var size = 0,
            i = 0,
            l = str.length,
            code = '';
        for (i = 0; i < l; i++) {
            code = str.charCodeAt(i);
            if (code < 0x0080) {
                size += 1;
            } else if (code < 0x0800) {
                size += 2;
            } else {
                size += 3;
            }
        }
        return size;
    };
    var _getType = function (inp) {
        var type = typeof inp,
            match;
        var key;

        if (type === 'object' && !inp) {
            return 'null';
        }
        if (type === "object") {
            if (!inp.constructor) {
                return 'object';
            }
            var cons = inp.constructor.toString();
            match = cons.match(/(\w+)\(/);
            if (match) {
                cons = match[1].toLowerCase();
            }
            var types = ["boolean", "number", "string", "array"];
            for (key in types) {
                if (cons == types[key]) {
                    type = types[key];
                    break;
                }
            }
        }
        return type;
    };
    var type = _getType(mixed_value);
    var val, ktype = '';

    switch (type) {
    case "function":
        val = "";
        break;
    case "boolean":
        val = "b:" + (mixed_value ? "1" : "0");
        break;
    case "number":
        val = (Math.round(mixed_value) == mixed_value ? "i" : "d") + ":" + mixed_value;
        break;
    case "string":
        val = "s:" + _utf8Size(mixed_value) + ":\"" + mixed_value + "\"";
        break;
    case "array":
    case "object":
        val = "a";
/*
            if (type == "object") {
                var objname = mixed_value.constructor.toString().match(/(\w+)\(\)/);
                if (objname == undefined) {
                    return;
                }
                objname[1] = this.serialize(objname[1]);
                val = "O" + objname[1].substring(1, objname[1].length - 1);
            }
            */
        var count = 0;
        var vals = "";
        var okey;
        var key;
        for (key in mixed_value) {
            if (mixed_value.hasOwnProperty(key)) {
                ktype = _getType(mixed_value[key]);
                if (ktype === "function") {
                    continue;
                }

                okey = (key.match(/^[0-9]+$/) ? parseInt(key, 10) : key);
                vals += this.serialize(okey) + this.serialize(mixed_value[key]);
                count++;
            }
        }
        val += ":" + count + ":{" + vals + "}";
        break;
    case "undefined":
        // Fall-through
    default:
        // if the JS object has a property which contains a null value, the string cannot be unserialized by PHP
        val = "N";
        break;
    }
    if (type !== "object" && type !== "array") {
        val += ";";
    }
    return val;
}



