jQuery(function ($) {

	let $modal = $('#sharemypost-modal-overlay'),
		admin_data = window.sharemypost_data || {},
		$main_tabs = $('.sharemypost-nav-tab'),
		$sub_tabs = $('.sharemypost-sub-tab'),
		$tab_contents = $('.sharemypost-tab-content'),
		$sub_panels = $('.sharemypost-sub-panel'),
		$sub_tabs_wrapper = $('.sharemypost-sub-tabs-wrapper'),
		$inline_color_type_select = $('#sharemypost_inline_color_type_select'),
		$inline_custom_color_container = $('#sharemypost_inline_custom_color_container');

	// ===== DEFAULT ACTIVE TABS =====
	let saved_main_tab = window.localStorage ? window.localStorage.getItem('sharemypost_active_main_tab') : null;
	let saved_sub_tab = window.localStorage ? window.localStorage.getItem('sharemypost_active_sub_tab') : null;

	$main_tabs.filter('[data-tab="' + (saved_main_tab || 'options') + '"]').addClass('active');

	let defaultSubTab = saved_sub_tab || 'inline_content';
	if (!$sub_tabs.filter('[data-subtab="' + defaultSubTab + '"]').length) {
		defaultSubTab = 'inline_content';
	}
	$sub_tabs.filter('[data-subtab="' + defaultSubTab + '"]').addClass('active');

	let $active_main = $main_tabs.filter('.active');
	if (!$active_main.length) {
		let $options_tab = $main_tabs.filter('[data-tab="options"]');
		if ($options_tab.length) {
			$options_tab.addClass('active');
			$active_main = $options_tab;
		}
	}

	if ($active_main.length && $active_main.data('tab') !== 'options') {
		$sub_tabs_wrapper.hide();
	} else {
		$sub_tabs_wrapper.show();
		$sub_panels.hide();
		let $active_sub = $sub_tabs.filter('.active');
		if ($active_sub.length) {
			$('#subtab-' + $active_sub.data('subtab')).show();
		} else {
			$('#subtab-inline_content').show();
		}
	}

	// ===== TAB HANDLING =====
	$('.sharemypost-nav-tab, .sharemypost-sub-tab').on('click', function (e) {
		e.preventDefault();
		let $this = $(this),
			is_main = $this.hasClass('sharemypost-nav-tab');

		if (is_main) {
			$tab_contents.hide();
			$('#sharemypost-' + $this.data('tab')).show();
			$main_tabs.removeClass('active');
			
			if ($this.data('tab') === 'options') {
				$sub_tabs_wrapper.show();
				$sub_panels.hide();
				let $active_sub = $sub_tabs.filter('.active');
				if ($active_sub.length) {
					$('#subtab-' + $active_sub.data('subtab')).show();
				} else {
					$('#subtab-inline_content').show();
				}
			} else {
				$sub_tabs_wrapper.hide();
			}
		} else {
			$sub_panels.hide();
			$('#subtab-' + $this.data('subtab')).show();
			$sub_tabs.removeClass('active');
		}
		$this.addClass('active');

		if (window.localStorage) {
			if (is_main) {
				window.localStorage.setItem('sharemypost_active_main_tab', $this.data('tab'));
			} else {
				window.localStorage.setItem('sharemypost_active_sub_tab', $this.data('subtab'));
			}
		}
	});

	// ===== INLINE COLOR TOGGLE =====
	$inline_color_type_select.on('change', function () {
		$inline_custom_color_container.toggle(this.value === 'custom');
		update_button_preview();
	}).trigger('change');

	// ===== LIVE PREVIEW =====
	function update_button_preview() {
		let $panel = $('.sharemypost-sub-panel:visible').first();
		if (!$panel.length) {
			$panel = $('#subtab-inline_content');
		}
		let $preview_inputs = $panel.find('.sharemypost-preview-input'),
			$preview = $panel.find('#sharemypost-button-preview');

		if (!$preview_inputs.length || !$preview.length) {
			return;
		}

		let config = {};
		$preview_inputs.each(function () {
			let $el = $(this);
			config[$el.data('preview-attr')] = $el.val();
		});

		let shape = config.shape || 'rounded',
			size = parseInt(config.size, 10) || 38,
			space = parseInt(config.space, 10) || 10,
			style = config.style || 'filled';

		let color_type = 'original',
    button_color = '#1f75e1';

		$preview.removeClass(function (index, class_name) {
			return (class_name.match(/(^|\s)sharemypost-(shape|style|color-type)-\S+/g) || []).join(' ');
		});

		$preview.addClass(`sharemypost-shape-${shape} sharemypost-style-${style}`);

		let preview_style = $preview[0].style;

		if (color_type === 'custom') {
			$preview.addClass('sharemypost-color-type-custom');
			preview_style.setProperty('--sharemypost-button-color', button_color);
		} else {
			preview_style.removeProperty('--sharemypost-button-color');
		}

		// Size + Gap
		preview_style.setProperty('--sharemypost-button-gap', space + 'px');
		preview_style.setProperty('--sharemypost-button-size', size + 'px');
		preview_style.setProperty('--sharemypost-button-icon-size', Math.max(12, Math.round(size * 0.55)) + 'px');

		let is_fixed_shape = ['circle', 'drop'].includes(shape),
			font_size = Math.max(10, Math.round(size * 0.33)) + 'px',
			padding_value = `${Math.max(6, Math.round(size * 0.22))}px ${Math.max(10, Math.round(size * 0.35))}px`;

		$preview.find('.sharemypost-share-button').each(function () {
			let btn_style = this.style;
			btn_style.setProperty('--sharemypost-button-font-size', font_size);
			btn_style.setProperty('--sharemypost-button-padding', padding_value);

			if (is_fixed_shape) {
				btn_style.width = size + 'px';
				btn_style.height = size + 'px';
				btn_style.padding = '0';
			} else {
				btn_style.width = 'auto';
				btn_style.height = 'auto';
				btn_style.padding = '';
			}
		});
	}

	// ===== PREVIEW EVENTS =====
	$('.sharemypost-preview-input').on('input change keyup', update_button_preview);
	update_button_preview();

	// ===== MODALS =====
	$('.sharemypost-network-universal').on('click', function (e) {
		e.preventDefault();
		if ($modal.length) {
			$modal.css('display', 'flex').hide().fadeIn(200);
		}
	});

	$('#sharemypost-modal-overlay, .sharemypost-modal-close').on('click', function (e) {
		if (e.target === this || $(e.target).hasClass('sharemypost-modal-close')) {
			$modal.fadeOut(200);
		}
	});

	// ===== NETWORK SORTING =====
	if ($.isFunction($.fn.sortable)) {
		$('.sharemypost-core-sortable').sortable({
			items: '> label.sharemypost-network-item',
			placeholder: 'ui-sortable-placeholder',
			forcePlaceholderSize: true,
			tolerance: 'pointer',
			cursor: 'move',
			start: function (e, ui) {
				ui.placeholder.height(ui.item.outerHeight());
				ui.placeholder.width(ui.item.outerWidth());
			},
			update: function () {
				let $container = $(this),
					order = $container.find('label.sharemypost-network-item').map(function () {
						return $(this).data('network');
					}).get();
				$($container.data('order-input')).val(order.join(','));
			}
		});
	}

	// Re-init sortable when a sub-panel becomes visible
	$('.sharemypost-sub-tab').on('click', function () {
		setTimeout(function () {
			if ($.isFunction($.fn.sortable)) {
				$('.sharemypost-core-sortable').sortable('refresh');
			}
		}, 50);
	});

	// ===== NETWORK ACTIVE STATE =====
	$('.sharemypost-network-checkbox, .sharemypost-network-item input').on('change', function () {
		$(this).closest('.sharemypost-network-item').toggleClass('is-active', this.checked);
	});

	// ===== FORM SUBMISSION - PERSIST TAB STATE =====
	$('.sharemypost-settings-form').on('submit', function () {
		if (window.localStorage) {
			let $active_main = $main_tabs.filter('.active');
			let $active_sub = $sub_tabs.filter('.active');
			if ($active_main.length) {
				window.localStorage.setItem('sharemypost_active_main_tab', $active_main.data('tab'));
			}
			if ($active_sub.length) {
				window.localStorage.setItem('sharemypost_active_sub_tab', $active_sub.data('subtab'));
			}
		}
	});
});