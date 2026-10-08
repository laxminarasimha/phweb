jQuery(document).ready(function ($) {

	function escape_html(str) {
		if (str === null || str === undefined) {
			return '';
		}
		return $('<div>').text(String(str)).html();
	}

	function escape_attr(str) {
		return escape_html(str).replace(/"/g, '&quot;').replace(/'/g, '&#039;');
	}

	function escape_url(url) {
    if (!url) {
    	return '';
    }

    let parsed = new URL(String(url), window.location.origin);

    if (parsed.protocol !== 'http:' && parsed.protocol !== 'https:') {
    	return '';
    }

    return parsed.href;
	}

	function sanitize_youtube_id(id) {
		if (!id) {
			return '';
		}
		let match = String(id).match(/[A-Za-z0-9_-]{11}/);
		return match ? match[0] : '';
	}

	// ===== Video play mode: LIGHTBOX, INLINE, NEWTAB =====
	$('.socialfeeds-youtube-feed').on('click','.socialfeeds-video-item a, .socialfeeds-media-link, .socialfeeds-carousel-item a, .socialfeeds-list-item iframe',function(e){
  	let $link = $(this).closest('a');
		if (!$link.length) $link = $(this);

		// If it's an iframe click (list view), let it be
		if ($link.is('iframe')) return;

		let $feed = $link.closest('.socialfeeds-youtube-feed');
		if (!$feed.length) return;

		let click_action = $feed.data('click') || $feed.attr('data-click') || 'newtab';

		// 1. New Tab (Default behavior)
		if (click_action === 'newtab') {
			return; // Let default click happen
		}

		e.preventDefault();
		let href = $link.attr('href');
		if (!href) return;

		let vid_match = href.match(/[?&]v=([^&]+)/) || href.match(/youtu\.be\/([^?&]+)/) || href.split('/').pop().match(/^([a-zA-Z0-9_-]{11})/);
		let vid = vid_match ? vid_match[1] : '';

		if (!vid) {
			// Fallback if video ID not found
			window.open(href, '_blank');
			return;
		}

		// 2. Lightbox
		if (click_action === 'lightbox'){
			let modal_html = `
				<div class="socialfeeds-modal">
					<div class="socialfeeds-modal-backdrop"></div>
					<div class="socialfeeds-modal-inner" style="width: 900px; max-width: 90vw; background: #000;">
						<button class="socialfeeds-modal-close" style="z-index:100; color:#fff; background:rgba(0,0,0,0.5);">×</button>
						<div class="socialfeeds-modal-content" style="position:relative; width:100%; aspect-ratio:16/9;">
							<iframe src="https://www.youtube.com/embed/${encodeURIComponent(vid)}?autoplay=1&rel=0" 
								frameborder="0" 
								allow="autoplay; encrypted-media" 
								allowfullscreen
								style="position:absolute; top:0; left:0; width:100%; height:100%;">
							</iframe>
						</div>
					</div>
				</div>`;

			$('body').append(modal_html);
			setTimeout(() => $('.socialfeeds-modal').addClass('active'), 10);
			return;
		}

		// 3. Inline
		if(click_action === 'inline'){
			// Find the play overlay and hide it (if it exists)
			let $overlay = $link.siblings('.socialfeeds-play-overlay');
			if ($overlay.length) $overlay.hide();

			// Should specifically target the media container to keep title/desc intact
			$link.replaceWith(`
				<iframe src="https://www.youtube.com/embed/${encodeURIComponent(vid)}?autoplay=1&mute=0&rel=0&playsinline=1"
					frameborder="0"
					allow="autoplay; encrypted-media"
					allowfullscreen
					style="width:100%; aspect-ratio:16/9; display:block;">
				</iframe>
			`);
		}
	});

	$(document).on('click', '.socialfeeds-modal-close, .socialfeeds-modal-backdrop', function (e) {
		$('.socialfeeds-modal').removeClass('active');
		setTimeout(() => $('.socialfeeds-modal').remove(), 300);
	});

	// ===== LOAD MORE (AJAX) =====
	$('.socialfeeds-load-more-btn').on('click', function (e){
		e.preventDefault();
		let $btn = $(this);
		if ($btn.data('feed-type') === 'instagram') {
			return;
		}
		let feed_id = $btn.data('feed-id'),
			feed_type = $btn.data('feed-type'),
			feed_input = $btn.data('feed-input'),
			limit = parseInt($btn.data('limit'), 10),
			loaded = parseInt($btn.data('loaded'), 10),
			page_token = $btn.attr('data-page-token') || '',
			$feed = $btn.closest('.socialfeeds-youtube-feed');


		if (!feed_type || !feed_input) {
			console.error('SocialFeeds: Missing required parameters for Load More');
			return;
		}

		$btn.prop('disabled', true).addClass('socialfeeds-loading');
		let original_text = $btn.text();

		let ajax_data = {
			action: 'socialfeeds_load_more_videos',
			feed_id: feed_id,
			feed_type: feed_type,
			feed_input: feed_input,
			limit: limit,
			pageToken: page_token,
			nonce: socialfeeds_ajax.nonce
		};

		$.ajax({
			url: socialfeeds_ajax.ajax_url,
			type: 'POST',
			data: ajax_data,
			dataType: 'json',
			success: function (response) {
				if (!response.success) {
					$btn.prop('disabled', false).removeClass('socialfeeds-loading').text('Error loading videos');
					return;
				}

				let data = response.data;

				if (data && data.html) {

					let $feed = $btn.closest('.socialfeeds-youtube-feed');
					let $itemsContainer = $feed.find('.socialfeeds-youtube-grid, .socialfeeds-youtube-list, .socialfeeds-youtube-carousel');

					// Fallback if not found
					if (!$itemsContainer.length) {
						$itemsContainer = $btn.closest('.socialfeeds-load-more-container');
					}

					if ($itemsContainer.length && !$itemsContainer.hasClass('socialfeeds-load-more-container')) {
						$itemsContainer.append(data.html);
					} else {
						$btn.closest('.socialfeeds-load-more-container').before(data.html);
					}

					let loaded_count = (data.items && data.items.length) ? data.items.length : 1;

					let new_loaded = loaded + loaded_count;
					$btn.data('loaded', new_loaded).attr('data-loaded', new_loaded);

					if (data.nextPageToken) {
						$btn.attr('data-page-token', data.nextPageToken);
						$btn.prop('disabled', false).removeClass('socialfeeds-loading').text(original_text);
					} else {
						$btn.text($btn.data('no-more-text') || 'No More Videos').hide();
					}

					if ($itemsContainer.hasClass('socialfeeds-youtube-carousel')) {
						$(window).trigger('resize');
					}

				} else if (data && data.items && data.items.length > 0) {
					let items = data.items;

					// Find the inner container where items live
					let $itemsContainer = $feed.find('.socialfeeds-youtube-grid, .socialfeeds-youtube-list, .socialfeeds-youtube-carousel');

					// Fallback if not found (shouldn't happen if structure is valid)
					if(!$itemsContainer.length){
						$itemsContainer = $btn.closest('.socialfeeds-load-more-container');
					}

					let is_grid = $itemsContainer.hasClass('socialfeeds-youtube-grid');
					let is_carousel = $itemsContainer.hasClass('socialfeeds-youtube-carousel');
					let is_list = $itemsContainer.hasClass('socialfeeds-youtube-list');

					items.forEach(function (item) {
						let vid = sanitize_youtube_id(item.videoId);
						if (!vid) {
							return;
						}
						let title = escape_html(item.title || 'Video');
						let thumb = escape_url((item.thumbnails && item.thumbnails.medium) ? item.thumbnails.medium.url :
							((item.thumbnails && item.thumbnails.default) ? item.thumbnails.default.url : ''));
						let desc = item.description || '';
						let short_desc = desc ? escape_html(desc.substring(0, 54) + '...') : '';

						let item_html = '';
						if (is_grid) {
							item_html = `
									<div class="socialfeeds-video-item">
										<a href="https://www.youtube.com/watch?v=${encodeURIComponent(vid)}" target="_blank" rel="noopener">
											<img src="${thumb}" alt="${escape_attr(item.title || 'Video')}" />
										</a>
										${title ? `<h5 class="socialfeeds-video-title">${title}</h5>` : ''}
										${short_desc ? `<p class="socialfeeds-card-desc">${short_desc}</p>` : ''}
									</div>`;
						} else if (is_carousel) {
							item_html = `
									<div class="socialfeeds-video-item socialfeeds-carousel-item">
										<a href="https://www.youtube.com/watch?v=${encodeURIComponent(vid)}" target="_blank" rel="noopener" style="display:block; position:relative;">
											<img src="${thumb}" alt="${escape_attr(item.title || 'Video')}" />
											<span class="socialfeeds-play-overlay"></span>
										</a>
										${title ? `<p class="socialfeeds-video-title">${title}</p>` : ''}
									</div>`;
						} else if (is_list) {
							item_html = `
									<div class="socialfeeds-video-item socialfeeds-list-item">
										${title ? `<h3 class="socialfeeds-video-title">${title}</h3>` : ''}
										<div class='socialfeeds-video-iframe-wrap'>
											<iframe width="560" height="315" src="https://www.youtube.com/embed/${encodeURIComponent(vid)}" frameborder="0" allowfullscreen></iframe>
										</div>
										${short_desc ? `<p class="socialfeeds-video-desc">${short_desc}</p>` : ''}
									</div>`;
						} else {
							// Default/Fallback
							item_html = `
									<div class="socialfeeds-video-item">
										<a href="https://www.youtube.com/watch?v=${encodeURIComponent(vid)}" target="_blank" rel="noopener">
											<img src="${thumb}" alt="${escape_attr(item.title || 'Video')}" />
										</a>
										${title ? `<h5 class="socialfeeds-video-title">${title}</h5>` : ''}
										${short_desc ? `<p class="socialfeeds-card-desc">${short_desc}</p>` : ''}
									</div>`;
						}

						// Append to the specific items container
						if ($itemsContainer.length && !$itemsContainer.hasClass('socialfeeds-load-more-container')) {
							$itemsContainer.append(item_html);
						} else {
							// Fallback: insert before button if we couldn't find the container
							$btn.closest('.socialfeeds-load-more-container').before(item_html);
						}
					});

					let new_loaded = loaded + items.length;
					$btn.data('loaded', new_loaded).attr('data-loaded', new_loaded);

					if (data.nextPageToken) {
						$btn.attr('data-page-token', data.nextPageToken);
						$btn.prop('disabled', false).removeClass('socialfeeds-loading').text(original_text);
					} else {
						$btn.text($btn.data('no-more-text') || 'No More Videos').hide();
					}

					// If we are in a carousel, trigger resize to update dots/nav
					if(is_carousel){
						$(window).trigger('resize');
					}

					// If items loaded are less than limit, we likely reached the end
					if (items.length < limit) {
						$btn.text($btn.data('no-more-text') || 'No More Videos').hide();
					}

				} else {
					$btn.text($btn.data('no-more-text') || 'No More Videos').hide();
				}
			},
			error: function (xhr, status, error) {
				console.error('SocialFeeds AJAX Error:', error);
				$btn.prop('disabled', false).removeClass('socialfeeds-loading').text('Error loading videos');
			}
		});
	});

	// ===== CAROUSEL INITIALIZATION =====
	$('.socialfeeds-youtube-carousel').each(function () {
		let $carousel = $(this);
		let $feedWrapper = $carousel.closest('.socialfeeds-youtube-feed');

		// Prevent double init
		if ($feedWrapper.find('.socialfeeds-carousel-stage').length) return;

		// 1. Wrap carousel in a stage for absolute positioning of arrows
		// We want the stage to include the carousel but NOT the load more button if it's there
		$carousel.wrap('<div class="socialfeeds-carousel-stage" style="position:relative;"></div>');
		let $stage = $carousel.parent();

		// 2. Create Nav Buttons (Inside Stage)
		let $nav = $('<div class="socialfeeds-carousel-nav">' +
			'<button class="socialfeeds-carousel-btn prev" aria-label="Previous" type="button"><span class="dashicons dashicons-arrow-left-alt2" style="font-family:dashicons; line-height:inherit;"></span></button>' +
			'<button class="socialfeeds-carousel-btn next" aria-label="Next" type="button"><span class="dashicons dashicons-arrow-right-alt2" style="font-family:dashicons; line-height:inherit;"></span></button>' +
			'</div>');

		$stage.append($nav);

		// 3. Create Dots (After Stage)
		let $dots = $('<div class="socialfeeds-carousel-dots"></div>');
		$stage.after($dots);

		// If there is a load more container, ensure it is after dots
		let $loadMore = $feedWrapper.find('.socialfeeds-load-more-container');
		if ($loadMore.length) {
			$feedWrapper.append($loadMore); // Move it to the very end
		}

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

			// Simple pagination: items per page
			let itemW = $carousel.find('.socialfeeds-carousel-item').first().outerWidth(true) || 300;
			let itemsPerPage = Math.floor(clientW / itemW) || 1;
			let totalItems = $carousel.find('.socialfeeds-carousel-item').length;
			let pageCount = Math.ceil(totalItems / itemsPerPage);

			// If scrolling is continuous, we approximate pages based on scrollWidth
			pageCount = Math.ceil(scrollW / clientW);

			for (let i = 0; i < pageCount; i++) {
				let $d = $('<span class="socialfeeds-dot"></span>');
				if (i === 0) $d.addClass('active');
				$d.data('page', i);
				$dots.append($d);
			}
		}

		// Initial calc
		setTimeout(updateDots, 500); // delay for layout settle
		$(window).on('resize', updateDots);

		// 4. Scroll Logic
		$nav.find('.prev').on('click', function (e) {
			e.preventDefault();
			let itemW = $carousel.find('.socialfeeds-carousel-item').first().outerWidth(true) || 300;
			$carousel.animate({ scrollLeft: $carousel.scrollLeft() - itemW }, 300, update_active_dot);
		});

		$nav.find('.next').on('click', function (e) {
			e.preventDefault();
			let itemW = $carousel.find('.socialfeeds-carousel-item').first().outerWidth(true) || 300;
			$carousel.animate({ scrollLeft: $carousel.scrollLeft() + itemW }, 300, update_active_dot);
		});

		// Dots Click
		$dots.on('click', '.socialfeeds-dot', function () {
			let page = $(this).data('page');
			let clientW = $carousel[0].clientWidth;
			$carousel.animate({ scrollLeft: page * clientW }, 400, update_active_dot);
		});

		function update_active_dot() {
			let scroll_left = $carousel.scrollLeft();
			let clientW = $carousel[0].clientWidth;
			let page = Math.round(scroll_left / clientW);
			$dots.find('.socialfeeds-dot').removeClass('active').eq(page).addClass('active');
		}

		$carousel.on('scroll', function () {
			clearTimeout($carousel.data('scrollTimer'));
			$carousel.data('scrollTimer', setTimeout(update_active_dot, 100));
		});

		// 5. Autoplay
		if ($carousel.data('autoplay')) {
			let speed = parseInt($carousel.data('autoplay-speed')) || 3000;
			let interval;

			function start_autoplay() {
				interval = setInterval(function () {
					if ($carousel.scrollLeft() + $carousel.innerWidth() >= $carousel[0].scrollWidth - 10) {
						$carousel.animate({ scrollLeft: 0 }, 600, update_active_dot);
					} else {
						let itemW = $carousel.find('.socialfeeds-carousel-item').first().outerWidth(true) || 300;
						$carousel.animate({ scrollLeft: $carousel.scrollLeft() + itemW }, 600, update_active_dot);
					}
				}, speed);
			}

			start_autoplay();

			$stage.hover(
				function () { clearInterval(interval); },
				function () { start_autoplay(); }
			);
		}
	});

});