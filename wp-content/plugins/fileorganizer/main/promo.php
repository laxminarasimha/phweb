<?php

if(!defined('ABSPATH')){
	die();
}

echo '
<style>


.wp-core-ui .fileorganizer_button1 {
background-color: #4CAF50;
border-color: transparent;
border-radius: 2px;
color: #fff;
}

.wp-core-ui .fileorganizer_button1:hover {
background-color:#3b963f;
border-color: transparent;
border-radius: 2px;
color: #fff;
}

.wp-core-ui .fileorganizer_button2 {
background-color: #0085ba;
border-color: transparent;
border-radius: 2px;
color: #fff;
}

.wp-core-ui .fileorganizer_button2:hover {
background-color: #0175a3;
border-color: transparent;
border-radius: 2px;
color: #fff;
}

.wp-core-ui .fileorganizer_button3 {
background-color: #365899;
border-color: transparent;
border-radius: 2px;
color: #fff;
}

.wp-core-ui .fileorganizer_button3:hover {
border-color: transparent;
background-color: #274785;
color:#fff;
}

.wp-core-ui .fileorganizer_button4 {
background-color: #14171A;
border-color: transparent;
border-radius: 2px;
color: #fff;
}

.wp-core-ui .fileorganizer_button4:hover {
background-color: #24292E;
color: #fff;
border-color: transparent;
}

.fileorganizer_promo-close{
float:right;
text-decoration:none;
margin: 5px 10px 0px 0px;
}

.fileorganizer_promo-close:hover{
color: red;
}

#fileorganizer_promo li {
list-style-position: inside;
list-style-type: circle;
}

.fileorganizer-loc-types {
display:flex;
flex-direction: row;
align-items:center;
flex-wrap: wrap;
}

.fileorganizer-loc-types li{
list-style-type:none !important;
margin-right: 10px;
}

</style>

<script>
jQuery(document).ready( function() {
	(function($) {
		$("#fileorganizer_promo .fileorganizer_promo-close").click(function(){
			var data;
			
			// Hide it
			$("#fileorganizer_promo").hide();
			
			// Save this preference
			$.get("'.esc_url(admin_url('admin-ajax.php?action=fileorganizer_hide_promo')).'&security='.esc_html(wp_create_nonce('fileorganizer_promo_nonce')).'", data, function(response) {
				//alert(response);
			});
		});
	})(jQuery);
});
</script>';

function fileorganizer_base_promo(){
	echo '<div class="notice notice-success" id="fileorganizer_promo" style="min-height:120px; background-color:#FFF; padding: 10px;">
	<a class="fileorganizer_promo-close" href="javascript:" aria-label="Dismiss this Notice">
		<span class="dashicons dashicons-dismiss"></span> Dismiss
	</a>
	<table>
	<tr>
		<th>
			<img src="'.esc_url(FILEORGANIZER_URL).'/images/logo.png" style="float:left; margin:10px 20px 10px 10px" width="100" />
		</th>
		<td>
			<p style="font-size:16px;">You have been using FileOrganizer for few days and we hope FileOrganizer is able to help you to manage files from your Website.<br/>
			If you like our plugin would you please show some love by doing actions like :
			</p>
			<p>
				<a class="button fileorganizer_button1" target="_blank" href="https://fileorganizer.net/pricing">Upgrade to Pro</a>
				<a class="button fileorganizer_button2" target="_blank" href="https://wordpress.org/support/view/plugin-reviews/fileorganizer">Rate it 5★\'s</a>
				<a class="button fileorganizer_button4" target="_blank" href="https://twitter.com/intent/tweet?text='.rawurlencode('I easily manage my #WordPress #files using - https://fileorganizer.net').'">Post on X about FileOrganizer</a>
			</p>
			<p style="font-size:16px">FileOrganizer Pro comes with features like <b>Allow User Roles, Change Upload Size, User Restrictions, User Role Restrictions, Email Alert etc.</b> that helps you to manage files more securely at multiple user level.</p>
	</td>
	</tr>
	</table>
</div>';
}

function fileorganizer_plugin_update_notice(){
	if(defined('SOFTACULOUS_PLUGIN_UPDATE_NOTICE')){
		return;
	}

	$to_update_plugins = apply_filters('softaculous_plugin_update_notice', []);

	if(empty($to_update_plugins)){
		return;
	}

	/* translators: %1$s is replaced with a "string" of name of plugins, and %2$s is replaced with "string" which can be "is" or "are" based on the count of the plugin */
	$msg = sprintf(__('New versions of %1$s %2$s available. Updating ensures better performance, security, and access to the latest features.', 'fileorganizer'), '<b>'.esc_html(implode(', ', $to_update_plugins)).'</b>', (count($to_update_plugins) > 1 ? 'are' : 'is')) . ' <a class="button button-primary" href='.esc_url(admin_url('plugins.php?plugin_status=upgrade')).'>Update Now</a>';

	define('SOFTACULOUS_PLUGIN_UPDATE_NOTICE', true); // To make sure other plugins don't return a Notice
	echo '<div class="notice notice-info is-dismissible" id="fileorganizer-plugin-update-notice">
		<p>'.$msg. '</p>
	</div>';

	wp_register_script('fileorganizer-update-notice', '', ['jquery'], '', true);
	wp_enqueue_script('fileorganizer-update-notice');
	wp_add_inline_script('fileorganizer-update-notice', 'jQuery("#fileorganizer-plugin-update-notice").on("click", function(e){
		let target = jQuery(e.target);

		if(!target.hasClass("notice-dismiss")){
			return;
		}

		var data;
		
		// Hide it
		jQuery("#fileorganizer-plugin-update-notice").hide();
		
		// Save this preference
		jQuery.post("'.admin_url('admin-ajax.php?action=fileorganizer_close_update_notice').'&security='.wp_create_nonce('fileorganizer_promo_nonce').'", data, function(response) {
			//alert(response);
		});
	});');
}

