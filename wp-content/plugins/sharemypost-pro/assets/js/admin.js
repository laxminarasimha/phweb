jQuery(function ($) {

    let pro_data = window.sharemypost_pro || {},
        $form = $('#sharemypost-custom-network-form'),
        $id = $('#custom_network_id'),
        $name = $('#custom_network_name'),
        $url = $('#custom_network_url'),
        $color = $('#custom_network_color'),
        $icon = $('#custom_network_icon'),
        $save_btn = $('#sharemypost-save-custom-network'),
        $cancel_btn = $('#sharemypost-cancel-edit-custom-network'),
        $message = $('#sharemypost-custom-network-message'),
        $preview_container = $('#sharemypost-custom-preview'),
        $preview_icon = $('#sharemypost-custom-preview-icon'),
        $preview_label = $('#sharemypost-custom-preview-label');

    function show_message(msg, type) {
        $message.html('<div class="notice notice-' + type + ' is-dismissible"><p>' + msg + '</p></div>');
        setTimeout(function () { $message.find('.notice').fadeOut(300, function () { $(this).remove(); }); }, 4000);
    }

    function update_preview() {
        let name = $name.val().trim() || 'My Network',
            color = $color.val() || '#1f75e1',
            icon_svg = $icon.val().trim();

        $preview_label.text(name);

        if(icon_svg){
            $preview_icon.html(icon_svg);
        }

        $preview_container.find('.sharemypost-share-button').css({
            '--sharemypost-button-color': color,
            'background': color,
            'color': '#fff'
        });
    }

    $name.on('input', update_preview);
    $color.on('input', update_preview);
    $icon.on('input', update_preview);

    function reset_form() {
        $id.val('');
        $name.val('');
        $url.val('');
        $color.val('#1f75e1');
        $icon.val('');
        $save_btn.text('Save Network');
        $cancel_btn.hide();
        $message.empty();
        update_preview();
    }

    $cancel_btn.on('click', reset_form);

    $save_btn.on('click', function (e) {
        e.preventDefault();

        let name = $name.val().trim(),
            share_url = $url.val().trim(),
            icon_svg = $icon.val().trim(),
            color = $color.val();

            if(!name){
            show_message('Network name is required.', 'error');
            return;
        }

        if(!share_url){
            show_message('Share URL is required.', 'error');
            return;
        }

        $save_btn.prop('disabled', true).text('Saving...');

        let data = {
            action: 'sharemypost_save_custom_network',
            nonce: pro_data.nonce,
            id: $id.val(),
            name: name,
            slug: name.toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_|_$/g, ''),
            url: share_url,
            icon: icon_svg,
            color: color
        };

        $.post(pro_data.ajax_url, data, function (response) {
            $save_btn.prop('disabled', false).text('Save Network');

            if(response.success){
                show_message(response.data.message, 'success');
                reload_custom_networks_list();
                reset_form();
            } else {
                show_message(response.data.message || 'Failed to save network.', 'error');
            }
        }).fail(function () {
            $save_btn.prop('disabled', false).text('Save Network');
            show_message('AJAX request failed.', 'error');
        });
    });

    function reload_custom_networks_list() {
        $.post(pro_data.ajax_url, {
            action: 'sharemypost_get_custom_networks_list',
            nonce: pro_data.nonce
        }, function (response) {
            if(response.success && response.data.html){
                let $new_list = $(response.data.html);
                $('#sharemypost-custom-networks-list').html($new_list);
                init_sortable();
                bind_network_actions();
            }
        });
    }

    function bind_network_actions() {
        $('.sharemypost-custom-network-edit').on('click', function (e) {
            e.stopPropagation();
            let id = $(this).data('id');

            $.post(pro_data.ajax_url, {
                action: 'sharemypost_get_custom_network',
                nonce: pro_data.nonce,
                id: id
            }, function (response) {
                if(response.success){
                    let net = response.data;
                    $id.val(net.id);
                    $name.val(net.name);
                    $url.val(net.url);
                    $color.val(net.color || '#1f75e1');
                    $icon.val(net.icon);
                    $save_btn.text('Update Network');
                    $cancel_btn.show();
                    update_preview();

                    $('html, body').animate({
                        scrollTop: $form.offset().top - 100
                    }, 300);
                }
            });
        });

        $('.sharemypost-custom-network-delete').on('click', function (e) {
            e.stopPropagation();
            let id = $(this).data('id');

            if(!confirm('Are you sure you want to delete this custom network? This action cannot be undone.')){
                return;
            }

            $.post(pro_data.ajax_url, {
                action: 'sharemypost_delete_custom_network',
                nonce: pro_data.nonce,
                id: id
            }, function (response) {
                if(response.success) {
                    show_message(response.data.message, 'success');
                    reload_custom_networks_list();
                } else {
                    show_message(response.data.message || 'Failed to delete network.', 'error');
                }
            });
        });
    }

    function init_sortable() {
        if ($.isFunction($.fn.sortable)) {
            $('.sharemypost-sortable-networks').sortable({
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
                            return $(this).data('custom-id');
                        }).get();

                    $('#sharemypost-custom-network-order').val(order.join(','));

                    $.post(pro_data.ajax_url, {
                        action: 'sharemypost_reorder_custom_networks',
                        nonce: pro_data.nonce,
                        order: order
                    }, function (response) {
                        if(!response.success) {
                            console.warn('Reorder failed:', response.data.message);
                        }
                    });
                }
            });
        }
    }

    init_sortable();
    bind_network_actions();

    // ===== FLOATING COLOR TOGGLE & PREVIEW OVERRIDE =====
    let $floating_color_type_select = $('#sharemypost_floating_color_type_select'),
        $floating_custom_color_container = $('#sharemypost_floating_custom_color_container'),
        $floating_color_input = $('input[name="sharemypost_settings[floating_button_color]"]');

    function update_pro_button_preview() {
        let is_floating_panel_visible = $('#subtab-floating_bar').is(':visible');
        if (is_floating_panel_visible) {
            let $preview = $('#subtab-floating_bar').find('#sharemypost-button-preview');
            if ($preview.length) {
                let color_type = $floating_color_type_select.val() || 'original',
                    button_color = $floating_color_input.val() || '#1f75e1';

                $preview.removeClass('sharemypost-color-type-original sharemypost-color-type-custom');
                $preview.addClass('sharemypost-color-type-' + color_type);

                let preview_style = $preview[0].style;
                if (color_type === 'custom') {
                    $preview.addClass('sharemypost-color-type-custom');
                    preview_style.setProperty('--sharemypost-button-color', button_color);
                } else {
                    preview_style.removeProperty('--sharemypost-button-color');
                }
            }
        }
    }

    $floating_color_type_select.on('change', function () {
        $floating_custom_color_container.toggle(this.value === 'custom');
        update_pro_button_preview();
    }).trigger('change');

    $('.sharemypost-preview-input').on('input change keyup', function() {
        setTimeout(update_pro_button_preview, 10);
    });

    update_preview();
});