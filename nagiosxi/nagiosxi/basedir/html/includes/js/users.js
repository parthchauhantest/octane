
$(document).ready(function(){$("a.masquerade_link").click(function(){show_child_content_throbber();var theurl=this.href;var txtHeader=get_language_string("MasqueradeAlertHeader");var txtMessage=get_language_string("MasqueradeMessageText");var txtContinue=get_language_string("ContinueText");var txtCancel=get_language_string("CancelText");var content="<div id='popup_header'><b>"+txtHeader+"</b></div><div id='popup_data'><p>"+txtMessage+"</p><a href='"+theurl+"' class='continue'>"+txtContinue+"</a></div></div>";hide_child_content_throbber();set_child_popup_content(content);display_child_popup("250px");$("a.continue").click(function(){get_ajax_data("masquerade",theurl);top.location=base_url;return false;});return false;});});