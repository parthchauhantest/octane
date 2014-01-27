popup = false;
function info(key1,key2,ver) {
	if(popup&&popup.closed==false) popup.close();
	var top  = (screen.availHeight - 240) / 2;
	var left = (screen.availWidth - 320) / 2;
	popup = window.open("info.php?key1=" + key1 + "&key2=" + key2 + "&version=" + ver,
						"Information", 
						"width=320, height=240, top=" + top + ", left=" + left + ", SCROLLBARS=YES, MERNUBAR=NO, DEPENDENT=YES");	
	popup.focus();
}
var myFocusObject = new Object();
function checkfields(fields,frm,object) {
	var error    = false;
	var ar_field = fields.split(",");
	for (i=0;i<ar_field.length;i++){
		if (frm[ar_field[i]].value == "") {
			//frm[ar_field[i]].focus();
			object.myValue = frm[ar_field[i]];
			return false;
			break;
		}
	}
	return true;
}
function checkfields2(fields,frm,object) {
	var error    = false;
	var ar_field = fields.split(",");
	for (i=0;i<ar_field.length;i++){
		if ((frm[ar_field[i]].value == "") || (frm[ar_field[i]].value == "0")) {
			//frm[ar_field[i]].focus();
			object.myValue = frm[ar_field[i]];
			return false;
			break;
		}
	}
	return true;
}
function checkboxes(fields,frm) {
	var retval   = false;
	var ar_field = fields.split(",");

	for (i=0;i<ar_field.length;i++){
		if (frm[ar_field[i]].checked == true) {
			retval = true;
		}
	}
	return retval;
}

<!-- YUI Messagebox -->
function msginit(msg,header,type) {
	YAHOO.namespace("msg.container");
	var handleOK = function() {
		this.hide();
		myFocusObject.myValue.focus();
	};
	if (type == 1) {
		var iconobj = YAHOO.widget.SimpleDialog.ICON_WARN;
	}
	if (type == 2) {
		var iconobj = YAHOO.widget.SimpleDialog.ICON_HELP;
	}
	YAHOO.msg.container.domainmsg = new YAHOO.widget.SimpleDialog("domainmsg", 
																		 { width: "300px",
																		   fixedcenter: true,
																		   visible: false,
																		   draggable: false,
																		   close: true,
																		   text: msg,
																		   modal: true,
																		   icon: iconobj,
																		   constraintoviewport: true,
																		   buttons: [ { text:"Ok", handler:handleOK, isDefault:true } ]
																		 } );
	YAHOO.msg.container.domainmsg.setHeader(header);
	YAHOO.msg.container.domainmsg.render("msgcontainer");
	YAHOO.msg.container.domainmsg.show();
}

<!-- YUI Confirmbox -->
function confirminit(msg,header,type,yes,no,key) {
	YAHOO.namespace("question.container");
	var handleYes = function() {
		confOpenerYes(key);
		this.hide();
	};
	var handleNo = function() {
		this.hide();
	};
	if (type == 1) {
		var iconobj = YAHOO.widget.SimpleDialog.ICON_WARN;
	}
	YAHOO.question.container.domainmsg = new YAHOO.widget.SimpleDialog("confirm1", 
																		 { width: "400px",
																		   fixedcenter: true,
																		   visible: false,
																		   draggable: false,
																		   close: true,
																		   text: msg,
																		   modal: true,
																		   icon: iconobj,
																		   constraintoviewport: true,
																		   buttons: [ { text:yes, handler:handleYes, isDefault:true },
																					 { text:no, handler:handleNo }]
																		 } );
	YAHOO.question.container.domainmsg.setHeader(header);
	YAHOO.question.container.domainmsg.render("confirmcontainer");
	YAHOO.question.container.domainmsg.show();
}


<!-- YUI Dialogbox -->
function dialoginit(key1,key2,ver,header) {
	YAHOO.namespace("dialog.container");

	var handleCancel = function() {
		this.cancel();
	};
	var handleSuccess = function(o){ 
		if(o.responseText !== undefined){
			document.getElementById('dialogcontent').innerHTML = o.responseText;
		}
	}
	var handleFailure = function(o){ 
		if(o.responseText !== undefined){ 
			document.getElementById('dialogcontent').innerHTML = "No information found";
		}
	}  
	var callback = 	{ 
		success:handleSuccess, 
		failure: handleFailure 
	}; 		
	sUrl = "info.php?key1=" + key1 + "&key2=" + key2 + "&version=" + ver;
	var request = YAHOO.util.Connect.asyncRequest('GET', sUrl, callback);
	
	if (typeof YAHOO.dialog.container.infodialog == "undefined") {
		YAHOO.dialog.container.infodialog = new YAHOO.widget.Dialog("infodialog", 
								{ width : "50em",
								  visible : false, 
								  draggable: true,
								  fixedcenter: true,
								  constraintoviewport : true,
								  buttons : [ { text:"Ok", handler:handleCancel, isDefault:true } ]
								});
		
	}
	
	YAHOO.dialog.container.infodialog.setHeader(header);
	YAHOO.dialog.container.infodialog.render();
	YAHOO.dialog.container.infodialog.show();
}

<!-- YUI Kalender -->
function calendarinit(lang,start,field,key,cont,obj) { 
	YAHOO.util.Event.onDOMReady(function(){

		var dialog, calendar;
	
		calendar = new YAHOO.widget.Calendar(obj, {
			iframe:false,          
			hide_blank_weeks:true,
			START_WEEKDAY:start
		});
		if (lang == "de_DE") {
			calendar.cfg.setProperty("MONTHS_LONG",    ["Januar", "Februar", "M\u00E4rz", "April", "Mai", "Juni", "Juli", "August", "September", "Oktober", "November", "Dezember"]);
			calendar.cfg.setProperty("WEEKDAYS_SHORT", ["So", "Mo", "Di", "Mi", "Do", "Fr", "Sa"]);
		}
			
		function cancelHandler() {
			this.hide();
		}
		
		function handleSelect(type,args,obj) {
			var dates = args[0]; 
			var date = dates[0];
			var year = date[0], month = date[1], day = date[2];
			
			var txtDate1 = document.getElementById(field);
			if (month < 10) { month = "0" + month;}
			if (day < 10)   { day = "0" + day;}
			txtDate1.value = year + "-" + month + "-" + day;
			dialog.hide();
		}		
			
		dialog = new YAHOO.widget.Dialog(cont, {
			context:[field, "tl", "bl"],
			width:"16em",
			draggable:true,
			close:true
		});
		calendar.render();
		dialog.render();
		dialog.hide();
	
		calendar.renderEvent.subscribe(function() {
			dialog.fireEvent("changeContent");
		});
		calendar.selectEvent.subscribe(handleSelect, calendar.cal1, true);
	
		YAHOO.util.Event.on(key, "click", dialog.show, dialog, true);
	});	
}
	
// Mutationsdialog oeffnen
function openMutDlgInit(field,divbox,header,key,langkey1,langkey2) {
	
	YAHOO.util.Event.onDOMReady(function(){
			
		var mutdialog;	
		
		var handleSuccess = function(o){ 
			if(o.responseText !== undefined){
				document.getElementById(divbox+'content').innerHTML = o.responseText;
			}
		}
		var handleFailure = function(o){ 
			if(o.responseText !== undefined){ 
				document.getElementById(divbox+'content').innerHTML = "No information found";
			}
		}  
		var callback = 	{ 
			success:handleSuccess, 
			failure: handleFailure 
		}; 		
		sUrl = "mutdialog.php?object=" + field;
		var request = YAHOO.util.Connect.asyncRequest('GET', sUrl, callback);
		
		var handleSave = function() {
			var source 			= document.getElementById(field);
			var targetSelect 	= document.getElementById(field+'Selected');
			var targetAvail 	= document.getElementById(field+'Avail');
      		for (i = 0; i < targetSelect.length; ++i) {
        		targetSelect.options[i].selected = true;
      		}	
      		for (i = 0; i < source.length; ++i) {
        		source.options[i].selected = false;
      		}
      		for (i = 0; i < targetSelect.length; ++i) {
        		for (y = 0; y < source.length; ++y) {
          			if (source.options[y].value == 
            			targetSelect.options[i].value) {
            			source.options[y].selected = true;
          			}
        		}
      		}
			this.cancel();
			if ((update) && (update == 1)) {
				updateForm(field);
			}
		};
		var handleCancel = function() {
			this.cancel();
		};
		mutdialog = new YAHOO.widget.Dialog(divbox, 
					{ 
					  fixedcenter : true,
					  visible : false, 
					  draggable: true,
					  modal: true,
					  constraintoviewport : true,
					  buttons : [ { text:langkey1, handler:handleSave, isDefault:true },
								  { text:langkey2, handler:handleCancel } ]
					});
		
		mutdialog.setHeader(header);
		mutdialog.render();
		mutdialog.hide();
		mutdialog.beforeShowEvent.subscribe(function() {
            getData(field);
		});

		YAHOO.util.Event.on(key, "click", mutdialog.show, mutdialog, true);
	});
}

// Mutieren Dialog Zusatzfunktionen
function getData(field) {
  var source 		= document.getElementById(field);
  var targetSelect 	= document.getElementById(field+'Selected');
  var targetAvail 	= document.getElementById(field+'Avail');
  for (i=0; i < targetSelect.length; i++) {
	targetSelect.options[i] = null;
  }
  targetSelect.length = 0;
  for (i=0; i < targetAvail.length; i++) {
	targetAvail.options[i] = null;
  }
  targetAvail.length = 0;
  for (i = 0; i < source.length; ++i) {
	if (source.options[i].selected == true) {
		NeuerEintrag1 = new Option(source.options[i].text, source.options[i].value, false, false);
		targetSelect.options[targetSelect.length] = NeuerEintrag1;
	} 
	if (source.options[i].selected == false) {
		if (source.options[i].text != "") {
		  NeuerEintrag2 = new Option(source.options[i].text, source.options[i].value, false, false);
		  targetAvail.options[targetAvail.length] = NeuerEintrag2;
		}
	} 	
  }
}
// Auswahl eintragen
function selValue(field) {
  var targetSelect 	= document.getElementById(field+'Selected');
  var targetAvail 	= document.getElementById(field+'Avail');
  if (targetAvail.selectedIndex != -1) {
	var DelOptions = new Array();
	for (i = 0; i < targetAvail.length; ++i) {
	  if (targetAvail.options[i].selected == true) {
		NeuerEintrag = new Option(targetAvail.options[i].text, targetAvail.options[i].value, false, false);
		targetSelect.options[targetSelect.length] = NeuerEintrag;
		DelOptions.push(i);
	  }
	}
	sort(targetSelect);
	DelOptions.reverse();
	for (var i = 0; i < DelOptions.length; ++i) {
	  targetAvail.options[DelOptions[i]] = null;
	}
  }
}
// Auswahl austragen
function desValue(field) {
  var targetSelect 	= document.getElementById(field+'Selected');
  var targetAvail 	= document.getElementById(field+'Avail');
  if (targetSelect.selectedIndex != -1) {
	var DelOptions = new Array();
	for (i = 0; i < targetSelect.length; ++i) {
	  if (targetSelect.options[i].selected == true) {
		NeuerEintrag = new Option(targetSelect.options[i].text, targetSelect.options[i].value, false, false);
		targetAvail.options[targetAvail.length] = NeuerEintrag;
		DelOptions.push(i);
	  }
	}
	sort(targetAvail);
	DelOptions.reverse();
	for (var i = 0; i < DelOptions.length; ++i) {
	  targetSelect.options[DelOptions[i]] = null;
	}
  }
}
// Sortieren
function sort(obj){
  var sortieren = new Array();
  var list = new Array();
  var i;

  // Liste in ein Array lesen
  for (i=0; i < obj.options.length; i++) {
	list[i] = new Array();
	list[i]["text"] = obj.options[i].text;
	list[i]["value"] = obj.options[i].value;
  }

  // umsortieren in ein eindimensionales Array
  for (i=0; i < obj.length; i++){
	sortieren[i]=list[i]["text"]+";"+list[i]["value"];
  }

  // eigentliches sortieren
  sortieren.sort();

  // sortiertes Array wieder zurück in das Listenarray zurücklesen
  for (i=0; i < sortieren.length; i++) {
	var felder = sortieren[i].split(";");
	list[i]["text"] = felder[0];
	list[i]["value"] = felder[1];
  }

  // Listenfeld löschen
  for (i=0; i < obj.options.length; i++) {
	obj.options[i] = null;
  }

  // sortiertes Listenarray in das Listenobjekt einfügen
  for (i=0; i < list.length; i++){
	NeuerEintrag = new Option(list[i]["text"], list[i]["value"], false, false);
	obj.options[i] = NeuerEintrag;
  }
}