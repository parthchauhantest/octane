
$(document).ready(function(){init_sorted_tables();});function init_sorted_tables(){$('table.hovercells').each(function(){var $table=$(this);$('th',$table).each(function(column){if($(this).is('.sort-header')){$(this).hover(function(){$(this).addClass('hover');},function(){$(this).removeClass('hover');}).click(function(){var ahref=$(this).children('a').get(0);var uri=ahref.href;document.location=uri;})}});$('tbody td.clickable',$table).each(function(){$(this).click(function(){var r=$(this).parent();var c=$(r).children().get(0);var cb=$(c).children(":checkbox");var cb1=$(cb).get(0);if(cb1.checked==true){cb1.checked=false;$(r).removeClass('selected');}
else{cb1.checked=true;$(r).addClass('selected');}})});$('tbody td',$table).each(function(){$(this).hover(function(){var r=$(this).parent();$(r).children().each(function(){$(this).addClass('hover');})},function(){var r=$(this).parent();$(r).children().each(function(){$(this).removeClass('hover');})})});$(":checkbox:not(#checkall)",$table).each(function(){$(this).click(function(){var r=$(this).parent().parent();if($(this).is(':checked'))
$(r).addClass('selected');else
$(r).removeClass('selected');});});$("#checkall",$table).each(function(){$(this).click(function(){if($(this).is(':checked')){$(":checkbox:not(#checkall)",$table).each(function(){var r=$(this).parents('tr').get(0);$(r).removeClass('selected');$(r).addClass('selected');this.checked=true;})}
else{$(":checkbox:not(#checkall)",$table).each(function(){var r=$(this).parents('tr').get(0);$(r).removeClass('selected');this.checked=false;})}});});});}