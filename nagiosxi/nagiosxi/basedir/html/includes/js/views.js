
var viewrotationenabled=false;var currentviewnum=0;var currentviewid="";var viewrotationspeed=7;var currentviewtitle="";var currentviewurl="";$(document).ready(function(){$("#addtomyviews a").click(function(){show_parent_content_throbber();var theurl=$('#maincontentframe').contents()[0].URL;hide_throbber();if($.browser.msie){}
var txtHeader=get_language_string("AddToMyViewsHeader");var txtMessage=get_language_string("AddToMyViewsMessage");var t1=get_language_string("AddToMyViewsTitleBoxTitle");var txtSubmitButton=get_language_string("SubmitButton");var content="<div id='popup_header'><b>"+txtHeader+"</b></div><div id='popup_data'><p>"+txtMessage+"</p></div><form id='addview_form' method='get' action='"+ajax_helper_url+"'><input type='hidden' name='cmd' value='addview'><input type='hidden' name='url' value='"+theurl+"'><label for='addviewTitleBox'>"+t1+"</label><br class='nobr' /><input type='text' size='30' name='title' id='addviewTitleBox' value='' class='textfield' /><br class='nobr' /><div id='addviewFormButtons'><input type='submit' class='submitbutton' name='submitButton' value='"+txtSubmitButton+"' id='submitAddViewButton'></div></form>";hide_parent_content_throbber();set_popup_content(content);display_popup();$("#addview_form").submit(function(){hide_throbber();var params={};$(this).find(":input, :password, :checkbox, :radio, :submit, :reset").each(function(){params[this.name||this.id||this.parentNode.name||this.parentNode.id]=this.value;});params["nsp"]=nsp_str;$.ajax({type:"POST",url:this.getAttribute("action"),data:params,beforeSend:function(XMLHttpRequest){$("#popup_container").each(function(){this.origHTML=this.innerHTML;txtHeader=get_language_string("AjaxSendingHeader");txtMessage=get_language_string("AjaxSendingMessage");this.innerHTML="<div id='popup_header'><b>"+txtHeader+"</b></div><div id='popup_data'><p>"+txtMessage+"</p><div id='popup_throbber'></div></div>";});},success:function(msg){$("#popup_container").each(function(){txtHeader=get_language_string("AddViewSuccessHeader");txtMessage=get_language_string("AddViewSuccessMessage");this.innerHTML="<div id='popup_header'><b>"+txtHeader+"</b></div><div id='popup_data'><p>"+txtMessage+"</p></div>";fade_popup("green");});},error:function(msg){$("#popup_container").each(function(){txtHeader=get_language_string("AjaxErrorHeader");txtMessage=get_language_string("AjaxErrorMessage");this.innerHTML="<div id='popup_header'><b>"+txtHeader+"</b></div><div id='popup_data'><p>"+txtMessage+"</p></div>";});}});return false;});});$("#myviewsquickview").click(function(){start_view_rotation(0);return;});$("a.rotatemyviewslink").click(function(){if(viewrotationenabled==true){stop_view_rotation();}
else{var x=currentviewnum;var y=currentviewid;start_view_rotation(currentviewnum+1);}
return;});$("#myviewsinit").each(function(){var x=1;});$("#trashview").click(function(){show_parent_content_throbber();var x=currentviewnum;var y=currentviewid;if(y=="noviewid"){var txtHeader=get_language_string("NoViewsToDeleteHeader");var txtMessage=get_language_string("NoViewsToDeleteMessage");var content="<div id='popup_header'><b>"+txtHeader+"</b></div><div id='popup_data'><p>"+txtMessage+"</p></div>";hide_parent_content_throbber();set_popup_content(content);display_popup();fade_popup("red");return;}
var inrotation=false;if(viewrotationenabled==true)
inrotation=true;if(inrotation==true)
pause_view_rotation();get_ajax_data("deleteviewbyid",currentviewid);if(inrotation==true)
resume_view_rotation(currentviewnum);else{show_new_view(currentviewnum);}
var txtHeader=get_language_string("ViewDeletedHeader");var txtMessage=get_language_string("ViewDeletedMessage");var content="<div id='popup_header'><b>"+txtHeader+"</b></div><div id='popup_data'><p>"+txtMessage+"</p></div>";show_parent_content_throbber();set_popup_content(content);display_popup();fade_popup("red");});$("#myviewspeedslider").each(function(){var result=get_ajax_data("getusermeta","view_rotation_speed");if(result!="")
viewrotationspeed=parseInt(result);if(viewrotationspeed<10)
viewrotationspeed=10;start_view_rotation(0);$(this).slider({orientation:'vertical',value:viewrotationspeed,step:1,min:10,max:60,slide:function(event,ui){},change:function(event,ui){show_parent_content_throbber();viewrotationspeed=ui.value;pause_view_rotation();resume_view_rotation(currentviewnum+1);hide_parent_content_throbber();var optsarr={"keyname":"view_rotation_speed","keyvalue":viewrotationspeed,"autoload":false};var opts=array2json(optsarr);var result=get_ajax_data("setusermeta",opts);}});});$("#myviewsviewtitle").each(function(){});$("#pauseresumeview").click(function(){show_parent_content_throbber();pauseresume_view_rotation();hide_parent_content_throbber();});$("#myviewoverlay").click(function(){if(viewrotationenabled==true)
pause_view_rotation();});$("#editview").click(function(){show_parent_content_throbber();var inrotation=false;if(viewrotationenabled==true){inrotation=true;pause_view_rotation();}
var id=currentviewid;if(id=="noviewid"){var txtHeader=get_language_string("NoViewsToEditHeader");var txtMessage=get_language_string("NoViewsToEditMessage");var content="<div id='popup_header'><b>"+txtHeader+"</b></div><div id='popup_data'><p>"+txtMessage+"</p></div>";hide_parent_content_throbber();set_popup_content(content);display_popup();fade_popup("red");return;}
var theurl=currentviewurl;var thetitle=currentviewtitle;var txtHeader=get_language_string("EditViewHeader");var txtMessage=get_language_string("EditViewMessage");var t1=get_language_string("EditViewURLBoxTitle");var t2=get_language_string("EditViewTitleBoxTitle");var txtSubmitButton=get_language_string("SubmitButton");var content="<div id='popup_header'><b>"+txtHeader+"</b></div><div id='popup_data'><p>"+txtMessage+"</p></div><form id='editview_form' method='get' action='"+ajax_helper_url+"'><input type='hidden' name='cmd' value='updateview'><input type='hidden' name='id' value='"+id+"'><label for='addviewTitleBox'>"+t2+"</label><br class='nobr' /><input type='text' size='30' name='title' id='addviewTitleBox' value='"+thetitle+"' class='textfield' /><br class='nobr' /><label for='addviewURLBox'>"+t1+"</label><br class='nobr' /><input type='text' size='30' name='url' id='addviewURLBox' value='"+theurl+"'><br class='nobr' /><div id='addviewFormButtons'><input type='submit' class='submitbutton' name='submitButton' value='"+txtSubmitButton+"' id='submitAddViewButton'></div></form>";hide_parent_content_throbber();set_popup_content(content);display_popup();$("#editview_form").submit(function(){hide_throbber();var params={};$(this).find(":input, :password, :checkbox, :radio, :submit, :reset").each(function(){params[this.name||this.id||this.parentNode.name||this.parentNode.id]=this.value;});params["nsp"]=nsp_str;$.ajax({type:"POST",url:this.getAttribute("action"),data:params,beforeSend:function(XMLHttpRequest){$("#popup_container").each(function(){this.origHTML=this.innerHTML;txtHeader=get_language_string("AjaxSendingHeader");txtMessage=get_language_string("AjaxSendingMessage");this.innerHTML="<div id='popup_header'><b>"+txtHeader+"</b></div><div id='popup_data'><p>"+txtMessage+"</p><div id='popup_throbber'></div></div>";});},success:function(msg){$("#popup_container").each(function(){txtHeader=get_language_string("EditViewSuccessHeader");txtMessage=get_language_string("EditViewSuccessMessage");this.innerHTML="<div id='popup_header'><b>"+txtHeader+"</b></div><div id='popup_data'><p>"+txtMessage+"</p></div>";fade_popup("green");});},error:function(msg){$("#popup_container").each(function(){txtHeader=get_language_string("AjaxErrorHeader");txtMessage=get_language_string("AjaxErrorMessage");this.innerHTML="<div id='popup_header'><b>"+txtHeader+"</b></div><div id='popup_data'><p>"+txtMessage+"</p></div>";});}});if(inrotation==true)
resume_view_rotation(currentviewnum);else
show_new_view(currentviewnum);return false;});});$("a.addnewviewlink").click(function(){show_parent_content_throbber();var inrotation=false;if(viewrotationenabled==true){inrotation=true;}
var theurl="";var txtHeader=get_language_string("AddViewHeader");var txtMessage=get_language_string("AddViewMessage");var t1=get_language_string("AddViewURLBoxTitle");var t2=get_language_string("AddViewTitleBoxTitle");var txtSubmitButton=get_language_string("SubmitButton");var content="<div id='popup_header'><b>"+txtHeader+"</b></div><div id='popup_data'><p>"+txtMessage+"</p></div><form id='addview_form' method='get' action='"+ajax_helper_url+"'><input type='hidden' name='cmd' value='addview'><label for='addviewTitleBox'>"+t2+"</label><br class='nobr' /><input type='text' size='30' name='title' id='addviewTitleBox' value='' class='textfield' /><br class='nobr' /><label for='addviewURLBox'>"+t1+"</label><br class='nobr' /><input type='text' size='30' name='url' id='addviewURLBox' value='"+theurl+"'><br class='nobr' /><div id='addviewFormButtons'><input type='submit' class='submitbutton' name='submitButton' value='"+txtSubmitButton+"' id='submitAddViewButton'></div></form>";hide_parent_content_throbber();set_popup_content(content);display_popup();$("#addview_form").submit(function(){hide_throbber();var params={};$(this).find(":input, :password, :checkbox, :radio, :submit, :reset").each(function(){params[this.name||this.id||this.parentNode.name||this.parentNode.id]=this.value;});params["nsp"]=nsp_str;$.ajax({type:"POST",url:this.getAttribute("action"),data:params,beforeSend:function(XMLHttpRequest){$("#popup_container").each(function(){this.origHTML=this.innerHTML;txtHeader=get_language_string("AjaxSendingHeader");txtMessage=get_language_string("AjaxSendingMessage");this.innerHTML="<div id='popup_header'><b>"+txtHeader+"</b></div><div id='popup_data'><p>"+txtMessage+"</p><div id='popup_throbber'></div></div>";});},success:function(msg){$("#popup_container").each(function(){txtHeader=get_language_string("AddToMyViewsSuccessHeader");txtMessage=get_language_string("AddToMyViewsSuccessMessage");this.innerHTML="<div id='popup_header'><b>"+txtHeader+"</b></div><div id='popup_data'><p>"+txtMessage+"</p></div>";fade_popup("green");});},error:function(msg){$("#popup_container").each(function(){txtHeader=get_language_string("AjaxErrorHeader");txtMessage=get_language_string("AjaxErrorMessage");this.innerHTML="<div id='popup_header'><b>"+txtHeader+"</b></div><div id='popup_data'><p>"+txtMessage+"</p></div>";});}});if(inrotation==true){pause_view_rotation();resume_view_rotation(currentviewnum);}
else
show_new_view(currentviewnum);return false;});});});function reinit_views_menu(){$("a.myviewlink").click(function(){stop_view_rotation();var theparent=this.parentNode;var url=this.href;var title=this.innerHTML;var rawid=this.id;var id=rawid.substr(5);var num=parseInt(get_ajax_data("getviewnumfromid",id));currentviewnum=num;fill_view_screen(title,url,id);return;});}
function highlight_views_menu_item(id){$("#"+id).addClass("activemenulink");}
function update_views_menu(){var html=get_ajax_data("getviewsmenuhtml","");$("#myviewsmenu")[0].innerHTML=html;reinit_views_menu();}
function fill_view_screen(title,url,id){update_views_menu();highlight_views_menu_item("view-"+id);$("#trashview").css("visibility","visible");$("#editview").css("visibility","visible");var thetarget=$("#myviewsviewtitle");$("#myviewsviewtitle")[0].innerHTML=title;$("#maincontentframe").attr({src:url});currentviewid=id;currentviewurl=url;currentviewtitle=title;}
function show_new_view(num){var viewdata=get_ajax_data("getviewbynum",num);eval('var viewobj='+viewdata);fill_view_screen(viewobj.title,viewobj.url,viewobj.id);currentviewnum=num;}
function stop_view_rotation(){$(this).stopTime("viewrotationtimer");$("#pauseresumeview").css("visibility","hidden");$("#myviewspeedslider").css("visibility","hidden");viewrotationenabled=false;$("a.rotatemyviewslink")[0].innerHTML="Start Rotation";}
function start_view_rotation(num){show_new_view(num);$(this).stopTime("viewrotationtimer");$(this).everyTime(viewrotationspeed*1000,"viewrotationtimer",function(i){currentviewnum+=1;show_new_view(currentviewnum);});$("#pauseresumeview").css("visibility","visible");$("#myviewspeedslider").css("visibility","visible");viewrotationenabled=true;$("a.rotatemyviewslink")[0].innerHTML="Stop Rotation";}
function pause_view_rotation(){$(this).stopTime("viewrotationtimer");$("#pauseresumeview")[0].innerHTML="<a href='#'><img border='0' src='/nagiosxi/images/resume.png' alt='Resume' title='Resume'/></a>";viewrotationenabled=false;}
function resume_view_rotation(newviewnum){start_view_rotation(newviewnum);$("#pauseresumeview")[0].innerHTML="<a href='#'><img border='0' src='/nagiosxi/images/pause.png' alt='Pause' title='Pause'/></a>";viewrotationenabled=true;}
function pauseresume_view_rotation(){if(viewrotationenabled==false)
resume_view_rotation(currentviewnum+1);else
pause_view_rotation();}