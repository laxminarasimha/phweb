<?php

namespace ShareMyPostPro;

if(!defined('ABSPATH')){
exit;
}

class Ajax{

	static function hooks(){
		add_action('wp_ajax_sharemypost_save_custom_network', '\ShareMyPostPro\Ajax::network_save');
		add_action('wp_ajax_sharemypost_delete_custom_network', '\ShareMyPostPro\Ajax::network_delete');
		add_action('wp_ajax_sharemypost_get_custom_network', '\ShareMyPostPro\Ajax::network_get');
		add_action('wp_ajax_sharemypost_reorder_custom_networks', '\ShareMyPostPro\Ajax::network_reorder');
		add_action('wp_ajax_sharemypost_get_custom_networks_list', '\ShareMyPostPro\Ajax::get_list');
		add_action('wp_ajax_sharemypost_pro_version_notice', '\ShareMyPostPro\Ajax::sharemypost_pro_version_notice');
	}

	static function network_save() {
		check_ajax_referer('sharemypost_pro_admin_nonce', 'nonce');

		if(!current_user_can('manage_options')){
			wp_send_json_error(['message' => __('Unauthorized', 'sharemypost-pro')]);
		}

		$data = \ShareMyPostPro\CustomNetworks::sanitize_network($_POST);

		$name = isset($data['name']) ? $data['name'] : '';
		$url = isset($data['url']) ? $data['url'] : '';

		if(empty($name)){
			wp_send_json_error(['message' => __('Network name is required.', 'sharemypost-pro')]);
		}

		if(empty($url)) {
			wp_send_json_error(['message' => __('Share URL is required.', 'sharemypost-pro')]);
		}

		$id = \ShareMyPostPro\CustomNetworks::save($data);

		if($id){
			$network = \ShareMyPostPro\CustomNetworks::get($id);
			if($network){
				ob_start();
				\ShareMyPostPro\CustomNetworks::render_network_card($network);
				$card_html = ob_get_clean();
				wp_send_json_success([
					'message' => $data['id'] ? __('Network updated successfully.', 'sharemypost-pro') : __('Network added successfully.', 'sharemypost-pro'),
					'id' => $id,
					'network' => $network,
					'card_html' => $card_html,
				]);
			}
		}

		wp_send_json_error(['message' => __('Failed to save network.', 'sharemypost-pro')]);
	}



	static function network_delete() {
		check_ajax_referer('sharemypost_pro_admin_nonce', 'nonce');

		if(!current_user_can('manage_options')){
			wp_send_json_error(['message' => __('Unauthorized', 'sharemypost-pro')]);
		}

		$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
		if(!$id){
			wp_send_json_error(['message' => __('Invalid network ID.', 'sharemypost-pro')]);
		}

		\ShareMyPostPro\CustomNetworks::delete($id);
		wp_send_json_success(['message' => __('Network deleted successfully.', 'sharemypost-pro')]);
	}

	static function network_get() {
		check_ajax_referer('sharemypost_pro_admin_nonce', 'nonce');

		if(!current_user_can('manage_options')) {
			wp_send_json_error(['message' => __('Unauthorized', 'sharemypost-pro')]);
		}

		$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
		$network = \ShareMyPostPro\CustomNetworks::get($id);

		if($network){
			wp_send_json_success($network);
		}

		wp_send_json_error(['message' => __('Network not found.', 'sharemypost-pro')]);
	}

	static function network_reorder() {
		check_ajax_referer('sharemypost_pro_admin_nonce', 'nonce');

		if(!current_user_can('manage_options')){
			wp_send_json_error(['message' => __('Unauthorized', 'sharemypost-pro')]);
		}

		$order = isset($_POST['order']) ? array_map('intval', wp_unslash((array) $_POST['order'])) : [];

		if(empty($order)){
			wp_send_json_error(['message' => __('Invalid order data.', 'sharemypost-pro')]);
		}

		$networks = \ShareMyPostPro\CustomNetworks::get_all();
		$ordered = [];

		foreach ($order as $index => $id) {
			foreach ($networks as $network) {
				if ((isset($network['id']) ? (int) $network['id'] : 0) === (int) $id) {
					$network['sort_order'] = $index;
					$ordered[] = $network;
					break;
				}
			}
		}

		$ids_in_order = array_map('intval', $order);
		foreach ($networks as $network) {
			if(!in_array((isset($network['id']) ? (int) $network['id'] : 0), $ids_in_order, true)){
				$network['sort_order'] = count($ordered);
				$ordered[] = $network;
			}
		}
		update_option('sharemypost_custom_networks', $ordered);
		wp_send_json_success(['message' => __('Order saved successfully.', 'sharemypost-pro')]);
	}

	static function get_list() {
		check_ajax_referer('sharemypost_pro_admin_nonce', 'nonce');

		if(!current_user_can('manage_options')){
			wp_send_json_error(['message' => __('Unauthorized', 'sharemypost-pro')]);
		}

		$networks = \ShareMyPostPro\CustomNetworks::get_all();
		ob_start();

		if(empty($networks)){
			echo '<p class="sharemypost-empty-state">' . esc_html__('No custom networks yet. Add one using the form above.', 'sharemypost-pro') . '</p>';
		} else {
			echo '<div class="sharemypost-custom-networks-grid sharemypost-sortable-networks" id="sharemypost-custom-networks-sortable">';
			foreach ($networks as $network) {
				\ShareMyPostPro\CustomNetworks::render_network_card($network);
			}
			echo '</div>';
		}

		$html = ob_get_clean();
		wp_send_json_success(['html' => $html]);
	}
	
	static function sharemypost_pro_version_notice(){
		if(!current_user_can('activate_plugins')){
			wp_send_json_error(__('You do not have required access to do this action', 'sharemypost-pro'));
		}
		
		$type = '';
		if(!empty($_REQUEST['type'])){
			$type = sanitize_text_field(wp_unslash($_REQUEST['type']));
		}

		if(empty($type)){
			wp_send_json_error(__('Unknow version difference type', 'sharemypost-pro'));
		}
		
		update_option('sharemypost_version_'. $type .'_nag', time() + WEEK_IN_SECONDS);
		wp_send_json_success();
	}
}
