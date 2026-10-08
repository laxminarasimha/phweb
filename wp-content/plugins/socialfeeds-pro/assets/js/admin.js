jQuery(document).ready(function ($) {
    let selected_type = null,
    current_posts = [],
    current_account = null,
    socialfeeds_fb_posts = [],
    socialfeeds_fb_account = null,
    socialfeeds_filtered_posts = [],
    socialfeeds_visible_count = 0,
    google_reviews_preview_data = null,
    fetch_and_update_preview; // Define in scope

    // Initialize global sort if not exists
    window.socialfeeds_current_sort = 'newest';
    window.socialfeeds_preview_device = 'desktop';

    function show_toast(message, type = 'success') {
        let toast = $('<div>')
            .addClass('socialfeeds-toast')
            .addClass(type)
            .html(`<span class="dashicons dashicons-yes"></span> ${message}`);

        $('body').append(toast);
        toast.fadeIn(300).delay(3000).fadeOut(300, function () {
            toast.remove();
        });
    }


    // handle edit icon visibility based on edit mode vs new feed
    if($('#socialfeeds-instagram-wizard-form').length){
        if (!$('#socialfeeds-instagram-wizard-form input[name="edit_id"]').val()) {
            // NEW feed - hide edit icon until saved
            $('#socialfeeds-instagram-wizard-form').find('.socialfeeds-edit-name-btn').hide();
        } else {
            // EXISTING feed - show edit icon immediately (override inline style)
            $('#socialfeeds-instagram-wizard-form').find('.socialfeeds-edit-name-btn').show();
        }
    }

    // Connection Type Card Selection in Modal & Wizard
    $('.socialfeeds-connection-card input[type="radio"]').on('change' , function(){
        let selected_type = $(this).val();
        let $modal = $(this).closest('.socialfeeds-modal-content');
        let is_facebook = $modal.parent().attr('id') === 'socialfeeds-fb-connection-modal' || $modal.find('input[name="fb_token_type"]').length > 0;

        // Update card selection visual state (only within the same parent row)
        $(this).closest('.socialfeeds-connection-type-cards').find('.socialfeeds-connection-card').removeClass('selected');
        $(this).closest('.socialfeeds-connection-card').addClass('selected');

        // Toggle feature bullets
        if (is_facebook) {
            // For Facebook, we now only have Advanced Page connection.
            // Features and App Group are already visible via PHP.
            return;
        } else {
            if (selected_type === 'advanced') {
                $('#socialfeeds-features-basic').slideUp(200);
                $('#socialfeeds-features-advanced').slideDown(200);
                $('#socialfeeds-modal-ig-user-id-group').slideDown(200);
                $('#socialfeeds-modal-ig-app-group').slideDown(200);
                $('#socialfeeds-wizard-ig-user-id-group').slideDown(200);
                $('#socialfeeds-wizard-ig-app-group').slideDown(200);
                $('#socialfeeds-standalone-ig-user-id-group').slideDown(200);
                $('.socialfeeds-source-connection-type').find('#socialfeeds-modal-ig-app-group').slideDown(200);
            } else {
                $('#socialfeeds-features-advanced').slideUp(200);
                $('#socialfeeds-features-basic').slideDown(200);
                $('#socialfeeds-modal-ig-user-id-group').slideUp(200);
                // Show App Group for Basic to support Long-lived tokens
                $('#socialfeeds-modal-ig-app-group').slideDown(200);
                $('#socialfeeds-wizard-ig-user-id-group').slideUp(200);
                $('#socialfeeds-wizard-ig-app-group').slideDown(200);
                $('#socialfeeds-standalone-ig-user-id-group').slideUp(200);
                $('.socialfeeds-source-connection-type').find('#socialfeeds-modal-ig-app-group').slideUp(200);
            }
        }
    });

    // Also handle direct click on connection cards (for better UX)
    $('.socialfeeds-connection-card').on('click', function(){
        let $radio = $(this).find('input[type="radio"]');
        if (!$radio.is(':checked')) {
            $radio.prop('checked', true).trigger('change');
        }
    });

    // Connection Type in Configure Source page
    $('input[name="instagram_source_token_type"]').on('change', function(){
        let selected_type = $(this).val();

        $('.socialfeeds-source-connection-type .socialfeeds-connection-card').removeClass('selected');
        $(this).closest('.socialfeeds-connection-card').addClass('selected');

        // Toggle feature notes
        if (selected_type === 'advanced') {
            $('#socialfeeds-source-features-basic').slideUp(200);
            $('#socialfeeds-source-features-advanced').slideDown(200);
        } else {
            $('#socialfeeds-source-features-advanced').slideUp(200);
            $('#socialfeeds-source-features-basic').slideDown(200);
        }
    });

    // validate insta access token (standalone page)
    $('#socialfeeds-ig-validate-btn').on('click', function (e) {
        e.preventDefault();
        let button = $(this),
        token = $('#socialfeeds-standalone-token-input').val() || '';
        token = token.trim();
        // Token empty check
        if (!token) {
            $('#socialfeeds-standalone-token-message').show().css('color', 'red').text('Please paste a long-lived access token.');
            return;
        }

        // Determine token type from connection type selection if available
        let token_type = $('input[name="instagram_source_token_type"]:checked').val() || 'basic';

        // Disable button while validating
        button.prop('disabled', true).text('Validating...');

        $.ajax({
            url: socialfeeds_pro.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'socialfeeds_pro_validate_instagram_token',
                access_token: token,
                token_type: token_type,
                instagram_user_id: $('#socialfeeds-standalone-ig-user-id').val() || '',
                instagram_app_id: $('#socialfeeds-standalone-app-id').val() || '',
                instagram_app_secret: $('#socialfeeds-standalone-app-secret').val() || '',
                nonce: socialfeeds_pro.nonce
            },
            success: function (resp) {
                if (resp && resp.success) {
                    window.location.href = socialfeeds_pro.ajax_url.replace('admin-ajax.php', '') + 'admin.php?page=socialfeeds&action=create&connection_type=manual#instagram';
                } else {
                    let msg = (resp && resp.data && resp.data.message) ? resp.data.message : 'Token validation failed'
                    $('#socialfeeds-standalone-token-message').show().css('color', 'red').text(msg);
                }
            },
            error: function () {
                $('#socialfeeds-standalone-token-message').show().css('color', 'red').text('Network error validating token');
            },
            complete: function () {
                button.prop('disabled', false).text('Validate & Connect');
            }
        });
    });

    // Add Extra account vaildate
    $('#socialfeeds-wizard-validate-token-btn').on('click', function (e) {
        e.preventDefault();

        let token = $('#socialfeeds-wizard-token-input').val().trim(),
        btn = $(this),
        msg_div = $('#socialfeeds-wizard-token-message');

        if (!token) {
            msg_div.show().css('color', '#d32f2f').text('Please enter an access token').fadeIn();
            return;
        }

        btn.prop('disabled', true).text('Validating...');

        $.ajax({
            url: socialfeeds_pro.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'socialfeeds_pro_validate_instagram_token',
                access_token: token,
                token_type: $('input[name="wizard_instagram_token_type"]:checked').val() || 'basic',
                instagram_user_id: $('#socialfeeds-wizard-ig-user-id').val() || '',
                instagram_app_id: $('#socialfeeds-wizard-app-id').val() || '',
                instagram_app_secret: $('#socialfeeds-wizard-app-secret').val() || '',
                nonce: socialfeeds_pro.nonce
            },
            success: function (resp) {
                if (resp && resp.success) {
                    msg_div.show().css('color', '#4caf50').text('✓ Account connected! Reloading...').fadeIn();
                    location.reload();

                } else {
                    let msg = (resp && resp.data && resp.data.message) ? resp.data.message : 'Token validation failed'
                    msg_div.show().css('color', 'red').text(msg).fadeIn();
                }
            },
            error: function (xhr) {
                btn.prop('disabled', false).text('Validate & Connect');
                let errmsg = (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) ? xhr.responseJSON.data.message : 'Network error';
                msg_div.show().css('color', '#d32f2f').text('✗ Error: ' + errmsg).fadeIn();
            },
            complete: function () {
                btn.prop('disabled', false).text('Validate & Connect');
            }
        });

    });

    $('input[name="instagram_layout"]').on('change', function () {
        if (current_posts.length > 0) { render_preview_grid(current_posts, get_instagram_settings()); }
    });

    // Instagram Feed Type Card Selection
    $('.socialfeeds-feed-type-card, .socialfeeds-type-card').on('click', function () {
        if ($(this).hasClass('socialfeeds-locked')) return;

        $('.socialfeeds-feed-type-card, .socialfeeds-type-card').removeClass('selected');
        $(this).addClass('selected');

        selected_type = $(this).data('type');
        $('#socialfeeds-select-type-btn-instagram').removeClass('socialfeeds-disabled');
    });

    $('#socialfeeds-select-type-btn-instagram').on('click', function (e) {
        e.preventDefault();

        let final_type = $('.socialfeeds-type-card.selected').data('type');
        if (!final_type) final_type = selected_type;
        if (!final_type) return;

        let url = 'admin.php?page=socialfeeds&action=create&type=' + encodeURIComponent(final_type) + '#instagram';
        window.location.href = url;
    });

    // Manual Account Connect
    $('#socialfeeds-add-new-account-btn').on('click', function (e) {
        e.preventDefault();
        $('#socialfeeds-new-account-form').slideDown();
        $('#socialfeeds-manual-token-input').focus();
    });

    $('#socialfeeds-cancel-new-account-btn, #socialfeeds-cancel-manual-token-btn').on('click', function (e) {
        e.preventDefault();
        $('#socialfeeds-new-account-form').slideUp();
        $('#socialfeeds-token-message').hide().text('');
    });

    $('#socialfeeds-validate-token-btn').on('click', function (e) {
        e.preventDefault();
        let btn = $(this),
        token = $('#socialfeeds-manual-token-input').val() || '';
        token = token.trim();
        if (!token) {
            $('#socialfeeds-token-message').show().css('color', 'red').text('Please paste a long-lived access token.');
            return;
        }

        btn.prop('disabled', true).text('Validating...');
        $.post(socialfeeds_pro.ajax_url, {
            action: 'socialfeeds_pro_validate_instagram_token', // Use Pro action
            access_token: token,
            nonce: socialfeeds_pro.nonce
        }, function (resp) {
            if (resp && resp.success && resp.data && resp.data.account) {
                let acc = resp.data.account;
                location.reload();
            } else {
                let msg = (resp && resp.data && resp.data.message) ? resp.data.message : 'Token validation failed';
                $('#socialfeeds-token-message').show().css('color', 'red').text(msg);
            }
        }, 'json').fail(function () {
            $('#socialfeeds-token-message').show().css('color', 'red').text('Network error validating token');
        }).always(function () {
            btn.prop('disabled', false).text('✓ Validate & Connect');
        });
    });

    $('.socialfeeds-account-item').on('click', function (e) {
        let $r = $(this).find('input[type="radio"]');
        if ($r.length) {
            let val = $r.val();
            let name = $r.attr('name');
            $(`input[name="${name}"][value="${val}"]`).prop('checked', true);
            $(`input[name="${name}"]`).closest('.socialfeeds-account-item').removeClass('selected');
            $(`input[name="${name}"][value="${val}"]`).closest('.socialfeeds-account-item').addClass('selected');
            $r.trigger('change');
        }
    });

    // Step Navigation
    $('.socialfeeds-step-next-btn').on('click', function (e) {
        e.preventDefault();
        let next_step = $(this).data('next-step');
        $('.socialfeeds-wizard-step').removeClass('active').hide();
        $('#socialfeeds-step-' + next_step).addClass('active').show();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    $('.socialfeeds-step-back-btn').on('click', function (e) {
        e.preventDefault();
        let back_step = $(this).data('back-step');
        $('.socialfeeds-wizard-step').removeClass('active').hide();
        $('#socialfeeds-step-' + back_step).addClass('active').show();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    // Wizard Token Form
    $('#socialfeeds-wizard-add-account-btn').on('click', function (e) {
        e.preventDefault();
        $('#socialfeeds-wizard-token-form').slideDown();
        $('#socialfeeds-wizard-token-input').focus();
    });

    $('#socialfeeds-wizard-cancel-token-btn').on('click', function (e) {
        e.preventDefault();
        $('#socialfeeds-wizard-token-form').slideUp();
        $('#socialfeeds-wizard-token-input').val('');
        $('#socialfeeds-wizard-token-message').hide().text('');
    });

    // Source Type selection
    $('select[name="instagram_source_type"], select[name="instagram_source_type_sidebar"], input[name="instagram_source_type"]').on('change', function () {
        let type = $(this).val(),
            label_el = $('#socialfeeds-source-label, #socialfeeds-source-label-sidebar'),
            help_el = $('#socialfeeds-source-help, #socialfeeds-source-help-sidebar'),
            input_el = $('#socialfeeds-source-input-field, #socialfeeds-source-input-field-sidebar'),
            wrap_el = $('#socialfeeds-source-input-wrap, #socialfeeds-source-input-wrap-sidebar');

        if ($('#socialfeeds-hidden-feed-type').length) {
            $('#socialfeeds-hidden-feed-type').val(type);
        }

        if (type === 'hashtag') {
            label_el.text('Hashtag');
            help_el.text('Enter hashtag name (without #)');
            input_el.attr('placeholder', 'travel, photography, food...');
            wrap_el.show();
        } else {
            wrap_el.hide();
            if (type === 'manual') {
                input_el.val('');
            }
        }
    });

    if($('#socialfeeds-instagram-source-type').length > 0){
        $('#socialfeeds-instagram-source-type').trigger('change');
    }

    $('#socialfeeds-fetch-preview-btn, #socialfeeds-fetch-preview-btn-sidebar').on('click', function(e){
        e.preventDefault();
        fetch_and_update_preview();
    });

    function get_current_device() {
        let $activeBtn = $('.socialfeeds-preview-device-btn.active:visible');
        if ($activeBtn.length) {
            let width = parseInt($activeBtn.data('width'));
            if (width === 375) return 'mobile';
            if (width === 768) return 'tablet';
            return 'desktop';
        }
        return window.socialfeeds_preview_device || 'desktop';
    }

    function get_responsive_post_limit(settings) {
        let device = get_current_device();

        if (device === 'mobile') {
            return parseInt(settings.instagram_number_posts_mobile) || 6;
        }

        if (device === 'tablet') {
            return parseInt(
                settings.instagram_number_posts_tablet ||
                settings.instagram_number_posts_desktop
            ) || 8;
        }

        return parseInt(settings.instagram_number_posts_desktop) || 12;
    }

    // Preview rendering
    // Carousel Controls Init (YouTube Style)
    function init_carousel_controls($grid) {
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

        let $nav = $(`
            <div class="socialfeeds-carousel-nav">
                <button class="socialfeeds-carousel-btn prev"><span class="dashicons dashicons-arrow-left-alt2" style="font-family:dashicons; line-height:inherit;"></span></button>
                <button class="socialfeeds-carousel-btn next"><span class="dashicons dashicons-arrow-right-alt2" style="font-family:dashicons; line-height:inherit;"></span></button>
            </div>
        `);
        $wrapper.append($nav);

        let count = $grid.children().length,
        items_per_page = 3; // Default

        if ($grid.find('.socialfeeds-preview-item').first().length) {
            let itemW = $grid.find('.socialfeeds-preview-item').first().outerWidth(),
            gridW = $grid.width();
            if (itemW > 0 && gridW > 0) {
                items_per_page = Math.round(gridW / itemW);
                if (items_per_page < 1) items_per_page = 1;
            }
        }

        let total_pages = Math.ceil(count / items_per_page),
        $dots = $('<div class="socialfeeds-carousel-dots"></div>');

        for (let i = 0; i < total_pages; i++) {
            let $dot = $('<span class="socialfeeds-dot"></span>');
            if (i === 0) $dot.addClass('active');

            $dot.on('click', () => {
                $grid[0].scrollTo({
                    left: i * ($grid.width()),
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
            $grid[0].scrollBy({ left: $grid.width(), behavior: 'smooth' });
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

    function render_preview_grid(posts, settings) {
        if (!posts || posts.length === 0) {
            $('#socialfeeds-instagram-preview-grid').html('<div class="socialfeeds-no-preview">No posts available</div>');
            // Cleanup carousel if exists
            let $p = $('#socialfeeds-instagram-preview-grid').parent();
            if ($p.hasClass('socialfeeds-carousel-stage')) {
                $p.find('.socialfeeds-carousel-nav').remove();
                $p.siblings('.socialfeeds-carousel-dots').remove();
                $('#socialfeeds-instagram-preview-grid').unwrap();
            }
            return;
        }

        let padding = parseInt(settings.instagram_padding, 10) || 8,
        post_limit = get_responsive_post_limit(settings);

        /* FILTER POSTS (POST vs REEL) */
        const filtered = posts.filter(function (post) {
            let media_type = (post.media_type || '').toUpperCase(),
            permalink = post.permalink || '',
            is_reel = post.is_reel === true || (post.permalink && post.permalink.includes('/reel/')) || post.media_type === 'REEL',
            is_post = !is_reel;

            if (!settings.instagram_show_feed_posts && is_post) return false;
            if (!settings.instagram_show_reels && is_reel) return false;
            return true;
        });

        if (filtered.length === 0) {
            $('#socialfeeds-instagram-preview-grid').html(
                '<div class="socialfeeds-no-preview">No posts to display</div>'
            );
            // Cleanup carousel if exists
            let $p = $('#socialfeeds-instagram-preview-grid').parent();
            if ($p.hasClass('socialfeeds-carousel-stage')) {
                $p.find('.socialfeeds-carousel-nav').remove();
                $p.siblings('.socialfeeds-carousel-dots').remove();
                $('#socialfeeds-instagram-preview-grid').unwrap();
            }
            return;
        }
        socialfeeds_filtered_posts = filtered;

        if (typeof socialfeeds_visible_count === 'undefined' || socialfeeds_visible_count === 0) {
            socialfeeds_visible_count = post_limit;
        }
        const display_posts = socialfeeds_filtered_posts.slice(0, socialfeeds_visible_count);
        let html = '';
        display_posts.forEach(function (post) {
            let media_type = post.media_type ? post.media_type.toUpperCase() : 'IMAGE',
            media_url = post.media_url || '',
            thumbnail_url = post.thumbnail_url || '',
            permalink = post.permalink || '';
            html += '<div class="socialfeeds-preview-item hover-' + settings.instagram_hover_state + '" data-permalink="' + permalink + '" data-media-url="' + media_url + '" data-media-type="' + media_type + '">';

            /* MEDIA */
            let aspect_class = settings.instagram_layout === 'masonry' ? 'auto' : settings.instagram_aspect_ratio;
            if (settings.instagram_aspect_ratio === 'instagram') {
                aspect_class = (post.is_reel || media_type === 'VIDEO') ? 'portrait' : 'square';
            }

            html += '<div class="socialfeeds-preview-media aspect-' + aspect_class + '" style="position:relative; overflow:hidden;">';

            if (media_type === 'VIDEO') {
                let thumbnail = thumbnail_url || media_url;
                if (thumbnail && thumbnail.toLowerCase().indexOf('.mp4') === -1) {
                    html += '<img src="' + thumbnail + '" alt="Video">';
                } else if(thumbnail){
                    html += '<video src="' + thumbnail + '#t=0.001" preload="metadata" playsinline muted style="width:100%; height:100%; object-fit:cover; display:block; pointer-events:none;"></video>';
                } else {
                    html += '<div class="socialfeeds-video-placeholder">📹 Video</div>';
                }
            } else if (media_url && media_url.toLowerCase().indexOf('.mp4') === -1) {
                html += '<img src="' + media_url + '" alt="Post">';
            } else {
                html += '<div class="socialfeeds-video-placeholder">📷 No image</div>';
            }

            if (settings.instagram_hover_state === 'overlay') {
                html += '<div class="socialfeeds-hover-overlay"></div>';
            }

            if (media_type === 'VIDEO' && settings.instagram_show_play_icon) {
                html += '<span class="socialfeeds-play-overlay"><span class="dashicons dashicons-arrow-right"></span></span>';
            }

            html += '</div>'; // media

            var align_map = { left: 'flex-start', center: 'center', right: 'flex-end' };
            var align_val = align_map[settings.instagram_alignment] || 'flex-start';
            html += '<div class="socialfeeds-preview-stats" style="justify-content:' + align_val + '">';
            if (settings.instagram_caption_enabled && post.caption) {
                html += '<div class="caption" style="text-align:' + (settings.instagram_alignment || 'left') + '">' + post.caption + '</div>';
            }
            if (settings.instagram_likes && post.like_count !== undefined) {
                html += '<span class="likes"><span class="dashicons dashicons-heart socialfeeds-likes"></span> ' + post.like_count + '</span>';
            }
            if (settings.instagram_comments && post.comments_count !== undefined) {
                html += '<span class="comments"><span class="dashicons dashicons-admin-comments socialfeeds-comments"></span> ' + post.comments_count + '</span>';
            }
            html += '</div>'; // stats
            html += '</div>'; // item
        });

        let preview_box = $('#socialfeeds-instagram-preview-grid');
        preview_box.html(html);

        // Apply Color Scheme (like YouTube)
        let $preview_wrapper = $('.socialfeeds-preview-box-wrapper'),
        color_scheme = settings.instagram_color_scheme || 'light',
        custom_color = settings.instagram_custom_color || '#000000';

        if (color_scheme === 'dark') {
            $preview_wrapper.css({ background: '#0f0f0f', padding: '20px', borderRadius: '8px' });
        } else if (color_scheme === 'light') {
            $preview_wrapper.css({ background: '#ffffff', padding: '20px', borderRadius: '8px', border: '1px solid #eeeeee' });
        } else if (color_scheme === 'custom') {
            $preview_wrapper.css('background', custom_color);
            $preview_wrapper.css({ padding: '20px', borderRadius: '8px' });
        } else {
            $preview_wrapper.css({ background: '', padding: '', borderRadius: '', border: '' });
        }

        // Determine if dark mode for text colors
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

        // Apply text colors based on scheme
        let text_color = is_dark ? '#ffffff' : '#1d2327',
        meta_color = is_dark ? '#cccccc' : '#606060';

        preview_box.find('.socialfeeds-preview-stats').css('color', text_color);
        preview_box.find('.socialfeeds-preview-stats .caption').css('color', text_color);
        preview_box.find('.socialfeeds-preview-stats .likes, .socialfeeds-preview-stats .comments, .socialfeeds-preview-stats .views').css('color', meta_color);

        //Header
        let $header = $('#socialfeeds-instagram-preview-header');
        $header.find('.preview-username').css('color', text_color);
        $header.find('.preview-bio, .preview-followers, .preview-media').css('color', meta_color);

        // Clear previous layout classes
        preview_box.removeClass('layout-grid layout-carousel layout-masonry');

        // Apply layout style
        if (settings.instagram_layout === 'carousel') {
            preview_box.addClass('layout-carousel');

            let items_to_show = get_preview_columns(settings);
            if (items_to_show < 1) items_to_show = 1;

            let item_width_calc = `calc((100% - ${(items_to_show - 1) * padding}px) / ${items_to_show})`;

            preview_box.css({
                display: 'flex',
                gap: padding + 'px',
                overflowX: 'hidden',
                scrollBehavior: 'smooth'
            });
            // Reset others
            preview_box.css({
                'column-count': '',
                'grid-template-columns': '',
                'display': 'flex'
            });

            preview_box.find('.socialfeeds-preview-item').css({
                flex: '0 0 ' + item_width_calc,
                maxWidth: item_width_calc
            });

            init_carousel_controls(preview_box);

        } else { // Not Carousel
            // Cleanup carousel wrapper/controls
            let $parent = preview_box.parent();
            if ($parent.hasClass('socialfeeds-carousel-stage')) {
                $parent.find('.socialfeeds-carousel-nav').remove();
                $parent.siblings('.socialfeeds-carousel-dots').remove();
                preview_box.unwrap(); // Removes .socialfeeds-carousel-stage
            } else {
                preview_box.parent().find('.socialfeeds-carousel-nav, .socialfeeds-carousel-dots').remove();
                preview_box.siblings('.socialfeeds-carousel-dots').remove();
            }

            preview_box.find('.socialfeeds-preview-item').css({ flex: '', maxWidth: '' });
            preview_box.css({ overflowX: '', scrollBehavior: '' });

            if (settings.instagram_layout === 'masonry') {
                preview_box.addClass('layout-masonry').css({
                    columnGap: padding + 'px',
                    display: 'block'
                });
                preview_box.find('.socialfeeds-preview-item').css({
                    width: '100%',
                    display: 'inline-block',
                    marginBottom: padding + 'px',
                    breakInside: 'avoid',
                    pageBreakInside: 'avoid'
                });
                function update_masonry_cols() {
                    let cols = get_preview_columns(settings);
                    preview_box.css('column-count', cols);
                }
                update_masonry_cols();
            }
            else { // grid layout
                preview_box.addClass('layout-grid').css({
                    display: 'grid',
                    gap: padding + 'px'
                });

                function update_grid_cols() {
                    let cols = get_preview_columns(settings);
                    preview_box.css('grid-template-columns', 'repeat(' + cols + ', 1fr)');
                }
                update_grid_cols();
            }
        }

        // Buttons Container
        if (settings.instagram_follow_button_enabled || settings.instagram_load_more_enabled) {
            let buttons_html = '<div class="socialfeeds-preview-actions" style="grid-column: 1 / -1; display: flex; justify-content: center; gap: 10px; margin-top: 20px; width: 100%; flex-wrap: wrap;">';

            if (settings.instagram_load_more_enabled) {
                // Create unique class for hover
                let loadMoreBtnClass = 'socialfeeds-load-more-btn-' + Date.now();

                buttons_html += `
                <button type="button" class="button socialfeeds-load-more-btn ${loadMoreBtnClass}" 
                    id="socialfeeds-load-more-btn"
                    data-load-count="${settings.instagram_load_more_count}"
                    style="background: ${settings.instagram_load_more_bg_color}; 
                        color: ${settings.instagram_load_more_text_color};">
                    ${settings.instagram_load_more_text}
                </button>
                <style>
                    .${loadMoreBtnClass}:hover {
                        background: ${settings.instagram_load_more_hover_color} !important;
                    }
                </style>
            `;
            }

            if (settings.instagram_follow_button_enabled) {
                let ig_username = current_account ? current_account.username : '',
                ig_url = ig_username ? `https://www.instagram.com/${ig_username}/` : 'https://www.instagram.com/',
                followBtnClass = 'socialfeeds-follow-btn-' + Date.now();
                buttons_html += `
                <a href="${ig_url}" target="_blank" class="button ${followBtnClass}" 
                    style="background: ${settings.instagram_follow_button_bg_color}; 
                        color: ${settings.instagram_follow_button_text_color}; 
                        display: inline-flex; align-items: center; gap: 6px; border: none; text-decoration: none;">
                    <span class="dashicons dashicons-instagram" style="font-size: 18px; width: 18px; height: 18px; line-height: 1;"></span>
                    ${settings.instagram_follow_button_text}
                </a>
                <style>
                    .${followBtnClass}:hover {
                        background: ${settings.instagram_follow_button_hover_color} !important;
                    }
                </style>
            `;
            }

            buttons_html += '</div>';

            // Clear existing
            $('.socialfeeds-preview-actions').remove();

            if (settings.instagram_layout === 'carousel') {
                let $stage = preview_box.parent(),
                $dots = $stage.next('.socialfeeds-carousel-dots');
                if ($dots.length) {
                    $dots.after(buttons_html);
                } else {
                    $stage.after(buttons_html);
                }
            } else if (settings.instagram_layout === 'masonry') {
                preview_box.after(buttons_html);
            } else {
                preview_box.append(buttons_html);
            }
        }

        if (settings.instagram_load_more_enabled && socialfeeds_visible_count < socialfeeds_filtered_posts.length) {
            $('.socialfeeds-load-more-btn').show();
        }
        preview_box.show();
    }

    function get_preview_columns(settings) {
        let device = get_current_device();

        if (device === 'mobile') {
            return parseInt(settings.instagram_columns_mobile) || 1;
        }
        if (device === 'tablet') {
            return parseInt(settings.instagram_columns_tablet) || 2;
        }
        return parseInt(settings.instagram_columns_desktop) || 3;
    }


    fetch_and_update_preview = function () {
        let selected_account_index = $('input[name="instagram_selected_account"]:checked').val();
        if (selected_account_index === undefined) {
            $('#socialfeeds-instagram-preview-grid').html('<div class="socialfeeds-no-preview">Please select an account</div>');
            return;
        }

        let feed_type = $('#socialfeeds-instagram-source-type-sidebar').val() || $('#socialfeeds-instagram-source-type').val() || $('#socialfeeds-hidden-feed-type').val() || socialfeeds_pro.feedType || 'username',
        source_input_sidebar = $('#socialfeeds-source-input-field-sidebar').val(),
        source_input_wizard = $('#socialfeeds-source-input-field').val(),
        source_input = (source_input_sidebar !== undefined && source_input_sidebar !== '') ? source_input_sidebar : (source_input_wizard || ''),
        status_span = $('#socialfeeds-fetch-status'),
        loader = $('.socialfeeds-wizard-loader-overlay');

        status_span.show().text('Fetching posts...');
        loader.addClass('active');

        $.ajax({
            url: socialfeeds_pro.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'socialfeeds_pro_instagram_fetch_posts',
                feed_type: feed_type,
                source_input: source_input,
                selected_account: selected_account_index,
                limit: 100,
                nonce: socialfeeds_pro.nonce
            },
            success: function (response) {
                loader.removeClass('active');

                if (!response || !response.success || response.data.error) {
                    let err = (response && response.data && response.data.error) ? response.data.error : 'Failed to load posts';
                    status_span.text('Error: ' + err).css('color', '#ef4444').show();
                    $('#socialfeeds-instagram-preview-grid').html('<div class="socialfeeds-no-preview">' + err + '</div>');
                    return;
                }

                current_account = response.data.account || null;
                current_posts = response.data.posts || [];

                if (current_posts.length > 0) {
                    status_span.text('Successfully fetched ' + current_posts.length + ' posts').css('color', '#10b981').show();

                    const settings = get_instagram_settings();

                    render_instagram_header_preview(current_account, settings);

                    const sorted_posts = apply_instagram_sort(current_posts);
                    render_preview_grid(sorted_posts, settings);

                    if (current_posts[0]?.media_url) {
                        $('#preview_url_hidden').val(current_posts[0].media_url);
                    }
                    status_span.text('Loaded ' + current_posts.length + ' posts').show();
                } else {
                    $('#socialfeeds-instagram-preview-grid')
                        .html('<div class="socialfeeds-no-preview">No posts found for this account</div>');
                    status_span.text('No posts found').show();
                }
            },
            error: function (xhr) {
                loader.removeClass('active');

                let error_msg = 'Error fetching posts.';
                if (xhr.responseJSON && xhr.responseJSON.data?.message) {
                    error_msg = xhr.responseJSON.data.message;
                }

                $('#socialfeeds-instagram-preview-grid').html('<div class="socialfeeds-no-preview">' + error_msg + '</div>');

                status_span.text(error_msg).show();
            }
        });
    };


    function get_instagram_settings() {
        return {
            instagram_columns_desktop: $('#socialfeeds-instagram-columns-desktop').val() || 3,
            instagram_columns_tablet: $('#socialfeeds-instagram-columns-tablet').val() || 2,
            instagram_columns_mobile: $('#socialfeeds-instagram-columns-mobile').val() || 1,
            instagram_number_posts_desktop: parseInt($('#socialfeeds-instagram-number-posts-desktop').val(), 10) || 12,
            instagram_number_posts_mobile: $('#socialfeeds-instagram-number-posts-mobile').val() || 6,
            instagram_number_posts_tablet: parseInt($('#socialfeeds-instagram-number-posts-tablet').val(), 10) || 8,
            instagram_padding: $('#socialfeeds-instagram-padding').val() || 8,
            instagram_aspect_ratio: $('#socialfeeds-instagram-aspect-ratio').val() || 'square',
            instagram_layout: $('input[name="instagram_layout"]:checked').val() || 'grid',
            instagram_show_feed_posts: $('#socialfeeds-instagram-show-feed-posts').is(':checked'),
            instagram_show_reels: $('#socialfeeds-instagram-show-reels').is(':checked'),
            instagram_play_mode: $('#socialfeeds-instagram-play-mode').val() || 'newtab',
            instagram_header_enabled: $('#socialfeeds-instagram-header-enabled').is(':checked'),
            instagram_likes: $('#socialfeeds-instagram-likes').is(':checked'),
            instagram_comments: $('#socialfeeds-instagram-comments').is(':checked'),
            instagram_caption_enabled: $('#socialfeeds-instagram-caption-enabled').is(':checked'),
            instagram_hover_state: $('#socialfeeds-instagram-hover-state').val() || 'overlay',
            instagram_alignment: $('#socialfeeds-instagram-alignment').val() || 'left',
            instagram_show_play_icon: $('#socialfeeds-instagram-show-play-icon').is(':checked'),
            instagram_color_scheme: $('#socialfeeds-instagram-color-scheme').val() || 'light',
            instagram_custom_color: $('#socialfeeds-instagram-custom-color').val() || '#000000',
            instagram_header_style: $('#socialfeeds-instagram-header-style').val() || 'left',
            instagram_header_size: $('#socialfeeds-instagram-header-size').val() || 'medium',
            instagram_show_bio_text: $('#socialfeeds-instagram-show-bio-text').is(':checked'),
            instagram_show_followers: $('#socialfeeds-instagram-show-followers').is(':checked'),
            instagram_media_count: $('#socialfeeds-instagram-media-count').is(':checked'),
            instagram_load_more_enabled: $('#socialfeeds-instagram-load-more-enabled').is(':checked'),
            instagram_load_more_count: parseInt($('#socialfeeds-instagram-load-more-count').val(), 10) || 12,
            instagram_load_more_text: $('#socialfeeds-instagram-load-more-text').val() || 'Load More',
            instagram_load_more_bg_color: $('#socialfeeds-instagram-load-more-bg-color').val(),
            instagram_load_more_hover_color: $('#socialfeeds-instagram-load-more-hover-color').val(),
            instagram_load_more_text_color: $('#socialfeeds-instagram-load-more-text-color').val(),
            instagram_follow_button_enabled: $('#socialfeeds-instagram-follow-button-enabled').is(':checked'),
            instagram_follow_button_text: $('#socialfeeds-instagram-follow-button-text').val() || 'Follow on Instagram',
            instagram_follow_button_bg_color: $('#socialfeeds-instagram-follow-button-bg-color').val(),
            instagram_follow_button_text_color: $('#socialfeeds-instagram-follow-button-text-color').val(),
            instagram_follow_button_hover_color: $('#socialfeeds-instagram-follow-button-hover-color').val(),
            instagram_custom_avatar: $('#socialfeeds-custom-avatar-url').val()
        };
    }

    $('input[name="instagram_selected_account"]').on('change', function () {
        fetch_and_update_preview();
    });

    $(document).on('change input', '.socialfeeds-sidebar-content input, .socialfeeds-sidebar-content select', function () {
        let $target = $(this),
        name = $target.attr('name');

        // Update display values for range sliders
        if ($target.attr('type') === 'range') {
            let suffix = (name.includes('padding') || name.includes('spacing')) ? 'px' : (name.includes('posts') ? ' Posts' : ' Columns');
            $target.closest('.socialfeeds-control-group').find('.socialfeeds-value-display').text($target.val() + suffix);
        }

        // Show/Hide custom color group
        if (name === 'instagram_color_scheme') {
            if ($target.val() === 'custom') $target.closest('.socialfeeds-accordion-body').find('#socialfeeds-custom-color-group').show();
            else $target.closest('.socialfeeds-accordion-body').find('#socialfeeds-custom-color-group').hide();
        } else if (name === 'facebook_color_scheme') {
            if ($target.val() === 'custom') $('.socialfeeds-fb-custom-color').show();
            else $('.socialfeeds-fb-custom-color').hide();
        }

        if (current_posts.length > 0) {
            const settings = get_instagram_settings();

            // Special handling for post limit changes
            if (name && name.startsWith('instagram_number_posts')) {
                let loader = $('.socialfeeds-wizard-loader-overlay');
                loader.addClass('active');

                // Reset visible count to the new limit
                socialfeeds_visible_count = get_responsive_post_limit(settings);

                // Small timeout to allow loader to appear
                setTimeout(() => {
                    const sorted_posts = apply_instagram_sort(current_posts);
                    render_instagram_header_preview(current_account, settings);
                    render_preview_grid(sorted_posts, settings);
                    loader.removeClass('active');
                }, 500);
                return;
            }

            const sorted_posts = apply_instagram_sort(current_posts);
            render_instagram_header_preview(current_account, settings);
            render_preview_grid(sorted_posts, settings);
        }
    });

    // Trigger when sort dropdown changes
    $('#socialfeeds-instagram-sort-by').on('change', function () {
        window.socialfeeds_current_sort = $(this).val();
        if (!current_posts || !current_posts.length) return;
        const settings = get_instagram_settings();
        const sorted_posts = apply_instagram_sort(current_posts);
        render_preview_grid(sorted_posts, settings);
        if (sorted_posts[0]?.media_url) {
            $('#preview_url_hidden').val(sorted_posts[0].media_url);
        }
    });

    // Setup Media Uploader for Custom Avatar
    let custom_avatar_frame;
    $('.socialfeeds-upload-avatar-btn').on('click', function (e) {
        e.preventDefault();
        if (custom_avatar_frame) {
            custom_avatar_frame.open();
            return;
        }
        custom_avatar_frame = wp.media({
            title: 'Select Custom Avatar',
            button: { text: 'Use this image' },
            multiple: false
        });
        custom_avatar_frame.on('select', function () {
            let attachment = custom_avatar_frame.state().get('selection').first().toJSON();
            $('#socialfeeds-custom-avatar-url').val(attachment.url).trigger('change');
            $('.socialfeeds-avatar-preview-wrap').show().find('img').attr('src', attachment.url);
            $('.socialfeeds-remove-avatar-btn').show();
        });
        custom_avatar_frame.open();
    });

    $('.socialfeeds-remove-avatar-btn').on('click', function (e) {
        e.preventDefault();
        $('#socialfeeds-custom-avatar-url').val('').trigger('change');
        $('.socialfeeds-avatar-preview-wrap').hide().find('img').attr('src', '');
        $(this).hide();
    });

    // Connection Modal Select Option
    $('.socialfeeds-ig-select-option').on('click', function (e) {
        e.preventDefault();
        let connection_type = $(this).data('type'),
        admin_page = socialfeeds_pro.admin_page_url;
        if (connection_type === 'manual') {
            window.location.href = admin_page + '&action=create&connection_type=' + connection_type + '&step=token#instagram';
            return;
        }
        window.location.href = admin_page + '&action=create&connection_type=' + connection_type + '#instagram';
    });

    // Check pre-selected account - MOVED INSIDE THE IF BLOCK
    if ($('input[name="instagram_selected_account"]:checked').length) {
        setTimeout(function () {
            if (typeof fetch_and_update_preview === 'function') fetch_and_update_preview();
        }, 500);
    }

    function apply_instagram_sort(posts) {
        if (!posts || !posts.length) return posts;
        let sorted = [...posts];
        switch (window.socialfeeds_current_sort) {
            case 'newest':
                sorted.sort((a, b) => new Date(b.timestamp) - new Date(a.timestamp));
                break;
            case 'likes':
                sorted.sort((a, b) => (b.like_count || 0) - (a.like_count || 0));
                break;
            case 'random':
                for (let i = sorted.length - 1; i > 0; i--) {
                    const j = Math.floor(Math.random() * (i + 1));
                    [sorted[i], sorted[j]] = [sorted[j], sorted[i]];
                }
                break;
        }
        return sorted;
    }


    function render_instagram_header_preview(account, settings) {
        let header_box = $('#socialfeeds-instagram-preview-header');

        if (!settings.instagram_header_enabled || !account) {
            header_box.hide().empty();
            return;
        }

        // Use custom avatar if enabled, otherwise default profile picture
        let avatar_url = settings.instagram_custom_avatar && settings.instagram_custom_avatar.trim() !== '' ? settings.instagram_custom_avatar : account.profile_picture_url;

        let html = `
            <div class="socialfeeds-instagram-header preview ${settings.instagram_header_style || 'standard'} ${settings.instagram_header_size || 'medium'}">
                ${avatar_url ? `
                <div class="preview-avatar">
                    <img src="${avatar_url}" alt="">
                </div>` : ''}

                <div class="preview-meta">
                    <div class="preview-username">
                        ${account.username || ''}
                    </div>
                    ${settings.instagram_show_bio_text && account.biography ? `
                    <div class="preview-bio">
                        ${account.biography}
                    </div>` : ''}
                    ${settings.instagram_show_followers && account.followers_count ? `
                    <div class="preview-followers">
                        <strong>${account.followers_count}</strong> Followers
                    </div>` : ''}
                    ${settings.instagram_media_count && account.media_count ? `
                    <div class="preview-media">
                        <strong>${account.media_count}</strong> Posts
                    </div>` : ''}
                </div>
            </div>
        `;

        header_box.html(html).show();
    }

    // wizard form submission
    $('#socialfeeds-instagram-wizard-form').on('submit', function (e) {
        e.preventDefault();
        let form = $(this);

        // Ensure source input is populated from selected account radio if empty
        let selected_account_val = form.find('input[name="instagram_selected_account"]:checked');
        let feed_type = $('#socialfeeds-instagram-source-type-sidebar').val() || $('#socialfeeds-instagram-source-type').val() || $('#socialfeeds-hidden-feed-type').val();
        if (selected_account_val.length && selected_account_val.val() && feed_type !== 'hashtag' && feed_type !== 'manual') {
            let source_input = form.find('input[name="source_input"]');
            if (source_input.length && !source_input.val()) source_input.val(selected_account_val.val());
        }

        let submit_btn = form.find('button[type="submit"]'),
        original_text = submit_btn.text();
        submit_btn.prop('disabled', true).text('Saving...');

        let fd = new FormData(this);
        // Ensure action is correct and nonce is present
        fd.append('action', 'socialfeeds_pro_insta_save_settings');
        fd.append('nonce', socialfeeds_pro.nonce);

        $.ajax({
            url: socialfeeds_pro.ajax_url,
            type: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            //nonce:socialfeeds_pro.nonce,
            dataType: 'json',
            success: function (res) {
                if (res && res.success) {
                    show_toast('Feed saved successfully!');

                    if (res.data && res.data.feed_id) {
                        let feed_id = res.data.feed_id;
                        
                        // Update global tracking to prevent conflicts
                        if (window.socialfeedsData && window.socialfeedsData.existing_ids && !socialfeedsData.existing_ids.includes(feed_id)) {
                            socialfeedsData.existing_ids.push(feed_id);
                        }

                        // Update hidden input so next save updates this feed
                        if (form.find('input[name="edit_id"]').length) {
                            form.find('input[name="edit_id"]').val(feed_id);
                        } else {
                            form.append('<input type="hidden" name="edit_id" value="' + feed_id + '">');
                        }

                        // Update shortcode display
                        let shortcode_text = '[socialfeeds id="' + feed_id + '" platform="instagram"]';
                        form.find('#socialfeeds-top-shortcode').text(shortcode_text);
                        form.find('.socialfeeds-copy-shortcode').attr('data-shortcode', shortcode_text);

                        // Update URL without reload
                        let currentUrl = new URL(window.location.href);
                        currentUrl.searchParams.set('edit_id', feed_id);
                        window.history.pushState({ path: currentUrl.href }, '', currentUrl.href);

                        // ensure name controls are available now that the feed exists
                        $('.socialfeeds-save-name-btn').attr('data-feed-id', feed_id).data('feed-id', feed_id);
                        $('#socialfeeds-instagram-wizard-form').find('.socialfeeds-edit-name-btn').show();
                        let $text = $('.socialfeeds-feed-name-text'),
                        $input = $('.socialfeeds-feed-name-input'),
                        saved_name = res.data?.feed_name || null;

                        if(saved_name){
                            $text.text(saved_name);
                            $input.val(saved_name);
                        } else if($text.length && $text.text().trim() === ''){
                            let raw_type = $('#socialfeeds-hidden-feed-type').val() || socialfeeds_pro.feedType || 'username';
                            let feed_type_labels = { channel: 'Timeline', manual: 'Tagged' };
                            let type_label = feed_type_labels[raw_type] || (raw_type.charAt(0).toUpperCase() + raw_type.slice(1));
                            let defaultName = 'Instagram Feed - ' + type_label + ' ' + feed_id;
                            $text.text(defaultName);
                            $input.val(defaultName);
                        }
                    }


                } else {
                    show_toast(res.data?.message || 'Error saving feed', 'error');
                }
            },
            error: function (xhr) {
                show_toast('Network error saving feed', 'error');
            },
            complete: function () {
                submit_btn.prop('disabled', false).text(original_text);
            }
        });
    });

    // Instagram Connection Modal Back/Manual logic
    $('#socialfeeds-ig-manual-btn').on('click', function (e) {
        e.preventDefault();
        $('#socialfeeds-ig-modal-main').fadeOut(200, function () {
            $('#socialfeeds-ig-modal-token').fadeIn(200);
            $('#socialfeeds-ig-token-input').focus();
        });
    });

    $('#socialfeeds-ig-back-btn, #socialfeeds-ig-cancel-btn').on('click', function (e) {
        e.preventDefault();
        $('#socialfeeds-ig-modal-token').fadeOut(200, function () {
            $('#socialfeeds-ig-modal-main').fadeIn(200);
        });
    });

    // Delete Instagram Account
    $('.socialfeeds-delete-account-btn').on('click', function(e){
        e.preventDefault();
        e.stopPropagation();

        let btn = $(this),
            account_id = btn.data('account-id');

        if (!account_id) return;

        if (!confirm('Are you sure you want to delete this Instagram account? All feeds using this account may stop working.')) {
            return;
        }

        btn.prop('disabled', true).css('opacity', '0.5');

        $.ajax({
            url: socialfeeds_pro.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'socialfeeds_pro_delete_instagram_account',
                account_id: account_id,
                nonce: socialfeeds_pro.nonce
            },
            success: function (resp) {
                if (resp && resp.success) {
                    show_toast(resp.data.message || 'Account deleted successfully');
                    setTimeout(function () {
                        location.reload();
                    }, 1000);
                } else {
                    let msg = (resp && resp.data && resp.data.message) ? resp.data.message : 'Error deleting account';
                    show_toast(msg, 'error');
                    btn.prop('disabled', false).css('opacity', '1');
                }
            },
            error: function () {
                show_toast('Network error deleting account', 'error');
                btn.prop('disabled', false).css('opacity', '1');
            }
        });
    });

    $('#socialfeeds-instagram .socialfeeds-preview-device-btn').on('click', function(e){
        e.preventDefault();
        $('#socialfeeds-instagram .socialfeeds-preview-device-btn').removeClass('active');
        $(this).addClass('active');

        let width = $(this).data('width'),
        $preview_wrap = $('#socialfeeds-instagram .socialfeeds-customize-preview');
        if (width === '100%') {
            $preview_wrap.css('width', '').removeClass('socialfeeds-device-mode');
            window.socialfeeds_instagram_device = 'desktop';
        } else {
            $preview_wrap.css('width', width).addClass('socialfeeds-device-mode');
            if (parseInt(width) === 375) {
                window.socialfeeds_instagram_device = 'mobile';
            } else if (parseInt(width) === 768) {
                window.socialfeeds_instagram_device = 'tablet';
            }
        }
        // Sync global device variable
        window.socialfeeds_preview_device = window.socialfeeds_instagram_device;

        $('#socialfeeds-instagram-preview-grid').css(
            'max-width',
            width === '100%' ? '100%' : width + 'px'
        );

        if (current_posts.length) {
            socialfeeds_visible_count = 0; // Reset visible count to force post limit refresh
            render_preview_grid(current_posts, get_instagram_settings());
        }
    });

    // Instagram Wizard Next Step Handler
    $('#socialfeeds-ig-next-btn').on('click', function (e) {
        e.preventDefault();
        let selected_account = $('input[name="instagram_selected_account"]:checked').val();

        if (!selected_account) {
            alert('Please select an Instagram account to proceed.');
            return;
        }

        // Update the hidden source field so it saves correctly (only for timeline/username feeds)
        let feed_type = $('#socialfeeds-hidden-feed-type').val();
        if (feed_type !== 'hashtag' && feed_type !== 'manual') {
            $('#socialfeeds-source-input-field').val(selected_account);
        }

        // Switch to the Customize tab
        $('#socialfeeds-instagram-tab-customize').trigger('click');

        // Ensure scroll to top
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    // Instagram Tab Switching Logic
    $('#socialfeeds-instagram-wizard-form .socialfeeds-wizard-tab').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation(); // Stop bubbling to prevent Core JS from catching this

        let tab_name = $(this).data('tab');

        // Update Tabs styling
        $('#socialfeeds-instagram-wizard-form .socialfeeds-wizard-tab').removeClass('active');
        $(this).addClass('active');

        // Update Content visibility
        $('#socialfeeds-instagram-wizard-form .socialfeeds-wizard-step').removeClass('active').hide();

        let step_id = tab_name === 'customize' ? 'socialfeeds-step-2' : 'socialfeeds-step-1';
        let $target_content = $('#' + step_id);
        if ($target_content.length) {
            $target_content.addClass('active').show().attr('style', 'display: block !important;');
        }

        // Specific actions
        if (tab_name === 'customize') {
            if (typeof fetch_and_update_preview === 'function') {
                fetch_and_update_preview();
            }
        }
    });

    $('#socialfeeds-ig-add-new-modal-trigger').on('click', function (e) {
        e.preventDefault();
        $('#socialfeeds-ig-connection-modal').fadeIn().css('display', 'flex');
    });

    $('#socialfeeds-edit-source-btn').on('click', function (e) {
        e.preventDefault();
        // Return to source tab
        $('#socialfeeds-instagram-tab-source').trigger('click');
    });

    // Note: Use delegating for modal close since it might be dynamic or shared
    $(document).on('click', '.socialfeeds-modal-close[data-modal="socialfeeds-ig-connection-modal"], #socialfeeds-ig-connection-modal', function (e) {
        if (e.target !== this && !$(e.target).hasClass('socialfeeds-modal-close')) return;
        $('#socialfeeds-ig-connection-modal').fadeOut();
    });

    // Google modal close handler
    $(document).on('click', '.socialfeeds-modal-close[data-modal="socialfeeds-google-connection-modal"], #socialfeeds-google-connection-modal', function (e) {
        if (e.target !== this && !$(e.target).hasClass('socialfeeds-modal-close')) return;
        $('#socialfeeds-google-connection-modal').fadeOut();
    });


    $('#socialfeeds-ig-modal-token-form').on('submit', function (e) {
        e.preventDefault();
        let token = $('#socialfeeds-modal-ig-token').val(),
        token_type = $('input[name="instagram_token_type"]:checked').val() || 'basic',
        submit_btn = $(this).find('button[type="submit"]'),
        original_text = submit_btn.text();

        if (!token) {
            show_toast('Please enter an access token', 'error');
            return;
        }

        // Skip if user hasn't changed the masked token
        if (token.indexOf('•') !== -1) {
            show_toast('Please paste a new access token (the current value is masked)', 'error');
            return;
        }

        submit_btn.prop('disabled', true).text('Saving...');

        $.ajax({
            url: socialfeeds_pro.ajax_url,
            type: 'POST',
            data: {
                action: 'socialfeeds_pro_validate_instagram_token',
                access_token: token,
                token_type: token_type,
                instagram_user_id: $('#socialfeeds-modal-ig-user-id').val() || '',
                instagram_app_id: $('#socialfeeds-modal-ig-app-id').val() || '',
                instagram_app_secret: $('#socialfeeds-modal-ig-app-secret').val() || '',
                nonce: socialfeeds_pro.nonce
            },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    show_toast('Instagram settings saved successfully!');
                    setTimeout(function () {
                        $('#socialfeeds-ig-connection-modal').fadeOut(200, function () {
                            $(this).removeClass('active');
                            location.reload();
                        });
                    }, 1000);
                } else {
                    let err_msg = 'Error saving settings';
                    if (response.data) {
                        err_msg = (typeof response.data === 'object' && response.data.message) ? response.data.message : (typeof response.data === 'string' ? response.data : err_msg);
                    }
                    show_toast(err_msg, 'error');
                }
            },
            error: function () {
                show_toast('Error connecting to server', 'error');
            },
            complete: function () {
                submit_btn.prop('disabled', false).text(original_text);
            }
        });
    });

    $('#socialfeeds-back-btn').on('click', function (e) {
        e.preventDefault();
        window.location.href = 'admin.php?page=socialfeeds&action=create#instagram';
    });

    function re_initialize_instagram_form_handlers() {
        let url_params = new URLSearchParams(window.location.search),
        action = url_params.get('action'),
        hash = window.location.hash,
        edit_id_param = url_params.get('edit_id') || url_params.get('feed_id') || url_params.get('id');

        // If we're creating or editing an Instagram feed
        if ((action === 'create' || action === 'edit') && hash === '#instagram') {

            let new_id = edit_id_param;
            if (!new_id) {
                let current_text = $('#socialfeeds-top-shortcode').text() || '';
                let match = current_text.match(/id="([^"]+)"/);
                if (match) {
                    new_id = match[1];
                }
            }

            if (new_id && new_id !== 'undefined') {
                // Add hidden edit_id field if it doesn't exist
                if ($('#socialfeeds-instagram-wizard-form input[name="edit_id"]').length === 0) {
                    $('#socialfeeds-instagram-wizard-form').append('<input type="hidden" name="edit_id" value="' + new_id + '">');
                } else {
                    $('#socialfeeds-instagram-wizard-form input[name="edit_id"]').val(new_id);
                }

                // Update top shortcode display
                $form.find('#socialfeeds-top-shortcode').text('[socialfeeds id="' + new_id + '" platform="instagram"]');
                $form.find('.socialfeeds-copy-shortcode').attr('data-shortcode', '[socialfeeds id="' + new_id + '" platform="instagram"]');
            }
        }
    }

    // Call on load
    re_initialize_instagram_form_handlers();

    // Re-call when hash changes (since main plugin uses hash navigation)
    $(window).on('hashchange', function () {
        re_initialize_instagram_form_handlers();
    });

    // Instagram Fullscreen Handler
    $('#socialfeeds-instagram-wizard-form .socialfeeds-fullscreen-btn').on('click', function (e) {
        // Only trigger if we are inside Instagram customize tab
        let $instacontainer = $(this).closest('.socialfeeds-wizard-step');

        if ($instacontainer.length) {
            e.preventDefault();
            let $icon = $(this).find('.socialfeed-fullscreen');

            $instacontainer.toggleClass('socialfeeds-fullscreen');
            $('body').toggleClass('socialfeeds-body-lock');

            if ($instacontainer.hasClass('socialfeeds-fullscreen')) {
                $icon.removeClass('dashicons-fullscreen-alt').addClass('dashicons-fullscreen-exit-alt');
                if (typeof show_toast === 'function') show_toast('Fullscreen mode enabled');
            } else {
                $icon.removeClass('dashicons-fullscreen-exit-alt').addClass('dashicons-fullscreen-alt');
                if (typeof show_toast === 'function') show_toast('Fullscreen mode disabled');
            }
        }
    });

    // Instagram Item Click Handler
    $(document).on('click', '#socialfeeds-instagram-preview-grid .socialfeeds-preview-item', function (e) {
        // Prevent default if it's a link, though we usually handle it
        e.preventDefault();

        let $item = $(this),
        settings = get_instagram_settings(),
        mode = settings.instagram_play_mode || 'newtab',
        permalink = $item.attr('data-permalink'),
        media_url = $item.attr('data-media-url'),
        media_type = $item.attr('data-media-type');

        if (mode === 'newtab') {
            if (permalink) {
                window.open(permalink, '_blank');
            } else {
                show_toast('No permalink available', 'error');
            }
        } else if (mode === 'lightbox') {
            if (!media_url) {
                show_toast('No media URL available', 'error');
                return;
            }

            // High-end Lightbox Overlay
            let media_content = '';
            if (media_type === 'VIDEO') {
                media_content = `
                    <video src="${media_url}" controls autoplay 
                        style="max-width: 90vw; max-height: 80vh; border-radius: 12px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); border: 2px solid rgba(255,255,255,0.1);">
                        Your browser does not support the video tag.
                    </video>`;
            } else {
                media_content = `
                    <img src="${media_url}" 
                        style="max-width: 90vw; max-height: 80vh; border-radius: 12px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); object-fit: contain; border: 2px solid rgba(255,255,255,0.1);">`;
            }

            let $lightbox = $(`
                <div class="socialfeeds-premium-lightbox" style="position: fixed; inset: 0; z-index: 999999; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(8px); background: rgba(0, 0, 0, 0.85); opacity: 0; transition: opacity 0.3s ease;">
                    <button class="close-btn" style="position: absolute; top: 30px; right: 30px; width: 44px; height: 44px; background: rgba(255,255,255,0.1); border: none; border-radius: 50%; color: white; font-size: 24px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s;">&times;</button>
                    <div class="lightbox-content-wrap" style="transform: scale(0.9); transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);">
                        ${media_content}
                        ${permalink ? `<div style="text-align: center; margin-top: 15px;"><a href="${permalink}" target="_blank" style="color: white; text-decoration: none; background: rgba(255,255,255,0.1); padding: 8px 16px; border-radius: 20px; font-size: 13px; font-weight: 500;">View on Instagram</a></div>` : ''}
                    </div>
                </div>
            `);

            $('body').append($lightbox);

            // Animate In
            setTimeout(() => {
                $lightbox.css('opacity', '1');
                $lightbox.find('.lightbox-content-wrap').css('transform', 'scale(1)');
            }, 10);

            let close_lightbox = () => {
                $lightbox.css('opacity', '0');
                $lightbox.find('.lightbox-content-wrap').css('transform', 'scale(0.9)');
                setTimeout(() => $lightbox.remove(), 300);
            };

            $lightbox.on('click', function (evt) {
                if (evt.target === this || $(evt.target).hasClass('close-btn')) {
                    close_lightbox();
                }
            });

            $(document).on('keydown.sf_lightbox', function (evt) {
                if (evt.key === 'Escape') {
                    close_lightbox();
                    $(document).off('keydown.sf_lightbox');
                }
            });

        } else if (mode === 'inline') {
            if (media_type !== 'VIDEO') {
                // If it's an image, maybe fallback to newtab?
                if (permalink) window.open(permalink, '_blank');
                return;
            }

            let $media_box = $item.find('.socialfeeds-preview-media');
            if ($media_box.find('video').length) return; // Already playing

            // Show Loading state if needed?
            $media_box.html(`
                <video src="${media_url}" controls autoplay playsinline
                    style="width: 100%; height: 100%; object-fit: cover; border-radius: inherit;">
                </video>
            `);
        }
    });

    $('#socialfeeds-add-new-feed').on('click', function (e) {
        e.preventDefault();
        let $modal = $('#socialfeeds-ig-connection-modal');
        if ($modal.hasClass('active')) {
            $modal.removeClass('active').fadeOut(300, function () {
                window.location.href = 'admin.php?page=socialfeeds#instagram';
            });
        } else {
            window.location.href = 'admin.php?page=socialfeeds#instagram';
        }
    });

    // Instagram Preview Load More Handler
    $(document).on('click', '#socialfeeds-load-more-btn', function (e) {
        e.preventDefault();
        let $btn = $(this),
        original_text = $btn.text();

        $btn.text('Loading...').addClass('socialfeeds-loading').prop('disabled', true);

        setTimeout(() => {
            let settings = get_instagram_settings(),
            load_count = parseInt($btn.attr('data-load-count')) || 12;

            socialfeeds_visible_count += load_count;

            let sorted_posts = apply_instagram_sort(current_posts);
            render_preview_grid(sorted_posts, settings);

        }, 500);
    });

    // Facebook Wizard Logic
    $('.socialfeeds-fb-step-next-btn').on('click', function(e){
        e.preventDefault();
        let account = $('input[name="facebook_selected_account"]:checked').val();
        if(account === undefined){
            alert('Please select a Facebook account.');
            return;
        }
        $('#socialfeeds-facebook-wizard-form .socialfeeds-wizard-tab[data-tab="customize"]').trigger('click');
    });

    // Handle edit icon visibility for Facebook based on edit mode vs new feed
    if ($('#socialfeeds-facebook-wizard-form').length) {
        let fb_edit_id = $('#socialfeeds-facebook-wizard-form input[name="edit_id"]').val();
        if (!fb_edit_id || fb_edit_id === '0') {
            // NEW feed — hide pencil until saved
            $('#socialfeeds-facebook-wizard-form').find('.socialfeeds-edit-name-btn').hide();
        } else {
            // EXISTING feed — show pencil immediately
            $('#socialfeeds-facebook-wizard-form').find('.socialfeeds-edit-name-btn').show();
        }
    }

    $('#socialfeeds-facebook-wizard-form .socialfeeds-wizard-tab').on('click', function(e){
        e.preventDefault();
        let tab = $(this).data('tab');
        $('#socialfeeds-facebook-wizard-form .socialfeeds-wizard-tab').removeClass('active');
        $(this).addClass('active');
        $('#socialfeeds-facebook-wizard-form .socialfeeds-wizard-step').removeClass('active').hide();
        let step = tab === 'source' ? 'socialfeeds-fb-step-1' : 'socialfeeds-fb-step-2';
        $('#' + step).addClass('active').show();
        if(tab === 'customize') {
            fetch_facebook_preview();
            // sync name if available
            let saved_name = $('.socialfeeds-feed-name-text').text();
            if(saved_name) $('.socialfeeds-feed-name-input').val(saved_name);
        }
    });

    $('#socialfeeds-facebook-wizard-form .socialfeeds-edit-name-btn').on('click', function(){
        let $wrap = $(this).closest('.socialfeeds-inline-name-wrapper');
        $wrap.find('.socialfeeds-feed-name-text').hide();
        $wrap.find('.socialfeeds-feed-name-input').show().focus();
        $(this).hide();
        $wrap.find('.socialfeeds-save-name-btn').show();
    });

    $('#socialfeeds-facebook-wizard-form .socialfeeds-save-name-btn').on('click', function(){
        let $wrap = $(this).closest('.socialfeeds-inline-name-wrapper');
        let new_name = $wrap.find('.socialfeeds-feed-name-input').val();
        $wrap.find('.socialfeeds-feed-name-text').text(new_name).show();
        $wrap.find('.socialfeeds-feed-name-input').hide();
        $(this).hide();
        $wrap.find('.socialfeeds-edit-name-btn').show();
    });

    $('.socialfeeds-fb-sidebar-tab-btn').on('click', function(){
        let target = $(this).data('target');
        $(this).addClass('active').siblings().removeClass('active');
        $('.socialfeeds-sidebar-tab-pane').removeClass('active').hide();
        $('#' + target).addClass('active').fadeIn(200);
    });

    $('#socialfeeds-facebook-add-account-btn').on('click', function(){
        $('#socialfeeds-facebook-token-form').slideToggle();
    });

    $('#socialfeeds-fb-validate-btn').on('click', function(){
        let btn = $(this);
        let data = {
            action: 'socialfeeds_pro_validate_facebook_token',
            access_token: $('#socialfeeds-fb-token-input').val(),
            facebook_page_id: $('#socialfeeds-fb-page-id').val(),
            token_type: $('input[name="fb_token_type"]').val() || 'advanced',
            facebook_app_id: $('#socialfeeds-fb-app-id').val(),
            facebook_app_secret: $('#socialfeeds-fb-app-secret').val(),
            nonce: socialfeeds_pro.nonce
        };
        btn.prop('disabled', true).text('Connecting...');
        $.post(socialfeeds_pro.ajax_url, data, function(res){
            if(res.success){
                show_toast('Account connected!');
                location.reload();
            } else {
                alert(res.data.message || 'Error connecting account');
            }
            btn.prop('disabled', false).text('Connect');
        });
    });

    $('.socialfeeds-delete-facebook-account-btn').on('click', function(e){
        e.preventDefault();
        if(!confirm('Are you sure you want to delete this account?')) return;
        let btn = $(this);
        $.post(socialfeeds_pro.ajax_url, {
            action: 'socialfeeds_pro_delete_facebook_account',
            account_id: btn.data('account-id'),
            nonce: socialfeeds_pro.nonce
        }, function(res){
            if(res.success) location.reload();
        });
    });

    $('.socialfeeds-feed-type-v2 .socialfeeds-type-card').on('click', function(e){
        if($(this).closest('.socialfeeds-fb-type-selection').length || $(this).closest('.socialfeeds-feed-main-card').find('#socialfeeds-select-type-btn-facebook').length){
            $(this).addClass('selected').siblings().removeClass('selected');
        }
    });

     $('#socialfeeds-select-type-btn-facebook').on('click', function(e){
        e.preventDefault();
        let selected_card = $(this).closest('.socialfeeds-feed-main-card').find('.socialfeeds-type-card.selected');
        let type = selected_card.data('type') || 'timeline';
        window.location.href = socialfeeds_pro.admin_page_url + '&action=create&type=' + type + '#facebook';
    });

    function fetch_facebook_preview(){
        let form = $('#socialfeeds-facebook-wizard-form');
        let data = {
            action: 'socialfeeds_pro_facebook_fetch_posts',
            selected_account: form.find('input[name="facebook_selected_account"]:checked').val(),
            limit: form.find('input[name="facebook_posts_per_page"]').val(),
            feed_type: form.find('select[name="facebook_feed_type"]').val() || form.find('input[name="feed_type"]').val(),
            sort_by: form.find('select[name="facebook_sort_by"]').val() || 'newest',
            nonce: socialfeeds_pro.nonce
        };
        $('#socialfeeds-facebook-preview').html('<div style="display:flex; align-items:center; justify-content:center; min-height:300px; width:100%;"><div class="socialfeeds-loader"></div></div>');
        $.post(socialfeeds_pro.ajax_url, data, function(res){
            if(res.success){
                socialfeeds_fb_posts = res.data.posts || [];
                socialfeeds_fb_account = res.data.account || null;
                // Apply sorting to posts before rendering
                let posts = apply_facebook_sort(socialfeeds_fb_posts, data.sort_by);
                render_facebook_preview(posts, socialfeeds_fb_account);
            } else {
                $('#socialfeeds-facebook-preview').html('<p style="text-align:center; padding:20px;">Error fetching preview.</p>');
            }
        });
    }

    function apply_facebook_sort(posts, sortBy){
        if(!posts || !posts.length) return posts;
        let sorted = [...posts];
        switch(sortBy) {
            case 'most_liked':
                sorted.sort((a, b) => (b.like_count || 0) - (a.like_count || 0));
                break;
            case 'most_commented':
                sorted.sort((a, b) => (b.comment_count || 0) - (a.comment_count || 0));
                break;
            case 'random':
                for (let i = sorted.length - 1; i > 0; i--) {
                    const j = Math.floor(Math.random() * (i + 1));
                    [sorted[i], sorted[j]] = [sorted[j], sorted[i]];
                }
                break;
            case 'newest':
            default:
                sorted.sort((a, b) => new Date(b.created_time) - new Date(a.created_time));
                break;
        }
        return sorted;
    }

    function render_facebook_preview(posts, account){
        if(!posts || !posts.length){
            $('#socialfeeds-facebook-preview').html('<p style="text-align:center; padding:20px;">No posts found.</p>');
            return;
        }

        let form = $('#socialfeeds-facebook-wizard-form');
        let cols = form.find('input[name="facebook_columns_desktop"]').val() || 3;
        let padding = form.find('input[name="facebook_padding"]').val() || 8;
        let ratio = form.find('select[name="facebook_aspect_ratio"]').val() || 'square';
        let color_scheme = form.find('select[name="facebook_color_scheme"]').val() || 'light';
        let custom_color = form.find('input[name="facebook_custom_color"]').val() || '#000000';
        let header_enabled = form.find('input[name="facebook_header_enabled"]').is(':checked');
        let cover_enabled = form.find('input[name="facebook_header_cover_enabled"]').is(':checked');
        let avatar_enabled = form.find('input[name="facebook_header_avatar_enabled"]').is(':checked');
        let caption_enabled = form.find('input[name="facebook_header_caption_enabled"]').is(':checked');
        let stats_enabled = form.find('input[name="facebook_header_stats_enabled"]').is(':checked');
        let show_likes_engagement = form.find('input[name="facebook_likes"]').is(':checked');
        let show_comments_engagement = form.find('input[name="facebook_comments"]').is(':checked');
        let show_caption = form.find('input[name="facebook_show_caption"]').is(':checked');
        let hover_state = form.find('select[name="facebook_hover_state"]').val() || 'overlay';
        let play_mode = form.find('select[name="facebook_play_mode"]').val() || 'newtab';
        
        let load_more_enabled = form.find('input[name="facebook_load_more_enabled"]').is(':checked');
        let load_more_text = form.find('input[name="facebook_load_more_text"]').val() || 'Load More';
        let load_more_bg = form.find('input[name="facebook_load_more_bg_color"]').val() || '#E74C3C';
        let load_more_text_color = form.find('input[name="facebook_load_more_text_color"]').val() || '#FFFFFF';
        let load_more_hover = form.find('input[name="facebook_load_more_hover_color"]').val() || '#f76606';
        let facebook_load_more_count =  form.find('input[name="facebook_load_more_count"]').val() || 2;

        let follow_enabled = form.find('input[name="facebook_follow_button_enabled"]').is(':checked');
        let follow_text = form.find('input[name="facebook_follow_button_text"]').val() || 'Follow on Facebook';
        let follow_bg = form.find('input[name="facebook_follow_button_bg_color"]').val() || '#1877F2';
        let follow_text_color = form.find('input[name="facebook_follow_button_text_color"]').val() || '#FFFFFF';
        let follow_hover = form.find('input[name="facebook_follow_button_hover_color"]').val() || '#0e5a9a';

        // Apply background style
        let container = $('.socialfeeds-preview-box-wrapper');
        if(color_scheme === 'dark') container.css({background: '#0f0f0f', color: '#fff', border: '1px solid #333'});
        else if(color_scheme === 'custom') container.css({background: custom_color, color: (is_dark_color(custom_color) ? '#fff' : '#1e293b'), border: 'none'});
        else container.css({background: '#fff', color: '#1e293b', border: '1px solid #e2e8f0'});

        let html = '';

        // Render Header V2
        if(header_enabled) {
            let account_name = (account && account.name) ? account.name : 'Facebook Page';
            let avatar_url = (account && account.picture) ? (account.picture.data ? account.picture.data.url : account.picture.url) : null;
            let cover_url = (account && account.cover) ? account.cover.source : null;
            let about = (account && account.about) ? account.about : 'No description available.';
            let fan_count = (account && account.fan_count) ? format_count(account.fan_count) : '0';
            let followers_count = (account && account.followers_count) ? format_count(account.followers_count) : '0';
            
            let header_v2_class = 'socialfeeds-fb-header-v2' + (cover_enabled ? '' : ' no-cover');
            html += '<div class="' + header_v2_class + '" style="background:#fff; border-radius:8px; overflow:hidden; box-shadow:0 1px 2px rgba(0,0,0,0.1); margin-bottom:25px; border:1px solid #e2e8f0;">';
            
            // Cover Photo
            if(cover_enabled) {
                let cover_style = cover_url ? `background-image:url('${cover_url}');` : `background:linear-gradient(to bottom, #1877f2, #0a56b2);`;
                html += `<div class="socialfeeds-fb-cover" style="width:100%; height:200px; background-size:cover; background-position:center; position:relative; ${cover_style}"></div>`;
            }

            let content_padding = cover_enabled ? '0 32px 24px' : '24px 32px 24px';
            html += '<div class="socialfeeds-fb-header-content" style="display:flex; padding:' + content_padding + '; position:relative; z-index:2; align-items:flex-start;">';

            // Profile Picture
            if(avatar_enabled) {
                let avatar_margin = cover_enabled ? '-55px' : '0';
                html += '<div class="socialfeeds-fb-avatar-wrap" style="width:110px; height:110px; border-radius:50%; background:#fff; padding:3px; flex-shrink:0; margin-top:' + avatar_margin + '; box-shadow:0 0 0 4px #fff; position:relative;">';
                if(avatar_url) {
                    html += '<img src="'+avatar_url+'" style="width:100%; height:100%; border-radius:50%; object-fit:cover;" />';
                } else {
                    html += '<div style="width:100%; height:100%; background:#1877f2; color:#fff; border-radius:50%; display:flex; align-items:center; justify-content:center;"><span class="dashicons dashicons-facebook" style="font-size:40px; width:40px; height:40px;"></span></div>';
                }
                html += '</div>';
            }

            let info_margin = cover_enabled ? '15px' : '0';
            html += '<div class="socialfeeds-fb-info-wrap" style="margin-left:20px; margin-top:' + info_margin + '; flex:1;">';
            html += '<h3 class="socialfeeds-fb-name" style="margin:0 0 4px; font-size:24px; font-weight:700; color:#050505;">'+account_name+'</h3>';
            
            if(caption_enabled) {
                html += '<div class="socialfeeds-fb-caption" style="font-size:14px; color:#65676b; margin-bottom:4px;">'+about+'</div>';
            }

            if(stats_enabled) {
                html += '<div class="socialfeeds-fb-stats" style="display:flex; align-items:center; gap:8px; font-size:13px; color:#65676b;">';
                html += '<span class="socialfeeds-fb-stat-item"><strong>'+fan_count+'</strong> likes</span>';
                html += '<span class="socialfeeds-fb-stat-sep">•</span>';
                html += '<span class="socialfeeds-fb-stat-item"><strong>'+followers_count+'</strong> followers</span>';
                html += '</div>';
            }
            
            html += '</div>';
            html += '</div>';
            html += '</div>';
        }

        // Apply layout style
        let preview_box = $('#socialfeeds-facebook-preview');
        preview_box.removeClass('layout-grid layout-list layout-carousel');
        preview_box.css({ display: '', gap: '', gridTemplateColumns: '', overflowX: '', scrollBehavior: '', flex: '', maxWidth: '', columnCount: '' });

        let layout = form.find('input[name="facebook_layout"]:checked').val() || 'grid';
        let items_container_style = '';
        
        if (layout === 'carousel') {
            items_container_style = `display: flex; gap: ${padding}px; overflow-x: hidden; scroll-behavior: smooth;`;
        } else if (layout === 'list') {
            items_container_style = `display: flex; flex-direction: column; gap: ${padding}px;`;
        } else {
            items_container_style = `display: grid; grid-template-columns: repeat(${cols}, 1fr); gap: ${padding}px;`;
        }

        html += '<div class="socialfeeds-facebook-inner layout-' + layout + '" style="' + items_container_style + '">';

        posts.forEach(post => {
            let media_url = post.full_picture || '';
            let permalink = post.permalink_url || '';
            let media_type = (post.type === 'video' || post.type === 'reel') ? 'VIDEO' : 'IMAGE';
            let item_type = post.type || 'timeline';
            
            let item_style = '';
            if (layout === 'carousel') {
                let item_width_calc = `calc((100% - ${(cols - 1) * padding}px) / ${cols})`;
                item_style = `style="flex: 0 0 ${item_width_calc}; max-width: ${item_width_calc};"`;
            }

            html += `<div class="socialfeeds-facebook-item socialfeeds-preview-item socialfeeds-fb-type-${item_type} hover-${hover_state}" data-permalink="${permalink}" data-media-url="${media_url}" data-media-type="${media_type}" ${item_style}>`;
            
            let aspect_class = (layout === 'list') ? 'auto' : ratio;
            html += `<div class="socialfeeds-preview-media aspect-${aspect_class}" style="position:relative; overflow:hidden;">`;
            
            if(media_url) {
                 html += '<img src="'+media_url+'" style="width:100%; height:100%; object-fit:cover;">';
            } else {
                 html += '<div class="socialfeeds-video-placeholder">📹 No Media</div>';
            }

            if (hover_state === 'overlay') {
                html += '<div class="socialfeeds-hover-overlay"></div>';
            }

            if(item_type === 'album' && post.count){
                html += '<div class="socialfeeds-fb-album-count"><span class="dashicons dashicons-images-alt2"></span> '+post.count+' photo'+(post.count > 1 ? 's' : '')+'</div>';
            }
            if(item_type === 'event' && post.created_time){
                html += '<div class="socialfeeds-fb-event-badge"><span class="dashicons dashicons-calendar-alt"></span> EVENT</div>';
            }

            if (media_type === 'VIDEO' && play_mode !== 'newtab') {
                html += '<span class="socialfeeds-play-overlay"><span class="dashicons dashicons-arrow-right"></span></span>';
            }

            html += '</div>'; // media
            html += '<div class="socialfeeds-facebook-content">';
            if(show_caption && post.message) html += '<div class="socialfeeds-facebook-message" style="font-weight:600; font-size:12px;">'+(post.message.length > 40 ? post.message.substring(0, 40) + '...' : post.message)+'</div>';
            
            if (item_type === 'event') {
                if (post.start_time) {
                    let event_date = new Date(post.start_time).toLocaleDateString([], { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' });
                    html += `<div class="socialfeeds-fb-event-time" style="font-size:12px; color:#64748b; margin-top:5px; display:flex; align-items:center; gap:5px;"><span class="dashicons dashicons-clock" style="font-size:14px; width:14px; height:14px;"></span> ${event_date}</div>`;
                }
                if (post.place && post.place.name) {
                    html += `<div class="socialfeeds-fb-event-place" style="font-size:12px; color:#64748b; margin-top:5px; display:flex; align-items:center; gap:5px;"><span class="dashicons dashicons-location" style="font-size:14px; width:14px; height:14px;"></span> ${post.place.name}</div>`;
                }
            }

            if (item_type === 'album' && post.description) {
                let album_desc = post.description.length > 60 ? post.description.substring(0, 60) + '...' : post.description;
                html += `<div class="socialfeeds-fb-album-desc" style="font-size:13px; color:#64748b; margin-top:5px;">${album_desc}</div>`;
            }
            
            if(show_likes_engagement || show_comments_engagement) {
                html += '<div class="socialfeeds-fb-engagement" style="display:flex; align-items:center; gap:12px; padding:8px 0; margin-top:8px;">';
                if(show_likes_engagement) {
                    let like_count = post.like_count || post.likes || 0;
                    html += '<span class="socialfeeds-fb-like-count" style="display:flex; align-items:center; gap:6px; color:#999; font-size:11px; font-weight:500; cursor:default;"><span class="dashicons dashicons-heart" style="font-size:14px; width:14px; height:14px;"></span><span class="fb-like-count">' + like_count + '</span></span>';
                }
                if(show_comments_engagement) {
                    let comment_count = post.comment_count || post.comments || 0;
                    html += '<span class="socialfeeds-fb-comment-count" style="display:flex; align-items:center; gap:6px; color:#999; font-size:11px; font-weight:500; cursor:default;"><span class="dashicons dashicons-admin-comments" style="font-size:14px; width:14px; height:14px;"></span><span class="fb-comment-count">' + comment_count + '</span></span>';
                }
                html += '</div>';
            }
            
            html += '</div>'; // content
            html += '</div>'; // item
        });

        // Cleanup carousel if it was active
        let $p = preview_box.parent();
        if ($p.hasClass('socialfeeds-carousel-stage')) {
            $p.find('.socialfeeds-carousel-nav').remove();
            $p.siblings('.socialfeeds-carousel-dots').remove();
            preview_box.unwrap();
        }

        html += '</div>'; // close inner container
        
        // Render Buttons
        if(load_more_enabled || follow_enabled) {
            html += '<div class="socialfeeds-preview-footer" style="display:flex; justify-content:center; gap:15px; margin-top:25px; flex-wrap:wrap;">';
            if(load_more_enabled) {
                let lm_count = form.find('input[name="facebook_load_more_count"]').val() || 9;
                html += `<button type="button" class="socialfeeds-fb-load-more-btn" data-load-count="${lm_count}" data-base-bg="${load_more_bg}" data-hover-bg="${load_more_hover}" style="background:${load_more_bg}; color:${load_more_text_color}; border:none; padding:10px 24px; border-radius:6px; font-weight:600; cursor:pointer; transition:all 0.2s;">${load_more_text}</button>`;
            }
            if(follow_enabled) {
                html += `<button type="button" class="socialfeeds-fb-follow-preview-btn" data-base-bg="${follow_bg}" data-hover-bg="${follow_hover}" style="background:${follow_bg}; color:${follow_text_color}; border:none; padding:10px 24px; border-radius:6px; font-weight:600; cursor:pointer; transition:all 0.2s; display:flex; align-items:center;"><span class="dashicons dashicons-facebook" style="font-size:18px; width:18px; height:18px; line-height:1; margin-right:6px;"></span> ${follow_text}</button>`;
            }
            html += '</div>';
        }

        preview_box.html(html);

        // Add hover effect for preview buttons
        $('.socialfeeds-fb-load-more-btn, .socialfeeds-fb-follow-preview-btn').off('mouseenter mouseleave').on('mouseenter', function(){
            $(this).css('background', $(this).data('hover-bg'));
        }).on('mouseleave', function(){
            $(this).css('background', $(this).data('base-bg'));
        });

        $('.socialfeeds-fb-follow-preview-btn').off('click').on('click', function(e){
            e.preventDefault();
            if (account && account.id) {
                window.open('https://www.facebook.com/' + account.id, '_blank');
            } else {
                let id = form.find('input[name="facebook_selected_account"]:checked').val() === undefined ? form.find('input[name="source_input"]').val() : '';
                if(id) window.open('https://www.facebook.com/' + id, '_blank');
            }
        });

        if (layout === 'carousel') {
            init_carousel_controls(preview_box.find('.socialfeeds-facebook-inner'));
        }
    }

    function is_dark_color(hex) {
        if (!hex) return false;
        hex = hex.replace('#', '');
        if (hex.length === 3) hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2];
        let r = parseInt(hex.substring(0, 2), 16);
        let g = parseInt(hex.substring(2, 4), 16);
        let b = parseInt(hex.substring(4, 6), 16);
        let brightness = (r * 299 + g * 587 + b * 114) / 1000;
        return brightness < 128;
    }

    function format_count(num) {
        if (num >= 1000000) {
            return (num / 1000000).toFixed(1).replace(/\.0$/, '') + 'M';
        }
        if (num >= 1000) {
            return (num / 1000).toFixed(1).replace(/\.0$/, '') + 'k';
        }
        return num;
    }

    $(document).on('click', '#socialfeeds-facebook-wizard-form .socialfeeds-fullscreen-btn', function (e) {
        let $fbcontainer = $(this).closest('.socialfeeds-wizard-step');

        if ($fbcontainer.length) {
            e.preventDefault();
            let $icon = $(this).find('.dashicons');

            $fbcontainer.toggleClass('socialfeeds-fullscreen');
            $('body').toggleClass('socialfeeds-body-lock');

            if ($fbcontainer.hasClass('socialfeeds-fullscreen')) {
                $icon.removeClass('dashicons-fullscreen-alt').addClass('dashicons-fullscreen-exit-alt');
                if (typeof show_toast === 'function') show_toast('Fullscreen mode enabled');
            } else {
                $icon.removeClass('dashicons-fullscreen-exit-alt').addClass('dashicons-fullscreen-alt');
                if (typeof show_toast === 'function') show_toast('Fullscreen mode disabled');
            }
        }
    });

    $(document).on('change input', '.socialfeeds-fb-trigger', function(){
        let $form = $('#socialfeeds-facebook-wizard-form');
        let name = $(this).attr('name');
        let refetch_fields = ['facebook_selected_account', 'facebook_posts_per_page', 'facebook_feed_type'];

        if (name === 'facebook_feed_type') {
            $form.find('input[name="feed_type"]').val($(this).val());
        }

        if (socialfeeds_fb_posts.length > 0 && !refetch_fields.includes(name)) {
            // Instant update from cached data
            let sortBy = $form.find('select[name="facebook_sort_by"]').val() || 'newest';
            let sorted_posts = apply_facebook_sort(socialfeeds_fb_posts, sortBy);
            render_facebook_preview(sorted_posts, socialfeeds_fb_account);
        } else {
            // Full re-fetch (required for data-changing fields)
            fetch_facebook_preview();
        }
    });

    $('#socialfeeds-facebook-wizard-form').on('submit', function(e){
        e.preventDefault();
        let form = $(this);
        let data = form.serialize();

        let account_index = form.find('input[name="facebook_selected_account"]:checked').val();
        if (account_index !== undefined) {
            data += '&selected_account=' + account_index;
        }

        let submit_btn = form.find('button[type="submit"]'),
        original_text = submit_btn.text();
        submit_btn.prop('disabled', true).text('Saving...');

        $.post(socialfeeds_pro.ajax_url, data, function(res){
            if (res && res.success) {
                show_toast('Facebook feed saved!');

                let feed_id = res.data && res.data.feed_id ? res.data.feed_id : form.find('input[name="edit_id"]').val();

                if (feed_id) {
                    // Update global tracking to prevent conflicts
                    if (window.socialfeedsData && window.socialfeedsData.existing_ids && !socialfeedsData.existing_ids.includes(feed_id)) {
                        socialfeedsData.existing_ids.push(feed_id);
                    }
                    // Update hidden edit_id so next save updates this feed
                    if (form.find('input[name="edit_id"]').length) {
                        form.find('input[name="edit_id"]').val(feed_id);
                    } else {
                        form.append('<input type="hidden" name="edit_id" value="' + feed_id + '">');
                    }

                    // Update shortcode display
                    let shortcode_text = '[socialfeeds id="' + feed_id + '" platform="facebook"]';
                    form.find('#socialfeeds-top-shortcode').text(shortcode_text);
                    form.find('.socialfeeds-copy-shortcode').attr('data-shortcode', shortcode_text);

                    // Update URL without reload
                    let currentUrl = new URL(window.location.href);
                    currentUrl.searchParams.set('edit_id', feed_id);
                    window.history.pushState({ path: currentUrl.href }, '', currentUrl.href);

                    // Show pencil now that feed is saved
                    $('.socialfeeds-save-name-btn').attr('data-feed-id', feed_id).data('feed-id', feed_id);
                    $('#socialfeeds-facebook-wizard-form').find('.socialfeeds-edit-name-btn').show();

                    // Update feed name display
                    let $text = $('.socialfeeds-feed-name-text'),
                        $input = $('.socialfeeds-feed-name-input'),
                        saved_name = res.data && res.data.feed_name ? res.data.feed_name : null;

                    if (saved_name) {
                        $text.text(saved_name);
                        $input.val(saved_name);
                    } else if ($text.length && $text.text().trim() === '') {
                        let default_name = 'Facebook Feed ' + feed_id;
                        $text.text(default_name);
                        $input.val(default_name);
                    }
                }
            } else {
                show_toast(res && res.data && res.data.message ? res.data.message : 'Error saving feed.', 'error');
            }
        }).fail(function(){
            show_toast('Network error saving feed.', 'error');
        }).always(function(){
            submit_btn.prop('disabled', false).text(original_text);
        });
    });

    // Facebook Modal Form Submission
    $('#socialfeeds-fb-modal-token-form').on('submit', function(e){
        e.preventDefault();
        let $form = $(this),
            $btn = $form.find('button[type="submit"]'),
            token = $form.find('#socialfeeds-modal-fb-token').val().trim(),
            page_id = $form.find('#socialfeeds-modal-fb-page-id').val().trim(),
            token_type = $form.find('input[name="fb_token_type"]').val() || 'advanced';

        if(!token || !page_id){
            alert('Please enter both Facebook Page ID and Access Token.');
            return;
        }

        $btn.prop('disabled', true).text('Connecting...');

        $.ajax({
            url: socialfeeds_pro.ajax_url,
            type: 'POST',
            data: {
                action: 'socialfeeds_pro_validate_facebook_token',
                access_token: token,
                facebook_page_id: page_id,
                token_type: token_type,
                facebook_app_id: $form.find('#socialfeeds-modal-fb-app-id').val(),
                facebook_app_secret: $form.find('#socialfeeds-modal-fb-app-secret').val(),
                nonce: socialfeeds_pro.nonce
            },
            success: function(resp){
                if(resp.success){
                    show_toast('Facebook Page connected successfully!');
                    location.reload();
                } else {
                    alert(resp.data.message || 'Error connecting Facebook Page.');
                }
            },
            error: function(){
                alert('Network error connecting Facebook Page.');
            },
            complete: function(){
                $btn.prop('disabled', false).text('Connect Page');
            }
        });
    });

    // Add new feed button in FB modal
    $('#socialfeeds-fb-add-new-feed').on('click', function(e){
        e.preventDefault();
        window.location.href = socialfeeds_pro.admin_page_url + '&action=create#facebook';
    });

    // Delete FB account from modal
    $(document).on('click', '.socialfeeds-delete-fb-account-btn', function(e){
        e.preventDefault();
        if(!confirm('Are you sure you want to delete this connected Page?')) return;
        
        let $btn = $(this),
            account_id = $btn.data('account-id');

        $.post(socialfeeds_pro.ajax_url, {
            action: 'socialfeeds_pro_delete_facebook_account',
            account_id: account_id,
            nonce: socialfeeds_pro.nonce
        }, function(resp){
            if(resp.success){
                $btn.closest('.socialfeeds-account-item-static').fadeOut(300, function(){ $(this).remove(); });
                show_toast('Page deleted.');
            } else {
                alert(resp.data.message || 'Error deleting page.');
            }
        });
    });

    // Facebook Header Cover Toggle
    $('input[name="facebook_header_cover_enabled"]').on('change', function(){
        let is_checked = $(this).is(':checked');
        $('#facebook-cover-upload-group').slideToggle(is_checked ? 300 : 0);
        if(typeof fetch_facebook_preview === 'function') {
            setTimeout(() => fetch_facebook_preview(), 300);
        }
    });

    // Facebook Custom Cover Upload Handler
    let facebook_cover_frame;
    $(document).on('click', '.facebook-cover-upload-btn', function(e){
        e.preventDefault();
        if(facebook_cover_frame) {
            facebook_cover_frame.open();
            return;
        }
        facebook_cover_frame = wp.media({
            title: 'Select Facebook Background Image',
            button: { text: 'Use this image' },
            multiple: false
        });
        facebook_cover_frame.on('select', function(){
            let attachment = facebook_cover_frame.state().get('selection').first().toJSON();
            let $preview_box = $('.socialfeeds-cover-preview-box');
            let $cover_input = $('input[name="facebook_header_cover_image"]');
            let $cover_id_input = $('input[name="facebook_header_cover_image_id"]');
            
            $cover_input.val(attachment.url);
            $cover_id_input.val(attachment.id);
            
            // Update preview in sidebar
            $preview_box.html('<img src="' + attachment.url + '" style="max-width:100%; max-height:100%; border-radius:4px;" />');
            
            show_toast('Background image uploaded successfully');
            
            if(typeof fetch_facebook_preview === 'function') {
                fetch_facebook_preview();
            }
        });
        facebook_cover_frame.open();
    });

    // Facebook Header Avatar Toggle
    $('input[name="facebook_header_avatar_enabled"]').on('change', function(){
        if(typeof fetch_facebook_preview === 'function') {
            fetch_facebook_preview();
        }
    });

    // Facebook Caption Toggle
    $('input[name="facebook_header_caption_enabled"]').on('change', function(){
        if(typeof fetch_facebook_preview === 'function') {
            fetch_facebook_preview();
        }
    });

    // Facebook Stats Toggle
    $('input[name="facebook_header_stats_enabled"]').on('change', function(){
        if(typeof fetch_facebook_preview === 'function') {
            fetch_facebook_preview();
        }
    });

    // Facebook Load More Handler
    $(document).on('click', '.socialfeeds-fb-load-more-btn', function(e){
        e.preventDefault();
        let $btn = $(this);
        let original_text = $btn.text();
        let count = parseInt($btn.data('load-count')) || 9;
        
        $btn.text('Loading...').prop('disabled', true);
        
        let form = $('#socialfeeds-facebook-wizard-form');
        let initial_limit = parseInt(form.find('input[name="facebook_posts_per_page"]').val()) || 12;
        let current_count = socialfeeds_fb_posts.length || 0;
        let new_limit = current_count + count;
        
        let data = {
            action: 'socialfeeds_pro_facebook_fetch_posts',
            selected_account: form.find('input[name="facebook_selected_account"]:checked').val(),
            limit: new_limit,
            feed_type: form.find('select[name="facebook_feed_type"]').val() || form.find('input[name="feed_type"]').val(),
            sort_by: form.find('select[name="facebook_sort_by"]').val() || 'newest',
            nonce: socialfeeds_pro.nonce
        };
        
        $.post(socialfeeds_pro.ajax_url, data, function(res){
            if(res.success){
                socialfeeds_fb_posts = res.data.posts || [];
                socialfeeds_fb_account = res.data.account || null;
                let sortBy = form.find('select[name="facebook_sort_by"]').val() || 'newest';
                let posts = apply_facebook_sort(socialfeeds_fb_posts, sortBy);
                render_facebook_preview(posts, socialfeeds_fb_account);
            }
            $btn.text(original_text).prop('disabled', false);
        });
    });

    // Facebook Item Click Handler (Lightbox/Inline)
    $(document).on('click', '#socialfeeds-facebook-preview .socialfeeds-facebook-item', function(e){
        e.preventDefault();

        let $item = $(this);
        let permalink = $item.data('permalink');
        let media_url = $item.data('media-url');
        let media_type = $item.data('media-type');
        let form = $('#socialfeeds-facebook-wizard-form');
        let play_mode = form.find('select[name="facebook_play_mode"]').val() || 'newtab';

        if (play_mode === 'newtab') {
            if (permalink) window.open(permalink, '_blank');
        } else if (play_mode === 'lightbox') {
            if (!media_url) {
                show_toast('No media URL available', 'error');
                return;
            }

            let media_content = '';
            if (media_type === 'VIDEO') {
                media_content = `
                    <video src="${media_url}" controls autoplay
                        style="max-width: 90vw; max-height: 80vh; border-radius: 12px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); border: 2px solid rgba(255,255,255,0.1);">
                        Your browser does not support the video tag.
                    </video>`;
            } else {
                media_content = `
                    <img src="${media_url}"
                        style="max-width: 90vw; max-height: 80vh; border-radius: 12px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); object-fit: contain; border: 2px solid rgba(255,255,255,0.1);">`;
            }

            let $lightbox = $(`
                <div class="socialfeeds-premium-lightbox" style="position: fixed; inset: 0; z-index: 999999; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(8px); background: rgba(0, 0, 0, 0.85); opacity: 0; transition: opacity 0.3s ease;">
                    <button class="close-btn" style="position: absolute; top: 30px; right: 30px; width: 44px; height: 44px; background: rgba(255,255,255,0.1); border: none; border-radius: 50%; color: white; font-size: 24px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s;">&times;</button>
                    <div class="lightbox-content-wrap" style="transform: scale(0.9); transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);">
                        ${media_content}
                        ${permalink ? `<div style="text-align: center; margin-top: 15px;"><a href="${permalink}" target="_blank" style="color: white; text-decoration: none; background: rgba(255,255,255,0.1); padding: 8px 16px; border-radius: 20px; font-size: 13px; font-weight: 500;">View on Facebook</a></div>` : ''}
                    </div>
                </div>
            `);

            $('body').append($lightbox);

            setTimeout(() => {
                $lightbox.css('opacity', '1');
                $lightbox.find('.lightbox-content-wrap').css('transform', 'scale(1)');
            }, 10);

            let close_lightbox = () => {
                $lightbox.css('opacity', '0');
                $lightbox.find('.lightbox-content-wrap').css('transform', 'scale(0.9)');
                setTimeout(() => $lightbox.remove(), 300);
            };

            $lightbox.on('click', function (evt) {
                if (evt.target === this || $(evt.target).hasClass('close-btn')) {
                    close_lightbox();
                }
            });

            $(document).on('keydown.sf_lightbox', function (evt) {
                if (evt.key === 'Escape') {
                    close_lightbox();
                    $(document).off('keydown.sf_lightbox');
                }
            });

        } else if (play_mode === 'inline') {
            if (media_type !== 'VIDEO') {
                // Images open in new tab in inline mode (same behaviour as Instagram)
                if (permalink) window.open(permalink, '_blank');
                return;
            }
            let $media_box = $item.find('.socialfeeds-preview-media');
            if ($media_box.find('video').length) return; // Already playing
            $media_box.html(`<video src="${media_url}" controls autoplay playsinline style="width: 100%; height: 100%; object-fit: cover; border-radius: inherit;"></video>`);
        }
    });

    // Initial Fetch for Edit Mode
    if($('#socialfeeds-facebook-wizard-form .socialfeeds-wizard-tab[data-tab="customize"]').hasClass('active')){
        fetch_facebook_preview();
    }
    
    $('.socialfeeds-delete-google-account-btn').on('click', function(e) {
            e.preventDefault();
            if(!confirm('Are you sure you want to delete this connected location?')) return;
        
            let $btn = $(this),
                account_id = $btn.data('account-id');

            $.post(socialfeeds_pro.ajax_url, {
                action: 'socialfeeds_pro_delete_google_account',
                account_id: account_id,
                nonce: socialfeeds_pro.nonce
            }, function(resp){
                if(resp.success){
                    $btn.closest('.socialfeeds-account-item-static').fadeOut(300, function(){ $(this).remove(); });
                    show_toast('Location deleted.');
                }else{
                    alert(resp.data.message || 'Error deleting location.');
                }
            });
    });
    
    $('#socialfeeds-wizard-validate-place-btn').on('click', function (e) {
        e.preventDefault();

        let btn = $(this),
        msg_div = $('#socialfeeds-google-wizard-token-message'),
        place_id = $('#socialfeeds-google-wizard-place-id').val().trim();

        if(!place_id){
            msg_div.show().css('color', '#d32f2f').text('Please enter a Place ID').fadeIn();
            return;
        }

        btn.prop('disabled', true).text('Adding...');

        $.ajax({
            url: socialfeeds_pro.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'socialfeeds_pro_add_google_place',
                place_id: place_id,
                place_name: '',
                place_address: '',
                nonce: socialfeeds_pro.nonce
            },
            success: function (resp) {
                if(resp && resp.success){
                    msg_div.show().css('color', '#4caf50').text('✓ Location added!').fadeIn();
                }else{
                    let msg = (resp && resp.data && resp.data.message) ? resp.data.message : 'Failed to add location';

                    msg_div.show().css('color', '#d32f2f').text(msg).fadeIn();
                }
            },
            error: function (xhr) {
                let errmsg = (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) ? xhr.responseJSON.data.message : 'Network error';
                msg_div.show().css('color', '#d32f2f').text('✗ Error: ' + errmsg).fadeIn();
            },
            complete: function () {
                btn.prop('disabled', false).text('Add');
            }
        });
    });

    $('#socialfeeds-google-modal-token-form').on('submit', function (e) {
        e.preventDefault();

        let $form = $(this),
            apiKey = $('#socialfeeds-modal-google-api-key').val().trim(),
            submitBtn = $form.find('button[type="submit"]'),
            originalText = submitBtn.text();

        if(!apiKey){
            show_toast('Please enter your Google API key.', 'error');
            return;
        }

        submitBtn.prop('disabled', true).text('Saving...');

        $.ajax({
            url: socialfeeds_pro.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'socialfeeds_pro_save_google_api_key',
                google_api_key: apiKey,
                nonce: socialfeeds_pro.nonce
            },
            success: function (response) {
                if(response && response.success){
                    show_toast('Google API key saved successfully.');
                    setTimeout(function () {
                    location.reload();
                    }, 800);
                }else{
                    let err = 'Unable to save API key.';
                    if (response && response.data && response.data.message) {
                    err = response.data.message;
                    }
                    show_toast(err, 'error');
                }
            },
            error: function () {
                show_toast('Error saving Google API key.', 'error');
            },
            complete: function () {
                submitBtn.prop('disabled', false).text(originalText);
            }
        });
    });

    $('form#socialfeeds-google-wizard-form input[name="google_reviews_selected_account"]').on('change', function () {
        let $form = $(this).closest('#socialfeeds-google-wizard-form');
        if ($form.find('#socialfeeds-step-2').is(':visible')) {
            fetch_google_reviews_preview();
        }
    });

    $('#socialfeeds-google-header-enabled, #socialfeeds-google-header-text, input[name="google_reviews_layout"], #socialfeeds-google-columns-desktop, #socialfeeds-google-padding').on('change input', function () {
        if (google_reviews_preview_data) {
            render_google_reviews_preview(google_reviews_preview_data);
        }
    });

    // Google Reviews Wizard Form Submission
    $('#socialfeeds-google-wizard-form').on('submit', function(e){
        e.preventDefault();
        let form = $(this);
        let data = form.serialize();

        let account_index = form.find('input[name="google_reviews_selected_account"]:checked').val();
        if (account_index !== undefined) {
            data += '&selected_account=' + account_index;
        }

        let submit_btn = form.find('button[type="submit"]'),
        original_text = submit_btn.text();
        submit_btn.prop('disabled', true).text('Saving...');

        $.post(socialfeeds_pro.ajax_url, data, function(res){
            if(res && res.success){
                show_toast('Google Reviews feed saved!');

                let feed_id = res.data && res.data.feed_id ? res.data.feed_id : form.find('input[name="edit_id"]').val();

                if(feed_id){
                    // Update global tracking to prevent conflicts
                    if (window.socialfeedsData && window.socialfeedsData.existing_ids && !socialfeedsData.existing_ids.includes(feed_id)) {
                        socialfeedsData.existing_ids.push(feed_id);
                    }
                    // Update hidden edit_id so next save updates this feed
                    if(form.find('input[name="edit_id"]').length){
                        form.find('input[name="edit_id"]').val(feed_id);
                    }else{
                        form.append('<input type="hidden" name="edit_id" value="' + feed_id + '">');
                    }

                    // Update shortcode display
                    let shortcode_text = '[socialfeeds id="' + feed_id + '" platform="google_reviews"]';
                    form.find('#socialfeeds-top-shortcode').text(shortcode_text);
                    form.find('.socialfeeds-copy-shortcode').attr('data-shortcode', shortcode_text);

                    // Update URL without reload
                    let currentUrl = new URL(window.location.href);
                    currentUrl.searchParams.set('edit_id', feed_id);
                    window.history.pushState({ path: currentUrl.href }, '', currentUrl.href);

                    // Show pencil now that feed is saved
                    $('.socialfeeds-save-name-btn').attr('data-feed-id', feed_id).data('feed-id', feed_id);
                    $('#socialfeeds-google-wizard-form').find('.socialfeeds-edit-name-btn').show();

                    // Update feed name display
                    let $text = $('.socialfeeds-feed-name-text'),
                        $input = $('.socialfeeds-feed-name-input'),
                        saved_name = res.data && res.data.feed_name ? res.data.feed_name : null;

                    if(saved_name){
                        $text.text(saved_name);
                        $input.val(saved_name);
                    }else if ($text.length && $text.text().trim() === ''){
                        let default_name = 'Google Reviews Feed ' + feed_id;
                        $text.text(default_name);
                        $input.val(default_name);
                    }
                }
            }else{
                show_toast(res && res.data && res.data.message ? res.data.message : 'Error saving feed.', 'error');
            }
        }).fail(function(){
            show_toast('Network error saving feed.', 'error');
        }).always(function(){
            submit_btn.prop('disabled', false).text(original_text);
        });
    });

    $('#socialfeeds-google-add-location-btn').on('click', function (e) {
        e.preventDefault();

        let btn = $(this),
            msg_div = $('#socialfeeds-google-wizard-token-message'),
            place_id = $('#socialfeeds-google-wizard-place-id').val().trim();

        if (!place_id) {
            msg_div.show().css('color', '#d32f2f').text('Please enter a Place ID');
            return;
        }

        btn.prop('disabled', true).text('Adding...');

        $.ajax({
            url: socialfeeds_pro.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'socialfeeds_pro_add_google_place',
                place_id: place_id,
                place_name: '',
                place_address: '',
                nonce: socialfeeds_pro.nonce
            },
            success: function (resp) {
                if (resp && resp.success) {
                    msg_div.show().css('color', '#4caf50').text('✓ Location added!');
                } else {
                    let msg = resp?.data?.message || 'Failed to add location';
                    msg_div.show().css('color', '#d32f2f').text(msg);
                }
            },
            error: function (xhr) {
                let errmsg = xhr?.responseJSON?.data?.message || 'Network error';
                msg_div.show().css('color', '#d32f2f').text('✗ Error: ' + errmsg);
            },
            complete: function () {
                btn.prop('disabled', false).text('Add Location');
            }
        });
    });

    // Google Reviews instant preview updates
    $('#socialfeeds-google-header-enabled, #socialfeeds-google-header-title, #socialfeeds-google-custom-header-text, #socialfeeds-google-header-description, #socialfeeds-google-show-date, #socialfeeds-google-rating-enabled, #socialfeeds-google-rating-bg-color, #socialfeeds-google-show-text, #socialfeeds-google-show-author, #socialfeeds-google-show-author-image, input[name="google_reviews_layout"], #socialfeeds-google-columns-desktop, #socialfeeds-google-columns-mobile, #socialfeeds-google-padding, #socialfeeds-google-sort-by, #socialfeeds-google-min-rating, #socialfeeds-google-hover-state, #socialfeeds-rating-bg-color, #socialfeeds-rating-hover-color, #socialfeeds-google-color-scheme, #socialfeeds-google-custom-color').on('change input', function (e) {
        if($('input[name="google_reviews_selected_account"]:checked').length > 0){
            fetch_google_reviews_preview();
        }
    });

    // Device toggle re-render
    $('.socialfeeds-preview-device-btn').on('click', function() {
        if($('#socialfeeds-google-wizard-form:visible').length > 0 && $('input[name="google_reviews_selected_account"]:checked').length > 0){
            fetch_google_reviews_preview();
        }
    });

    // Update rating star colors instantly
    $('#socialfeeds-rating-bg-color, #socialfeeds-rating-hover-color, #socialfeeds-google-custom-color').on('change input', function (e) {
        let bgColor = $('#socialfeeds-rating-bg-color').val() || '#efad05';
        let hoverColor = $('#socialfeeds-rating-hover-color').val() || '#CC0000';
        
        // Update existing stars in preview
        $('.socialfeeds-preview-item .dashicons-star-filled, .socialfeeds-preview-item .dashicons-star-empty').css('color', bgColor);
        
        // Update header star
        $('.socialfeeds-preview-header .dashicons-star-filled').css('color', bgColor);
        
        // For custom color input, trigger full preview update
        if($(this).attr('id') === 'socialfeeds-google-custom-color'){
            if($('input[name="google_reviews_selected_account"]:checked').length > 0){
                fetch_google_reviews_preview();
            }
        }
    });

    $('#socialfeeds-google-next-btn').on('click', function(e){    
        e.preventDefault();

        let $form = $(this).closest('#socialfeeds-google-wizard-form');
        let selectedLocation = $form.find('input[name="google_reviews_selected_account"]:checked').val();

        if(!selectedLocation && selectedLocation !== '0'){
            alert('Please select a location to continue');
            return;
        }

        $form.find('#socialfeeds-google-tab-customize').trigger('click');
    });

    $('#socialfeeds-google-wizard-form .socialfeeds-wizard-tab').on('click', function(e){
        e.preventDefault();
        e.stopPropagation();

        let $form = $(this).closest('#socialfeeds-google-wizard-form');
        let tab = $(this).data('tab');

        if (tab === 'customize') {
            let selectedLocation = $form.find('input[name="google_reviews_selected_account"]:checked').val();
            if(!selectedLocation && selectedLocation !== '0'){
                alert('Please select a location to continue');
                return;
            }
        }

        $form.find('.socialfeeds-wizard-tab').removeClass('active');
        $(this).addClass('active');

        $form.find('.socialfeeds-wizard-step').removeClass('active').hide();
        let targetStep = tab === 'source' ? 'socialfeeds-step-1' : 'socialfeeds-step-2';
        $form.find('#' + targetStep).addClass('active').show();

        if (tab === 'customize' && typeof fetch_google_reviews_preview === 'function') {
            fetch_google_reviews_preview();
        }

        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    $('#socialfeeds-wizard-validate-place-btn, #socialfeeds-google-add-location-btn').on('click', function(e){
        e.preventDefault();

        let btn = $(this),
            msg_div = $('#socialfeeds-google-wizard-token-message'),
            place_id = $('#socialfeeds-google-wizard-place-id').val().trim();

        if(!place_id){
            msg_div.show().css('color', '#d32f2f').text('Please enter a Place ID').fadeIn();
            return;
    }

    btn.prop('disabled', true).text('Adding...');

        $.ajax({
            url: socialfeeds_pro.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'socialfeeds_pro_add_google_place',
                place_id: place_id,
                place_name: '',
                place_address: '',
                nonce: socialfeeds_pro.nonce
            },
            success: function (resp) {
                if(resp && resp.success){
                    msg_div.show().css('color', '#4caf50').text('✓ Location added!').fadeIn();
                    location.reload();
                }else{
                    let msg = (resp && resp.data && resp.data.message) ? resp.data.message : 'Failed to add location';
                    msg_div.show().css('color', '#d32f2f').text(msg).fadeIn();
                }
            },
            error: function (xhr) {
                let errmsg = (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message)? xhr.responseJSON.data.message : 'Network error';
                msg_div.show().css('color', '#d32f2f').text('✗ Error: ' + errmsg).fadeIn();
            },
            complete: function () {
                btn.prop('disabled', false).text('Add');
            }
        });
    });

    $('#socialfeeds-google-fetch-preview-btn-sidebar').on('click', function(e){    
        e.preventDefault();
        if(typeof fetch_google_reviews_preview === 'function'){
            fetch_google_reviews_preview();
        }
    });

    if ($('#socialfeeds-google-tab-customize').length && $('#socialfeeds-google-tab-customize').hasClass('active')) {
        let $selectedAccount = $('input[name="google_reviews_selected_account"]:checked');
        if ($selectedAccount.length === 0) {
            let $radios = $('input[name="google_reviews_selected_account"]');
            if ($radios.length === 1) {
                $radios.prop('checked', true).closest('.socialfeeds-account-item').addClass('selected');
                $selectedAccount = $radios;
            }
        }

        if ($selectedAccount.length > 0 && typeof fetch_google_reviews_preview === 'function') {
            fetch_google_reviews_preview();
        }
    }

    function google_reviews_settings() {
        return {
            google_reviews_header_enabled: $('#socialfeeds-google-header-enabled').is(':checked'),
            google_custom_header_text: $('#socialfeeds-google-custom-header-text').val() || '',
            google_reviews_header_description: $('#socialfeeds-google-header-description').is(':checked'),
            google_reviews_header_title: $('#socialfeeds-google-header-title').is(':checked'),
            show_rating: $('#socialfeeds-google-rating-enabled').is(':checked'),
            show_date: $('#socialfeeds-google-show-date').is(':checked'),
            show_text: $('#socialfeeds-google-show-text').is(':checked'),
            show_author: $('#socialfeeds-google-show-author').is(':checked'),
            show_author_image: $('#socialfeeds-google-show-author-image').is(':checked'),
            layout: $('input[name="google_reviews_layout"]:checked').val(),
            columns_desktop: parseInt($('#socialfeeds-google-columns-desktop').val()) || 3,
            columns_mobile: parseInt($('#socialfeeds-google-columns-mobile').val()) || 1,
            spacing: parseInt($('#socialfeeds-google-padding').val()) || 14,
            sort_by: $('#socialfeeds-google-sort-by').val() || 'newest',
            min_rating: parseInt($('#socialfeeds-google-min-rating').val()) || 1,
            hover_state: $('#socialfeeds-google-hover-state').val() || 'overlay',
            rating_bg_color: $('#socialfeeds-google-rating-bg-color').val() || '#efad05',
            rating_hover_color: $('#socialfeeds-rating-hover-color').val() || '#CC0000',
            color_scheme: $('#socialfeeds-google-color-scheme').val() || 'light',
            custom_color: $('#socialfeeds-google-custom-color').val() || '#000000'

        };
    }

    function render_google_reviews_preview(data) {

        let settings = google_reviews_settings();
        let $preview_wrapper = $('.socialfeeds-preview-box-wrapper'),
        color_scheme = settings.color_scheme || 'light',
        custom_color = settings.custom_color || '#000000';

        if(color_scheme === 'dark'){
            $preview_wrapper.css({ background: '#0f0f0f', padding: '20px', borderRadius: '8px' });
        }else if (color_scheme === 'light'){
            $preview_wrapper.css({ background: '#ffffff', padding: '20px', borderRadius: '8px', border: '1px solid #eeeeee' });
        }else if (color_scheme === 'custom'){
            $preview_wrapper[0].style.setProperty('background', custom_color, 'important');
            $preview_wrapper.css({ padding: '20px', borderRadius: '8px' });
        }else{
            $preview_wrapper.css({ background: '', padding: '', borderRadius: '', border: '' });
        }
        // Determine if dark mode for text colors
        let is_dark = (color_scheme === 'dark');
        if(color_scheme === 'custom'){
            is_dark = (function (hex) {
                if (!hex || hex.indexOf('#') !== 0) return false;
                let r = parseInt(hex.slice(1, 3), 16),
                g = parseInt(hex.slice(3, 5), 16),
                b = parseInt(hex.slice(5, 7), 16);
                return (r * 0.299 + g * 0.587 + b * 0.114) < 128;
            }(custom_color));
        }

        let header_html = '';

        // === HEADER ====
        if(settings.google_reviews_header_enabled){
            header_html += '<div style="margin-bottom:18px; padding-bottom:12px; border-bottom:1px solid #e2e8f0;">';
            if(settings.google_reviews_header_title){
                let title = settings.google_custom_header_text?.trim()? settings.google_custom_header_text: (data.place_name || data.name || '');
                if (title) {
                    header_html += '<h3 style="margin:0 0 6px; font-size:20px;">'+ $('<div>').text(title).html()+ '</h3>';
                }
            }

            if(settings.google_reviews_header_description){

                if(data.address || data.rating !== undefined || data.url){
                    header_html += '<div style="display:flex; gap:12px; font-size:16px; flex-wrap:wrap;">';

                    if(data.address){
                        header_html += '<span>' + $('<div>').text(data.address).html() + '</span>';
                    }

                    if(data.rating !== undefined){
                        header_html += '<span>' + data.rating + ' <span class="dashicons dashicons-star-filled" style="color: ' + settings.rating_bg_color + ';"></span> (' + (data.review_count || data.user_ratings_total || 0) + ')</span>';
                    }

                    if(data.url){
                        header_html += '<a href="' + data.url + '" target="_blank">View on Google</a>';
                    }

                    header_html += '</div>';
                }
            }

            header_html += '</div>';

        }else{
            header_html = '';
        }

        $('#socialfeeds-google-preview-header') .html(header_html) .toggle(!!header_html);
            
        let reviews = Array.isArray(data.reviews) ? [...data.reviews] : [];
        reviews = reviews.filter(review => (review.rating || 0) >= settings.min_rating);
        let sortBy = settings.sort_by || 'newest';

        if(sortBy === 'rating'){
            reviews.sort((a, b) => (b.rating || 0) - (a.rating || 0));
        }

        else if(sortBy === 'newest'){
            reviews.sort((a, b) => {
                return (b.time || 0) - (a.time || 0); 
            });
        }

        else if(sortBy === 'random'){
            reviews.sort(() => Math.random() - 0.5);
        }

        // SETTINGS
        let device = get_current_device();
        let columns = settings.columns_desktop || 3;
        if(device === 'mobile'){
            columns = settings.columns_mobile || 1;
        }
        let gap = (settings.spacing || 14) + 'px';
        let layout = settings.layout || 'grid';
        let items_html = '';

        // ===LAYOUT =================

        if(layout === 'grid' || layout === 'list'){
            items_html += '<div>';
        }  
        else if(layout === 'carousel'){
        }

        // ==== ITEMS ====
        reviews.slice(0, 6).forEach(function(review) {

            let author_block = '';

            if(settings.show_author || settings.show_author_image){
                
                author_block += '<div style="display:flex; align-items:center; gap:10px; margin-bottom:10px;">';

                // IMAGE
                if(settings.show_author_image && review.profile_photo_url){
                    author_block += '<img src="' + review.profile_photo_url + '" style="width:36px;height:36px;border-radius:50%;">';
                }

                // NAME
                if(settings.show_author && review.author_name){
                    author_block += '<div style="font-weight:600;">' + review.author_name + '</div>';
                }

                author_block += '</div>';
            }

            // STARS
            let stars = '';
            let star_count = parseInt(review.rating) || 0;

            for(let i = 0; i < 5; i++){
                if (i < star_count) {
                    stars += '<span class="dashicons dashicons-star-filled" style="color: ' + settings.rating_bg_color + ';"></span>';
                }else{
                    stars += '<span class="dashicons dashicons-star-empty" style="color: ' + settings.rating_bg_color + ';"></span>';
                }
            }

            let meta_html = '';

            if(settings.show_rating){
                meta_html += '<div style="font-size:13px;">' + stars + '</div>';
            }

            if(settings.show_date && review.relative_time_description){
                meta_html += '<div style="font-size:12px; color:#666;">' + review.relative_time_description + '</div>';
            }

            let text_html = '';

            if(settings.show_text && review.text){
                text_html = '<div style="font-size:15px; margin-top:6px;">' + review.text + '</div>';
            }

            // CARD STYLE
            let card_style = 'background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:14px; position:relative; overflow:hidden;';

            // CAROUSEL FIX
            if(layout === 'carousel'){
                card_style += ' flex:0 0 auto; scroll-snap-align:start;';
            }

            items_html += '<div class="socialfeeds-preview-item hover-' + settings.hover_state + '" style="' + card_style + '">' +
                '<div class="socialfeeds-preview-media" style="background: none !important; "transition: transform 0.3s ease, box-shadow 0.3s ease;">' + author_block +
                (settings.hover_state === 'overlay' ? '<div class="socialfeeds-hover-overlay"></div>' : '') +meta_html +text_html +
                '</div>' +
                '</div>';
        });

        if(layout !== 'carousel'){
            items_html += '</div>';
        }

        $('#socialfeeds-google-preview-grid').html(items_html);

        // Apply layout styles
        let preview_box = $('#socialfeeds-google-preview-grid');

        // Cleanup previous styles
        preview_box.css({
            display: '',
            overflowX: '',
            gap: '',
            scrollSnapType: ''
        });
        preview_box.find('> div').css({
            display: '',
            'grid-template-columns': '',
            gap: '',
            'column-count': '',
            'column-gap': '',
            minWidth: '',
            flex: '',
            scrollSnapAlign: ''
        });
        
        //  carousel
        let $parent = preview_box.parent();
        if($parent.hasClass('socialfeeds-carousel-stage')){
            $parent.find('.socialfeeds-carousel-nav').remove();
            $parent.siblings('.socialfeeds-carousel-dots').remove();
            preview_box.unwrap();
        }

        if(layout === 'grid'){
            preview_box.find('> div').css({
                display: 'grid',
                'grid-template-columns': 'repeat(' + columns + ', 1fr)',
                gap: gap
            });
        } 
        else if (layout === 'list') {
            preview_box.find('> div').css({
                display: 'grid',
                'grid-template-columns': '1fr',
                gap: gap
            });
        }
        else if (layout === 'carousel') {
            let items_per_page = Math.max(1, Math.min(columns, reviews.length || columns));
            preview_box.css({
                display: 'flex',
                overflowX: 'auto',
                gap: gap,
                scrollSnapType: 'x mandatory'
            });
            // Set item width to show exactly items_per_page items
            let gapValue = parseInt(gap) || 14;
            let widthExpression = 'calc((100% - ' + ((items_per_page - 1) * gapValue) + 'px) / ' + items_per_page + ')';
            preview_box.find('> div').css({
                flex: '0 0 auto',
                width: widthExpression,
                minWidth: '0',
                maxWidth: widthExpression
            });
            init_carousel_controls(preview_box);
        }
    }

    function fetch_google_reviews_preview() {
        let selected_account_index = $('input[name="google_reviews_selected_account"]:checked').val();
        let status_span = $('#socialfeeds-fetch-status');
        let loader = $('.socialfeeds-wizard-loader-overlay');

        if(selected_account_index === undefined || selected_account_index === null){
            $('#socialfeeds-google-preview-grid').html('<div class="socialfeeds-no-preview">Please select a Google Reviews location.</div>');
            status_span.text('Please select a location.').css('color', '#ef4444').show();
            return;
        }

        status_span.show().text('Fetching reviews...').css('color', '#2563eb');
        loader.addClass('active');

        $.ajax({
            url: socialfeeds_pro.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'socialfeeds_pro_fetch_google_reviews_preview',
                selected_account_index: selected_account_index,
                nonce: socialfeeds_pro.nonce
            },
            success: function (response) {
                loader.removeClass('active');
                if (!response || !response.success || !response.data) {
                    let err = (response && response.data && response.data.message) ? response.data.message : 'Failed to fetch Google reviews';
                    status_span.text(err).css('color', '#ef4444').show();
                    $('#socialfeeds-google-preview-grid').html('<div class="socialfeeds-no-preview">' + err + '</div>');
                    return;
                }

            google_reviews_preview_data = response.data;
            render_google_reviews_preview(response.data);
            status_span.text('Review preview loaded').css('color', '#10b981').show();

            },
            error: function (xhr) {
                loader.removeClass('active');
                let err = 'Error fetching Google reviews.';
                if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                    err = xhr.responseJSON.data.message;
                }
                status_span.text(err).css('color', '#ef4444').show();
                $('#socialfeeds-google-preview-grid').html('<div class="socialfeeds-no-preview">' + err + '</div>');
            }
        });
    }

});
