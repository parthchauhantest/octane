
var isfullscreen=false;var embedded_mcfh=0;var embedded_mch=0;var embedded_mcw=0;var feedbackcentered=false;var popupcentered=false;var childpopupcentered=false;$(document).ready(function(){if(navigator.userAgent.match(/iPad|iPhone/i)){$('#maincontent').css('overflow','auto');$('#maincontentframe').prop('scrollable','no');$('#viewtools').hide();}
resize_content();$("#closetrialnotice").click(function(){var p=this.parentNode;var gp=p.parentNode;$(gp).remove();var optsarr={"ignore_trial_notice":1};var opts=array2json(optsarr);get_ajax_data("setsessionvars",opts);});$("#closefreenotice").click(function(){var p=this.parentNode;var gp=p.parentNode;$(gp).remove();var optsarr={"ignore_free_notice":1};var opts=array2json(optsarr);get_ajax_data("setsessionvars",opts);});$("#topmenucontainer ul.menu li a").click(function(){show_parent_content_throbber();});$("#fullscreen").click(function(){var x=1;if(isfullscreen==false){embedded_mcfh=$("#maincontentframe").css('height');embedded_mcw=$("#maincontent").css('width');embedded_mch=$("#maincontent").css('height');$("body").css("margin","0px");$("#leftnav").slideUp("fast");$("#header").slideUp("fast");$("#footer").slideUp("fast");$("#viewtools").slideUp("fast");isfullscreen=true;$("#fullscreen").css("left","0px");do_fullscreen();}
else{$("body").css("margin","5px");$("#leftnav").slideDown("fast");$("#header").slideDown("fast");$("#footer").slideDown("fast");$("#viewtools").slideDown("fast");isfullscreen=false;$("#fullscreen").css("left","200px");$("#maincontentframe").css('height',embedded_mcfh);$("#maincontent").css('height',embedded_mch);$("#maincontent").css('width',embedded_mcw);$("#maincontent").css('top','98px');$("#maincontent").css('left','200px');}});$("#hostsearchBox").each(function(){$(this).autocomplete({source:suggest_url+'?type=host',minLength:1});});$("#userList #searchBox").each(function(){$(this).autocomplete({source:suggest_url+'?type=users',minLength:1});});$("#perfgraphspage #searchBox").each(function(){$(this).autocomplete({source:suggest_url+'?type=host',minLength:1});});$("#navbarSearchBox").each(function(){$(this).autocomplete({source:suggest_url+'?type=host',minLength:1});});$("#searchBox").focus(function(){if($(this).is(".newdata")){}
else{this.value="";$(this).addClass("newdata");}});$("#hostsearchBox").focus(function(){if($(this).is(".newdata")){}
else{this.value="";$(this).addClass("newdata");}});$("#navbarSearchBox").focus(function(){if($(this).is(".newdata")){}
else{this.value="";$(this).addClass("newdata");}});$.datepicker.setDefaults({closeText:'X',altFormat:'yy-mm-dd',dateFormat:'yy-mm-dd'});var datepickerup=false;$('.reportstartdatepicker').click(function(){if(datepickerup==true){$('#startdatepickercontainer').datepicker('destroy');datepickerup=false;}
else{datepickerup=true;$('#startdatepickercontainer').datepicker({closeText:'X',onSelect:function(dateText,inst){$('#startdateBox').val(dateText);$('#startdatepickercontainer').datepicker('destroy');$('#reportperiodDropdown').val('custom');}});}});$('.reportenddatepicker').click(function(){if(datepickerup==true){$('#enddatepickercontainer').datepicker('destroy');datepickerup=false;}
else{datepickerup=true;$('#enddatepickercontainer').datepicker({closeText:'X',onSelect:function(dateText,inst){$('#enddateBox').val(dateText.concat(' 23:59:59'));$('#enddatepickercontainer').datepicker('destroy');$('#reportperiodDropdown').val('custom');}});}});$("#schedulepagereport a").click(function(){regexpNagiosChild='/(http[s]?:\/\/)(.*nagiosxi\/)(.*)/';var success=true;var baseurl=base_url+"/includes/components/scheduledreporting/schedulereport.php?type=page";try{var windowurl=$('#maincontentframe').contents()[0].URL;}
catch(err){success=false;}
if(success){windowurl.match(regexpNagiosChild);var the_rest=RegExp.$3;var newwindowurl=the_rest;var theurl=baseurl+"&url="+encodeURIComponent(newwindowurl)+"&wurl="+encodeURIComponent(windowurl);$("#maincontentframe").attr({src:theurl});}
else{alert("Cannot schedule outside pages.\n\nAny page not under the nagiosxi/ folder cannot be scheduled.");}});$("#permalink a").click(function(){show_parent_content_throbber();var baseurl=permalink_base;var windowurl=$('#maincontentframe').contents()[0].URL;var theurl=permalink_base+"&xiwindow="+encodeURIComponent(windowurl);var txtHeader=get_language_string("PermalinkHeader");var txtMessage=get_language_string("PermalinkMessage");var t1=get_language_string("PermalinkURLBoxTitle");var jsurl=base_url+"/includes/js/jquery/"
content="<div id='popup_header'><b>"+txtHeader+"</b></div><div id='popup_data'><p>"+txtMessage+"</p></div><label for='permalinkURLBox'>"+t1+"</label><br class='nobr' /><input type='text' size='50' name='url' id='permalinkURLBox' value='"+theurl+"'>  <a id='permalink-copy' href='#'>Copy To Clipboard</a><br class='nobr' /> <script type='text/javascript'> $('#popup_layer').on('mouseover', function(){ $(this).off('mouseover'); $('a#permalink-copy').zclip({ path:'"+jsurl+"ZeroClipboard.swf',  copy:function(){return $('input#permalinkURLBox').val();},afterCopy:function(){ $('#popup_layer').draggable('enable'); } }); }); </script>";$("#popup_layer").draggable({disabled:true});hide_parent_content_throbber();set_popup_content(content);display_popup(450);});$("#keepalive").each(function(){$(this).everyTime(5*60*1000,"keepalivetimer",function(i){get_ajax_data("keepalive",i);});});$("div.dashifybutton").hover(function(){var p=$(this).parent();$(p).addClass("dashlettablehover");$(p).fadeTo("slow",0.33);},function(){var p=$(this).parent();$(p).removeClass("dashlettablehover");$(p).fadeTo("slow",1.0);});$(".dashifybutton").click(function(){var theparent=this.parentNode;var x=1;});$(".menusectionbutton").click(function(){var menusection=this.parentNode;var lastclass=$(menusection).attr('class').split(' ').slice(-1);if(lastclass=="menusection-collapsed"){$(menusection).removeClass("menusection-collapsed");var optsarr={"keyname":"menu_collapse_options","menuid":this.id,"keyvalue":1,"autoload":false};var opts=array2json(optsarr);get_ajax_data('setusermeta',opts);}
else{$(menusection).addClass("menusection-collapsed");var optsarr={"keyname":"menu_collapse_options","menuid":this.id,"keyvalue":0,"autoload":false};var opts=array2json(optsarr);get_ajax_data('setusermeta',opts);}});$(".menusectiontitle").click(function(){var menusection=this.parentNode;var lastclass=$(menusection).attr('class').split(' ').slice(-1);if(lastclass=="menusection-collapsed")
$(menusection).removeClass("menusection-collapsed");});$("#popout a").click(function(){var theurl=$('#maincontentframe').contents()[0].URL;window.open(theurl);});$("#popup_layer").each(function(){$(this).draggable();});$("#close_popup_link").click(function(){close_popup();});$("#child_popup_layer").each(function(){$(this).draggable();});$("#close_child_popup_link").click(function(){close_child_popup();});$(":submit").click(function(){hide_message();});$("#get_online_help_link").click(function(){hide_throbber();});$(".tablesorter tbody tr td a").click(function(){hide_throbber();});$("#feedbacklayer a").click(function(){hide_throbber();});$("#notices a").click(function(){hide_throbber();});$("span").each(function(){});$("#notices").draggable();$("#close_notices_link").click(function(){hide_throbber();$.ajax({type:"POST",url:this.href,nsp:nsp_str});$("#notices").remove();return false;});$("#feedback_layer").each(function(){$(this).draggable();});$("#close_feedback_link").click(function(){hide_throbber();if($.browser.msie){$("form select").css("visibility","visible");}
$("#feedback_layer").css("visibility","hidden");});$("#feedback a").click(function(){show_parent_content_throbber();if(feedbackcentered==false)
center_feedback();hide_parent_content_throbber();if($.browser.msie){$("form select").css("visibility","hidden");}
$("#feedback_layer").css("visibility","visible");$("#feedback_container").each(function(){if(this.origHTML)
this.innerHTML=this.origHTML;});$("#feedback_form textarea").each(function(){this.value='';});init_feedback_submit();});function init_feedback_submit(){$("#feedback_form").submit(function(){hide_throbber();var params={};$(this).find(":input, :password, :checkbox, :radio, :submit, :reset").each(function(){params[this.name||this.id||this.parentNode.name||this.parentNode.id]=this.value;});$.ajax({type:"POST",url:this.getAttribute("action"),data:params,beforeSend:function(XMLHttpRequest){$("#feedback_container").each(function(){this.origHTML=this.innerHTML;txtHeader=get_language_string("FeedbackSendingHeader");txtMessage=get_language_string("FeedbackSendingMessage");this.innerHTML="<div id='feedback_header'><b>"+txtHeader+"</b></div><div id='feedback_data'><p>"+txtMessage+"</p><div id='feedback_throbber'></div></div>";});},success:function(msg){$("#feedback_container").each(function(){txtHeader=get_language_string("FeedbackSuccessHeader");txtMessage=get_language_string("FeedbackSuccessMessage");this.innerHTML="<div id='feedback_header'><b>"+txtHeader+"</b></div><div id='feedback_data'><p>"+txtMessage+"</p></div>";});},error:function(msg){$("#feedback_container").each(function(){txtHeader=get_language_string("FeedbackErrorHeader");txtMessage=get_language_string("FeedbackErrorMessage");this.innerHTML="<div id='feedback_header'><b>"+txtHeader+"</b></div><div id='feedback_data'><p>"+txtMessage+"</p></div>";});}});return false;});}});function center_feedback(){var l=$("#feedback_layer");var wh=$(window).height();var ww=$(window).width();var lh=$(l).height();var lw=$(l).width();var newtop=(wh-lh)/2;var newleft=(ww-lw)/2;$("#feedback_layer").css("top",newtop);$("#feedback_layer").css("left",newleft);feedbackcentered=true;}
function stripe_table(table){$("tr:odd",table).addClass("odd");$("tr:even",table).removeClass("odd");}
function get_language_string(s){var r="??";$.ajax({type:"GET",async:false,url:ajax_helper_url,data:{cmd:"getlangstring",str:s,nsp:nsp_str},success:function(data){r=data;}});return r;}
function get_datetime_string(s,z){var r="??";$.ajax({type:"GET",async:false,url:ajax_helper_url,data:{cmd:"getdatetimestring",t:s,zs:z,nsp:nsp_str},success:function(data){r=data;}});return r;}
function get_ajax_data(c,o){var r="??";$.ajax({type:"GET",async:false,url:ajax_helper_url,data:{cmd:c,opts:o,nsp:nsp_str},success:function(data){r=data;}});return r;}
function get_ajax_data_with_callback(c,o,pfc){var r="??";$.ajax({type:"GET",async:true,url:ajax_helper_url,data:{cmd:c,opts:o,nsp:nsp_str},success:function(data){eval(pfc+'("'+escape(data)+'")');}});}
function get_ajax_data_innerHTML(c,o,doasync,obj){$.ajax({type:"GET",async:doasync,url:ajax_helper_url,data:{cmd:c,opts:o,nsp:nsp_str},success:function(data){var theobj=obj;$(obj).html(data);}});return true;}
function get_ajax_data_innerHTML_with_callback(c,o,doasync,obj,pfc){$.ajax({type:"GET",async:doasync,url:ajax_helper_url,data:{cmd:c,opts:o,nsp:nsp_str},success:function(data){var theobj=obj;obj.innerHTML=data;var funcname=pfc+'()';var funcname=pfc+'("'+escape(data)+'")';var x=1;eval(pfc+'("'+escape(data)+'")');}});return true;}
function get_ajax_data_imagesrc(c,o,doasync,obj){var r="??";$.ajax({type:"GET",async:doasync,url:ajax_helper_url,data:{cmd:c,opts:o,nsp:nsp_str},success:function(data){var theobj=obj;obj.src=data;}});return true;}
function get_ajax_data_imagesrc_with_callback(c,o,doasync,obj,pfc){var r="??";$.ajax({type:"GET",async:doasync,url:ajax_helper_url,data:{cmd:c,opts:o,nsp:nsp_str},success:function(data){var theobj=obj;obj.src=data;var funcname=pfc+'()';eval(pfc+'()');}});return true;}
function show_throbber(){$("#throbber").css("visibility","visible");}
function hide_throbber(){$("#throbber").css("visibility","hidden");}
function hide_message(){$("#message").css("visibility","hidden");}
function remove_message(){$("#message").remove();}
jQuery.event.add(window,"resize",resize_content);jQuery.event.add(window,"load",center_content_throbbers);jQuery.event.add(window,"resize",center_content_throbbers);function resize_content(){if(isfullscreen==true){do_fullscreen();return;}
var h=$(window).height();var w=$(window).width();var mf=$("#mainframe");if(!mf)
return;var p=$("#mainframe").position();if(!p)
return;var t=p.top;var l=p.left;var newh=h-t-66;$("#mainframe").css('height',newh+"px");var mfh=$("#mainframe").height();var mfhln=mfh-12;$("#leftnav").css('height',mfhln+"px");$("#maincontent").css('height',mfh+"px");var p=$("#maincontent").position();if(!p)
return;var t=p.top;var l=p.left;var lm=parseInt($("#maincontent").css('margin-left'));var rm=parseInt($("#maincontent").css('margin-right'));var lp=parseInt($("#maincontent").css('padding-left'));var rp=parseInt($("#maincontent").css('padding-right'));var lnmr=parseInt($("#leftnav").css("margin-right"));var lnml=parseInt($("#leftnav").css("margin-left"));var lnpr=parseInt($("#leftnav").css("padding-right"));var lnpl=parseInt($("#leftnav").css("padding-left"));var lnw=parseInt($("#leftnav").width());var neww=w-lm-rm-lp-rp-lnw-lnmr-lnml-lnpr-lnpl-40;$("#maincontent").css('width',neww+"px");var mcfh=mfh;$("#maincontentframe").css('height',mcfh+"px");$("#myviewoverlay").css('height',mcfh-20);$("#myviewoverlay").css('width',neww-20);$("#myviewoverlay").css('opacity',0.1);}
function do_fullscreen(){var d=document;if(!d)
return;var db=document.body;if(!db)
return;var sw=parseInt(document.body.scrollWidth);var sh=parseInt(document.body.scrollHeight);var wh=$(window).height();var ww=$(window).width();var h=wh-3;var w=ww-2;$("#maincontentframe").css('height',h);$("#maincontent").css('height',h);$("#maincontent").css('width',w);$("#maincontent").css('top','0');$("#maincontent").css('left','0');$("#myviewoverlay").css('height',h-20);$("#myviewoverlay").css('width',w-20);}
function display_popup(cwidth,cheight){$("#popup_close").each(function(){$(this).css("visibility","visible");});if(cwidth)
$("#popup_content").css("width",cwidth);if(cheight)
$("#popup_content").css("height",cwidth);$("#popup_layer").css("background-color","#F1F1F1");$("#popup_layer").css("opacity","1.0");$("#popup_layer").css("display","block");var lh=$("#popup_layer").height();var ch=$("#popup_content").height();var chmt=parseInt($("#popup_content").css("margin-top"));var chmb=parseInt($("#popup_content").css("margin-bottom"));height=ch+chmt+chmb;$("#popup_layer").css("height",height);var lw=$("#popup_layer").width();var cw=$("#popup_content").width();var cwmr=parseInt($("#popup_content").css("margin-right"));var cwml=parseInt($("#popup_content").css("margin-left"));width=cw+cwmr+cwml;$("#popup_layer").css("width",width);if(popupcentered==false)
center_popup();$("#popup_layer").css("visibility","visible");$("#popup_layer").each(function(){$(this).fadeIn("fast");});}
function set_popup_content(content){$("#popup_container").each(function(){$(this).html(content);});var c=$("#popup_container");}
function fade_popup(color,time){time=typeof(time)!='undefined'?time:1000;$("#popup_close").each(function(){$(this).css("visibility","hidden");});$("#popup_layer").each(function(){var c="#D0FF76";if(color=="red")
c="#FF9999";var myColors=[{param:'background-color',cycles:"1",isFade:false,colorList:["#F1F1F1",c]}];$(this).colorBlend(myColors);$(this).oneTime(time,"popuptimer",function(i){$(this).fadeOut();$(this).oneTime(500,"popuptimer2",function(i){close_popup();});});});}
function close_popup(){hide_throbber();$("#popup_close").each(function(){$(this).css("visibility","hidden");});if($.browser.msie){}
$("#popup_layer").css("visibility","hidden");}
function center_popup(){var l=$("#popup_layer");var wh=$(window).height();var ww=$(window).width();var lh=$(l).height();var lw=$(l).width();var newtop=(wh-lh)/2;var newleft=(ww-lw)/2;$("#popup_layer").css("top",newtop);$("#popup_layer").css("left",newleft);popupcentered=true;}
function resize_child_popup(){var lh=$("#child_popup_layer").height();var ch=$("#child_popup_content").height();var chmt=parseInt($("#child_popup_content").css("margin-top"));var chmb=parseInt($("#child_popup_content").css("margin-bottom"));height=ch+chmt+chmb;$("#child_popup_layer").css("height",height);var lw=$("#child_popup_layer").width();var cw=$("#child_popup_content").width();var cwmr=parseInt($("#child_popup_content").css("margin-right"));var cwml=parseInt($("#child_popup_content").css("margin-left"));width=cw+cwmr+cwml;$("#child_popup_layer").css("width",width);}
function display_child_popup(h){height=h;$("#child_popup_layer").css("background-color","#F1F1F1");$("#child_popup_layer").css("opacity","1.0");$("#child_popup_layer").css("display","block");resize_child_popup();if(childpopupcentered==false)
center_child_popup();$("#child_popup_layer").css("visibility","visible");$("#child_popup_layer").each(function(){$(this).fadeIn("fast");});}
function set_child_popup_content(content){$("#child_popup_container").each(function(){this.innerHTML=content;});}
function fade_child_popup(color,time){time=typeof(time)!='undefined'?time:1000;$("#child_popup_layer").each(function(){var c="#D0FF76";if(color=="red")
c="#FF9999";var myColors=[{param:'background-color',cycles:"1",isFade:false,colorList:["#F1F1F1",c]}];$(this).colorBlend(myColors);$(this).oneTime(time,"child_popuptimer",function(i){$(this).fadeOut();$(this).oneTime(500,"child_popuptimer2",function(i){close_child_popup();});});});}
function close_child_popup(){hide_throbber();if($.browser.msie){$("form select").css("visibility","visible");}
$("#child_popup_layer").css("visibility","hidden");}
function center_child_popup(){var l=$("#child_popup_layer");var wh=$(window).height();var ww=$(window).width();var lh=$(l).height();var lw=$(l).width();var newtop=(wh-lh)/2;var newleft=(ww-lw)/2;$("#child_popup_layer").css("top",newtop);$("#child_popup_layer").css("left",newleft);childpopupcentered=true;}
function center_content_throbbers(){var l=$("#parentcontentthrobber");var wh=$(window).height();var ww=$(window).width();var lh=$(l).height();var lw=$(l).width();var newtop=(wh-lh)/2;var newleft=(ww-lw)/2;$("#parentcontentthrobber").css("top",newtop);$("#parentcontentthrobber").css("left",newleft);var l=$("#childcontentthrobber");var wh=$(window).height();var ww=$(window).width();var lh=$(l).height();var lw=$(l).width();var newtop=(wh-lh)/2;var newleft=(ww-lw)/2;$("#childcontentthrobber").css("top",newtop);$("#childcontentthrobber").css("left",newleft);}
function show_parent_content_throbber(){$("#parentcontentthrobber").css("visibility","visible");}
function hide_parent_content_throbber(){$("#parentcontentthrobber").css("visibility","hidden");}
function show_child_content_throbber(){$("#childcontentthrobber").css("visibility","visible");}
function hide_child_content_throbber(){$("#childcontentthrobber").css("visibility","hidden");}
function array2json(arr){var parts=[];var is_list=(Object.prototype.toString.apply(arr)==='[object Array]');for(var key in arr){var value=arr[key];if(typeof value=="object"){if(is_list)parts.push(array2json(value));else parts[key]=parts.push('"'+key+'":'+array2json(value));}else{var str="";if(!is_list)str='"'+key+'":';if(typeof value=="number")str+=value;else if(value===false)str+='false';else if(value===true)str+='true';else str+='"'+value+'"';parts.push(str);}}
var json=parts.join(",");if(is_list)return'['+json+']';return'{'+json+'}';}