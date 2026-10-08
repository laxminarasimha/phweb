jQuery(document).ready(function ($) {

	// ===== INSTAGRAM PREMIUM LIGHTBOX (Matches Preview) =====
	function init_insta_Modal() {
		if (window._socialfeeds_modal_inited) return;
		window._socialfeeds_modal_inited = true;

		$(document).on('click', '.socialfeeds-open-modal-media', function (e) {
			let $feedContainer = $(this).closest('.socialfeeds-instagram-feed, .socialfeeds-facebook-feed');
			let isFacebook = $feedContainer.hasClass('socialfeeds-facebook-feed');
			let playMode = $feedContainer.attr('data-play-mode') || 'newtab';

			// Only intercept if play mode is lightbox
			if (playMode !== 'lightbox') return;

			e.preventDefault();

			let mediaUrl = $(this).attr('data-media') || $(this).find('img').attr('src') || '';
			let type = $(this).attr('data-type') || 'IMAGE';
			let permalink = $(this).attr('data-permalink') || '#';

			if (!mediaUrl) return;

			let media_content = '';
			if (type === 'VIDEO') {
				media_content = `
					<video src="${mediaUrl}" controls autoplay 
						style="max-width: 90vw; max-height: 80vh; border-radius: 12px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); border: 2px solid rgba(255,255,255,0.1);">
						Your browser does not support the video tag.
					</video>`;
			} else {
				media_content = `
					<img src="${mediaUrl}" 
						style="max-width: 90vw; max-height: 80vh; border-radius: 12px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); object-fit: contain; border: 2px solid rgba(255,255,255,0.1);">`;
			}

			const $lightbox = $(`
				<div class="socialfeeds-premium-lightbox" style="position: fixed; inset: 0; z-index: 999999; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(8px); background: rgba(0, 0, 0, 0.85); opacity: 0; transition: opacity 0.3s ease;">
					<button class="close-btn" style="position: absolute; top: 30px; right: 30px; width: 44px; height: 44px; background: rgba(255,255,255,0.1); border: none; border-radius: 50%; color: white; font-size: 24px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s;">&times;</button>
					<div class="lightbox-content-wrap" style="transform: scale(0.9); transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);">
						${media_content}
						${permalink && permalink !== '#' ? `<div style="text-align: center; margin-top: 15px;"><a href="${permalink}" target="_blank" style="color: white; text-decoration: none; background: rgba(255,255,255,0.1); padding: 8px 16px; border-radius: 20px; font-size: 13px; font-weight: 500;">View on ${isFacebook ? 'Facebook' : 'Instagram'}</a></div>` : ''}
					</div>
				</div>
			`);

			$('body').append($lightbox);

			// Animate In
			setTimeout(() => {
				$lightbox.css('opacity', '1');
				$lightbox.find('.lightbox-content-wrap').css('transform', 'scale(1)');
			}, 10);

			const close_lightbox = () => {
				$lightbox.css('opacity', '0');
				$lightbox.find('.lightbox-content-wrap').css('transform', 'scale(0.9)');
				setTimeout(() => $lightbox.remove(), 300);
			};

			$lightbox.on('click', function (evt) {
				if (evt.target === this || $(evt.target).hasClass('close-btn')) {
					close_lightbox();
				}
			});

			$(document).on('keydown.sf_lightbox_frontend', function (evt) {
				if (evt.key === 'Escape') {
					close_lightbox();
					$(document).off('keydown.sf_lightbox_frontend');
				}
			});
		});
	}

	$('.socialfeeds-load-more-btn').on('click', function (e) {
		e.preventDefault();

		let $btn = $(this);
		
		let feedType = $btn.data('feed-type');

		// ===== INSTAGRAM LOAD MORE =====
		if (feedType === 'instagram') {
			let nextUrl = $btn.attr('data-next-url');
			let loaded = parseInt($btn.data('loaded'), 10) || 0;
			let $feed = $btn.closest('.socialfeeds-instagram-feed');
			let $itemsContainer = $feed.find('.socialfeeds-instagram-inner');

			if (!nextUrl) {
				$btn.hide();
				return;
			}

			let original_text = $btn.text();
			$btn.prop('disabled', true).addClass('socialfeeds-loading');

			$.ajax({
				url: socialfeeds_ajax.ajax_url,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'socialfeeds_load_more_instagram',
					next_url: nextUrl,
					load_count: parseInt($btn.data('load-count')) || 12,
					show_reels: $btn.data('show-reels'),
	    			show_feed_posts: $btn.data('show-feed-posts'),
					layout: $btn.data('layout'),
					padding: $btn.data('padding'),
					cols: $btn.data('cols'),
					aspect_ratio: $btn.data('aspect-ratio'),
					caption_enabled: $btn.data('caption-enabled'),
					likes_enabled: $btn.data('likes-enabled'),
					comments_enabled: $btn.data('comments-enabled'),
					views_enabled: $btn.data('views-enabled'),
					hover_state: $btn.data('hover-state'),
					alignment: $btn.data('alignment') || 'left',
					play_mode: $btn.data('play-mode'),
					show_play_icon: $btn.data('show-play-icon'),
					nonce: socialfeeds_ajax.nonce
				},
				success: function (response) {

					if (!response.success || !response.data) {
						$btn.text('No More Posts').hide();
						return;
					}

					if (response.data.html) {
						$itemsContainer.append(response.data.html);
						// If carousel, trigger resize to update dots/nav
						if ($itemsContainer.hasClass('layout-carousel')) {
							$(window).trigger('resize');
						}
					}

					let new_loaded = loaded + response.data.count;
					$btn.data('loaded', new_loaded).attr('data-loaded', new_loaded);

					if (response.data.next) {
						$btn.attr('data-next-url', response.data.next);
						$btn.prop('disabled', false)
							.removeClass('socialfeeds-loading')
							.text(original_text);
					} else {
						$btn.text('No More Posts').hide();
					}
				},
				error: function () {
					console.error('SocialFeeds Instagram AJAX Error');
					$btn.prop('disabled', false)
						.removeClass('socialfeeds-loading')
						.text('Error loading posts');
				}
			});
		}
		// ===== FACEBOOK LOAD MORE =====
		else if (feedType === 'facebook') {
			let original_text = $btn.text();
			let $feed = $btn.closest('.socialfeeds-facebook-feed');
			let $itemsContainer = $feed.find('.socialfeeds-facebook-inner');
			let feedId = $btn.data('feed-id');
			let loadCount = parseInt($btn.data('load-count')) || 9;
			// Use Facebook cursor-based pagination — numeric offset causes duplicate posts
			let afterCursor = $btn.attr('data-after-cursor') || '';

			if (!afterCursor) {
				$btn.text('No More Posts').hide();
				return;
			}

			// Collect IDs of posts already on the page so the server can filter duplicates
			let existingIds = [];
			$itemsContainer.find('.socialfeeds-facebook-item[data-post-id]').each(function () {
				let pid = $(this).attr('data-post-id');
				if (pid) existingIds.push(pid);
			});

			$btn.prop('disabled', true).text('Loading...');

			$.ajax({
				url: socialfeeds_ajax.ajax_url,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'socialfeeds_load_more_facebook',
					feed_id: feedId,
					load_count: loadCount,
					after_cursor: afterCursor,
					existing_ids: existingIds,
					nonce: socialfeeds_ajax.nonce
				},
				success: function (response) {
					if (!response.success || !response.data || !response.data.html) {
						$btn.text('No More Posts').hide();
						return;
					}

					$itemsContainer.append(response.data.html);

					// If carousel, trigger resize
					if ($itemsContainer.hasClass('layout-carousel')) {
						$(window).trigger('resize');
					}

					// Store the next cursor so the following click fetches the correct page
					let nextCursor = response.data.next_cursor || '';
					$btn.attr('data-after-cursor', nextCursor);

					if (response.data.has_more && nextCursor) {
						$btn.prop('disabled', false).text(original_text);
					} else {
						$btn.text('No More Posts').hide();
					}
				},
				error: function () {
					$btn.prop('disabled', false).text('Error loading posts');
				}
			});
		}
	});

	init_insta_Modal();

	// ===== INSTAGRAM CAROUSEL =====
	function init_insta_Carousel() {
		$('.socialfeeds-instagram-inner.layout-carousel, .socialfeeds-facebook-inner.layout-carousel').each(function () {
			let $carousel = $(this);
			let $feedWrapper = $carousel.closest('.socialfeeds-instagram-feed');

			// Prevent double init
			if ($feedWrapper.find('.socialfeeds-carousel-stage').length) return;

			$carousel.wrap('<div class="socialfeeds-carousel-stage" style="position:relative; width:100%;"></div>');
			let $stage = $carousel.parent();

			let $nav = $('<div class="socialfeeds-carousel-nav">' +
				'<button class="socialfeeds-carousel-btn prev" aria-label="Previous" type="button"><span class="dashicons dashicons-arrow-left-alt2"></span></button>' +
				'<button class="socialfeeds-carousel-btn next" aria-label="Next" type="button"><span class="dashicons dashicons-arrow-right-alt2"></span></button>' +
				'</div>');

			$stage.append($nav);

			let $dots = $('<div class="socialfeeds-carousel-dots"></div>');
			$stage.after($dots);

			function updateDots() {
				$dots.empty();
				let scrollW = $carousel[0].scrollWidth;
				let clientW = $carousel[0].clientWidth;
				if (clientW >= scrollW) {
					$dots.hide();
					$nav.hide();
					return;
				}

				$dots.show();
				$nav.show();

				let pageCount = Math.ceil(scrollW / clientW);
				for (let i = 0; i < pageCount; i++) {
					let $d = $('<span class="socialfeeds-dot"></span>');
					if (i === 0) $d.addClass('active');
					$d.data('page', i);
					$dots.append($d);
				}
			}

			setTimeout(updateDots, 300);
			$(window).on('resize', updateDots);

			// Scroll Events
			$nav.find('.prev').on('click', function (e) {
				e.preventDefault();
				$carousel.animate({ scrollLeft: $carousel.scrollLeft() - $carousel.width() }, 300, update_active_dot);
			});

			$nav.find('.next').on('click', function (e) {
				e.preventDefault();
				$carousel.animate({ scrollLeft: $carousel.scrollLeft() + $carousel.width() }, 300, update_active_dot);
			});

			$dots.on('click', '.socialfeeds-dot', function () {
				let page = $(this).data('page');
				$carousel.animate({ scrollLeft: page * $carousel.width() }, 400, update_active_dot);
			});

			function update_active_dot() {
				let scroll_left = $carousel.scrollLeft();
				let clientW = $carousel[0].clientWidth;
				let page = Math.round(scroll_left / (clientW || 1));
				$dots.find('.socialfeeds-dot').removeClass('active').eq(page).addClass('active');
			}

			$carousel.on('scroll', function () {
				clearTimeout($carousel.data('scrollTimer'));
				$carousel.data('scrollTimer', setTimeout(update_active_dot, 100));
			});
		});
	}

	init_insta_Carousel();
});