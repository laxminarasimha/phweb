<?php

namespace ShareMyPostPro\Settings;

// Are we being accessed directly ?
if(!defined('ABSPATH')){
	exit;
}

class License{

	static function template(){
		global $sharemypost;
		
		// Add header
		if(isset($_REQUEST['save_sharemypost_pro_license'])){
			self::save();
		}
		
		// Handle delete license
		if(isset($_REQUEST['delete_sharemypost_pro_license'])){
			self::delete();
		}

		echo '<div class="sharemypost-license-tab">
				<div class="sharemypost-license-card">
					
					<!-- Version Row -->
					<div class="sharemypost-license-row">
						<div class="sharemypost-license-label">' . esc_html__('ShareMyPost Version', 'sharemypost-pro') . '</div>
						<div class="sharemypost-license-value">' . (defined('SHAREMYPOST_PRO_VERSION') ? esc_html(SHAREMYPOST_PRO_VERSION) . ' (Pro Version)' : 'N/A') . '</div>
					</div>

					<!-- License Row -->
					<div class="sharemypost-license-row">
						<div class="sharemypost-license-label">' . esc_html__('ShareMyPost License', 'sharemypost-pro') . '</div>
						<div class="sharemypost-license-value">
							<form method="post" action="" class="sharemypost-license-input-wrapper">
							<input type="hidden" name="sharemypost_pro_license_nonce" value="' . esc_attr( wp_create_nonce( 'sharemypost_pro_license' ) ) . '" />

								<div class="sharemypost-license-input-row">
									' . (defined('SHAREMYPOST_PRO_VERSION') && empty($sharemypost->license['active']) ? '<span class="sharemypost-license-badge">Unlicensed</span>' : '') . '
									<input type="text" name="sharemypost_pro_license" class="sharemypost-license-input" value="' . (empty($sharemypost->license['license']) ? '' : esc_html($sharemypost->license['license'])) . '" placeholder="SHARE-11111-22222-33333-44444">
								</div>

								<div class="sharemypost-license-buttons">
									<button name="save_sharemypost_pro_license" class="sharemypost-license-btn sharemypost-license-btn-update" type="submit">' . esc_html__('Update License', 'sharemypost-pro') . '</button>';
									
									// Show delete button only if license exists
									if(!empty($sharemypost->license['license'])) {
										echo '<button name="delete_sharemypost_pro_license" class="sharemypost-license-btn sharemypost-license-btn-delete" type="submit" onclick="return confirm(\'' . esc_js(__('Are you sure you want to delete the license? This will deactivate your license on this site.', 'sharemypost-pro')) . '\')">' . esc_html__('Delete License', 'sharemypost-pro') . '</button>';
									}
									
									echo '</div>';

								if(!empty($sharemypost->license)){
									$expires = isset($sharemypost->license['expires']) ? $sharemypost->license['expires'] : '';
									$expires = !empty($expires) ? substr($expires, 0, 4) . '/' . substr($expires, 4, 2) . '/' . substr($expires, 6) : '';
									$has_plid = !empty($sharemypost->license['has_plid']);
									echo '<div class="sharemypost-license-info">
										<span>License Status: <b>' . (empty($sharemypost->license['status_txt']) ? 'N.A.' : wp_kses_post($sharemypost->license['status_txt'])) . '</b></span>
										' . (!empty($sharemypost->license['expires']) && $sharemypost->license['expires'] <= gmdate('Ymd') ? '<span>License Expires: <b class="sharemypost-text-danger">' . esc_attr($expires) . '</b></span>' : (empty($has_plid) && !empty($expires) ? '<span>License Expires: <b>' . esc_html($expires) . '</b></span>' : '')) . '
									</div>';
								}
						echo '	</form>
						</div>
					</div>

					<!-- URL Row -->
					<div class="sharemypost-license-row">
						<div class="sharemypost-license-label">' . esc_html__('URL', 'sharemypost-pro') . '</div>
						<div class="sharemypost-license-value mono">' . esc_url(get_site_url()) . '</div>
					</div>

					<!-- Path Row -->
					<div class="sharemypost-license-row">
						<div class="sharemypost-license-label">' . esc_html__('Path', 'sharemypost-pro') . '</div>
						<div class="sharemypost-license-value mono">' . esc_html(ABSPATH) . '</div>
					</div>

					<!-- IP Row -->
					<div class="sharemypost-license-row">
						<div class="sharemypost-license-label">' . esc_html__('Server\'s IP Address', 'sharemypost-pro') . '</div>
						<div class="sharemypost-license-value mono">' . esc_html($_SERVER['SERVER_ADDR']) . '</div>
					</div>

					<!-- Writable Row -->
					<div class="sharemypost-license-row">
						<div class="sharemypost-license-label">' . esc_html__('.htaccess is writable', 'sharemypost-pro') . '</div>
						<div class="sharemypost-license-value">
							' . (is_writable(ABSPATH . '.htaccess') ? '<span class="sharemypost-text-success">Yes</span>' : '<span class="sharemypost-text-danger">No</span>') . '
						</div>
					</div>
				</div>
		</div>';
	}
	
	static function save(){
		global $sharemypost, $lic_resp;
		
		// Verify nonce
		if(!isset($_POST['sharemypost_pro_license_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['sharemypost_pro_license_nonce'])), 'sharemypost_pro_license')){
			echo '<div style="margin-top:65px;" class="notice notice-error is-dismissible"><p>';
			echo esc_html__('Nonce verification failed', 'sharemypost-pro');
			echo '</p></div>';
			return;
		}

		$license = sanitize_text_field(wp_unslash($_POST['sharemypost_pro_license']));

		if(empty($license)){
			echo '<div style="margin-top:65px;" class="notice notice-error is-dismissible"><p>';
			echo esc_html__('The license key was not submitted', 'sharemypost-pro');
			echo '</p></div>';
			return;
		}
		
		sharemypost_pro_load_license($license);
		
		if(is_wp_error($lic_resp) || 200 !== wp_remote_retrieve_response_code($lic_resp)){
			if(is_wp_error($lic_resp)){
				echo '<div style="margin-top:65px;" class="notice notice-error is-dismissible"><p>';
				echo esc_html($lic_resp->get_error_message());
				echo '</p></div>';
				return;
			} else{
				echo '<div style="margin-top:65px;" class="notice notice-error is-dismissible"><p>';
				echo esc_html__('An error occurred, please try again. Response code: ', 'sharemypost-pro') . esc_attr(wp_remote_retrieve_response_code($lic_resp));
				echo '</p></div>';
				return;
			}
		} else {
			$tmp = json_decode(wp_remote_retrieve_body($lic_resp), true);
			if(empty($tmp)){
				echo '<div style="margin-top:65px;" class="notice notice-error is-dismissible"><p>';
				echo esc_html__('The license key is invalid', 'sharemypost-pro');
				echo '</p></div>';
				return;
			}
			
			echo'<div class="sharemypost-notice is-success">
			'. esc_html__('Your license has been successfully activated!', 'sharemypost-pro').'
			</div>';
		}
	}

	static function delete(){
		global $sharemypost;

		// Verify nonce
		if(!isset($_POST['sharemypost_pro_license_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['sharemypost_pro_license_nonce'])), 'sharemypost_pro_license')){
			echo '<div style="margin-top:65px;" class="notice notice-error is-dismissible"><p>';
			echo esc_html__('Nonce verification failed', 'sharemypost-pro');
			echo '</p></div>';
			return;
		}

		if(isset($_POST['delete_sharemypost_pro_license'])){
			// Delete the license option
			delete_option('sharemypost_license');

			// Clear the global license data
			if(isset($sharemypost->license)) {
				$sharemypost->license = array();
			}
			
			echo'<div class="sharemypost-notice is-success">
			'. esc_html__('License has been successfully deleted!', 'sharemypost-pro').'
			</div>';
		}
	}

}