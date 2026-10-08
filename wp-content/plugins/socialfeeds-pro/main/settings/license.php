<?php

namespace SocialFeedsPro\Settings;

// Are we being accessed directly ?
if(!defined('ABSPATH')){
	exit;
}

class License{

	static function template(){
		global $socialfeeds;
		
		// Add header
		if(isset($_REQUEST['save_socialfeeds_pro_license'])){
			self::save();
		}
		
		// Handle delete license
		if(isset($_REQUEST['delete_socialfeeds_pro_license'])){
			self::delete();
		}

		echo '<div class="socialfeeds-license-tab">
				<div class="socialfeeds-license-card">
					
					<!-- Version Row -->
					<div class="socialfeeds-license-row">
						<div class="socialfeeds-license-label">' . esc_html__('SocialFeeds Version', 'socialfeeds-pro') . '</div>
						<div class="socialfeeds-license-value">' . (defined('SOCIALFEEDS_PRO_VERSION') ? esc_html(SOCIALFEEDS_PRO_VERSION) . ' (Pro Version)' : 'N/A') . '</div>
					</div>

					<!-- License Row -->
					<div class="socialfeeds-license-row">
						<div class="socialfeeds-license-label">' . esc_html__('SocialFeeds License', 'socialfeeds-pro') . '</div>
						<div class="socialfeeds-license-value">
							<form method="post" action="" class="socialfeeds-license-input-wrapper">
							    <input type="hidden" name="socialfeeds_pro_license_nonce" value="' . esc_attr( wp_create_nonce( 'socialfeeds_pro_license' ) ) . '" />

								<div class="socialfeeds-license-input-row">
									' . (defined('SOCIALFEEDS_PRO_VERSION') && empty($socialfeeds->license['active']) ? '<span class="socialfeeds-license-badge">Unlicensed</span>' : '') . '
									<input type="text" name="socialfeeds_pro_license" class="socialfeeds-license-input" value="' . (empty($socialfeeds->license['license']) ? '' : esc_html($socialfeeds->license['license'])) . '" placeholder="SOCIA-11111-22222-33333-44444">
								</div>

								<div class="socialfeeds-license-buttons">
									<button name="save_socialfeeds_pro_license" class="socialfeeds-license-btn socialfeeds-license-btn-update" type="submit">' . esc_html__('Update License', 'socialfeeds-pro') . '</button>';
									
									// Show delete button only if license exists
									if(!empty($socialfeeds->license['license'])) {
										echo '<button name="delete_socialfeeds_pro_license" class="socialfeeds-license-btn socialfeeds-license-btn-delete" type="submit" onclick="return confirm(\'' . esc_js(__('Are you sure you want to delete the license? This will deactivate your license on this site.', 'socialfeeds-pro')) . '\')">' . esc_html__('Delete License', 'socialfeeds-pro') . '</button>';
									}
									
									echo '</div>';

								if(!empty($socialfeeds->license)){
									$expires = $socialfeeds->license['expires'];
									$expires = substr($expires, 0, 4) . '/' . substr($expires, 4, 2) . '/' . substr($expires, 6);
									echo '<div class="socialfeeds-license-info">
										<span>License Status: <b>' . (empty($socialfeeds->license['status_txt']) ? 'N.A.' : wp_kses_post($socialfeeds->license['status_txt'])) . '</b></span>
										' . ($socialfeeds->license['expires'] <= gmdate('Ymd') ? '<span>License Expires: <b class="socialfeeds-text-danger">' . esc_attr($expires) . '</b></span>' : (empty($socialfeeds->license['has_plid']) ? '<span>License Expires: <b>' . esc_html($expires) . '</b></span>' : '')) . '
									</div>';
								}
						echo '	</form>
						</div>
					</div>

					<!-- URL Row -->
					<div class="socialfeeds-license-row">
						<div class="socialfeeds-license-label">' . esc_html__('URL', 'socialfeeds-pro') . '</div>
						<div class="socialfeeds-license-value mono">' . esc_url(get_site_url()) . '</div>
					</div>

					<!-- Path Row -->
					<div class="socialfeeds-license-row">
						<div class="socialfeeds-license-label">' . esc_html__('Path', 'socialfeeds-pro') . '</div>
						<div class="socialfeeds-license-value mono">' . esc_html(ABSPATH) . '</div>
					</div>

					<!-- IP Row -->
					<div class="socialfeeds-license-row">
						<div class="socialfeeds-license-label">' . esc_html__('Server\'s IP Address', 'socialfeeds-pro') . '</div>
						<div class="socialfeeds-license-value mono">' . esc_html($_SERVER['SERVER_ADDR']) . '</div>
					</div>

					<!-- Writable Row -->
					<div class="socialfeeds-license-row">
						<div class="socialfeeds-license-label">' . esc_html__('.htaccess is writable', 'socialfeeds-pro') . '</div>
						<div class="socialfeeds-license-value">
							' . (is_writable(ABSPATH . '.htaccess') ? '<span class="socialfeeds-text-success">Yes</span>' : '<span class="socialfeeds-text-danger">No</span>') . '
						</div>
					</div>
				</div>
		</div>';
	}
	
	static function save(){
		global $socialfeeds, $lic_resp;
		
		// Verify nonce
		if(!isset($_POST['socialfeeds_pro_license_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['socialfeeds_pro_license_nonce'])), 'socialfeeds_pro_license')){
			echo '<div style="margin-top:65px;" class="notice notice-error is-dismissible"><p>';
			echo esc_html__('Nonce verification failed', 'socialfeeds-pro');
			echo '</p></div>';
			return;
		}

		$license = sanitize_text_field(wp_unslash($_POST['socialfeeds_pro_license']));

		if(empty($license)){
			echo '<div style="margin-top:65px;" class="notice notice-error is-dismissible"><p>';
			echo esc_html__('The license key was not submitted', 'socialfeeds-pro');
			echo '</p></div>';
			return;
		}
		
		socialfeeds_pro_load_license($license);
		
		if(is_wp_error($lic_resp) || 200 !== wp_remote_retrieve_response_code($lic_resp)){
			if(is_wp_error($lic_resp)){
				echo '<div style="margin-top:65px;" class="notice notice-error is-dismissible"><p>';
				echo esc_html($lic_resp->get_error_message());
				echo '</p></div>';
				return;
			} else{
				echo '<div style="margin-top:65px;" class="notice notice-error is-dismissible"><p>';
				echo esc_html__('An error occurred, please try again. Response code: ', 'socialfeeds-pro') . esc_attr(wp_remote_retrieve_response_code($lic_resp));
				echo '</p></div>';
				return;
			}
		} else {
			$tmp = json_decode(wp_remote_retrieve_body($lic_resp), true);
			 if(empty($tmp)){
				echo '<div style="margin-top:65px;" class="notice notice-error is-dismissible"><p>';
				echo esc_html__('The license key is invalid', 'socialfeeds-pro');
				echo '</p></div>';
				return;
			}
			
			echo'<div class="socialfeeds-notice is-success">
			'. esc_html__('Your license has been successfully activated!', 'socialfeeds-pro').'
			</div>';
		}
	}

	static function delete(){
		global $socialfeeds;

		// Verify nonce
		if(!isset($_POST['socialfeeds_pro_license_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['socialfeeds_pro_license_nonce'])), 'socialfeeds_pro_license')){
			echo '<div style="margin-top:65px;" class="notice notice-error is-dismissible"><p>';
			echo esc_html__('Nonce verification failed', 'socialfeeds-pro');
			echo '</p></div>';
			return;
		}

		if(isset($_POST['delete_socialfeeds_pro_license'])){
			// Delete the license option
			delete_option('socialfeeds_license');

			// Clear the global license data
			if(isset($socialfeeds->license)) {
				$socialfeeds->license = array();
			}
			
			echo'<div class="socialfeeds-notice is-success">
			'. esc_html__('License has been successfully deleted!', 'socialfeeds-pro').'
			</div>';
		}
	}

}