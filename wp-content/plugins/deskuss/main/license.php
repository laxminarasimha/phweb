<?php

if(!defined('DESKUSS_VERSION')){
	exit('Hacking Attempt!');
}

function deskuss_license(){
	
	global $dsk_error, $dsk_lic_resp;
	
	if(isset($_REQUEST['save_dsk_license'])){
		check_admin_referer('deskuss-options');
	}

	if(isset($_POST['save_dsk_license'])){
	
		$license = deskuss_optpost('deskuss_license');
		
		if(empty($license)){
			$dsk_error['lic_invalid'] = __('The license key was not submitted', 'deskuss');
			return deskuss_license_T();
		}
		
		deskuss_load_license($license);
		
		if(is_array($dsk_lic_resp)){
			$json = json_decode($dsk_lic_resp['body'], true);
		}else{
		
			$dsk_error['resp_invalid'] = __('The response was malformed<br>'.var_export($dsk_lic_resp, true), 'deskuss');
			return deskuss_license_T();
			
		}
		
		if(empty($json['license'])){
		
			$dsk_error['lic_invalid'] = __('The license key is invalid', 'deskuss');
			return deskuss_license_T();
			
		}else{
			
			$GLOBALS['dsk_saved'] = true;
		}
		
	}
	
	deskuss_license_T();
	
}

function deskuss_license_T(){
	
	global $deskuss, $dsk_error;

	deskuss_page_header('Deskuss License', 1);

	echo '<style>
	#deskuss-settings-content {
		max-width: 800px !important;
		margin: 0 auto !important;
		width: 100% !important;
	}

	#deskuss-settings-content .postbox {
		background: #fff !important;
		border: 1px solid #e2e8f0 !important;
		border-radius: 12px !important;
		box-shadow: 0 1px 3px rgba(0,0,0,0.05) !important;
		overflow: hidden !important;
		margin-top: 15px !important;
	}

	#deskuss-settings-content .postbox .handlediv {
		display: none !important;
	}

	#deskuss-settings-content .postbox h2.hndle {
		padding: 16px 24px !important;
		border-bottom: 1px solid #e2e8f0 !important;
		font-size: 15px !important;
		font-weight: 600 !important;
		color: #0f172a !important;
		background: #fff !important;
		margin: 0 !important;
	}

	#deskuss-settings-content .postbox .inside {
		padding: 0 !important;
		margin: 0 !important;
	}

	#deskuss-settings-content .postbox table.users {
		width: 100% !important;
		max-width: 100% !important;
		margin: 0 !important;
		border-collapse: collapse !important;
		border: none !important;
	}

	#deskuss-settings-content .postbox table.users tr {
		border-bottom: 1px solid #f1f5f9 !important;
		background: #fff !important;
	}

	#deskuss-settings-content .postbox table.users tr:last-child {
		border-bottom: none !important;
	}

	#deskuss-settings-content .postbox table.users tr:hover td,
	#deskuss-settings-content .postbox table.users tr:hover th {
		background: #fafbfd !important;
	}

	#deskuss-settings-content .postbox table.users th {
		text-align: left !important;
		padding: 16px 24px !important;
		font-weight: 600 !important;
		color: #344054 !important;
		font-size: 13px !important;
		background: #f8fafc !important;
		width: 250px !important;
		vertical-align: middle !important;
		border-right: 1px solid #e2e8f0 !important;
		border-bottom: none !important;
	}

	#deskuss-settings-content .postbox table.users td {
		padding: 16px 24px !important;
		color: #334155 !important;
		font-size: 13px !important;
		background: #fff !important;
		vertical-align: middle !important;
		border-bottom: none !important;
	}

	#deskuss-settings-content .postbox table.users input[type="text"] {
		border: 1.5px solid #d0d5dd !important;
		border-radius: 6px !important;
		padding: 8px 12px !important;
		font-size: 13px !important;
		color: #0f172a !important;
		background: #fff !important;
		transition: all 0.2s ease-in-out !important;
		outline: none !important;
		box-sizing: border-box !important;
		box-shadow: 0 1px 2px rgba(16, 24, 40, 0.05) !important;
		vertical-align: middle !important;
	}

	#deskuss-settings-content .postbox table.users input[type="text"]:focus {
		border-color: #4f46e5 !important;
		box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12) !important;
	}

	#deskuss-settings-content .postbox table.users input[type="submit"] {
		background: #4f46e5 !important;
		color: #fff !important;
		border: none !important;
		border-radius: 6px !important;
		padding: 9px 18px !important;
		font-size: 13px !important;
		font-weight: 600 !important;
		cursor: pointer !important;
		transition: all 0.2s ease-in-out !important;
		box-shadow: 0 1px 3px rgba(79, 70, 229, 0.2) !important;
		height: auto !important;
		line-height: 1.4 !important;
		vertical-align: middle !important;
		margin-left: 8px !important;
	}

	#deskuss-settings-content .postbox table.users input[type="submit"]:hover {
		background: #4338ca !important;
		box-shadow: 0 2px 5px rgba(79, 70, 229, 0.3) !important;
	}
	</style>';

	if(!empty($GLOBALS['dsk_saved'])){
		echo '<div class="notice notice-success"><p>'. __('The settings were saved successfully', 'deskuss'). '</p></div><br />';
	}
	
	if(!empty($dsk_error)){
		deskuss_report_error($dsk_error);echo '<br />';
	}
	
	?>
	
	<div class="postbox">
	
		<button class="handlediv button-link" aria-expanded="true" type="button">
			<span class="screen-reader-text"><?php _e('Toggle panel: System Information');?></span>
			<span class="toggle-indicator" aria-hidden="true"></span>
		</button>
		
		<h2 class="hndle ui-sortable-handle">
			<span><?php echo __('System Information', 'deskuss'); ?></span>
		</h2>
		
		<div class="inside">
		
		<form action="" method="post" enctype="multipart/form-data">
		<?php wp_nonce_field('deskuss-options'); ?>
		<table class="wp-list-table fixed striped users" cellspacing="1" border="0" width="95%" cellpadding="10" align="center">
		<?php
			echo '
			<tr>				
				<th align="left" width="25%">'.__('Deskuss Version', 'deskuss').'</th>
				<td>'.DESKUSS_VERSION.'</td>
			</tr>';
			
			echo '
			<tr>			
				<th align="left" valign="top">'.__('Deskuss License', 'deskuss').'</th>
				<td align="left">
					'.(empty($deskuss->license) ? '<span style="color:red">Unlicensed</span> &nbsp; &nbsp; <a href="'.esc_url(DESKUSS_PRO_URL).'" target="_blank" class="button button-secondary" style="margin-left:4px;vertical-align:middle;">'.__('Buy License', 'deskuss').'</a> &nbsp; &nbsp;' : '').' 
					<input type="text" name="deskuss_license" value="'.(empty($deskuss->license) ? '' : $deskuss->license['license']).'" size="30" placeholder="e.g. DSKSS-11111-22222-33333-44444" style="width:300px;" /> &nbsp; 
					<input name="save_dsk_license" class="button button-primary" value="Update License" type="submit" />';
					
					if(!empty($deskuss->license)){
						
						$expires = $deskuss->license['expires'];
						$expires = substr($expires, 0, 4).'/'.substr($expires, 4, 2).'/'.substr($expires, 6);
						
						echo '<div style="margin-top:10px;">License Status : '.(empty($deskuss->license['status_txt']) ? 'N.A.' : $deskuss->license['status_txt']).' &nbsp; &nbsp; &nbsp;
						'.($deskuss->license['expires'] <= date('Ymd') ?  __('License Expires : ', 'deskuss') .'<span style="color:red">'.$expires.'</span>' : (empty($deskuss->license['has_plid']) ? __('License Expires : ', 'deskuss') . $expires : '')).'
						</div>';
					}
					
					
				echo 
				'</td>
			</tr>';
			
			echo '<tr>
				<th align="left">'.__('URL', 'deskuss').'</th>
				<td>'.get_site_url().'</td>
			</tr>
			<tr>				
				<th align="left">'.__('Path', 'deskuss').'</th>
				<td>'.ABSPATH.'</td>
			</tr>
			<tr>				
				<th align="left">'.__('Server\'s IP Address', 'deskuss').'</th>
				<td>'.$_SERVER['SERVER_ADDR'].'</td>
			</tr>
			<tr>				
				<th align="left">'.__('wp-config.php is writable', 'deskuss').'</th>
				<td>'.(is_writable(ABSPATH.'/wp-config.php') ? '<span style="color:red">Yes</span>' : '<span style="color:green">No</span>').'</td>
			</tr>';
			
			if(file_exists(ABSPATH.'/.htaccess')){
				echo '
			<tr>				
				<th align="left">'.__('.htaccess is writable', 'deskuss').'</th>
				<td>'.(is_writable(ABSPATH.'/.htaccess') ? '<span style="color:red">Yes</span>' : '<span style="color:green">No</span>').'</td>
			</tr>';
			
			}
			
		?>
		</table>
		</form>
		
		</div>
	</div>

<?php
	
	deskuss_page_footer();

}

function deskuss_page_header($title = 'Deskuss', $no_sidebar = 0){

	global $deskuss, $deskuss_header_printed;

	if (!empty($deskuss_header_printed)) {
		return;
	}
	$deskuss_header_printed = true;

	$version = DESKUSS_VERSION;

	echo '<div id="deskuss-admin-wrap"' . (!empty($no_sidebar) ? ' class="dsk-no-sidebar"' : '') . '>';

	if (empty($no_sidebar)) {
		echo '<div id="deskuss-sidebar">';
		echo '<div id="deskuss-sidebar-logo">
			'.(file_exists(DESKUSS_DIR.'/images/deskuss-logo-40.png') ? '<img src="'.esc_url(DESKUSS_URL . '/images/deskuss-logo-40.png').'" alt="Deskuss Logo">' : '<span class="dashicons dashicons-tickets-alt" style="font-size:36px;width:36px;height:36px;line-height:36px;color:#818cf8;"></span>').'
			<div class="dsk-logo-details">
				<span class="dsk-logo-text">deskuss</span>
				<span class="dsk-version">v' . esc_html($version) . '</span>
			</div>
		</div>';

		$current_page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : '';

		$nav_items = array(
			'settings' => array( 'label' => __('Settings', 'deskuss'), 'icon' => 'dashicons-admin-settings', 'hash' => admin_url('admin.php?page=deskuss-settings') ),
			'license' => array( 'label' => __('License', 'deskuss'), 'icon' => 'dashicons-admin-network', 'hash' => admin_url('admin.php?page=deskuss_license') ),
		);

		echo '<nav id="deskuss-sidebar-nav">';
		foreach( $nav_items as $key => $item ){
			$is_active = ($current_page === 'deskuss_license' && $key === 'license') || ($current_page === 'deskuss-settings' && $key === 'settings');
			echo '<a href="' . esc_attr( $item['hash'] ) . '" class="deskuss-nav-item ' . ($is_active ? 'deskuss-nav-active' : '') . '" data-tab="' . esc_attr( $key ) . '">
				<span class="dsk-nav-icon dashicons ' . esc_attr( $item['icon'] ) . '"></span>'.esc_html( $item['label'] ).'
			</a>';
		}
		echo '</nav>';

		echo '</div>';
	}

	echo '<div id="deskuss-main-content">';

	echo '<div id="deskuss-top-header">
		<h1>'.esc_html($title).'</h1>
		<div class="deskuss-header-actions">
			<a href="' . esc_url( DESKUSS_DOCS ) . '" target="_blank" class="dsk-help-btn">
				<span class="dashicons dashicons-editor-help"></span> ' . __('Help').'
			</a>
		</div>
	</div>';

	echo '<div id="deskuss-body-row">
	<div id="deskuss-settings-content">';
}

function deskuss_page_footer(){

	global $deskuss, $deskuss_footer_printed;

	if (!empty($deskuss_footer_printed)) {
		return;
	}
	$deskuss_footer_printed = true;

	echo '</div>';

	$current_page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : '';

	if($current_page === 'deskuss_license' && empty($deskuss->license['active'])){
		echo '<div id="deskuss-right-bar">';
		echo '<div class="postbox" style="min-width:0px !important;border:1px solid #e2e8f0;border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,0.05);background:#fff;">
			<h2 class="hndle ui-sortable-handle" style="padding:16px 24px;border-bottom:1px solid #e2e8f0;font-size:15px;font-weight:600;color:#0f172a;background:#fff;margin:0;">
				<span>'.__('Get Deskuss Premium', 'deskuss').'</span>
			</h2>
			<div class="inside" style="padding:18px 24px;">
				<p style="margin-top:0;">'.__('Unlock the full power of Deskuss with a premium license.', 'deskuss').'</p>
				<ul style="list-style:disc;padding-left:20px;color:#334155;font-size:13px;line-height:1.8;">
					<li>'.__('Priority support', 'deskuss').'</li>
					<li>'.__('Automatic updates', 'deskuss').'</li>
					<li>'.__('Access to all premium features', 'deskuss').'</li>
				</ul>
				<center><a class="button button-primary" target="_blank" href="'.esc_url(DESKUSS_PRO_URL).'" style="margin-top:8px;">'.__('Buy License', 'deskuss').'</a></center>
			</div>
		</div>';
		echo '</div>';
	}

	echo '</div>';
	echo '</div>';
	echo '</div>';

?>
<style>
#deskuss-admin-wrap {
	margin-left: -20px;
	display: flex;
	min-height: calc(100vh - 32px);
	background: #f1f5f9;
	font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
}

#deskuss-admin-wrap.dsk-no-sidebar #deskuss-main-content {
	margin-left: 0;
}

#deskuss-sidebar {
	width: 240px;
	background: #1e293b;
	color: #fff;
	display: flex;
	flex-direction: column;
	flex-shrink: 0;
}

#deskuss-sidebar-logo {
	display: flex;
	align-items: center;
	padding: 16px 20px;
	border-bottom: 1px solid rgba(255,255,255,0.08);
	gap: 10px;
}

#deskuss-sidebar-logo img {
	width: 36px;
	height: 36px;
	border-radius: 6px;
}

.dsk-logo-details {
	display: flex;
	flex-direction: column;
}

.dsk-logo-text {
	font-size: 15px;
	font-weight: 600;
	color: #fff;
	text-transform: capitalize;
}

.dsk-version {
	font-size: 11px;
	color: #94a3b8;
}

#deskuss-sidebar-nav {
	display: flex;
	flex-direction: column;
	padding: 12px 0;
}

.deskuss-nav-item {
	display: flex;
	align-items: center;
	padding: 10px 20px;
	color: #cbd5e1;
	text-decoration: none;
	font-size: 13px;
	font-weight: 500;
	transition: all 0.15s ease;
	gap: 10px;
}

.deskuss-nav-item:hover {
	background: rgba(255,255,255,0.06);
	color: #fff;
}

.deskuss-nav-active {
	background: rgba(79, 70, 229, 0.15);
	color: #818cf8;
}

.deskuss-nav-active:hover {
	background: rgba(79, 70, 229, 0.2);
	color: #818cf8;
}

.dsk-nav-icon {
	font-size: 18px;
	width: 18px;
	height: 18px;
}

#deskuss-main-content {
	flex: 1;
	display: flex;
	flex-direction: column;
	min-width: 0;
}

#deskuss-top-header {
	display: flex;
	align-items: center;
	justify-content: space-between;
	padding: 18px 30px;
	background: #fff;
	border-bottom: 1px solid #e2e8f0;
}

#deskuss-top-header h1 {
	font-size: 20px;
	font-weight: 600;
	color: #0f172a;
	margin: 0;
}

.deskuss-header-actions {
	display: flex;
	align-items: center;
	gap: 12px;
}

.dsk-help-btn {
	display: inline-flex;
	align-items: center;
	gap: 4px;
	padding: 6px 14px;
	border: 1px solid #d0d5dd;
	border-radius: 6px;
	color: #344054;
	text-decoration: none;
	font-size: 13px;
	font-weight: 500;
	background: #fff;
	transition: all 0.15s ease;
}

.dsk-help-btn:hover {
	background: #f9fafb;
	border-color: #98a2b3;
	color: #1d2939;
}

#deskuss-body-row {
	display: flex;
	flex: 1;
	gap: 24px;
	padding: 24px 30px;
}

#deskuss-settings-content {
	flex: 1;
	min-width: 0;
}

#deskuss-right-bar {
	width: 280px;
	flex-shrink: 0;
}

#deskuss-admin-wrap.dsk-no-sidebar #deskuss-right-bar {
	display: none;
}
</style>
<?php
}