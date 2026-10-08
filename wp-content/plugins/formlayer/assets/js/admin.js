jQuery(document).ready(function($){

	// Toast Notification
	function show_toast(message, type = 'success') {
		let toast = $('<div>')
			.addClass('formlayer-toast')
			.addClass(type) 
			.html(`<span class="dashicons dashicons-${type === 'success' ? 'yes' : 'warning'}"></span> ${message}`);

		$('body').append(toast); 

		toast.fadeIn(300).delay(3000).fadeOut(300, function () {
			toast.remove();
		});
	}

	// State management
	let state = {
		view: (typeof formlayer_admin !== 'undefined' && formlayer_admin.form_id && formlayer_admin.form_id !== '0') ? 'builder' : 'templates',
		template_category: 'all',
		template_search: '',
		field_search: '',
		display_id: null,
		form_title: 'Untitled Form',
		form_settings: {
			notifications: {
				enabled: true,
				to_email: '{admin_email}',
				reply_to: '',
				from_name: 'FormLayer',
				from_email: '{admin_email}',
				bcc: '',
				subject: 'New Form Submission',
				message: 'You have a new submission:\n\n{all_fields}'
			},
			email_confirmation: {
				enabled: false,
				to_email: '',
				reply_to: '{admin_email}',
				from_name: 'FormLayer',
				from_email: '{admin_email}',
				bcc: '',
				subject: 'Thank you for your submission!',
				message: 'Thank you! We have received your submission:\n\n{all_fields}',
				format: 'html'
			},
			confirmations: {
				type: 'message',
				message: 'Thank you for your submission!',
				redirect_url: '',
				hide_form: true
			},
			integrations: {},
			custom_css: ''
		},
		active_tab: 'editor',
		sidebar_tab: 'input-fields',
		fields: [],
		selected_field_id: null,
		categories: (typeof formlayer_admin !== 'undefined' && formlayer_admin.categories) ? formlayer_admin.categories : [
			{ id: 'general', label: 'General Fields', open: true },
			{ id: 'advanced', label: 'Advanced Fields', open: false },
			{ id: 'premium', label: 'Premium Fields', open: false }
		],
		field_types: (typeof formlayer_admin !== 'undefined' && formlayer_admin.fieldTypes) ? formlayer_admin.fieldTypes : [],
		templates: (typeof formlayer_admin !== 'undefined' && formlayer_admin.templates) ? formlayer_admin.templates : []
	};

	// Template engine helper
	function generate_unique_name_attr(base_name, ignore_id = null) {
		let slug = (base_name || '').toLowerCase().replace(/[^a-z0-9]/g, '_').replace(/_+/g, '_').replace(/^_|_$/g, '');
		if (!slug) slug = 'field';
		
		let is_unique = false;
		let name_attr = slug;
		let counter = 1;
		
		while (!is_unique) {
			let exists = (state.fields || []).some(function(f) { 
				return f.id !== ignore_id && (f.name_attr === name_attr || (!f.name_attr && ('field_' + f.id) === name_attr)); 
			});
			if (!exists) {
				is_unique = true;
			} else {
				name_attr = slug + '_' + counter;
				counter++;
			}
		}
		return name_attr;
	}

	const utils = {
		tmpl: function(key, data = {}) {
			let html = (typeof formlayer_admin !== 'undefined' && formlayer_admin.html_templates && formlayer_admin.html_templates[key]) ? formlayer_admin.html_templates[key] : '';
			Object.keys(data).forEach(k => {
				const val = data[k] === undefined || data[k] === null ? '' : data[k];
				html = html.replace(new RegExp(`{{${k}}}`, 'g'), val);
			});
			return html;
		},
		esc_attr: function(str) {
			if(!str) return '';
			return String(str).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/'/g, '&#39;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
		},
		esc_html: function(str) {
			if(!str) return '';
			return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
		}
	};

	/**
	 * FormLayer Admin SPA Tab Handling
	 */
	function handle_tabs(){
		let hash_full = window.location.hash.trim().replace('#', '');
		let hash = hash_full.split('/')[0];
		let $nav = $('#formlayer-main-nav');
		
		// Handle hash or query param
		if(!hash){
			let url_params = new URLSearchParams(window.location.search);
			hash = url_params.get('tab') || 'forms';
		}

		let $target_tab = $('#formlayer-tab-' + hash),
    $nav_item = $nav.find(`a[data-tab="${hash}"]`);

		if($target_tab.length){
			// Hide all tabs
			$('.formlayer-tab-content').hide();
			// Show target tab
			$target_tab.fadeIn(200);

			// Update nav state
			$nav.find('.formlayer-tab-item').removeClass('active');
			$nav_item.addClass('active');

			// Special handling for builder layout
			if(hash === 'formbuilder'){
				$('.formlayer-admin-wrapper').addClass('formlayer-builder-active');
				if(typeof render === 'function'){
					render();
				}
			}else{
				$('.formlayer-admin-wrapper').removeClass('formlayer-builder-active');
				
				// Remove form_id from URL
				const url = new URL(window.location.href);
				if(url.searchParams.has('form_id')){
					url.searchParams.delete('form_id');
					window.history.replaceState({}, document.title, url.toString());
					if(typeof formlayer_admin !== 'undefined'){
						formlayer_admin.form_id = 0;
					}
				}
			}
		}else if(hash === 'forms'){
			// Fallback for empty hash or 'forms'
			$('.formlayer-tab-content').hide();
			$('#formlayer-tab-forms').show();
			$nav.find('.formlayer-tab-item').removeClass('active');
			$nav.find('a[data-tab="forms"]').addClass('active');
		}
	}

	// Tab change listener
	$(window).on('hashchange', function(){
		handle_tabs();
	});

	// Choice Modal State
	let choice_modal_state = {
		mode: 'create', // 'create' | 'edit' | 'template'
		form_id: 0,
		post_id: 0,
		title: '',
		template_id: '',
		template_obj: null
	};

	function open_builder_choice_modal(opts){
		choice_modal_state = $.extend({
			mode: 'create',
			form_id: 0,
			post_id: 0,
			title: '',
			template_id: '',
			template_obj: null
		}, opts);

		let $modal = $('#formlayer-builder-choice-modal');
		if(!$modal.length) return;

		let $title = $('#formlayer-choice-modal-title');
		let $sub = $('#formlayer-choice-modal-subtitle');
		let $btnDash = $('#formlayer-choice-btn-dashboard .btn-text');
		let $btnGuten = $('#formlayer-choice-btn-gutenberg .btn-text');

		// Reset buttons state
		$('#formlayer-choice-btn-dashboard').prop('disabled', false);
		$('#formlayer-choice-btn-gutenberg').prop('disabled', false);
		$('#formlayer-choice-btn-gutenberg').find('.dashicons').attr('class', 'dashicons dashicons-wordpress-alt');

		if(choice_modal_state.mode === 'create'){
			$title.text('Create New Form');
			$sub.text('Choose how you would like to build your new form:');
			$btnDash.text('Open Dashboard Builder');
			$btnGuten.text('Open WordPress Editor');
		} else if(choice_modal_state.mode === 'edit'){
			let name = choice_modal_state.title ? choice_modal_state.title : 'Form';
			$title.text('Edit Form: ' + name);
			$sub.text('Choose which editor you would like to use:');
			$btnDash.text('Open in Dashboard Builder');
			$btnGuten.text('Open in WordPress Editor');
		} else if(choice_modal_state.mode === 'template'){
			let tName = (choice_modal_state.template_obj && choice_modal_state.template_obj.title) ? choice_modal_state.template_obj.title : 'Template';
			$title.text('Use Template: ' + tName);
			$sub.text('Choose where you want to open and customize this template:');
			$btnDash.text('Open in Dashboard Builder');
			$btnGuten.text('Open in WordPress Editor');
		}

		$modal.addClass('active');
	}

	function close_builder_choice_modal(){
		$('#formlayer-builder-choice-modal').removeClass('active');
	}

	$(document).on('click', '#formlayer-choice-modal-close', function(e){
		e.preventDefault();
		close_builder_choice_modal();
	});

	$(document).on('click', '#formlayer-builder-choice-modal', function(e){
		if(e.target === this){
			close_builder_choice_modal();
		}
	});

	// Dashboard Builder Choice Click
	$(document).on('click', '#formlayer-choice-card-dashboard, #formlayer-choice-btn-dashboard', function(e){
		e.preventDefault();
		e.stopPropagation();
		close_builder_choice_modal();

		if(choice_modal_state.mode === 'create'){
			state.view = 'builder';
			state.fields = [];
			state.form_title = 'Untitled Form';
			state.display_id = null;
			formlayer_admin.form_id = 0;
			formlayer_admin.post_id = 0;
			window.location.hash = 'formbuilder/customize';
			render();
		} else if(choice_modal_state.mode === 'edit'){
			let fid = choice_modal_state.form_id || formlayer_admin.form_id;
			let targetUrl = 'admin.php?page=formlayer&tab=formbuilder&form_id=' + fid;
			if(typeof formlayer_admin !== 'undefined' && formlayer_admin.admin_url){
				targetUrl = formlayer_admin.admin_url + targetUrl;
			}
			window.location.href = targetUrl;
		} else if(choice_modal_state.mode === 'template'){
			let t = choice_modal_state.template_obj;
			if(t){
				state.form_title = t.title === 'Start from Scratch' ? 'Untitled Form' : t.title;
				state.fields = t.fields ? JSON.parse(JSON.stringify(t.fields)) : [];
				state.form_settings = t.settings ? JSON.parse(JSON.stringify(t.settings)) : state.form_settings;
				state.view = 'builder';
				render();
			}
		}
	});

	// WordPress Editor Choice Click
	$(document).on('click', '#formlayer-choice-card-gutenberg, #formlayer-choice-btn-gutenberg', function(e){
		e.preventDefault();
		e.stopPropagation();

		if(choice_modal_state.mode === 'create'){
			close_builder_choice_modal();
			window.location.href = formlayer_admin.gutenberg_new_url;
		} else if(choice_modal_state.mode === 'edit'){
			close_builder_choice_modal();
			let pid = choice_modal_state.post_id;
			if(pid){
				let targetUrl = 'post.php?post=' + pid + '&action=edit';
				if(typeof formlayer_admin !== 'undefined' && formlayer_admin.admin_url){
					targetUrl = formlayer_admin.admin_url + targetUrl;
				}
				window.location.href = targetUrl;
			} else {
				window.location.href = formlayer_admin.gutenberg_new_url;
			}
		} else if(choice_modal_state.mode === 'template'){
			let t = choice_modal_state.template_obj;
			if(!t) {
				close_builder_choice_modal();
				return;
			}
			if(choice_modal_state.template_id === 'scratch'){
				close_builder_choice_modal();
				window.location.href = formlayer_admin.gutenberg_new_url;
				return;
			}

			let $btn = $('#formlayer-choice-btn-gutenberg');
			$btn.prop('disabled', true).find('.dashicons').attr('class', 'dashicons dashicons-update formlayer-spin');
			$btn.find('.btn-text').text('Opening WordPress Editor...');

			$.ajax({
				url: formlayer_admin.ajax_url,
				method: 'POST',
				data: {
					action: 'formlayer_create_gutenberg_template',
					nonce: formlayer_admin.nonce,
					title: t.title,
					fields: JSON.stringify(t.fields || []),
					settings: JSON.stringify(t.settings || {})
				},
				success: function(res){
					if(res.success && res.data && res.data.edit_url){
						window.location.href = res.data.edit_url;
					} else {
						close_builder_choice_modal();
						alert(res.data && res.data.message ? res.data.message : 'Error creating form in WordPress Editor.');
					}
				},
				error: function(){
					close_builder_choice_modal();
					alert('Server error occurred while opening WordPress Editor.');
				}
			});
		}
	});

	// Add New Form (Opens Choice Modal)
	$(document).on('click', '#formlayer-add-new-form-btn, #formlayer-add-new-form', function(e){
		e.preventDefault();
		open_builder_choice_modal({ mode: 'create' });
	});

	// Edit Form in table list (Opens Choice Modal)
	$(document).on('click', '.formlayer-edit-form-btn, .formlayer-form-title-link', function(e){
		e.preventDefault();
		let $el = $(this);
		let form_id = $el.data('form-id') || $el.closest('tr').find('.formlayer-row-cb').val();
		let post_id = $el.data('post-id');
		let title = $el.data('title') || $el.text().trim();
		open_builder_choice_modal({
			mode: 'edit',
			form_id: form_id,
			post_id: post_id,
			title: title
		});
	});

	function sync_gutenberg_button(){
		let $btn = $('#formlayer-open-gutenberg');
		if(!$btn.length){
			return;
		}
		$btn.css('display', 'inline-flex');
	}

	$('#formlayer-open-gutenberg').on('click', function(e){
		e.preventDefault();
		const $btn = $(this);

		if(state.active_tab === 'settings'){
			sync_ui_to_settings();
		}

		$btn.prop('disabled', true).html('<span class="dashicons dashicons-update formlayer-spin" style="font-size: 16px; width: 16px; height: 16px; margin-top: 2px;"></span> <span>Opening WordPress Editor...</span>');

		$.ajax({
			url: formlayer_admin.ajax_url,
			method: 'POST',
			data: {
				action: 'formlayer_create_gutenberg_template',
				nonce: formlayer_admin.nonce,
				form_id: formlayer_admin.form_id || 0,
				post_id: formlayer_admin.post_id || 0,
				title: state.form_title || 'Untitled Form',
				fields: JSON.stringify(state.fields || []),
				settings: JSON.stringify(state.form_settings || {})
			},
			success: function(res){
				if(res.success && res.data && res.data.edit_url){
					window.location.href = res.data.edit_url;
				} else {
					$btn.prop('disabled', false).html('<span class="dashicons dashicons-wordpress-alt" style="font-size: 16px; width: 16px; height: 16px; margin-top: 2px;"></span> <span>WordPress Editor</span>');
					alert(res.data && res.data.message ? res.data.message : 'Error opening WordPress Editor.');
				}
			},
			error: function(){
				$btn.prop('disabled', false).html('<span class="dashicons dashicons-wordpress-alt" style="font-size: 16px; width: 16px; height: 16px; margin-top: 2px;"></span> <span>WordPress Editor</span>');
				alert('Server error occurred.');
			}
		});
	});

	// Initial tab load
	handle_tabs();

	// Move all modals to body to escape transformed containers and center correctly
	$('.formlayer-modal-overlay').appendTo('body');
	
	// Select All handler
	$('#formlayer-select-all').on('change', function(){
		$('.formlayer-row-cb').prop('checked', $(this).prop('checked'));
	});

	$('.formlayer-row-cb').on('change', function(){
		if($('.formlayer-row-cb:checked').length === $('.formlayer-row-cb').length){
			$('#formlayer-select-all').prop('checked', true);
		}else{
			$('#formlayer-select-all').prop('checked', false);
		}
	});

	// Bulk Action handler
	$('#formlayer-apply-bulk').on('click', function(e){
		e.preventDefault();
		let action = $('#formlayer-bulk-action').val();
		let selected_ids = $('.formlayer-row-cb:checked').map(function(){
			return $(this).val();
		}).get();

		if(!action){
			show_toast('Please select an action.', 'error');
			return;
		}

		if(selected_ids.length === 0){
			show_toast('Please select at least one form.', 'error');
			return;
		}

		if(!confirm(`Are you sure you want to ${action} selected forms?`)){
			return;
		}

		let $btn = $(this);
		$btn.prop('disabled', true).text('Applying...');

		$.post(formlayer_admin.ajax_url, {
			action: 'formlayer_bulk_action',
			bulk_action: action,
			ids: selected_ids,
			nonce: formlayer_admin.nonce
		}, function(response){
			if(response.success){
				location.reload();
			}else{
				show_toast(response.data.message || 'Error applying bulk action', 'error');
				$btn.prop('disabled', false).text('Apply');
			}
		});
	});

	// Save Settings handler
	$('#formlayer-save-settings').on('click', function(e){
		e.preventDefault();
		let $btn = $(this);
		$status = $('#formlayer-settings-status'),
		original_text = $btn.text();
		
		let settings = {};
		$('input[name], select[name], textarea[name]').each(function(){
			let name = $(this).attr('name');
			if($(this).attr('type') === 'checkbox'){
				settings[name] = $(this).is(':checked') ? '1' : '0';
			}else{
				settings[name] = $(this).val();
			}
		});

		$btn.prop('disabled', true).text('Saving...');
		if($status.length) $status.removeClass('success error').empty();

		$.post(formlayer_admin.ajax_url, {
			action: 'formlayer_save_settings',
			settings: settings,
			nonce: formlayer_admin.nonce
		}, function(response){
			if(response.success){
				show_toast('Settings saved successfully!', 'success');
				$btn.prop('disabled', false).text(original_text);
			}else{
				show_toast(response.data.message || 'Error saving settings', 'error');
				$btn.prop('disabled', false).text(original_text);
			}
		});
	});

	// Captcha Tab Switching
	$('.captcha-tab-item').on('click', function(){
		let target = $(this).data('target');
		$('.captcha-tab-item').removeClass('active');
		$(this).addClass('active');
		
		$('.captcha-pane').removeClass('active');
		$(`#pane-${target}`).addClass('active');
	});

	// Delete form handler
	$('.formlayer-delete-form').on('click', function(e){
		e.preventDefault();
		var form_id = $(this).data('form-id');
		if(!form_id) return;
		
		if(!confirm('Are you sure you want to delete this form? This action cannot be undone.')){
			return;
		}
		
		var $btn = $(this);
		$btn.css('opacity', '0.5');
		
		$.post(formlayer_admin.ajax_url, {
			action: 'formlayer_delete_form',
			form_id: form_id,
			nonce: formlayer_admin.nonce
		}, function(response){
			if(response.success){
				show_toast('Form deleted successfully!', 'success');
				$btn.closest('tr').fadeOut(300, function(){
					$(this).remove();
					// If no rows left, show "No forms found"
					let $tbody = $('#formlayer-tab-forms .formlayer-table tbody');
					if ($tbody.find('tr').length === 0) {
						$tbody.html(`
							<tr>
								<td colspan="7" style="text-align:center; padding:60px 0; color:var(--formlayer-text-muted);">
									<div class="formlayer-empty-title">No forms found</div>
									<p style="margin:0;">Create your first form to start collecting data.</p>
								</td>
							</tr>
						`);
					}
				});
			}else{
				show_toast(response.data.message || 'Error deleting form', 'error');
				$btn.css('opacity', '1');
			}
		});
	});

	let $app = $('#formlayer-formbuilder-app');


	function load_form(id){
		$.ajax({
			url: formlayer_admin.ajax_url,
			method: 'GET',
			data: {
				action: 'formlayer_load_form',
				nonce: formlayer_admin.nonce,
				form_id: id
			},
			success: function(response){
				if(typeof response === 'string'){
					try{ response = JSON.parse(response); } catch(e){}
				}
				if(response.success && response.data.form_data){
					const data = response.data.form_data;
					state.form_title = data.title || 'Untitled Form';
					state.fields = data.fields || [];
					state.form_settings = data.settings || state.form_settings;
					state.display_id = response.data.display_id;
					if(response.data.post_id){
						formlayer_admin.post_id = response.data.post_id;
					}
					sync_gutenberg_button();
				}else if(!response.success){
					console.error('Form load error:', response);
				}
				render();
			},
			error: function(jqXHR, textStatus, errorThrown){
				console.error("AJAX Error details:", textStatus, errorThrown, jqXHR.responseText);
				if(textStatus !== 'parsererror'){
					show_toast('Failed to load form. ' + textStatus, 'error');
				}
				render();
			}
		});
	}

	function render(){
		let current_hash = window.location.hash.replace('#', ''),
		current_tab = current_hash.split('/')[0],
		is_editing = (formlayer_admin.form_id && formlayer_admin.form_id !== '0'),
		should_update_hash = (current_tab === 'formbuilder' || (is_editing && !current_tab));

		if(state.view === 'templates'){
			if(should_update_hash){
				window.location.hash = 'formbuilder';
			}
			$('#formlayer-builder-view').hide();
			$('#formlayer-templates-view').show();
			render_templates();
		}else{
			if(should_update_hash){
				window.location.hash = 'formbuilder/customize';
			}
			$('#formlayer-templates-view').hide();
			$('#formlayer-builder-view').show();
			render_builder();
			sync_gutenberg_button();
		}
	}

	function render_templates(){
		let categories = [
			{ id: 'all', label: 'All Templates', icon: 'dashicons-admin-page' },
			{ id: 'general', label: 'General', icon: 'dashicons-forms' },
			{ id: 'education', label: 'Education', icon: 'dashicons-welcome-learn-more' },
			{ id: 'marketing', label: 'Marketing', icon: 'dashicons-share' },
			{ id: 'crm', label: 'Sales & CRM', icon: 'dashicons-admin-users' },
			{ id: 'feedback', label: 'User Feedback', icon: 'dashicons-awards' },
			{ id: 'hr', label: 'HR & Talent', icon: 'dashicons-businessman' },
			{ id: 'it', label: 'IT & Technical', icon: 'dashicons-laptop' },
			{ id: 'finance', label: 'Commercial & Finance', icon: 'dashicons-money-alt' }
		];

		let search_term = state.template_search.toLowerCase();
		
		let templates_list = (state.templates && state.templates.length) ? state.templates : ((typeof formlayer_admin !== 'undefined' && formlayer_admin.templates) ? formlayer_admin.templates : []);
		
		let filtered = templates_list.filter(function(t){
			let matches_cat = (state.template_category === 'all' || t.cat === state.template_category || t.id === 'scratch');
			let matches_search = t.title.toLowerCase().includes(search_term) || 
								 (t.desc ? t.desc.toLowerCase().includes(search_term) : false);
			return matches_cat && matches_search;
		});

		// Update Sidebar with Counts
		let cats_html = categories.map(function(cat){
			let count = templates_list.filter(function(t){ 
				let is_cat = (cat.id === 'all' ? t.id !== 'scratch' : t.cat === cat.id);
				let is_search = t.title.toLowerCase().includes(search_term) || (t.desc ? t.desc.toLowerCase().includes(search_term) : false);
				return is_cat && is_search;
			}).length;

			return `
				<li class="${state.template_category === cat.id ? 'active' : ''}" data-cat="${cat.id}">
					<div class="formlayer-template-cat-item">
						<span><span class="dashicons ${cat.icon}"></span> ${cat.label}</span>
					</div>
				</li>
			`;
		}).join('');
		$('#formlayer-template-cats-list').html(cats_html);

		let grid_html = filtered.length > 0 ? filtered.map(function(t){
			let badge = '';
			let is_pro = !!t.is_pro;
			let pro_active = !!formlayer_admin.is_pro;

			if(t.id === 'scratch'){
				badge = '<div class="template-header-badge" style="background:var(--fl-primary); color:#fff;">Starter</div>';
			}else if(is_pro && !pro_active){
				badge = '<div class="template-header-badge" style="background:#f59e0b; color:#fff;">PRO</div>';
			}else if(!is_pro){
				badge = `<div class="template-header-badge">${t.cat ? t.cat.charAt(0).toUpperCase() + t.cat.slice(1) : 'General'}</div>`;
			}

			let button_html = (is_pro && !pro_active) 
				? `<button type="button" class="formlayer-btn-upgrade-pro formlayer-btn-upgrade-pro-el">Upgrade to PRO</button>`
				: `<button type="button" class="formlayer-btn-use-template" title="${utils.esc_attr(t.title)}">Use Template</button>`;

			return utils.tmpl('template_card', {
				id: t.id,
				locked_class: is_pro && !pro_active ? 'is-pro-locked' : '',
				badge_html: badge,
				icon: t.icon,
				title: t.title,
				desc: t.desc,
				button_html: button_html
			});
		}).join('') : utils.tmpl('no_templates_found');
		$('#formlayer-templates-grid').html(grid_html);
	}

	function render_builder(){
		let shortcode = state.display_id ? `[formlayer id="${state.display_id}"]` : (formlayer_admin.form_id && formlayer_admin.form_id !== '0' ? `[formlayer id="${formlayer_admin.form_id}"]` : 'Save First....');
		
		$('#formlayer-builder-title').text(state.form_title);
		$('#formlayer-shortcode-val').val(shortcode);

		// Render Canvas Fields
		let canvas_html = state.fields.length === 0 ? render_empty_state() : render_fields();
		$('#formlayer-dropzone').html(canvas_html);

		// Render Sidebar Panes
		render_sidebar_content();
	}

	function render_empty_state(){
		return utils.tmpl('empty_state');
	}

	function refresh_canvas(){
		let canvas_html = state.fields.length === 0 ? render_empty_state() : render_fields();
		$('#formlayer-dropzone').html(canvas_html);
	}

	function render_fields(){
		return state.fields.map(function(field, index){
			let label_placement = field.label_placement || 'top',
			label_style = field.style_label_color ? `style="color:${field.style_label_color};"` : '',
			input_style = field.style_border_radius ? `style="border-radius:${field.style_border_radius}px;"` : '';
			
			let label_html = (!['submit', 'section', 'terms', 'gdpr'].includes(field.type) && label_placement !== 'hidden') ? utils.tmpl('field_label', {
				label_style: label_style,
				label: field.label || '',
				required_mark: field.required ? '<span class="required">*</span>' : ''
			}) : '';

			let actions_html = utils.tmpl('field_actions', {
				move_up_disabled: index === 0 ? 'disabled' : '',
				move_down_disabled: index === state.fields.length - 1 ? 'disabled' : ''
			});

			return utils.tmpl('field_instance', {
				id: field.id,
				active_class: state.selected_field_id === field.id ? 'active' : '',
				label_placement: label_placement,
				container_class: field.container_class || '',
				label_html: label_html,
				input_style: input_style,
				input_html: render_field_input(field),
				help_html: field.help_text ? `<div class="formlayer-field-help">${field.help_text}</div>` : '',
				actions_html: actions_html
			});
		}).join('');
	}

	function render_field_input(field){
		const options = Array.isArray(field.options) ? field.options : (typeof field.options === 'string' ? field.options.split('\n') : ['Option 1', 'Option 2', 'Option 3']);
		const normalized_options = options.map(opt => {
			if(typeof opt === 'string') return { label: opt, value: opt, default: false };
			return opt;
		});
		
		switch(field.type){
			case 'textarea': {
				const rows = field.rows || 3;
				const cols = field.cols || '';
				const t_style = cols ? `width:${cols}px !important;` : 'width:100%;';
				return `<textarea placeholder="${field.placeholder || ''}" rows="${rows}" cols="${cols}" style="${t_style}" disabled>${field.default_value || field.value || ''}</textarea>`;
			}
			case 'dropdown':
				return `
				<select class="formlayer-builder-select" disabled style="width:100%;">
					<option value="">${field.placeholder || 'Select Option'}</option>
					${normalized_options.map(function(opt){ 
						const is_selected = (field.default_value === opt.value || opt.default);
						return `<option ${is_selected ? 'selected' : ''}>${opt.label}</option>`; 
					}).join('')}
				</select>`;
			case 'radio':
				return `
				<div class="formlayer-options-preview">
					${normalized_options.map(function(opt, i){
						const is_checked = (field.default_value === opt.value || opt.default);
						return `
						<label class="formlayer-option-row">
							<input type="radio" name="preview_${field.id}" ${is_checked ? 'checked' : ''} disabled> <span>${opt.label}</span>
						</label>
					`; }).join('')}
				</div>`;
			case 'checkbox':
			case 'multiple':
				return `
				<div class="formlayer-options-preview">
					${normalized_options.map(function(opt, i){
						const is_checked = (Array.isArray(field.default_value) ? field.default_value.includes(opt.value) : (field.default_value === opt.value || opt.default));
						return `
						<label class="formlayer-option-row">
							<input type="checkbox" ${is_checked ? 'checked' : ''} disabled> <span>${opt.label}</span>
						</label>
					`; }).join('')}
				</div>`;
			case 'hidden':
				return utils.tmpl('hidden_field_preview', { value: field.value || '(No Value)' });
			case 'password':
				return `<input type="password" placeholder="${field.placeholder || '********'}" disabled>`;
			case 'terms':
				const t_label_style = field.style_label_color ? `style="color:${field.style_label_color};"` : '';
				return `
					<div class="formlayer-terms-wrap">
						<input type="checkbox" disabled> 
						<span class="formlayer-terms-label" ${t_label_style}>${field.terms_label || 'I agree to the <a href="#">Terms & Conditions</a>'}</span>
					</div>`;
			case 'gdpr':
				const g_label_style = field.style_label_color ? `style="font-size:14px; color:${field.style_label_color};"` : 'style="font-size:14px; color:#475569;"';
				return `
					<div class="formlayer-gdpr-wrap">
						<input type="checkbox" disabled> 
						<div style="line-height:1.4;">
							<div class="formlayer-gdpr-label" ${g_label_style}>${field.gdpr_label || 'Accept GDPR Policy'}</div>
							${field.gdpr_description ? `<div style="color:#64748b; font-size:12px; margin-top:4px;">${field.gdpr_description}</div>` : ''}
						</div>
					</div>`;
			case 'captcha': {
				const isPro = formlayer_admin && formlayer_admin.is_pro;
				const prov_label = { 
					hcaptcha: 'hCaptcha', 
					turnstile: isPro ? 'Cloudflare Turnstile' : 'Turnstile (Pro)', 
					recaptcha: isPro ? 'Google reCAPTCHA' : 'reCAPTCHA (Pro)' 
				}[field.captcha_provider || 'hcaptcha'];
				const theme_label = field.captcha_theme ? field.captcha_theme.charAt(0).toUpperCase() + field.captcha_theme.slice(1) : 'Global Default';
				return utils.tmpl('captcha_preview', { provider: prov_label + ' (' + theme_label + ')' });
			}
			case 'image':
			case 'file':
			case 'camera': {
				const btn_style = `background: ${field.file_btn_bg || '#5525d6'} !important; color: ${field.file_btn_color || '#ffffff'} !important;`;
				return utils.tmpl('file_upload_box', {
					btn_style: btn_style,
					btn_text: 'Choose File',
					chosen_text: 'No file chosen'
				});
			}
			case 'richtext':
				return utils.tmpl('richtext_preview');
			case 'name': {
				let sub_fields = '';
				if(field.enable_first_name !== false) sub_fields += utils.tmpl('sub_field_preview', { type:'text', placeholder: field.placeholder_first || 'First Name', label: field.label_first || 'First Name' });
				if(field.enable_middle_name) sub_fields += utils.tmpl('sub_field_preview', { type:'text', placeholder: field.placeholder_middle || 'Middle Name', label: field.label_middle || 'Middle Name' });
				if(field.enable_last_name !== false) sub_fields += utils.tmpl('sub_field_preview', { type:'text', placeholder: field.placeholder_last || 'Last Name', label: field.label_last || 'Last Name' });
				return utils.tmpl('name_fields_preview', { sub_fields: sub_fields });
			}
			case 'address': {
				let sub_fields = '';
				if(field.enable_street !== false) sub_fields += utils.tmpl('sub_field_preview', { full_width_class:'full-width', type:'text', placeholder: field.placeholder_street || 'Street Address', label: field.label_street || 'Street Address' });
				if(field.enable_city !== false) sub_fields += utils.tmpl('sub_field_preview', { type:'text', placeholder: field.placeholder_city || 'City', label: field.label_city || 'City' });
				if(field.enable_state !== false) sub_fields += utils.tmpl('sub_field_preview', { type:'text', placeholder: field.placeholder_state || 'State / Province', label: field.label_state || 'State / Province' });
				if(field.enable_zip !== false) sub_fields += utils.tmpl('sub_field_preview', { type:'text', placeholder: field.placeholder_zip || 'Zip / Postal Code', label: field.label_zip || 'Zip / Postal Code' });
				if(field.enable_country !== false) {
					sub_fields += `
					<div class="formlayer-sub-field">
						<select class="formlayer-builder-select" disabled style="width:100%;">
							<option>${field.placeholder_country || 'Select Country'}</option>
							<option>United States</option>
							<option>United Kingdom</option>
							<option>Canada</option>
						</select>
						<div class="formlayer-sub-label">${field.label_country || 'Country'}</div>
					</div>`;
				}
				return utils.tmpl('address_grid_preview', { sub_fields: sub_fields });
			}
			case 'number':
				return `<input type="number" placeholder="${field.placeholder || ''}" min="${field.min || ''}" max="${field.max || ''}" disabled>`;
			case 'phone':
				return `<input type="tel" placeholder="${field.placeholder || 'Phone Number'}" disabled>`;
			case 'mask':
				return `<input type="text" placeholder="${field.placeholder || '(+1) 000-0000'}" disabled>`;
			case 'country':
				return `
				<div class="formlayer-select-wrap">
					<span class="dashicons dashicons-admin-site"></span>
					<select class="formlayer-builder-select" disabled style="width:100%;">
						<option>${field.placeholder || 'Select Country'}</option>
						<option ${field.default_value === 'US' || field.default_value === 'United States' ? 'selected' : ''}>United States</option>
						<option ${field.default_value === 'GB' || field.default_value === 'United Kingdom' ? 'selected' : ''}>United Kingdom</option>
						<option ${field.default_value === 'CA' || field.default_value === 'Canada' ? 'selected' : ''}>Canada</option>
						<option ${field.default_value === 'AU' || field.default_value === 'Australia' ? 'selected' : ''}>Australia</option>
						<option ${field.default_value === 'DE' || field.default_value === 'Germany' ? 'selected' : ''}>Germany</option>
						<option ${field.default_value === 'FR' || field.default_value === 'France' ? 'selected' : ''}>France</option>
					</select>
				</div>`;
			case 'date':
				return `
				<div class="formlayer-date-wrap">
					<input type="date" disabled>
					<span class="dashicons dashicons-calendar-alt"></span>
				</div>`;
			case 'rating':
				return `
				<div class="formlayer-rating-preview">
					${[1,2,3,4,5].map(function(i){ return `<span class="dashicons dashicons-star-filled" style="color:#e2e8f0; font-size:24px; width:24px; height:24px;"></span>`; }).join('')}
				</div>`;
			case 'submit': {
				let align = field.btn_align || 'left',
				size = field.btn_size || 'md',
				bg = field.btn_bg_color || '',
				txt = field.btn_text_color || '',
				bg_h = field.btn_bg_hover || '',
				txt_h = field.btn_text_hover || '',
				rad = field.style_border_radius ? field.style_border_radius + 'px' : '';
				
				let b_style = '';
				if(bg) b_style += `background:${bg} !important;`;
				if(txt) b_style += `color:${txt} !important;`;
				if(rad) b_style += `border-radius:${rad} !important;`;
				
				let btn_id = `btn-preview-${field.id}`;
				let hover_style = '';
				if(bg_h || txt_h) {
					hover_style = `<style>#${btn_id}:hover { 
						${bg_h ? `background: ${bg_h} !important;` : ''} 
						${txt_h ? `color: ${txt_h} !important;` : ''} 
					}</style>`;
				}

				let size_classes = { sm: 'btn-sm', md: 'btn-md', lg: 'btn-lg' },
				align_classes = { left: 'align-left', center: 'align-center', right: 'align-right', full: 'align-full' };
				
				return `${hover_style}
				<div class="formlayer-submit-preview ${align_classes[align]}">
					<button id="${btn_id}" class="formlayer-submit-btn ${size_classes[size]}" type="button" style="${b_style}">${field.label || 'Submit'}</button>
				</div>`;
			}
			case 'section':
				return `
				<div style="border-bottom: 2px solid #e2e8f0; padding-bottom: 10px; margin-bottom: 15px;">
					<h3 style="margin:0; font-size:18px; color:#1e293b;">${field.label || 'Section Title'}</h3>
					${field.help_text ? `<p style="margin:5px 0 0 0; font-size:13px; color:#64748b;">${field.help_text}</p>` : ''}
				</div>`;
			default:
				return `<input type="text" placeholder="${field.placeholder || ''}" value="${field.default_value || field.value || ''}" disabled>`;
		}
	}

	function render_sidebar_content(){
		$('.formlayer-sidebar-tab').removeClass('active');
		$(`.formlayer-sidebar-tab[data-tab="${state.sidebar_tab}"]`).addClass('active');
		
		$('.formlayer-sidebar-pane').removeClass('active');
		if(state.sidebar_tab === 'input-fields'){
			$('#formlayer-pane-fields').addClass('active');
			render_fields_palette();
		}else{
			$('#formlayer-pane-customization').addClass('active');
			render_advanced_options();
		}
	}

	function render_fields_palette(){
		let search_term = (state.field_search || '').toLowerCase();
		
		let html = state.categories.map(function(cat){
			let fields = state.field_types.filter(function(f){ 
				let matches_cat = f.category === cat.id;
				let matches_search = !search_term || f.label.toLowerCase().includes(search_term) || f.type.toLowerCase().includes(search_term);
				return matches_cat && matches_search;
			});

			if(fields.length === 0 && search_term) return '';

			let fields_html = fields.map(function(f){
				return utils.tmpl('palette_field', {
					type: f.type,
					icon: f.icon || 'dashicons-admin-customizer',
					label: f.label
				});
			}).join('');

			return utils.tmpl('sidebar_category', {
				open_class: (cat.open || search_term) ? 'open' : '',
				id: cat.id,
				label: cat.label,
				fields_html: fields_html
			});
		}).join('');
		
		let final_html = html || `<div class="formlayer-no-results">No fields found for "${search_term}"</div>`;
		$('#formlayer-builder-categories').html(final_html);
	}

	function render_advanced_options(){
		let field = state.fields.find(function(f){ return f.id === state.selected_field_id; });
		if(!field){
			$('#formlayer-pane-customization').html('<div class="formlayer-no-field-selected">Select a field to customize</div>');
			return;
		}

		let accordion_open = state.accordion_open || 'general';

		let general_content = '';
		
		// Admin Label
		general_content += utils.tmpl('control_group', {
			label: 'Admin Field Label',
			info_html: utils.tmpl('info_icon', { title: 'Used only in the admin panel' }),
			input_html: `<input type="text" class="formlayer-input-full" data-prop="label" value="${field.label || ''}" placeholder="Name">`
		});

		// Placeholder
		if(['text', 'email', 'number', 'textarea', 'password', 'url', 'address', 'tel', 'mask', 'country', 'phone'].includes(field.type)){
			general_content += utils.tmpl('control_group', {
				label: 'Placeholder',
				input_html: `<input type="text" class="formlayer-input-full" data-prop="placeholder" value="${field.placeholder || ''}" placeholder="${field.type === 'country' ? 'Select Country' : 'Placeholder text'}">`
			});
		}

		// Label Placement
		if(!['gdpr', 'submit'].includes(field.type)){
			general_content += utils.tmpl('control_group', {
				label: 'Label Placement',
				info_html: utils.tmpl('info_icon', { title: 'Control where the label appears relative to the input' }),
				input_html: `
					<select class="formlayer-input-full" data-prop="label_placement">
						<option value="top" ${field.label_placement === 'top' ? 'selected' : ''}>Top</option>
						<option value="left" ${field.label_placement === 'left' ? 'selected' : ''}>Left</option>
						<option value="right" ${field.label_placement === 'right' ? 'selected' : ''}>Right</option>
						<option value="hidden" ${field.label_placement === 'hidden' ? 'selected' : ''}>Hidden</option>
					</select>`
			});
		}

		// Options Manager
		if(['dropdown', 'radio', 'checkbox', 'multiple'].includes(field.type)){
			const options_html = (field.options || []).map((opt, idx) => {
				const is_obj = typeof opt === 'object';
				return utils.tmpl('option_edit_row', {
					index: idx,
					default_checked: (is_obj ? opt.default : false) ? 'checked' : '',
					label: is_obj ? opt.label : opt,
					value: is_obj ? opt.value : opt
				});
			}).join('') + `<button id="formlayer-btn-add-option" class="formlayer-btn-outline" style="width:100%; margin-top:10px;"><span class="dashicons dashicons-plus"></span> Add Option</button>`;

			general_content += utils.tmpl('control_group', {
				label: 'Field Options',
				input_html: `<div class="formlayer-options-manager">${options_html}</div>`
			});
		}

		// Submit Button specifics
		if(field.type === 'submit'){
			general_content += utils.tmpl('control_group', {
				label: 'Button Alignment',
				input_html: `
					<select class="formlayer-input-full" data-prop="btn_align">
						<option value="left" ${field.btn_align === 'left' ? 'selected' : ''}>Left</option>
						<option value="center" ${field.btn_align === 'center' ? 'selected' : ''}>Center</option>
						<option value="right" ${field.btn_align === 'right' ? 'selected' : ''}>Right</option>
						<option value="full" ${field.btn_align === 'full' ? 'selected' : ''}>Full Width</option>
					</select>`
			});
			general_content += utils.tmpl('control_group', {
				label: 'Button Size',
				input_html: `
					<select class="formlayer-input-full" data-prop="btn_size">
						<option value="sm" ${field.btn_size === 'sm' ? 'selected' : ''}>Small</option>
						<option value="md" ${field.btn_size === 'md' ? 'selected' : ''}>Medium</option>
						<option value="lg" ${field.btn_size === 'lg' ? 'selected' : ''}>Large</option>
					</select>`
			});
		}

		// GDPR specific
		if(field.type === 'gdpr'){
			general_content += utils.tmpl('control_group', {
				label: 'GDPR Label',
				input_html: `<input type="text" class="formlayer-input-full" data-prop="gdpr_label" value="${field.gdpr_label || 'Accept GDPR Policy'}">`
			});
			general_content += utils.tmpl('control_group', {
				label: 'Policy Description',
				input_html: `<textarea class="formlayer-input-full" data-prop="gdpr_description" style="height:60px;">${field.gdpr_description || ''}</textarea>`
			});
		}

		// Terms specific
		if(field.type === 'terms'){
			general_content += utils.tmpl('control_group', {
				label: 'Terms Label',
				input_html: `<input type="text" class="formlayer-input-full" data-prop="terms_label" value="${field.terms_label || 'I agree to the <a href=\'#\'>Terms & Conditions</a>'}">`
			});
		}

		// Default Value
		if(!['submit', 'section', 'terms', 'gdpr'].includes(field.type)){
			general_content += utils.tmpl('control_group', {
				label: 'Default Value',
				input_html: `<input type="text" class="formlayer-input-full" data-prop="default_value" value="${field.default_value || field.value || ''}" placeholder="Initial value">`
			});
		}

		// Container Class
		general_content += utils.tmpl('control_group', {
			label: 'Container CSS Class',
			input_html: `<input type="text" class="formlayer-input-full" data-prop="container_class" value="${field.container_class || ''}" placeholder="e.g. half-width">`
		});

		// Help Text
		general_content += utils.tmpl('control_group', {
			label: 'Help Message',
			input_html: `<textarea class="formlayer-input-full" data-prop="help_text" style="height:60px;" placeholder="Brief info for users">${field.help_text || ''}</textarea>`
		});

		// Style Customization Section
		let style_html = '<div class="formlayer-divider formlayer-style-divider">Style Customization</div>';
		
		style_html += `
			<div class="formlayer-control-group">
				<div class="formlayer-flex-center-between" style="margin-bottom: 8px;">
					<label class="formlayer-control-label" style="margin-bottom:0 !important;">Label Color</label>
					<input type="color" class="formlayer-input-color" data-prop="style_label_color" value="${field.style_label_color || '#334155'}" style="width:30px; height:30px;">
				</div>
				${['file', 'image', 'camera'].includes(field.type) ? `
				<div class="formlayer-flex-center-between" style="margin-bottom: 8px;">
					<label class="formlayer-control-label" style="margin-bottom:0 !important;">Button Background</label>
					<input type="color" class="formlayer-input-color" data-prop="file_btn_bg" value="${field.file_btn_bg || '#5525d6'}" style="width:30px; height:30px;">
				</div>
				<div style="display:flex; align-items:center; justify-content:space-between;">
					<label class="formlayer-control-label" style="margin-bottom:0 !important;">Button Text Color</label>
					<input type="color" class="formlayer-input-color" data-prop="file_btn_color" value="${field.file_btn_color || '#ffffff'}" style="width:30px; height:30px;">
				</div>` : ''}
			</div>`;

		const general_accordion = utils.tmpl('sidebar_accordion', {
			open_class: accordion_open === 'general' ? 'open' : '',
			id: 'general',
			title: (field.label || 'Field') + ' Settings',
			content_html: general_content + style_html
		});

		let advanced_content = '';

		// Field Name / Merge Tag
		advanced_content += utils.tmpl('control_group', {
			label: 'Name Attribute / Merge Tag',
			info_html: utils.tmpl('info_icon', { title: 'Use {field_name} in Email Notifications to output this field\'s value' }),
			input_html: `<input type="text" class="formlayer-input-full" data-prop="name_attr" value="${field.name_attr || generate_unique_name_attr(field.label, field.id)}" placeholder="${generate_unique_name_attr(field.label, field.id)}">`
		});

		if(field.type === 'name'){
			advanced_content += `
				<div class="formlayer-control-group">
					<label class="formlayer-control-label">Enable Fields</label>
					<div style="display:grid; grid-template-columns: 1fr; gap:8px;">
						<label class="formlayer-flex-center-gap8" style="font-size:13px;"><input type="checkbox" data-prop="enable_first_name" ${field.enable_first_name !== false ? 'checked' : ''}> First Name</label>
						<label class="formlayer-flex-center-gap8" style="font-size:13px;"><input type="checkbox" data-prop="enable_middle_name" ${field.enable_middle_name ? 'checked' : ''}> Middle Name</label>
						<label class="formlayer-flex-center-gap8" style="font-size:13px;"><input type="checkbox" data-prop="enable_last_name" ${field.enable_last_name !== false ? 'checked' : ''}> Last Name</label>
					</div>
				</div>
				<div class="formlayer-control-group">
					<label class="formlayer-control-label">Sub Labels</label>
					<input type="text" class="formlayer-input-full" data-prop="label_first" value="${field.label_first || 'First Name'}" style="margin-bottom:5px;">
					<input type="text" class="formlayer-input-full" data-prop="label_middle" value="${field.label_middle || 'Middle Name'}" style="margin-bottom:5px;">
					<input type="text" class="formlayer-input-full" data-prop="label_last" value="${field.label_last || 'Last Name'}">
				</div>`;
		}

		if(field.type === 'address'){
			advanced_content += `
				<div class="formlayer-control-group">
					<label class="formlayer-control-label">Enable Fields</label>
					<div style="display:grid; grid-template-columns: 1fr; gap:8px;">
						<label class="formlayer-flex-center-gap8" style="font-size:13px;"><input type="checkbox" data-prop="enable_street" ${field.enable_street !== false ? 'checked' : ''}> Street Address</label>
						<label class="formlayer-flex-center-gap8" style="font-size:13px;"><input type="checkbox" data-prop="enable_city" ${field.enable_city !== false ? 'checked' : ''}> City</label>
						<label class="formlayer-flex-center-gap8" style="font-size:13px;"><input type="checkbox" data-prop="enable_state" ${field.enable_state !== false ? 'checked' : ''}> State / Province</label>
						<label class="formlayer-flex-center-gap8" style="font-size:13px;"><input type="checkbox" data-prop="enable_zip" ${field.enable_zip !== false ? 'checked' : ''}> Zip / Postal Code</label>
						<label class="formlayer-flex-center-gap8" style="font-size:13px;"><input type="checkbox" data-prop="enable_country" ${field.enable_country !== false ? 'checked' : ''}> Country List</label>
					</div>
				</div>`;
		}

		if(field.type === 'number'){
			advanced_content += `
				<div class="formlayer-control-group" style="display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
					<div>
						<label class="formlayer-control-label">Min Value</label>
						<input type="number" class="formlayer-input-full" data-prop="min" value="${field.min || ''}">
					</div>
					<div>
						<label class="formlayer-control-label">Max Value</label>
						<input type="number" class="formlayer-input-full" data-prop="max" value="${field.max || ''}">
					</div>
				</div>`;
		}

		if(field.type === 'textarea'){
			advanced_content += utils.tmpl('control_group', {
				label: 'Rows (Height)',
				input_html: `<input type="number" class="formlayer-input-full" data-prop="rows" value="${field.rows || 3}">`
			});
		}

		if(field.type === 'date'){
			advanced_content += utils.tmpl('control_group', {
				label: 'Date Format',
				input_html: `
					<select class="formlayer-input-full" data-prop="date_format">
						<option value="Y-m-d" ${field.date_format === 'Y-m-d' ? 'selected' : ''}>YYYY-MM-DD</option>
						<option value="d/m/Y" ${field.date_format === 'd/m/Y' ? 'selected' : ''}>DD/MM/YYYY</option>
						<option value="m/d/Y" ${field.date_format === 'm/d/Y' ? 'selected' : ''}>MM/DD/YYYY</option>
					</select>`
			});
		}

		if(field.type === 'captcha'){
			const isPro = formlayer_admin && formlayer_admin.is_pro;
			advanced_content += utils.tmpl('control_group', {
				label: 'Provider',
				input_html: `
					<select class="formlayer-input-full" data-prop="captcha_provider">
						<option value="hcaptcha" ${field.captcha_provider === 'hcaptcha' ? 'selected' : ''}>hCaptcha</option>
						<option value="turnstile" ${!isPro ? 'disabled' : ''} ${field.captcha_provider === 'turnstile' ? 'selected' : ''}>${!isPro ? 'Turnstile (Pro)' : 'Cloudflare Turnstile'}</option>
						<option value="recaptcha" ${!isPro ? 'disabled' : ''} ${field.captcha_provider === 'recaptcha' ? 'selected' : ''}>${!isPro ? 'reCAPTCHA (Pro)' : 'reCAPTCHA v2'}</option>
					</select>`
			});
			advanced_content += utils.tmpl('control_group', {
				label: 'Theme',
				input_html: `
					<select class="formlayer-input-full" data-prop="captcha_theme">
						<option value="light" ${field.captcha_theme === 'light' ? 'selected' : ''}>Light</option>
						<option value="dark" ${field.captcha_theme === 'dark' ? 'selected' : ''}>Dark</option>
					</select>`
			});
		}

		if(field.type === 'submit'){
			advanced_content += `
				<div class="formlayer-control-group">
					<label class="formlayer-control-label">Button Styling</label>
					<div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
						<div>
							<label style="font-size:11px; color:#64748b; display:block; margin-bottom:4px;">Background</label>
							<input type="color" class="formlayer-input-color" data-prop="btn_bg_color" value="${field.btn_bg_color || '#5525d6'}" style="width:100%; height:32px;">
						</div>
						<div>
							<label style="font-size:11px; color:#64748b; display:block; margin-bottom:4px;">Text</label>
							<input type="color" class="formlayer-input-color" data-prop="btn_text_color" value="${field.btn_text_color || '#ffffff'}" style="width:100%; height:32px;">
						</div>
						<div>
							<label style="font-size:11px; color:#64748b; display:block; margin-bottom:4px;">Hover Bg</label>
							<input type="color" class="formlayer-input-color" data-prop="btn_bg_hover" value="${field.btn_bg_hover || '#441eb1'}" style="width:100%; height:32px;">
						</div>
						<div>
							<label style="font-size:11px; color:#64748b; display:block; margin-bottom:4px;">Hover Text</label>
							<input type="color" class="formlayer-input-color" data-prop="btn_text_hover" value="${field.btn_text_hover || '#ffffff'}" style="width:100%; height:32px;">
						</div>
					</div>
				</div>
				<div class="formlayer-control-group">
					<label class="formlayer-control-label">Border Radius (px)</label>
					<input type="number" class="formlayer-input-full" data-prop="style_border_radius" value="${field.style_border_radius || '10'}" min="0" max="50">
				</div>`;
		}

		advanced_content += `
			<div class="formlayer-control-group">
				<label class="formlayer-control-label" style="display:flex; align-items:center; gap:8px;">
					<input type="checkbox" data-prop="required" ${field.required ? 'checked' : ''}>
					Required Field
				</label>
			</div>`;

		const advanced_accordion = utils.tmpl('sidebar_accordion', {
			open_class: accordion_open === 'advanced' ? 'open' : '',
			id: 'advanced',
			title: 'Advanced',
			content_html: advanced_content
		});

		$('#formlayer-pane-customization').html(`<div class="formlayer-customization-form">${general_accordion}${advanced_accordion}</div>`);
	}

	// Helper functions for Form Settings & Entries
	function sync_settings_to_ui(){
		const s = state.form_settings;
		$('#form-setting-notif-enabled').prop('checked', s.notifications.enabled);
		$('#form-setting-notif-email').val(s.notifications.to_email);
		$('#form-setting-notif-replyto').val(s.notifications.reply_to || '');
		$('#form-setting-notif-fromname').val(s.notifications.from_name || '');
		$('#form-setting-notif-fromemail').val(s.notifications.from_email || '');
		$('#form-setting-notif-bcc').val(s.notifications.bcc || '');
		$('#form-setting-notif-subject').val(s.notifications.subject);
		$('#form-setting-notif-format').val(s.notifications.format || 'html').trigger('change');
		$('#form-setting-notif-message').val(s.notifications.message);

		// Ensure name_attr is defined for all fields before triggering sync
		(state.fields || []).forEach(function(f){
			if(['submit', 'section', 'gdpr', 'terms'].includes(f.type)) return;
			if (!f.name_attr) {
				f.name_attr = generate_unique_name_attr(f.label, f.id);
			}
		});

		$(document).trigger('formlayer_sync_settings_to_ui', [s, state.fields]);
		$('#form-setting-conf-type').val(s.confirmations.type).trigger('change');
		$('#form-setting-conf-message').val(s.confirmations.message);
		$('#form-setting-conf-url').val(s.confirmations.redirect_url);
		$('#form-setting-conf-hide').prop('checked', s.confirmations.hide_form !== false);
		if(s.integrations){
			const ints = ['slack', 'mailchimp', 'sheets', 'notion', 'trello', 'discord'];
			ints.forEach(function(slug){
				const data = s.integrations[slug] || {};
				$(`#form-setting-int-${slug}-enabled`).prop('checked', !!data.enabled).trigger('change');
				if(slug === 'slack' || slug === 'discord') $(`#form-setting-int-${slug}-webhook`).val(data.webhook || '');
				if(slug === 'mailchimp') $(`#form-setting-int-mailchimp-list`).val(data.list_id || '');
				if(slug === 'sheets') {
					$(`#form-setting-int-sheets-id`).val(data.spreadsheet_id || '');
					$(`#form-setting-int-sheets-name`).val(data.sheet_name || '');
				}
				if(slug === 'notion') $(`#form-setting-int-notion-db`).val(data.database_id || '');
				if(slug === 'trello') $(`#form-setting-int-trello-list`).val(data.list_id || '');
			});
		}
		$('#form-setting-custom-css').val(s.custom_css);
		
		// Render Merge Tags
		let tags_html = `<span class="formlayer-badge-tag" data-tag="{all_fields}">{all_fields}</span>`;
		tags_html += `<span class="formlayer-badge-tag" data-tag="{admin_email}">{admin_email}</span>`;
		tags_html += `<span class="formlayer-badge-tag" data-tag="{form_title}">{form_title}</span>`;
		tags_html += `<span class="formlayer-badge-tag" data-tag="{site_url}">{site_url}</span>`;
		
		(state.fields || []).forEach(function(f){
			if(['submit', 'section', 'gdpr', 'terms'].includes(f.type)) return;
			let name = f.name_attr || ('field_' + f.id);
			let label = f.label || name;
			tags_html += `<span class="formlayer-badge-tag" data-tag="{${name}}" title="${label}">{${name}}</span>`;
		});
		
		$('#formlayer-dynamic-merge-tags').html(tags_html);
	}

	function sync_ui_to_settings(){
		state.form_settings.notifications.enabled = $('#form-setting-notif-enabled').is(':checked');
		state.form_settings.notifications.to_email = $('#form-setting-notif-email').val();
		state.form_settings.notifications.reply_to = $('#form-setting-notif-replyto').val();
		state.form_settings.notifications.from_name = $('#form-setting-notif-fromname').val();
		state.form_settings.notifications.from_email = $('#form-setting-notif-fromemail').val();
		state.form_settings.notifications.bcc = $('#form-setting-notif-bcc').val();
		state.form_settings.notifications.subject = $('#form-setting-notif-subject').val();
		state.form_settings.notifications.format = $('#form-setting-notif-format').val();
		state.form_settings.notifications.message = $('#form-setting-notif-message').val();

		$(document).trigger('formlayer_sync_ui_to_settings', [state.form_settings]);
		state.form_settings.confirmations.type = $('#form-setting-conf-type').val();
		state.form_settings.confirmations.message = $('#form-setting-conf-message').val();
		state.form_settings.confirmations.redirect_url = $('#form-setting-conf-url').val();
		state.form_settings.confirmations.hide_form = $('#form-setting-conf-hide').is(':checked');
		state.form_settings.integrations = state.form_settings.integrations || {};
		['slack', 'mailchimp', 'sheets', 'notion', 'trello', 'discord'].forEach(function(slug){
			const data = { enabled: $(`#form-setting-int-${slug}-enabled`).is(':checked') };
			if(slug === 'slack' || slug === 'discord') data.webhook = $(`#form-setting-int-${slug}-webhook`).val();
			if(slug === 'mailchimp') data.list_id = $(`#form-setting-int-mailchimp-list`).val();
			if(slug === 'sheets') {
				data.spreadsheet_id = $(`#form-setting-int-sheets-id`).val();
				data.sheet_name = $(`#form-setting-int-sheets-name`).val();
			}
			if(slug === 'notion') data.database_id = $(`#form-setting-int-notion-db`).val();
			if(slug === 'trello') data.list_id = $(`#form-setting-int-trello-list`).val();
			state.form_settings.integrations[slug] = data;
		});
		state.form_settings.custom_css = $('#form-setting-custom-css').val();
	}

	function save_form(){
		const $btn = $('.formlayer-btn-save');
		const original_text = $btn.text();
		
		if(state.active_tab === 'settings'){
			sync_ui_to_settings();
		}

		$btn.prop('disabled', true).text('Saving...');

		const data = {
			action: 'formlayer_save_form',
			nonce: formlayer_admin.nonce,
			form_id: formlayer_admin.form_id,
			title: state.form_title,
			fields: JSON.stringify(state.fields),
			settings: JSON.stringify(state.form_settings)
		};

		$.post(formlayer_admin.ajax_url, data, function(response){
			if(response.success){
				let is_new = (!formlayer_admin.form_id || formlayer_admin.form_id === '0');
				if(is_new){
					formlayer_admin.form_id = response.data.form_id;
					state.display_id = response.data.display_id;
				}
				if(response.data.post_id){
					formlayer_admin.post_id = response.data.post_id;
				}
				show_toast('Form saved successfully!', 'success');
				
				// Dynamically add or update row in forms table without reload
				if(response.data.row_html){
					let $tbody = $('#formlayer-tab-forms .formlayer-table tbody');
					if(is_new){
						// Remove empty state row if it exists
						if($tbody.find('.formlayer-empty-title').length){
							$tbody.empty();
						}
						// Prepend the new form row
						$tbody.prepend(response.data.row_html);
					}else{
						// Replace existing row
						let $existing_row = $tbody.find('.formlayer-row-cb[value="' + response.data.display_id + '"]').closest('tr');
						if($existing_row.length){
							$existing_row.replaceWith(response.data.row_html);
						}
					}
				}
				
				render();
			}else{
				show_toast(response.data.message || 'Error saving form', 'error');
			}
			$btn.prop('disabled', false).text(original_text);
		});
	}

	// Template Search
	$('#formlayer-template-search').on('input', function(){
		state.template_search = $(this).val();
		render_templates();
	});

	// Template Category Switch
	$('#formlayer-template-cats-list').on('click', 'li', function(){
		state.template_category = $(this).data('cat');
		render_templates();
	});

	// Field Search
	$('.formlayer-fields-search').on('input', function(){
		state.field_search = $(this).val();
		render_fields_palette();
	});

	// Field Search Keyboard Shortcut (/)
	$(document).on('keydown', function(e){
		if(e.key === '/' && !$(e.target).is('input, textarea, select')){
			e.preventDefault();
			$('.formlayer-fields-search').focus();
		}
	});

	// Template Card Click - Opens choice modal for this template
	$('#formlayer-templates-grid').on('click', '.formlayer-btn-use-template', function(e){
		e.preventDefault();
		e.stopPropagation();
		const $card = $(this).closest('.formlayer-template-card');
		if($card.hasClass('is-pro-locked')) return;
		const id = $card.data('id');
		const t = state.templates.find(function(tmp){ return tmp.id === id; });
		if(!t) return;
		open_builder_choice_modal({
			mode: 'template',
			template_id: id,
			template_obj: t,
			title: t.title
		});
	});

	// Template Card Body Click
	$('#formlayer-templates-grid').on('click', '.formlayer-template-card', function(e){
		if($(e.target).closest('.formlayer-btn-upgrade-pro, .formlayer-btn-use-template').length) return;
		if($(this).hasClass('is-pro-locked')) return;
		const id = $(this).data('id');
		const t = state.templates.find(function(tmp){ return tmp.id === id; });
		if(!t) return;
		open_builder_choice_modal({
			mode: 'template',
			template_id: id,
			template_obj: t,
			title: t.title
		});
	});

	// Back to Templates
	$('.formlayer-btn-back-templates').on('click', function(){
		if(confirm('Are you sure you want to go back? Unsaved changes will be lost.')){
			state.view = 'templates';
			state.fields = [];
			formlayer_admin.form_id = 0;
			render();
		}
	});

	// Sidebar Tab
	$('.formlayer-sidebar-tab').on('click', function(){
		state.sidebar_tab = $(this).data('tab');
		render_sidebar_content();
	});

	// Category Header Toggle
	$('#formlayer-builder-categories').on('click', '.formlayer-category-header', function(){
		const $cat = $(this).closest('.formlayer-category');
		const cat_id = $cat.data('cat');
		const cat = state.categories.find(function(c){ return c.id === cat_id; });
		if(cat){
			cat.open = !cat.open;
			$cat.toggleClass('open');
		}
	});

	// Palette Field Click
	$('#formlayer-builder-categories').on('click', '.formlayer-palette-field', function(){
		const type = $(this).data('type');
		const field_type = state.field_types.find(function(f){ return f.type === type; });
		if(field_type){
			const new_field = {
				id: 'f' + Date.now(),
				type: type,
				label: field_type.label,
				required: false,
				options: ['Option 1', 'Option 2', 'Option 3']
			};
			new_field.name_attr = generate_unique_name_attr(new_field.label, new_field.id);
			state.fields.push(new_field);
			state.selected_field_id = new_field.id;
			render();
		}
	});

	// Field Instance Click
	$('#formlayer-dropzone').on('click', '.formlayer-field-instance', function(e){
		if($(e.target).closest('.formlayer-field-actions').length) return;
		state.selected_field_id = $(this).data('id');
		render();
	});

	// Field Delete
	$('#formlayer-dropzone').on('click', '.formlayer-field-delete', function(){
		const id = $(this).closest('.formlayer-field-instance').data('id');
		state.fields = state.fields.filter(function(f){ return f.id !== id; });
		if(state.selected_field_id === id) state.selected_field_id = null;
		render();
	});

	// Field Clone
	$('#formlayer-dropzone').on('click', '.formlayer-field-clone', function(){
		const id = $(this).closest('.formlayer-field-instance').data('id');
		const idx = state.fields.findIndex(function(f){ return f.id === id; });
		if(idx !== -1){
			const cloned = JSON.parse(JSON.stringify(state.fields[idx]));
			cloned.id = 'f' + Date.now();
			cloned.name_attr = generate_unique_name_attr(cloned.label, cloned.id);
			state.fields.splice(idx + 1, 0, cloned);
			render();
		}
	});

	// Field Move Up
	$('#formlayer-dropzone').on('click', '.formlayer-field-move-up', function(){
		const id = $(this).closest('.formlayer-field-instance').data('id');
		const idx = state.fields.findIndex(function(f){ return f.id === id; });
		if(idx > 0){
			const temp = state.fields[idx];
			state.fields[idx] = state.fields[idx - 1];
			state.fields[idx - 1] = temp;
			render();
		}
	});

	// Field Move Down
	$('#formlayer-dropzone').on('click', '.formlayer-field-move-down', function(){
		const id = $(this).closest('.formlayer-field-instance').data('id');
		const idx = state.fields.findIndex(function(f){ return f.id === id; });
		if(idx !== -1 && idx < state.fields.length - 1){
			const temp = state.fields[idx];
			state.fields[idx] = state.fields[idx + 1];
			state.fields[idx + 1] = temp;
			render();
		}
	});

	// Customization Form Inputs
	$('#formlayer-pane-customization').on('input change', '.formlayer-customization-form input, .formlayer-customization-form select, .formlayer-customization-form textarea', function(){
		let prop = $(this).data('prop');
		if(!prop) return;
		let field = state.fields.find(function(f){ return f.id === state.selected_field_id; });
		if(field){
			if($(this).attr('type') === 'checkbox'){
				field[prop] = $(this).is(':checked');
			}else{
				field[prop] = $(this).val();
			}
			refresh_canvas();
		}
	});

	// Accordion Header
	$('#formlayer-pane-customization').on('click', '.formlayer-accordion-header', function(){
		let $acc = $(this).closest('.formlayer-accordion');
		state.accordion_open = $acc.data('accordion');
		$('.formlayer-accordion').removeClass('open');
		$acc.addClass('open');
	});

	// Add Option Button
	$('#formlayer-pane-customization').on('click', '#formlayer-btn-add-option', function(){
		let field = state.fields.find(function(f){ return f.id === state.selected_field_id; });
		if(field){
			field.options = field.options || [];
			field.options.push({ label: 'New Option', value: 'new_option', default: false });
			render_advanced_options();
			refresh_canvas();
		}
	});

	// Remove Option Button
	$('#formlayer-pane-customization').on('click', '.formlayer-btn-remove-option', function(){
		let idx = $(this).closest('.formlayer-option-edit-row').data('index'),
		field = state.fields.find(function(f){ return f.id === state.selected_field_id; });
		if(field && field.options){
			field.options.splice(idx, 1);
			render_advanced_options();
			refresh_canvas();
		}
	});

	// Option Row Inputs
	$('#formlayer-pane-customization').on('input change', '.formlayer-option-label, .formlayer-option-value, .formlayer-option-default', function(){
		let $row = $(this).closest('.formlayer-option-edit-row'),
		idx = $row.data('index'),
		field = state.fields.find(function(f){ return f.id === state.selected_field_id; });
		if(field && field.options){
			let opt = field.options[idx];
			if(typeof opt === 'string'){
				field.options[idx] = { label: opt, value: opt, default: false };
			}
			
			if($(this).hasClass('formlayer-option-label')) field.options[idx].label = $(this).val();
			if($(this).hasClass('formlayer-option-value')) field.options[idx].value = $(this).val();
			if($(this).hasClass('formlayer-option-default')){
				const is_checked = $(this).is(':checked');
				if(field.type === 'radio' || field.type === 'dropdown'){
					field.options.forEach(o => o.default = false);
					field.default_value = is_checked ? field.options[idx].value : '';
				}
				field.options[idx].default = is_checked;
			}
			refresh_canvas();
		}
	});

	// Save Button
	$('.formlayer-btn-save').on('click', function(){
		save_form();
	});

	// Builder Tab Switch
	$('.formlayer-builder-tab').on('click', function(){
		state.active_tab = $(this).data('tab');
		$('.formlayer-builder-tab').removeClass('active');
		$(this).addClass('active');
		
		$('.formlayer-builder-pane').hide();
		$(`#formlayer-pane-${state.active_tab}`).show();
		
		if(state.active_tab === 'settings') sync_settings_to_ui();
		if(state.active_tab === 'entries' && window.formlayerAdminPro && typeof window.formlayerAdminPro.load_entries === 'function') {
			window.formlayerAdminPro.load_entries();
		}
	});

	// Form Settings Modal Open
	$('#formlayer-btn-form-settings').on('click', function(){
		sync_settings_to_ui();
		$('#formlayer-form-settings-modal').addClass('active');
	});

	// Modal Close
	$('.formlayer-modal-close, .formlayer-modal-overlay').on('click', function(e){
		if($(e.target).hasClass('formlayer-modal-overlay') || $(e.target).hasClass('formlayer-modal-close')){
			$('.formlayer-modal-overlay').removeClass('active');
		}
	});

	// Settings Section Switching
	$('.formlayer-modal-sidebar').on('click', 'li', function(){
		let section = $(this).data('section');
		$('.formlayer-modal-sidebar li').removeClass('active');
		$(this).addClass('active');
		
		$('.formlayer-settings-section').hide();
		$(`#formlayer-settings-${section}`).show();
	});

	// Apply Settings
	$('#formlayer-settings-apply-btn').on('click', function(){
		sync_ui_to_settings();
		$('#formlayer-form-settings-modal').removeClass('active');
	});

	// Device Toggles
	$('.formlayer-device-btn').on('click', function(){
		let device = $(this).data('device');
		$('.formlayer-device-btn').removeClass('active');
		$(this).addClass('active');
		
		$('.formlayer-canvas-inner').removeClass('desktop tablet mobile').addClass(device);
	});

	// Fullscreen Toggle
	$('.formlayer-fullscreen-toggle').on('click', function(){
		$('.formlayer-admin-wrapper').toggleClass('formlayer-builder-fullscreen');
	});

	// Insert Merge Tag
	$('#formlayer-dynamic-merge-tags').on('click', '.formlayer-badge-tag', function(){
		let tag = $(this).data('tag');
		let $textarea = $('#form-setting-notif-message');
		let pos = $textarea.prop('selectionStart');
		let val = $textarea.val();
		$textarea.val(val.substring(0, pos) + tag + val.substring(pos));
		$textarea.prop('selectionStart', pos + tag.length);
		$textarea.prop('selectionEnd', pos + tag.length);
		$textarea.focus();
		// Trigger change to sync state later if needed
		$textarea.trigger('input');
	});

	// Copy Shortcode
	$('.formlayer-copy-shortcode').on('click', function(){
		let $input = $('#formlayer-shortcode-val');
		$input.select();
		document.execCommand('copy');
		
		let original_text = $(this).html();
		$(this).html('<span class="dashicons dashicons-yes"></span> Copied!');
		setTimeout(() => {
			$(this).html(original_text);
		}, 2000);
	});

	// Copy Shortcode from List
	$('.formlayer-copy-shortcode-list').on('click', function (e){
		e.preventDefault();
		let shortcode = $(this).data('shortcode');
		if(!shortcode) return;

		let $temp = $('<input>');
		$('body').append($temp);
		$temp.val(shortcode).select();
		document.execCommand('copy');
		$temp.remove();

		let $icon = $(this).find('.dashicons');
		$icon.removeClass('dashicons-admin-page').addClass('dashicons-yes');
		show_toast('Shortcode copied to clipboard!', 'success');

		setTimeout(() => {
			$icon.removeClass('dashicons-yes').addClass('dashicons-admin-page');
		}, 2000);
	});

	// Edit Title
	$('#formlayer-edit-title-btn').on('click', function(){
		$('#formlayer-builder-title').hide();
		$('#formlayer-builder-title-input').val(state.form_title).show().focus();
		$(this).hide();
	});

	$('#formlayer-builder-title-input').on('blur', function(){
		let new_title = $(this).val().trim() || 'Untitled Form';
		state.form_title = new_title;
		$('#formlayer-builder-title').text(new_title).show();
		$(this).hide();
		$('#formlayer-edit-title-btn').show();
	});

	$('#formlayer-builder-title-input').on('keypress', function(e){
		if(e.which === 13){
			$(this).blur();
		}
	});

	// Confirmation Type Toggle
	$('#form-setting-conf-type').on('change', function(){
		if($(this).val() === 'message'){
			$('#conf-group-message').show();
			$('#conf-group-redirect').hide();
		}else{
			$('#conf-group-message').hide();
			$('#conf-group-redirect').show();
		}
	});

	// Integration Toggle
	$('input[id^="form-setting-int-"][id$="-enabled"]').on('change', function(){
		let slug = $(this).attr('id').replace('form-setting-int-', '').replace('-enabled', '');
		if($(this).is(':checked')){
			$(`#${slug}-integration-fields`).show();
		}else{
			$(`#${slug}-integration-fields`).hide();
		}
	});

	// Migration Feature
	$('#formlayer-migration-source').on('change', function(){
		let source = $(this).val();
		if(!source){
			$('#formlayer-migration-forms-container').hide();
			return;
		}

		$('#formlayer-migration-forms-list').html('<div style="padding:15px; color:#64748b;">Loading forms...</div>');
		$('#formlayer-migration-forms-container').show();
		$('#formlayer-btn-run-migration').prop('disabled', true);

		$.get(formlayer_admin.ajax_url, {
			action: 'formlayer_get_migration_source_forms',
			source: source,
			nonce: formlayer_admin.nonce
		}, function(response){
			if(response.success && response.data.forms.length > 0){
				let html = '';
				response.data.forms.forEach(function(f){
					html += `
					<label style="display: flex; align-items: center; gap: 10px; padding: 8px 12px; background: #fff; border: 1px solid #e2e8f0; border-radius: 6px; cursor: pointer; user-select: none;">
						<input type="checkbox" class="formlayer-migration-cb" value="${f.id}" style="margin: 0; width: 16px; height: 16px; accent-color: #3b82f6;">
						<span style="font-weight: 600; color: #334155; font-size: 13px;">${f.title}</span>
						<span style="color: #64748b; font-size: 11px; margin-left: auto; background: #f1f5f9; padding: 2px 6px; border-radius: 4px;">ID: #${f.id}</span>
					</label>`;
				});
				$('#formlayer-migration-forms-list').html(html);
				$('#formlayer-btn-run-migration').prop('disabled', false);
			} else {
				$('#formlayer-migration-forms-list').html('<div style="padding:15px; color:#ef4444;">No forms found or plugin not active.</div>');
			}
		});
	});

	$('#formlayer-btn-run-migration').on('click', function(e){
		e.preventDefault();
		let source = $('#formlayer-migration-source').val();
		let ids = $('.formlayer-migration-cb:checked').map(function(){ return $(this).val(); }).get();

		if(ids.length === 0){
			alert('Please select at least one form to migrate.');
			return;
		}

		let $btn = $(this);
		let originalHtml = $btn.html();
		$btn.prop('disabled', true).html('<span class="dashicons dashicons-update" style="animation: fl_spin 1s linear infinite; margin-top:2px;"></span> Migrating...');

		$('#formlayer-migration-status').hide().removeClass('notice-success notice-error').html('');

		$.post(formlayer_admin.ajax_url, {
			action: 'formlayer_run_form_migration',
			source: source,
			form_ids: ids,
			nonce: formlayer_admin.nonce
		}, function(response){
			$btn.prop('disabled', false).html(originalHtml);
			if(response.success){
				let result = response.data;
				let msg = `<p style="font-weight: 600; color: #15803d; margin: 0 0 10px 0;">Successfully migrated ${result.migrated_count} form(s)!</p><ul style="margin: 0; padding-left: 20px; list-style-type: disc;">`;
				result.migrated_list.forEach(function(f){
					msg += `<li><strong style="color:#0f172a;">${f.title}</strong> - <a href="${formlayer_admin.admin_page_url}&action=edit&form_id=${f.id}" style="color:#3b82f6; text-decoration:underline; font-weight:600;">Edit Migrated Form</a></li>`;
				});
				msg += '</ul>';
				$('#formlayer-migration-status').removeClass().css({background:'#f0fdf4', borderLeft:'4px solid #22c55e', color: '#166534'}).html(msg).show();
				
				// Uncheck source list
				$('.formlayer-migration-cb').prop('checked', false);
			} else {
				$('#formlayer-migration-status').removeClass().css({background:'#fef2f2', borderLeft:'4px solid #ef4444', color: '#991b1b'}).html(`<p style="margin:0;">${response.data.message || 'Migration failed.'}</p>`).show();
			}
		});
	});

	// Initialize
	if(formlayer_admin.form_id && formlayer_admin.form_id !== '0'){
		load_form(formlayer_admin.form_id);
	}else{
		render();
	}

});