jQuery(document).ready(function ($) {
	let next_page_token = null,
	is_fetching = false,
	socialfeeds_preview_device = 'desktop',
	selected_type = null;

	function escape_html(str) {
		if (str === null || str === undefined) {
			return '';
		}
		return $('<div>').text(String(str)).html();
	}

	function escape_attr(str) {
		return escape_html(str).replace(/"/g, '&quot;').replace(/'/g, '&#039;');
	}

  function safe_url(url) {
    if (!url) {
    	return '';
    }

    let parsed = new URL(String(url), window.location.origin);

    if (parsed.protocol !== 'http:' && parsed.protocol !== 'https:') {
    	return '';
    }

    return parsed.href;
  }

	function escape_url(url) {
		return escape_attr(safe_url(url));
	}

	$('#socialfeeds-youtube-videos-per-page').on('input', function () {
		let value = parseInt($(this).val(), 10);

		if (value > 200) {
			$(this).val(200);
		}

		if (value < 1) {
			$(this).val(1);
		}
	});

	// Toast message function
	function show_toast(message, type = 'success') {
		let toast = $('<div>')
			.addClass('socialfeeds-toast socialfeeds-toast') // site-seo compat
			.addClass(type)
			.html('<span class="dashicons dashicons-yes"></span> ' + escape_html(message));

		$('body').append(toast);

		// 3 seconds
		toast.fadeIn(300).delay(3000).fadeOut(300, function () {
			toast.remove();
		});
	}

	function generate_unique_id() {
		let existing_ids = socialfeedsData.existing_ids || [],
		max_id = 0;

		existing_ids.forEach(id => {
			let num_part = parseInt(id, 10);
			if (!isNaN(num_part) && num_part > max_id) {
				max_id = num_part;
			}
		});

		return max_id + 1;
	}

	function handle_tab() {
		let raw_hash = location.hash.trim().replace('#', ''),
		$wrap = $('.socialfeeds-wrap');
		if (!$wrap.length) return;

		// Extract base hash if there are parameters
		let hash = raw_hash.split('&')[0];

		if (!hash.length) {
			let $active_tab = $('.socialfeeds-tab-content.active');
			if ($active_tab.length) {
				hash = $active_tab.attr('id').replace('socialfeeds-', '');
			} else {
				hash = 'dashboard';
			}
		}

		let $tab = $('#socialfeeds-' + hash);
		if (!$tab.length) {
			// support direct Google Reviews wizard URLs
			if (hash === 'google-reviews' || hash === 'google_reviews') {
				hash = 'google';
				$tab = $('#socialfeeds-google');
			}
		}
		if (!$tab || !$tab.length) {
			// fallback to dashboard if hash unknown
			hash = 'dashboard';
			$tab = $('#socialfeeds-dashboard');
		}

		$('.socialfeeds-tab-content').removeClass('active').hide();
		$tab.addClass('active').fadeIn(200);

		// Update active state in nav
		$('.socialfeeds-nav-tab').removeClass('active');
		$('.socialfeeds-nav-tab[data-tab="' + hash + '"]').addClass('active');
	}

	window.addEventListener('hashchange', handle_tab);
	handle_tab();

	// Clean up URL when switching tabs
	$(document).on('click', '.socialfeeds-nav-tab', function (e) {
		let target_hash = $(this).attr('href');
		if (target_hash && target_hash.startsWith('#')) {
			let search = window.location.search;
			if (search !== '?page=socialfeeds' && search !== 'page=socialfeeds') {
				e.preventDefault();
				let base_url = socialfeedsData.ajax_url.replace('admin-ajax.php', '') + 'admin.php?page=socialfeeds';
				window.location.href = base_url + target_hash;
			}
		}
	});

	function init_socialfeeds_handlers() {
		// ===== FORM SUBMISSION =====
		$('.socialfeeds-ajax-form').on('submit', function (e) {
			e.preventDefault();
			let form = $(this),

			form_data = new FormData(this);
			form_data.append('nonce', socialfeedsData.nonce);

			let submit_btn = form.find('button[type="submit"]'),
			original_text = submit_btn.text();

			submit_btn.prop('disabled', true).text('Saving...');

			$.ajax({
				type: 'POST',
				url: socialfeedsData.ajax_url,
				data: form_data,
				processData: false,
				contentType: false,
				success: function (response) {
					if (response.success) {
						show_toast('Settings saved successfully!');
						setTimeout(function () {
							if (response.data && response.data.redirect) {
								window.location.href = response.data.redirect;
							}
						}, 1000);
					} else {
						show_toast(response.data.message || 'Error saving settings.', 'error');
					}
				},
				error: function (xhr, status, error) {
					show_toast('Error saving settings. Please try again.', 'error');
				},
				complete: function () {
					submit_btn.prop('disabled', false).text(original_text);
				}
			});
		});

		// ===== FEED SELECTION =====
		$('#socialfeeds-select-all').on('change', function () {
			$("input[name='selected_feeds[]']").prop('checked', this.checked);
		});

		// ===== DELETE FEED - Direct selector =====
		$('.socialfeeds-delete-feed-btn').on('click', function (e) {
			e.preventDefault();

			if (!confirm('Are you sure you want to delete this feed?')) {
				return;
			}

			let feed_id = $(this).data('feed-id'),
			platform = $(this).data('platform') || 'youtube';

			$.ajax({
				type: 'POST',
				url: socialfeedsData.ajax_url,
				data: {
					action: 'socialfeeds_delete_feeds',
					feed_id: feed_id,
					platform: platform,
					nonce: socialfeedsData.nonce
				},
				success: function (response) {
					if (response.success) {
						show_toast('Feed deleted successfully!');
						if(platform === 'instagram'){
							location.reload();
						} else{
							$('tr[data-feed-id="' + feed_id + '"]').fadeOut(300, function () {
								$(this).remove();
							});
						}

					} else {
						show_toast(response.data.message || 'Error deleting feed.', 'error');
					}
				},
				error: function () {
					show_toast('Error deleting feed. Please try again.', 'error');
				}
			});
		});

		// ===== BULK ACTIONS =====
		$('#socialfeeds-bulk-action-submit').on('click', function (e) {
			e.preventDefault();
			let bulk_action = $('#socialfeeds-bulk-action').val()
			let selected_feeds = [];
			$('input[name="selected_feeds[]"]:checked').each(function () {
				selected_feeds.push($(this).val());
			});

			if (selected_feeds.length === 0) {
				alert('Please select at least one feed.');
				return;
			}
			if (bulk_action === 'delete' && !confirm('Are you sure you want to delete selected feeds?')) {
				return;
			}

			$.ajax({
				type: 'POST',
				url: socialfeedsData.ajax_url,
				data: {
					action: 'socialfeeds_bulk_' + bulk_action + '_feeds',
					bulk_action: bulk_action,
					selected_feeds: selected_feeds,
					nonce: socialfeedsData.nonce
				},
				dataType: 'json',
				success: function (response) {
					if (response.success) {
						show_toast(response.message || 'Action completed successfully!');
						$('input[name="selected_feeds[]"]:checked').each(function () {
							$(this).closest('tr').fadeOut(300, function () { $(this).remove(); });
						});
					} else {
						show_toast(response.data.message || 'Error performing action.', 'error');
					}
				},
				error: function () {
					show_toast('Error performing action. Please try again.', 'error');
				}
			});
		});

		// ===== FEED TYPE CARD SELECTION =====
		$('.socialfeeds-feed-type-card, .socialfeeds-type-card').on('click', function () {
			if ($(this).hasClass('socialfeeds-locked')) {
				return false;
			}

			$('.socialfeeds-feed-type-card, .socialfeeds-type-card').removeClass('socialfeeds-selected selected shadow-lg border-primary');
			$(this).addClass($(this).hasClass('socialfeeds-type-card') ? 'selected border-primary' : 'socialfeeds-selected shadow-lg');

			selected_type = $(this).data('type');
			$('#socialfeeds-select-type-btn').removeClass('socialfeeds-disabled');
		});

		// Initialize selection UI based on current state
		let initial_selected = $('.socialfeeds-type-card.selected, .socialfeeds-feed-type-card.socialfeeds-selected').first();
		if (initial_selected.length) {
			selected_type = initial_selected.data('type');
			$('#socialfeeds-select-type-btn').removeClass('socialfeeds-disabled');
		}

		$('#socialfeeds-select-type-btn').on('click', function (e) {
			e.preventDefault();
			if (!selected_type) return;

			let url = 'admin.php?page=socialfeeds&action=create&type=' + encodeURIComponent(selected_type) + '#youtube';
			window.location.href = socialfeedsData.ajax_url.replace('admin-ajax.php', '') + url;
		});

		// ===== DASHBOARD TAB SWITCHING & MODALS =====
		$('.socialfeeds-tab-btn').on('click', function () {
			let tab = $(this).data('tab');
			$('.socialfeeds-tab-btn').removeClass('socialfeeds-tab-btn-active');
			$('.socialfeeds-tab-panel').removeClass('socialfeeds-tab-panel-active');
			$(this).addClass('socialfeeds-tab-btn-active');
			$('#' + tab).addClass('socialfeeds-tab-panel-active');
		});

		// ===== SIDEBAR TAB SWITCHING =====
		$('.socialfeeds-sidebar-tab-btn').on('click', function () {
			let target = $(this).data('target');
			$('.socialfeeds-sidebar-tab-btn').removeClass('active');
			$('.socialfeeds-sidebar-tab-pane').removeClass('active').hide();

			$(this).addClass('active');
			$('#' + target).addClass('active').fadeIn(200);
		});

		$('.socialfeeds-copy-shortcode-feeds').on('click', function () {
			let shortcode = $(this).attr('data-shortcode') || $(this).data('shortcode'),
			btn = $(this),
			original_html = btn.html();

			navigator.clipboard.writeText(shortcode).then(function () {
				btn.find('span.dashicons').removeClass('dashicons-admin-page').addClass('dashicons-yes');
				let text_node = btn.contents().filter(function () { return this.nodeType === 3; }).last();
				let original_text = text_node.text();
				text_node.replaceWith(' Copied!');

				show_toast('Shortcode copied to clipboard!');

				setTimeout(function () {
					btn.html(original_html);
				}, 2000);
			}).catch(function (err) {
				show_toast('Failed to copy: ' + err, 'error');
			});
		});

		$('.socialfeeds-copy-shortcode').on('click', function () {
			let shortcode = $(this).attr('data-shortcode') || $(this).data('shortcode'),
			btn = $(this),
			original_html = btn.html();
			navigator.clipboard.writeText(shortcode).then(function () {
				btn.find('span.dashicons').removeClass('dashicons-admin-page').addClass('dashicons-yes');
				let text_node = btn.contents().filter(function () { return this.nodeType === 3; }).last(),
				original_text = text_node.text();
				text_node.replaceWith(' Copied!');

				show_toast('Shortcode copied to clipboard!');

				setTimeout(function () {
					btn.html(original_html);
				}, 2000);
			}).catch(function (err) {
				show_toast('Failed to copy: ' + err, 'error');
			});
		});

		$('#socialfeeds-content-customize .socialfeeds-fullscreen-btn').on('click', function () {
			let $container = $(this).closest('#socialfeeds-content-customize'),
			$icon = $(this).find('.dashicons');

			$container.toggleClass('socialfeeds-fullscreen');
			$('body').toggleClass('socialfeeds-body-lock');

			if ($container.hasClass('socialfeeds-fullscreen')) {
				$icon.removeClass('dashicons-fullscreen-alt').addClass('dashicons-fullscreen-exit-alt');
				show_toast('Fullscreen mode enabled');
			} else {
				$icon.removeClass('dashicons-fullscreen-exit-alt').addClass('dashicons-fullscreen-alt');
				show_toast('Fullscreen mode disabled');
			}
		});

		$('.socialfeeds-instagram-settings').on('click', function (e) {
			e.preventDefault();
			let $modal = $('#socialfeeds-ig-connection-modal');
			if (!$modal.length) return;
			if (!$modal.parent().is('body')) $modal.appendTo(document.body);
			$('#socialfeeds-ig-modal-main').removeClass('hidden');
			$('#socialfeeds-ig-modal-token').removeClass('active').hide();
			$modal.addClass('active').css({ display: 'flex', visibility: 'visible', opacity: 0 }).stop().animate({ opacity: 1 }, 200);
		});

		$('.socialfeeds-facebook-settings').on('click', function (e){
			e.preventDefault();
			let $modal = $('#socialfeeds-fb-connection-modal');
			if (!$modal.length) return;
			if (!$modal.parent().is('body')) $modal.appendTo(document.body);
			$modal.addClass('active').css({ display: 'flex', visibility: 'visible', opacity: 0 }).stop().animate({ opacity: 1 }, 200);
		});

		$('.socialfeeds-google-settings').on('click', function (e){
			e.preventDefault();
			let $modal = $('#socialfeeds-google-connection-modal');
			if (!$modal.length) return;
			if (!$modal.parent().is('body')) $modal.appendTo(document.body);
			$modal.addClass('active').css({ display: 'flex', visibility: 'visible', opacity: 0 }).stop().animate({ opacity: 1 }, 200);
		});

		$('.socialfeeds-settings-btn').on('click', function (e) {
			e.preventDefault();
			let page_name = $(this).data('page'),
			page_url = socialfeedsData.ajax_url.replace('admin-ajax.php', '') + 'admin.php?page=' + page_name,
			$modal = $('#socialfeeds-settings-modal');
			if (!$modal.length) return;
			if (!$modal.parent().is('body')) $modal.appendTo(document.body);
			$modal.addClass('active').css({ display: 'flex', visibility: 'visible', opacity: 0 }).stop().animate({ opacity: 1 }, 200);
			$modal.data('page-url', page_url);
		});

		$('#socialfeeds-provide-api-key-btn').on('click', function(e){
			e.preventDefault();
			let $modal = $('#socialfeeds-settings-modal');
			if (!$modal.length) return;
			if (!$modal.parent().is('body')) $modal.appendTo(document.body);
			$modal.addClass('active').css({ display: 'flex', visibility: 'visible', opacity: 0 }).stop().animate({ opacity: 1 }, 200);
		});

		$('#socialfeeds-modal-api-form').on('submit', function (e) {
			e.preventDefault();
			let form = $(this),
			api_key = form.find('#socialfeeds-modal-api-key').val();

			if (!api_key || api_key.trim() === '') {
				show_toast('Please enter a YouTube API Key', 'error');
				return;
			}

			let submit_btn = form.find('button[type="submit"]'),
			original_text = submit_btn.text();
			submit_btn.prop('disabled', true).text('Saving...');

			$.ajax({
				type: 'POST',
				url: socialfeedsData.ajax_url,
				data: {
					action: 'socialfeeds_save_settings',
					youtube_api_key: api_key,
					nonce: socialfeedsData.nonce
				},
				dataType: 'json',
				success: function (response) {
					submit_btn.prop('disabled', false).text(original_text);
					if (response.success) {
						show_toast('YouTube API Key saved successfully!');
						setTimeout(function () {
							$('#socialfeeds-settings-modal').fadeOut(300, function () {
								$(this).removeClass('active');
							});
						}, 1500);
					} else {
						show_toast(response.data.message || 'Error saving API key', 'error');
					}
				},
				error: function (xhr) {
					submit_btn.prop('disabled', false).text(original_text);
					show_toast('Error saving API key. Please try again.', 'error');
				}
			});
		});

		$('.socialfeeds-modal-close').on('click', function (e) {
			e.preventDefault();
			let modal_id = $(this).data('modal') || $(this).closest('.socialfeeds-modal-overlay').attr('id'),
			$modal = $('#' + modal_id);
			$modal.fadeOut(200, function () {
				$(this).removeClass('active');
			});
		});

		$('#socialfeeds-youtube-videos-per-page').on('change', function () {
			next_page_token = null;
			is_fetching = false;
			fetch_preview('', false);
		});

		$('#socialfeeds-youtube-subscribe-button-enabled, #socialfeeds-youtube-show-duration, #socialfeeds-youtube-show-date, #socialfeeds-youtube-show-views, #socialfeeds-youtube-show-likes, #socialfeeds-youtube-show-comments, #socialfeeds-youtube-show-title, #socialfeeds-youtube-header-enabled, #socialfeeds-youtube-header-show-channel-name, #socialfeeds-youtube-header-show-logo, #socialfeeds-youtube-header-show-description, #socialfeeds-youtube-header-show-subscribers, #socialfeeds-youtube-header-text').on('change input', function () {
			update_preview_style();
		});

		$('#socialfeeds-next-btn').on('click', function (e) {
			e.preventDefault();
			next_page_token = null;

			// Manually switch UI to ensure "active" state is applied consistently
			$('.socialfeeds-wizard-tab').removeClass('active');
			$('.socialfeeds-wizard-tab[data-tab="customize"]').addClass('active');

			$('.socialfeeds-wizard-tab-content').removeClass('active').hide();
			$('#socialfeeds-content-customize').addClass('active').show();

			// Fetch preview once
			fetch_preview('', false);

			try { $('#socialfeeds-content-customize')[0].scrollIntoView({ behavior: 'smooth', block: 'start' }); } catch (e) { }
		});

		if ($('#socialfeeds-wizard-form, #socialfeeds-instagram-wizard-form, #socialfeeds-facebook-wizard-form, #socialfeeds-google-wizard-form').length > 0) {
			re_initialize_form_handlers();
		}
		init_dynamic_ui();
	}

	// Initial call
	init_socialfeeds_handlers();

	// ===== DASHBOARD TAB SWITCHING & MODALS =====

	function activate_tab_from_url() {
		let url = new URL(window.location.href),
		tab_param = url.searchParams.get('tab');
		if (tab_param && $('#' + tab_param).length) {
			let $btn = $('[data-tab="' + tab_param + '"]');
			if ($btn.length) {
				$('.socialfeeds-tab-btn').removeClass('socialfeeds-tab-btn-active');
				$('.socialfeeds-tab-panel').removeClass('socialfeeds-tab-panel-active');
				$btn.addClass('socialfeeds-tab-btn-active');
				$('#' + tab_param).addClass('socialfeeds-tab-panel-active');
			}
		}
	}

	activate_tab_from_url();

	function re_initialize_form_handlers() {
		let $forms = $('#socialfeeds-wizard-form, #socialfeeds-instagram-wizard-form, #socialfeeds-facebook-wizard-form, #socialfeeds-google-wizard-form');
		
		$forms.each(function() {
			let $form = $(this);
			let form_id = $form.attr('id') || '';
			let platform = 'youtube';
			
			if (form_id.indexOf('instagram') !== -1) {
				platform = 'instagram';
			} else if (form_id.indexOf('facebook') !== -1) {
				platform = 'facebook';
			} else if (form_id.indexOf('google') !== -1) {
				platform = 'google_reviews';
			}

			// handle edit icon visibility based on edit mode vs new feed
			if (!$form.find('input[name="edit_id"]').val()) {
				$form.find('.socialfeeds-edit-name-btn').hide();
			} else {
				$form.find('.socialfeeds-edit-name-btn').show();
			}
			
			// handle shortcode for new feeds (pre-save)
			if (!$form.find('input[name="edit_id"]').val()) {
				let existing_client = $form.find('input[name="client_feed_id"]');
				if (!existing_client.length || !existing_client.val()) {
					let pre_id = generate_unique_id();
					if (existing_client.length) {
						existing_client.val(pre_id);
					} else {
						$form.append('<input type="hidden" name="client_feed_id" value="' + pre_id + '">');
					}
					window.socialfeedsEmbedTempId = pre_id;
				}

				let current_id = $form.find('input[name="client_feed_id"]').val();
				if (current_id) {
					let shortcode = '[socialfeeds id="' + current_id + '" platform="' + platform + '"]';
					$form.find('#socialfeeds-top-shortcode').text(shortcode);
					$form.find('.socialfeeds-copy-shortcode').attr('data-shortcode', shortcode).data('shortcode', shortcode);
				}
			}
		});

		// Submit handler for YouTube (and anything not Instagram/Facebook PRO)
		$('#socialfeeds-wizard-form').on('submit', function (e) {
			e.preventDefault();
			let form = $(this);

			if (!form.find('input[name="edit_id"]').val()) {
				let client_input = form.find('input[name="client_feed_id"]');
				if (!client_input.length || !client_input.val()) {
					let gen_id = generate_unique_id();
					if (client_input.length) client_input.val(gen_id);
					else form.append('<input type="hidden" name="client_feed_id" value="' + gen_id + '">');
					window.socialfeedsEmbedTempId = gen_id;
				}
			}
			let form_data = new FormData(this);
			form_data.append('nonce', socialfeedsData.nonce);
			
			if (window.socialfeedsPreviewChannel && window.socialfeedsPreviewChannel.subscriberCount) {
				form_data.append('channel_subscriber_count', window.socialfeedsPreviewChannel.subscriberCount);
			}

			let submit_btn = form.find('button[type="submit"]'),
			original_text = submit_btn.text();
			submit_btn.prop('disabled', true).text('Saving...');

			$.ajax({
				type: 'POST',
				url: socialfeedsData.ajax_url,
				data: form_data,
				processData: false,
				contentType: false,
				success: function (response) {
					if (response.success) {
						show_toast('Feed saved successfully!');

						let saved_id = response.data?.feed_id || form_data.get('client_feed_id') || form_data.get('edit_id');
						let saved_name = response.data?.feed_name || null;
						
						if (saved_id) {
							if (socialfeedsData.existing_ids && !socialfeedsData.existing_ids.includes(saved_id)) {
								socialfeedsData.existing_ids.push(saved_id);
							}

							let $edit_input = form.find('input[name="edit_id"]');
							if (!$edit_input.length) {
								form.append('<input type="hidden" name="edit_id" value="' + saved_id + '">');
							} else {
								$edit_input.val(saved_id);
							}

							let url = new URL(window.location.href);
							url.searchParams.set('edit_id', saved_id);
							if(url.searchParams.get('action') === 'create') url.searchParams.set('action', 'edit');
							window.history.replaceState({}, '', url.toString());

							form.find('.socialfeeds-save-name-btn').attr('data-feed-id', saved_id);
							form.find('.socialfeeds-edit-name-btn').show();
							let $text = $('.socialfeeds-feed-name-text');
							let $input = $('.socialfeeds-feed-name-input');
							
							if(saved_name){
								$text.text(saved_name);
								$input.val(saved_name);
							} else if ($text.length && $text.text().trim() === ''){
								let defaultName = 'Feed - ' + (form.find('select[name="feed_type"]').val() || 'channel') + ' ' + saved_id;
								$text.text(defaultName);
								$input.val(defaultName);
							}
						}
					} else {
						show_toast(response.data.message || 'Error saving feed.', 'error');
					}
				},
				error: function () {
					show_toast('Error saving feed. Please try again.', 'error');
				},
				complete: function () {
					submit_btn.prop('disabled', false).text(original_text);
				}
			});
		});

		$('.socialfeeds-wizard-tab').on('click', function (e) {
			e.preventDefault();
			let tab_name = $(this).data('tab');
			let $form = $(this).closest('form');

			if (tab_name === 'customize') {
				if ($form.attr('id') === 'socialfeeds-wizard-form') {
					fetch_preview('', false);
				}
			}

			$form.find('.socialfeeds-wizard-tab-content').removeClass('active').hide();
			$form.find('.socialfeeds-wizard-tab').removeClass('active');
			$(this).addClass('active');
			$form.find('#socialfeeds-content-' + tab_name).addClass('active').show();
		});
	}

	function fetch_preview(page_token = '', append = false) {
		if (is_fetching) return;
		is_fetching = true;

		let $feed_type_el = $('#socialfeeds-feed-type');
		if (!$feed_type_el.length) { is_fetching = false; return; }
		let feed_type = $feed_type_el.val();

		let input_value = '';
		if (feed_type === 'channel') input_value = $('#socialfeeds-youtube-channel-input').val();
		else if (feed_type === 'playlist') input_value = $('#socialfeeds-youtube-playlist-input').val();
		else if (feed_type === 'search') input_value = $('#socialfeeds-youtube-search-input').val();
		else if (feed_type === 'single-videos') input_value = $('#socialfeeds-youtube-single-videos-input').val();
		else if (feed_type === 'live-streams') input_value = $('#socialfeeds-youtube-live-channel-input').val();
		let videos_per_page = parseInt($('#socialfeeds-youtube-videos-per-page').val()) || 6,
		load_more_count = parseInt($('#socialfeeds-youtube-load-more-count').val()) || 6;

		if (feed_type === 'social-wall') {
			if ($('input[name="socialwall_feeds[]"]:checked').length === 0) {
				alert('Please select at least one feed');
				is_fetching = false;
				return;
			}
		}

		if (feed_type !== 'social-wall' && (!input_value || !input_value.trim())) {
			alert('Please enter a value for ' + feed_type);
			is_fetching = false;
			return;
		}

		let data = {
			action: 'socialfeeds_youtube_preview_show',
			feed_type: feed_type,
			youtube_videos_per_page: append ? load_more_count : videos_per_page,
			pageToken: page_token,
			nonce: socialfeedsData.nonce
		};

		if (feed_type === 'channel') data.channel_id = input_value;
		else if (feed_type === 'playlist') data.playlist_id = input_value;
		else if (feed_type === 'search') data.search_term = input_value;
		else if (feed_type === 'single-videos') data.video_ids = input_value;
		else if (feed_type === 'live-streams') data.channel_id = input_value;

		let preview_grid = $('#socialfeeds-preview-grid'),
		loader_overlay = $('.socialfeeds-wizard-loader-overlay');

		if (!append) {
			preview_grid.empty();
			preview_grid.removeAttr('style'); // Reset styles in case of previous error state
			loader_overlay.addClass('active');
		} else {
			$('#socialfeeds-load-more-btn').addClass('socialfeeds-loading').prop('disabled', true);
		}

		$.ajax({
			url: socialfeedsData.ajax_url,
			type: 'POST',
			data: data,
			dataType: 'json',
			success: function (response) {
				is_fetching = false;
				if (!response.success) {
					// Stop loader and re-enable buttons
					loader_overlay.removeClass('active');
					$('#socialfeeds-load-more-btn').removeClass('socialfeeds-loading').prop('disabled', false).hide();

					// Display notice in live preview
					let msg = response.data.message || 'Unable to fetch preview.';
					if (!append) {
						// Apply centering styles to the GRID container itself to ensure perfect centering
						preview_grid.css({
							'display': 'flex',
							'align-items': 'center',
							'justify-content': 'center',
							'min-height': '350px', // Match roughly wrapper inner height
							'height': '100%',
							'flex-direction': 'column'
						});

						preview_grid.html(
							'<div class="socialfeeds-error-notice" style="text-align:center; padding:20px; color:#d63638; max-width: 80%;">' +
							'<span class="dashicons dashicons-warning" style="font-size:48px; width:48px; height:48px; display:block; margin:0 auto 15px;"></span>' +
							'<h3 style="margin:0 0 10px; font-size:18px;">' + escape_html(msg) + '</h3>' +
							'<p style="font-size:14px; margin:0; color:#646970;">Please check that the entered ID or Username is correct.</p>' +
							'</div>'
						);
					} else {
						show_toast(msg, 'error');
					}
					return;
				}

				loader_overlay.removeClass('active');
				$('#socialfeeds-load-more-btn').removeClass('socialfeeds-loading').prop('disabled', false);
				let items = response.data.items || [];
				next_page_token = response.data.nextPageToken || null;
				window.socialfeedsPreviewChannel = response.data.channel || null;
				if (append) append_preview_items(items);
				else render_preview_items(items);

				render_preview_header();
				set_load_more_visibility();
				set_subscribe_visibility();
			},
			error: function () {
				is_fetching = false;
				loader_overlay.removeClass('active');
				$('#socialfeeds-load-more-btn').removeClass('socialfeeds-loading').prop('disabled', false);
				preview_grid.html('<div class="socialfeeds-no-preview">Error fetching preview</div>');
			}
		});
	}

	function append_preview_items(items) {
		let $grid = $('#socialfeeds-preview-grid');
		items.forEach(item => {
			$grid.append(create_preview_item(item));
		});
		update_preview_style();
	}

	function render_preview_items(items) {
		let $grid = $('#socialfeeds-preview-grid').empty();
		items.forEach(item => {
			$grid.append(create_preview_item(item));
		});
		update_preview_style();
	}

	function create_preview_item(item) {
		let video_id = item.videoId || (item.id && (item.id.videoId || item.id)),
		snippet = item.snippet || item;
		thumbs = snippet.thumbnails || item.thumbnails || {},
		thumb = (thumbs.medium || thumbs.high || thumbs.default || {}).url || `https://i.ytimg.com/vi/${video_id}/hqdefault.jpg`,
		channelSubs = item.channel_subscribers || 0,
		stats = item.statistics || {},
		duration = item.duration || (item.contentDetails && item.contentDetails.duration) || '',
		date = item.publishedAt || snippet.publishedAt || '';

		let $item = $('<div class="socialfeeds-preview-item">')
			.attr('data-video-id', video_id)
			.attr('data-channel-id', snippet.channelId || '')
			.append($('<img class="socialfeeds-preview-thumbnail">').attr('src', safe_url(thumb) || ''))
			.append($('<div class="socialfeeds-preview-title">').text(snippet.title || ''))
			.append($('<div class="socialfeeds-preview-desc">').text((snippet.description || '').substring(0, 60) + ((snippet.description && snippet.description.length > 60) ? '...' : '')).hide());

		if (duration) $item.attr('data-duration', duration);
		if (date) $item.attr('data-date', date);
		if (stats.viewCount) $item.attr('data-views', stats.viewCount);
		if (stats.likeCount) $item.attr('data-likes', stats.likeCount);
		if (stats.commentCount) $item.attr('data-comments', stats.commentCount);
		if (channelSubs) {$item.attr('data-subscribers', channelSubs);}
		$item.on('click', function (e) {
			e.preventDefault();
			e.stopPropagation();

			let $this = $(this),
			video_id = $this.data('video-id'),
			click_action = $('#socialfeeds-youtube-click-action').val() || 'newtab';

			if (click_action === 'newtab') {
				window.open(`https://www.youtube.com/watch?v=${video_id}`, '_blank');
				return;
			}

			if (click_action === 'lightbox') {
				if (!$('#socialfeeds-admin-lightbox-style').length) {
					$('head').append(`
					<style id="socialfeeds-admin-lightbox-style">
						.socialfeeds-admin-modal { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 100000; display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s; pointer-events: none; }
						.socialfeeds-admin-modal.active { opacity: 1; pointer-events: auto; }
						.socialfeeds-admin-backdrop { position: absolute; inset: 0; background: rgba(0,0,0,0.85); }
						.socialfeeds-admin-modal-inner { position: relative; width: 90%; max-width: 900px; aspect-ratio: 16/9; background: #000; box-shadow: 0 20px 50px rgba(0,0,0,0.5); }
						.socialfeeds-admin-close { position: absolute; top: -40px; right: 0; color: #fff; font-size: 30px; line-height: 1; cursor: pointer; background: none; border: none; padding: 10px; }
						.socialfeeds-admin-close:hover { color: #f00; }
					</style>
				`);
				}

				let modal_html = `
				<div class="socialfeeds-admin-modal">
					<div class="socialfeeds-admin-backdrop"></div>
					<div class="socialfeeds-admin-modal-inner">
						<button class="socialfeeds-admin-close">&times;</button>
						<iframe width="100%" height="100%" src="https://www.youtube.com/embed/${encodeURIComponent(video_id)}?autoplay=1&rel=0" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
					</div>
				</div>`;

				let $modal = $(modal_html).appendTo('body');
				requestAnimationFrame(() => $modal.addClass('active'));

				$modal.find('.socialfeeds-admin-close, .socialfeeds-admin-backdrop').on('click', function () {
					$modal.removeClass('active');
					setTimeout(() => $modal.remove(), 300);
				});

				return;
			}

			let $thumb = $this.find('.socialfeeds-thumb');
			if (!$thumb.length) {
				$thumb = $this;
			}

			let is_full_replace = ($thumb[0] === $this[0]);

			$('.socialfeeds-preview-item.socialfeeds-playing').each(function () {
				if (this !== $this[0]) {
					let original = $(this).data('original-html-content'),
					was_full = $(this).data('is-full-replace');

					if (original) {
						if (was_full) {
							$(this).html(original).removeClass('socialfeeds-playing').css('min-height', '');
						} else {
							let $socialfeed_thumb = $(this).find('.socialfeeds-thumb');
							if ($socialfeed_thumb.length) $socialfeed_thumb.html(original);
							$(this).removeClass('socialfeeds-playing');
						}
					}
				}
			});

			if ($this.hasClass('socialfeeds-playing')) return;

			$this
				.data('original-html-content', $thumb.html())
				.data('is-full-replace', is_full_replace)
				.addClass('socialfeeds-playing');

			if (is_full_replace) {
				$this.css('min-height', $this.outerHeight());
			}

			$thumb.html(`
			<iframe
				src="https://www.youtube.com/embed/${encodeURIComponent(video_id)}?autoplay=1&mute=1&rel=0&playsinline=1"
				frameborder="0"
				allow="autoplay; encrypted-media"
				allowfullscreen
				style="width:100%; aspect-ratio:16/9; display:block;">
			</iframe>
		`);
		});

		return $item;
	}

	function render_preview_header() {
		let $header = $('#socialfeeds-preview-header');

		if (!$('#socialfeeds-youtube-header-enabled').is(':checked')) {
			$header.stop(true, true).hide().empty();
			return;
		}

		if (!window.socialfeedsPreviewChannel) {
			$header.stop(true, true).hide().empty();
			return;
		}

		let channel = window.socialfeedsPreviewChannel,
		show_name = $('#socialfeeds-youtube-header-show-channel-name').is(':checked'),
		show_logo = $('#socialfeeds-youtube-header-show-logo').is(':checked'),
		show_desc = $('#socialfeeds-youtube-header-show-description').is(':checked'),
		scheme = $('#socialfeeds-youtube-color-scheme').val() || 'light',
		custom_color = $('#socialfeeds-youtube-custom-color').val() || '#000000',
		show_subscribers = $('#socialfeeds-youtube-header-show-subscribers').is(':checked'),
		subscriberCount = channel.subscriberCount || channel.statistics?.subscriberCount || channel.channel_subscribers || 0;

		let is_dark = (scheme === 'dark');
		if (scheme === 'custom') {
			is_dark = (function (hex) {
				if (!hex || hex.indexOf('#') !== 0) return false;
				let r = parseInt(hex.slice(1, 3), 16),
				g = parseInt(hex.slice(3, 5), 16),
				b = parseInt(hex.slice(5, 7), 16);
				return (r * 0.299 + g * 0.587 + b * 0.114) < 128;
			})(custom_color);
		}

		let text_color = is_dark ? '#ffffff' : '#1d2327',
		desc_color = is_dark ? '#cccccc' : '#646970',
		custom_text = escape_html($('#socialfeeds-youtube-header-text').val()?.trim()),
		title = escape_html(channel.title || channel.snippet?.title || ''),
		description = escape_html(channel.description || channel.snippet?.description || ''),
		thumbnail = escape_url(channel.thumbnail || channel.snippet?.thumbnails?.medium?.url || channel.snippet?.thumbnails?.default?.url || ''),
		banner_url = $('#socialfeeds-youtube-header-banner-url').val()?.trim(),
		show_banner = $('#socialfeeds-youtube-header-show-banner').is(':checked'),
		channel_banner = channel.bannerExternalUrl || channel.brandingSettings?.image?.bannerExternalUrl || '',
		active_device_btn = document.querySelector('.socialfeeds-preview-device-btn.active'),
		preview_width = active_device_btn ? active_device_btn.getAttribute('data-width') : '100%',
		is_mobile = (preview_width === '375'),
		is_tablet = (preview_width === '768'),
		banner_max_height = is_mobile ? '80px' : (is_tablet ? '120px' : '150px'),
		logo_size = is_mobile ? '45px' : (is_tablet ? '50px' : '55px'),
		title_font_size = is_mobile ? '1.1rem' : '1.2rem',
		desc_font_size = is_mobile ? '13px' : '14px',
		header_gap = is_mobile ? '10px' : '12px',
		banner_margin = is_mobile ? '10px' : '15px',
		header_flex_dir = is_mobile ? 'column' : 'row',
		header_align_items = 'center',
		header_text_align = is_mobile ? 'center' : 'left';

		let html = '';

		if (show_banner) {
			let final_banner = escape_url(banner_url || channel_banner);
			if (final_banner) {
				html += `
					<div class="socialfeeds-header-banner" style="margin-bottom:${banner_margin}; border-radius:8px; overflow:hidden; width:100%;">
						<img src="${final_banner}" style="width:100%; height:auto; display:block; object-fit:cover; max-height:${banner_max_height};">
					</div>
				`;
			}
		}

		let show_custom_text = !!custom_text,
		show_channel_desc = !show_custom_text && show_desc && description;

		if (show_logo || show_name || show_desc) {
			html += `
				<div style="display:flex; flex-direction:${header_flex_dir}; align-items:${header_align_items}; gap:${header_gap}; margin-bottom:20px; text-align:${header_text_align};">
					${show_logo && thumbnail
					? `<img src="${thumbnail}" 
							style="width:${logo_size}; height:${logo_size}; border-radius:50%;
							border:1px solid ${is_dark ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.05)'};
							flex-shrink:0;">`
					: ''
				}
					<div style="display: flex; flex-direction: column; justify-content: center;">
						${show_name && title
					? `<div style="font-weight:700; font-size:${title_font_size}; color:${text_color}; line-height:1.2; display:flex; align-items:center; gap:8px;">
								${title}
								${show_subscribers && subscriberCount? `<span style="font-size:${desc_font_size}; font-weight:400; color:${desc_color};">${format_count(subscriberCount)} subscribers</span>`: ''}
							</div>`
					: ''
				}
					${(show_custom_text || show_channel_desc)
					? `<div style="font-size:${desc_font_size}; color:${desc_color}; margin-top:4px;">
							${show_custom_text ? custom_text : description}
						</div>`
					: ''
				}
					</div>
				</div>
			`;
		}

		if (!html.trim()) {
			$header.hide().empty();
			return;
		}

		$header.html(html).css({
			display: 'block',
			border: 'none',
			background: 'transparent',
			padding: '0'
		});
	}

	function set_load_more_visibility() {
		let $btn = $('#socialfeeds-load-more-btn'),
		btn_text = $('#socialfeeds-youtube-load-more-text').val() || 'Load More';
		$btn.text(btn_text);

		if ($('#socialfeeds-youtube-load-more-enabled').is(':checked') && next_page_token) {
			$btn.show();
		} else {
			$btn.hide();
		}
	}

	function set_subscribe_visibility() {
		let $wrap = $('.socialfeeds-subscribe-wrap'),
		$btn = $('#socialfeeds-subscribe-btn');

		if (!$('#socialfeeds-youtube-subscribe-button-enabled').is(':checked')) {
			$wrap.hide();
			return;
		}

		let channel_id = window.socialfeedsPreviewChannel ? window.socialfeedsPreviewChannel.channelId : null;

		if (!channel_id) {
			let $first_item = $('.socialfeeds-preview-item').first();
			if ($first_item.length) {
				channel_id = $first_item.attr('data-channel-id');
			}
		}

		if (!channel_id) {
			$wrap.hide();
			return;
		}

		let subscribe_url = `https://www.youtube.com/channel/${channel_id}?sub_confirmation=1`;
		$btn.attr({
			href: subscribe_url,
			target: '_blank',
			rel: 'noopener noreferrer'
		});

		$wrap.show();
	}

	function init_carousel_controls($grid) {
		if (!$grid.hasClass('carousel-mode')) return;

		// Clean up previous init
		let $wrapper = $grid.parent();
		if ($wrapper.hasClass('socialfeeds-carousel-stage')) {
			// Already wrapped, just remove controls to re-add
			$wrapper.find('.socialfeeds-carousel-nav').remove();
			$wrapper.parent().find('.socialfeeds-carousel-dots').remove();
		} else {
			// Wrap it
			$grid.wrap('<div class="socialfeeds-carousel-stage" style="position:relative;"></div>');
			$wrapper = $grid.parent();
		}
		// $wrapper is now the stage

		let items_to_scroll = 3,
		item_width = $grid.find('.socialfeeds-preview-item').outerWidth(true),
		scroll_one = item_width,
		scroll_three = items_to_scroll * item_width; // Approx

		let $nav = $(`
			<div class="socialfeeds-carousel-nav">
				<button class="socialfeeds-carousel-btn prev"><span class="dashicons dashicons-arrow-left-alt2" style="font-family:dashicons; line-height:inherit;"></span></button>
				<button class="socialfeeds-carousel-btn next"><span class="dashicons dashicons-arrow-right-alt2" style="font-family:dashicons; line-height:inherit;"></span></button>
			</div>
		`);
		$wrapper.append($nav);

		let count = $grid.children().length,
		items_per_page = 3; // Approx defaults
		if ($grid.find('.socialfeeds-preview-item').first().outerWidth() < 240) items_per_page = 4;

		let total_pages = Math.ceil(count / items_per_page),
		$dots = $('<div class="socialfeeds-carousel-dots"></div>');

		for (let i = 0; i < total_pages; i++) {
			let $dot = $('<span class="socialfeeds-dot"></span>');
			if (i === 0) $dot.addClass('active');

			$dot.on('click', () => {
				$grid[0].scrollTo({
					left: i * ($grid.width()), // Scroll full width or page width
					behavior: 'smooth'
				});
				$dots.find('.socialfeeds-dot').removeClass('active');
				$dot.addClass('active');
			});
			$dots.append($dot);
		}

		$wrapper.after($dots);

		$nav.find('.next').on('click', (e) => {
			e.preventDefault();
			$grid[0].scrollBy({ left: $grid.width(), behavior: 'smooth' }); // Scroll one 'page' which is view width
		});
		$nav.find('.prev').on('click', (e) => {
			e.preventDefault();
			$grid[0].scrollBy({ left: -$grid.width(), behavior: 'smooth' });
		});

		// Update dots on scroll
		$grid.on('scroll', function () {
			let page = Math.round($grid.scrollLeft() / $grid.width());
			$dots.find('.socialfeeds-dot').removeClass('active').eq(page).addClass('active');
		});
	}

	function format_count(num) {
		if (num < 1000) return num.toString();

		if (num < 1_000_000) {
			return (num / 1000)
				.toFixed(num >= 10_000 ? 0 : 1)
				.replace(/\.0$/, '') + 'K';
		}

		if (num < 1_000_000_000) {
			return (num / 1_000_000)
				.toFixed(num >= 10_000_000 ? 0 : 1)
				.replace(/\.0$/, '') + 'M';
		}

		return (num / 1_000_000_000)
			.toFixed(1)
			.replace(/\.0$/, '') + 'B';
	}



	function update_preview_style() {
		let $grid = $('#socialfeeds-preview-grid'),
		$preview_wrapper = $('.socialfeeds-preview-box-wrapper');
		if (!$grid.length) return;

		let is_mobile_preview = false,
		active_device_btn = document.querySelector('.socialfeeds-preview-device-btn.active');

		if (active_device_btn) {
			let width = active_device_btn.getAttribute('data-width');
			if (width !== '100%' && parseInt(width, 10) <= 500) {
				is_mobile_preview = true;
			}
		}

		let thumb_size = $('#socialfeeds-youtube-thumb-size').val() || 'medium',
		color_scheme = $('#socialfeeds-youtube-color-scheme').val() || 'inherit',
		hover_effect = $('#socialfeeds-youtube-hover-effect').val() || 'overlay';
		columns = is_mobile_preview ? parseInt($('#socialfeeds-youtube-grid-columns-mobile').val() || 1) : parseInt($('#socialfeeds-youtube-grid-columns-desktop').val() || 3),
		spacing = parseInt($('#socialfeeds-youtube-spacing').val() || 16),
		show_title = $('#socialfeeds-youtube-show-title').is(':checked'),
		show_desc = $('#socialfeeds-youtube-show-desc').is(':checked'),
		show_play_icon = $('#socialfeeds-youtube-show-play-icon').is(':checked'),
		custom_color = $('#socialfeeds-youtube-custom-color').val() || '#000000',
		display_style = $('[name="youtube_display_style"]:checked').val() || 'grid',
		show_duration = $('#socialfeeds-youtube-show-duration').is(':checked'),
		show_date = $('#socialfeeds-youtube-show-date').is(':checked'),
		show_views = $('#socialfeeds-youtube-show-views').is(':checked'),
		show_likes = $('#socialfeeds-youtube-show-likes').is(':checked'),
		show_comments = $('#socialfeeds-youtube-show-comments').is(':checked');

		if (color_scheme === 'dark') {
			$preview_wrapper.css({ background: '#0f0f0f', padding: '20px', borderRadius: '8px' });
		} else if (color_scheme === 'light') {
			$preview_wrapper.css({ background: '#ffffff', padding: '20px', borderRadius: '8px', border: '1px solid #eeeeee' });
		} else if (color_scheme === 'custom') {
			$preview_wrapper[0].style.setProperty('background', custom_color, 'important');
			$preview_wrapper.css({ padding: '20px', borderRadius: '8px' });
		} else {
			$preview_wrapper.css({ background: '', padding: '', borderRadius: '', border: '' });
		}

		let is_dark = (color_scheme === 'dark');
		if (color_scheme === 'custom') {
			is_dark = (function (hex) {
				if (!hex || hex.indexOf('#') !== 0) return false;
				let r = parseInt(hex.slice(1, 3), 16),
				g = parseInt(hex.slice(3, 5), 16),
				b = parseInt(hex.slice(5, 7), 16);
				return (r * 0.299 + g * 0.587 + b * 0.114) < 128;
			})(custom_color);
		}

		let item_text_color = is_dark ? '#ffffff' : '#1d2327',
		item_meta_color = is_dark ? '#cccccc' : '#606060',
		item_bg = (color_scheme === 'dark') ? '#0f0f0f' : ((color_scheme === 'custom') ? 'transparent' : '');

		let size = '180px';
		if (thumb_size === 'small') size = '120px';
		else if (thumb_size === 'large' || thumb_size === 'high') size = '240px';

		if (display_style === 'carousel') {
			$grid.addClass('carousel-mode');

			let items_to_show = is_mobile_preview ? parseInt($('#socialfeeds-youtube-grid-columns-mobile').val() || 1) : parseInt($('#socialfeeds-youtube-grid-columns-desktop').val() || 3);

			let item_width_calc = `calc((100% - ${(items_to_show - 1) * spacing}px) / ${items_to_show})`;
			$grid.css({ display: 'flex', gap: spacing + 'px', overflowX: 'hidden', scrollBehavior: 'smooth' });
			$grid.find('.socialfeeds-preview-item').css({ flex: '0 0 ' + item_width_calc, maxWidth: item_width_calc });
			init_carousel_controls($grid);
		} else {
			$grid.removeClass('carousel-mode');

			// Cleanup carousel wrapper/controls if they exist
			let $parent = $grid.parent();
			if ($parent.hasClass('socialfeeds-carousel-stage')) {
				$parent.find('.socialfeeds-carousel-nav').remove();
				$parent.siblings('.socialfeeds-carousel-dots').remove(); // Dots are siblings of stage
				$grid.unwrap(); // Removes .socialfeeds-carousel-stage, putting grid back in original container
			} else {
				// Fallback cleanup if structure is different
				$grid.parent().find('.socialfeeds-carousel-nav, .socialfeeds-carousel-dots').remove();
				$grid.siblings('.socialfeeds-carousel-dots').remove();
			}

			$grid.find('.socialfeeds-preview-item').css({ flex: '', maxWidth: '' });
			$grid.css({ display: 'grid', gap: spacing + 'px', scrollBehavior: '' });
			if (display_style === 'list') {
				// LIST Layout specific: Center it
				$grid.css({
					gridTemplateColumns: '1fr',
					maxWidth: '850px',
					margin: '0 auto'
				});
			} else {
				// Grid default
				$grid.css({
					gridTemplateColumns: `repeat(${columns}, 1fr)`,
					maxWidth: '',
					margin: ''
				});
			}

			$grid.scrollLeft(0);
		}

		$grid.find('.socialfeeds-preview-item').each(function (idx) {
			let $item = $(this),
			$img = $item.find('img'),
			$title = $item.find('.socialfeeds-preview-title'),
			$desc = $item.find('.socialfeeds-preview-desc');

			$item.css({
				background: item_bg,
				border: 'none',
				borderRadius: '0',
				boxShadow: 'none'
			});

			$title.css('color', item_text_color);
			$desc.css('color', item_meta_color);

			let $thumb = $item.find('.socialfeeds-thumb');
			if (!$thumb.length) {
				$img.wrap('<div class="socialfeeds-thumb"></div>');
				$thumb = $item.find('.socialfeeds-thumb');
			}
			$thumb.css({ position: 'relative', width: '100%', overflow: 'hidden', borderRadius: '0' });

			$item.find('.socialfeeds-preview-meta').remove();
			let $meta = $('<div class="socialfeeds-preview-meta">').css({
				fontSize: '13px',
				color: item_meta_color,
				marginTop: '6px',
				display: 'flex',
				gap: '8px',
				flexWrap: 'wrap'
			});

			if (show_duration && $item.data('duration')) {
				function format_duration(iso) {
					let h = 0, m = 0, s = 0;
					let match = iso.match(/PT(?:(\d+)H)?(?:(\d+)M)?(?:(\d+)S)?/);
					if (match) {
						h = parseInt(match[1] || '0', 10);
						m = parseInt(match[2] || '0', 10);
						s = parseInt(match[3] || '0', 10);
					}
					if (h > 0) {
						return h + ':' + String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
					} else {
						return m + ':' + String(s).padStart(2, '0');
					}
				}

				let $duration = $('<span class="socialfeeds-meta-duration">')
					.text(format_duration($item.data('duration')))
					.css({
						position: 'absolute',
						bottom: '8px',
						right: '8px',
						background: 'rgba(0,0,0,0.85)',
						color: '#fff',
						fontSize: '15px',
						padding: '5px 6px',
						borderRadius: '4px'
					});

				$thumb.append($duration);
			} else {
				$thumb.find('.socialfeeds-meta-duration').remove();
			}

			if (show_views && $item.data('views') !== undefined) {
				const views = parseInt($item.data('views'), 10);
				$meta.append(`<span class="socialfeeds-meta-item">${format_count(views)} views</span>`);
			}

			if (show_likes && $item.data('likes') !== undefined) {
				const likes = parseInt($item.data('likes'), 10);
				$meta.append(`<span class="socialfeeds-meta-item">${format_count(likes)} likes</span>`);
			}

			if (show_comments && $item.data('comments') !== undefined) {
				const comments = parseInt($item.data('comments'), 10);
				$meta.append(`<span class="socialfeeds-meta-item">${format_count(comments)} comments</span>`);
			}

			if (show_date && $item.data('date')) {
				$meta.append(
					`<span class="socialfeeds-meta-item">${new Date($item.data('date')).toLocaleDateString()}</span>`
				);
			}

			$meta.insertAfter($title);
			$desc.insertAfter($meta);

			$img.css({ width: '100%', aspectRatio: '16 / 9', objectFit: 'cover', display: 'block', transition: 'transform .25s ease', borderRadius: '0' });

			$item.off('mouseenter mouseleave');
			$img.css({ transform: '', filter: '' });
			$thumb.find('.socialfeeds-hover-overlay').remove();
			$item.css({ boxShadow: 'none', transform: '', transition: '' });

			if (hover_effect === 'overlay') {
				let $hover_overlay = $('<div class="socialfeeds-hover-overlay"></div>').css({
					position: 'absolute',
					inset: 0,
					background: 'rgba(0,0,0,0.4)',
					opacity: 0,
					transition: 'opacity .25s ease',
					pointerEvents: 'none'
				});

				$thumb.append($hover_overlay);

				$item.on('mouseenter', function () {
					$hover_overlay.css('opacity', 1);
				}).on('mouseleave', function () {
					$hover_overlay.css('opacity', 0);
				});

			} else if (hover_effect === 'scale') {

				$item.on('mouseenter', function () {
					$img.css('transform', 'scale(1.08)');
				}).on('mouseleave', function () {
					$img.css('transform', 'scale(1)');
				});

			} else if (hover_effect === 'shadow') {
				$item.css({ transition: 'box-shadow .25s ease, transform .25s ease' });

				$item.on('mouseenter', function () {
					$item.css({
						boxShadow: '0 12px 30px rgba(0,0,0,0.25)',
						transform: 'translateY(-4px)'
					});
				}).on('mouseleave', function () {
					$item.css({
						boxShadow: 'none',
						transform: ''
					});
				});

			}

			let $overlay = $thumb.find('.socialfeeds-play-overlay');
			if (show_play_icon && !$overlay.length) {
				$overlay = $('<div class="socialfeeds-play-overlay"></div>');
				$thumb.css('position', 'relative').append($overlay);
			}
			if ($overlay.length) $overlay.toggle(show_play_icon);

			$title.toggle(show_title);
			$desc.toggle(show_desc);
		});

		let sub_bg = $('#socialfeeds-youtube-subscribe-bg-color').val() || '#FF0000',
		sub_text = $('#socialfeeds-youtube-subscribe-text-color').val() || '#FFFFFF',
		sub_hover = $('#socialfeeds-youtube-subscribe-hover-color').val() || '#cc0000',
		load_bg = $('#socialfeeds-youtube-load-more-bg-color').val() || '#350ae1',
		load_text = $('#socialfeeds-youtube-load-more-text-color').val() || '#FFFFFF',
		load_hover = $('#socialfeeds-youtube-load-more-hover-color').val() || '#4608e4',
		sub_label = $('#socialfeeds-youtube-subscribe-text').val() || 'Subscribe',
		$subBtn = $('#socialfeeds-subscribe-btn');

		$subBtn
			.text(sub_label)
			.css({ backgroundColor: sub_bg, color: sub_text, transition: 'background-color .25s ease' })
			.off('mouseenter mouseleave')
			.on('mouseenter', function () {
				$(this).css('backgroundColor', sub_hover);
			})
			.on('mouseleave', function () {
				$(this).css('backgroundColor', sub_bg);
			});
		let $loadBtn = $('#socialfeeds-load-more-btn');

		$loadBtn
			.css({ backgroundColor: load_bg, color: load_text, transition: 'background-color .25s ease' })
			.off('mouseenter mouseleave')
			.on('mouseenter', function () {
				$(this).css('backgroundColor', load_hover);
			})
			.on('mouseleave', function () {
				$(this).css('backgroundColor', load_bg);
			});

		$('.socialfeeds-buttons').css({ display: 'flex', justifyContent: 'center', alignItems: 'center', gap: '15px', marginTop: '25px', width: '100%' });
	}

	function init_dynamic_ui() {
		// Listeners for live preview updates
		$('#socialfeeds-youtube-grid-columns-desktop, #socialfeeds-youtube-spacing, #socialfeeds-youtube-header-enabled, #socialfeeds-youtube-header-show-logo, #socialfeeds-youtube-header-show-channel-name, #socialfeeds-youtube-header-show-subscribers, #socialfeeds-youtube-header-show-description, #socialfeeds-youtube-header-text, #socialfeeds-youtube-load-more-enabled, #socialfeeds-youtube-color-scheme, #socialfeeds-youtube-hover-effect, #socialfeeds-youtube-show-title, #socialfeeds-youtube-show-desc, #socialfeeds-youtube-show-play-icon, #socialfeeds-youtube-lazy-load, #socialfeeds-youtube-show-duration, #socialfeeds-youtube-show-date, #socialfeeds-youtube-header-show-channel-name, #socialfeeds-youtube-header-show-logo, #socialfeeds-youtube-header-show-description, #socialfeeds-youtube-show-views, #socialfeeds-youtube-show-likes, #socialfeeds-youtube-show-comments, #socialfeeds-youtube-custom-color, [name="youtube_display_style"], #socialfeeds-youtube-subscribe-bg-color, #socialfeeds-youtube-subscribe-text-color, #socialfeeds-youtube-subscribe-hover-color, #socialfeeds-youtube-subscribe-text, #socialfeeds-youtube-load-more-bg-color, #socialfeeds-youtube-load-more-text-color, #socialfeeds-youtube-load-more-hover-color, #socialfeeds-youtube-load-more-count, #socialfeeds-youtube-header-show-banner, #socialfeeds-youtube-header-banner-url')
		.on('change input', function (e) {
				update_preview_style();
				render_preview_header();
				set_load_more_visibility();
				set_subscribe_visibility();
			});

		// Collapsible fieldsets
		$('.socialfeeds-fieldset legend').each(function () {
			if ($(this).find('.dashicons').length) return;
			$(this).css('cursor', 'pointer').prepend('<span class="dashicons dashicons-arrow-down-alt2" style="margin-right:5px;"></span>');
			$(this).on('click', function () {
				$(this).siblings().slideToggle();
				$(this).find('.dashicons').toggleClass('dashicons-arrow-down-alt2 dashicons-arrow-right-alt2');
			});
		});

		// Media Library Picker
		$('.socialfeeds-wrap').on('click', '.socialfeeds-pick-image', function (e) {
			e.preventDefault();
			let $btn = $(this),
			target = $btn.data('target'),
			$input = $(target);

			let frame = wp.media({
				title: 'Select or Upload Banner',
				button: { text: 'Use this banner' },
				multiple: false
			});

			frame.on('select', function () {
				let attachment = frame.state().get('selection').first().toJSON();
				$input.val(attachment.url).trigger('change');
			});

			frame.open();
		});

		// Embed Modal Copy
		$('#socialfeeds-embed-copy').on('click', function () {
			let text = $('#socialfeeds-embed-shortcode').val();
			navigator.clipboard.writeText(text).then(() => {
				$(this).text('Copied!');
				setTimeout(() => $(this).text('Copy'), 1500);
			});
		});

		$('.socialfeeds-layout-option').on('click', function (e) {
			if ($(this).hasClass('socialfeeds-locked')) {
				e.preventDefault();
				return false;
			}
			update_preview_style();
		});

		// Range sliders
		$('input[type="range"]').on('input', function () {
			$(this).closest('.socialfeeds-control-group').find('.socialfeeds-value-display').text($(this).val() + ' ' + ($(this).data('unit') || ''));
		});

		// Color Scheme Toggle
		$('#socialfeeds-youtube-color-scheme').on('change', function () {
			if ($(this).val() === 'custom') {
				$('#socialfeeds-custom-color-group').slideDown();
			} else {
				$('#socialfeeds-custom-color-group').slideUp();
			}
		});

		// Header Toggle
		$('#socialfeeds-youtube-header-enabled').on('change', function () {
			if ($(this).is(':checked')) {
				$('#socialfeeds-header-options, #socialfeeds-header-logo-options, #socialfeeds-header-desc-options, #socialfeeds-header-text-options').slideDown();
			} else {
				$('#socialfeeds-header-options, #socialfeeds-header-logo-options, #socialfeeds-header-desc-options, #socialfeeds-header-text-options').slideUp();
			}
		});

		// Preview Device Toggles
		$('.socialfeeds-preview-device-btn').on('click', function (e) {
			e.preventDefault();
			let width = $(this).data('width');
			$('.socialfeeds-preview-device-btn').removeClass('active');
			$(this).addClass('active');

			if (width === '100%') {
				$('.socialfeeds-customize-preview').css('width', '');
			} else {
				$('.socialfeeds-customize-preview').css('width', width);
			}

			if (width === '100%') {
				$('.socialfeeds-customize-preview').removeClass('socialfeeds-device-mode');
			} else {
				$('.socialfeeds-customize-preview').addClass('socialfeeds-device-mode');
			}

			update_preview_style();
			render_preview_header();
		});

		// Subscribe Button Toggle
		$('#socialfeeds-youtube-subscribe-button-enabled').on('change', function () {
			set_subscribe_visibility();
		});

		// Desktop preview inputs
		$(document).on('input change', '#socialfeeds-youtube-grid-columns-desktop, #socialfeeds-youtube-grid-columns-mobile', function () {
			update_preview_style();
		});

		// Load More Button
		$('#socialfeeds-load-more-btn').on('click', function (e) {
			e.preventDefault();
			if (!next_page_token) {
				return;
			}
			fetch_preview(next_page_token, true);
		});

		// Load More Button Toggle
		$('#socialfeeds-youtube-load-more-enabled').on('change', function () {
			if ($(this).is(':checked')) {
				$('#socialfeeds-load-more-settings, #socialfeeds-load-more-bg-settings, #socialfeeds-load-more-text-color-settings, #socialfeeds-load-more-hover-settings').slideDown();
			} else {
				$('#socialfeeds-load-more-settings, #socialfeeds-load-more-bg-settings, #socialfeeds-load-more-text-color-settings, #socialfeeds-load-more-hover-settings').slideUp();
			}
		});

		// change load more text
		$('#socialfeeds-youtube-load-more-text').on('input', function () {
			let btn_text = $(this).val();
			$('#socialfeeds-load-more-btn').text(btn_text);
		});

		// ===== Cache settings Save=====
		$('#socialfeeds-cache-settings-form').on('submit', function (e){
			e.preventDefault();

			let form = $(this),
			form_data = new FormData(this);

			form_data.append('nonce', socialfeedsData.nonce);

			let submit_btn = form.find('button[type="submit"]'),
			original_text = submit_btn.text();

			submit_btn.prop('disabled', true).text('Saving...');

			$.ajax({
				type: 'POST',
				url: socialfeedsData.ajax_url,
				data: form_data,
				processData: false,
				contentType: false,
				success: function (response) {
					if (response.success) {
						show_toast(response.data);
					} else {
						show_toast(response.data || 'Error saving cache settings.', 'error');
					}
				},
				error: function () {
					show_toast('Error saving cache settings. Please try again.', 'error');
				},
				complete: function () {
					submit_btn.prop('disabled', false).text(original_text);
				}
			});
		});

		// ===== Clear cache button =====
		$('.socialfeeds-clear-cache').on('click', function (e) {
			e.preventDefault();
			
			if (!confirm('Are you sure you want to clear all caches? This will reset the caching schedule.')) {
				return;
			}

			let btn = $(this),
			original_text = btn.html();
			btn.prop('disabled', true).html('<span class="dashicons dashicons-update socialfeeds-spinning"></span> Clearing...');
			$.ajax({
				type: 'POST',
				url: socialfeedsData.ajax_url,
				data: {
					action: 'socialfeeds_clear_cache',
					nonce: socialfeedsData.nonce
				},
				success: function (response) {
					if (response.success) {
						show_toast('All caches cleared successfully!');
							if (response.data && response.data.next_check_display) {
							$('#socialfeeds-next-check-time').text(response.data.next_check_display);
						}
					} else {
						show_toast(response.data.message || 'Error clearing caches.', 'error');
					}
				},
				error: function () {
					show_toast('Error clearing caches. Please try again.', 'error');
				},
				complete: function () {
					btn.prop('disabled', false).html(original_text);
				}
			});
		});

		// Accordions
		$('.socialfeeds-accordion-header').on('click', function () {
			let item = $(this).closest('.socialfeeds-accordion-item'),
			body = item.find('.socialfeeds-accordion-body');

			item.toggleClass('active');
			body.slideToggle(200);
		});

		$('.socialfeeds-edit-name-btn').on('click', function (e) {
			e.preventDefault();
			let wrapper = $(this).closest('.socialfeeds-inline-name-wrapper');
			wrapper.find('.socialfeeds-feed-name-text').hide();
			wrapper.find('.socialfeeds-feed-name-input').show().focus();
			wrapper.find('.socialfeeds-save-name-btn').show();
			$(this).hide();
		});

		$('.socialfeeds-save-name-btn').on('click', function (e) {
			e.preventDefault();

			let btn = $(this),
			wrapper = btn.closest('.socialfeeds-inline-name-wrapper'),
			newName = wrapper.find('.socialfeeds-feed-name-input').val(),
			feedId = btn.data('feed-id'),
			platform = btn.data('platform'),
			original_text = btn.html();

			btn.prop('disabled', true).html('<span class="dashicons dashicons-update socialfeeds-spinning"></span> Saving..');

			let formData = new FormData();
			formData.append('action', 'socialfeeds_update_feed_name');
			formData.append('nonce', socialfeedsData.nonce);
			formData.append('feed_id', feedId);
			formData.append('platform', platform);
			formData.append('name', newName);

			$.ajax({
				type: 'POST',
				url: socialfeedsData.ajax_url,
				data: formData,
				processData: false,
				contentType: false,
				success: function (response) {
					if (response.success && response.data.feed_id) {
						feedId = response.data.feed_id;
						row.attr('data-feed-id', feedId);
						btn.attr('data-feed-id', feedId);
					}
					let display_name = (response.data && response.data.name) ? response.data.name : newName;
					wrapper.find('.socialfeeds-feed-name-text').empty().append($('<strong>').text(display_name));
				},
				error: function () {
					show_toast('Error saving feed name. Please try again.', 'error');
				},
				complete: function () {
					wrapper.find('.socialfeeds-feed-name-input').hide();
					wrapper.find('.socialfeeds-feed-name-text').show();
					wrapper.find('.socialfeeds-edit-name-btn').show(); 
					btn.hide().prop('disabled', false).html(original_text);
				}
			});
		});

		// Initial load synchronization
		let initial_width = $('.socialfeeds-preview-device-btn.active').data('width') || '100%';
		$('.socialfeeds-customize-preview').css('width', initial_width);

		if (initial_width === '100%') {
			$('.socialfeeds-customize-preview').removeClass('socialfeeds-device-mode');
		} else {
			$('.socialfeeds-customize-preview').addClass('socialfeeds-device-mode');
		}

		update_preview_style();
		render_preview_header();
	}
});