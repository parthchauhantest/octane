<!-- FOOTER START -->
<div id="footer"> 

<div id="footermenucontainer">
	<div id="footernotice"><?php echo get_product_name();?> <?php echo get_product_version();?>  Copyright &copy; 2008-<?php echo date('Y'); ?> <a href="http://www.nagios.com/" target="_blank">Nagios Enterprises, LLC</a>.</div>
	
		<div id="tray_alerter"><img id="tray_alerter_image" src="<?php echo theme_image("throbber.gif");?>"></div>
		<div id="tray_alerter_popup">
		<strong>Information and Alerts:</strong>
		<div id="tray_alerter_popup_content" style="overflow: auto; border: 1px solid white; margin: 0pt 0pt 0pt 0px; padding: 5px; height: 100px;">
		<img src="<?php echo theme_image("throbber.gif");?>"> Loading data...
		</div>
		</div>
	
	<ul class="footermenu">

		<li><a href="<?php echo get_base_url();?>about/">About</a></li>
		<li><a href="<?php echo get_base_url();?>about/?legal">Legal</a></li>
	</ul>
</div>
<div id="checkforupdates">
<a href="<?php echo get_update_check_url();?>" target="_blank"><img src="<?php echo get_base_url();?>images/checkforupdates.png" alt="Check for updates" title="Check for updates" border="0"></a>
</div>
<div id="keepalive"></div>




	<script type="text/javascript">
	
	function get_tray_alert_content(){		
		var optsarr = {
			"func": "get_tray_alert_html",
			"args": ""
			}
		var opts=array2json(optsarr);
		get_ajax_data_with_callback("getxicoreajax",opts,"process_tray_alert_content");
		}
		
	function process_tray_alert_content(edata){
	
		data=unescape(edata);
		
		$("#tray_alerter_popup_content").html(data);
		
		var status=$("#tray_alerter_status").html();
		//alert(status);
		$("#tray_alerter").html(status);
		}

		
	$(document).ready(function(){
	
		get_tray_alert_content();
		
		$("#tray_alerter").everyTime(<?php echo get_dashlet_refresh_rate(30,"tray_alert");?>,"timer-tray_alerter", function(i) {
				get_tray_alert_content();
				});

		$("#tray_alerter").click(function(){
			var vis=$("#tray_alerter_popup").css("visibility");
			if(vis=="hidden")
				$("#tray_alerter_popup").css("visibility","visible");
			else
				$("#tray_alerter_popup").css("visibility","hidden");
			});
			
			

		});
	</script>	
</div> <!-- end footer div -->

<!-- FOOTER END -->
