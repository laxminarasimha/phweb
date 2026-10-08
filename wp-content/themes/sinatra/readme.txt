=== Sinatra ===
Contributors: sinatrateam
Tags: two-columns, right-sidebar, left-sidebar, footer-widgets, blog, news, custom-background, custom-menu, post-formats, sticky-post, editor-style, threaded-comments, translation-ready, custom-colors, featured-images, full-width-template, microformats, theme-options, e-commerce
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Stable tag: trunk

A lightweight and highly customizable multi-purpose theme that makes it easy for anyone to create their perfect website.

== Description ==
Sinatra is a lightweight and highly customizable multi-purpose theme that makes it easy for anyone to create their perfect website. It comes with microdata integration, unlimited colors, multiple layouts, pre-built demo websites and so much more. It's also translatable and built with best SEO practices. It works well with your favorite plugins such as WooCommerce, JetPack, page builders, SEO plugins and others.

== Frequently Asked Questions ==

= How to install Sinatra? =

1. Log into your WordPress Dashboard and go to Appearance » Themes and click the "Add New" button.
2. Type in "Sinatra" in the search field and press the "Enter" key on your keyboard.
3. Click the "Activate" button to activate Sinatra theme on your site.
4. Navigate to Appearance » Customize to access theme options.

== Changelog ==

= 1.5.0 =
* Improved: Block editor styles now load directly inside the iframed editor canvas (WordPress 7.1+).
* Fixed: Google Fonts selected in the Customizer not displayed in the block editor.
* Fixed: Post title typography and text selection color not applied in the block editor.
* Removed: Internet Explorer polyfills (HTML5 Shiv, Flexibility) and IE-only stylesheet.
* Removed: Deprecated 'script' and 'style' HTML5 theme support on WordPress 7.0+.
* Fixed: Duplicate Theme URI and Author URI in the stylesheet header (Author URI removed).
* Updated: Tested up to WordPress 7.1.

= 1.4.2 =
* Added: Gallery Grid block pattern and Card block style.
* Added: Custom Header support.
* Fixed: Potential CSS injection via Customizer typography and gradient settings (text-decoration, gradient-position and font-family are now sanitized and allowlisted).
* Fixed: Plugin activate/deactivate AJAX actions now require the activate_plugins capability.
* Fixed: Dynamic property deprecation notices on PHP 8.2+ by declaring the theme class properties.
* Fixed: Theme version constant out of sync with the theme header.
* Improved: Single text domain in the WooCommerce checkout template for language-pack compatibility.
* Improved: Added Theme URI, Author URI and a copyright notice to the stylesheet header.
* Updated: Tested up to WordPress 7.0.

= 1.4.1 =
* Improved: Notice class escaping in admin helpers.
* Improved: Theme location explicitly passed to wp_nav_menu().
* Improved: Header search widget routed through get_search_form() for filterability.
* Improved: Accessibility label on WooCommerce product search form.

= 1.4.0 =
* Fixed: Stored XSS vulnerability via insufficient output escaping in sinatra_excerpt().
* Fixed: Reflected XSS via unescaped search query in search forms and breadcrumbs.
* Fixed: CSS injection via unsanitized typography Customizer values.
* Fixed: CSS injection via background-image, background-size, background-repeat, and background-attachment using wrong sanitizers.
* Fixed: CSS injection via background-color-overlay using sanitize_text_field instead of color sanitizer.
* Fixed: Missing capability checks on AJAX handlers (dismiss notice, customizer tour).
* Fixed: Typo bug using $post instead of $_POST in dismiss notice handler.
* Fixed: Wrong escaping function (wp_kses_post) on metabox text input value attribute.
* Fixed: Unescaped background-image URLs in inline styles.
* Fixed: WooCommerce cart item data output without escaping.
* Fixed: Missing echo on out-of-stock badge output.
* Fixed: DOM XSS via innerHTML replaced with textContent.
* Fixed: Default Customizer sanitize callback changed from sinatra_no_sanitize to sanitize_text_field.
* Fixed: Admin ajaxurl passed without esc_url().
* Improved: Added proper sanitizer for checkbox-group Customizer controls.
* Improved: Added allowlist validation for CSS property values in Customizer.
* Improved: Defense-in-depth output escaping in dynamic styles.
* Updated: WordPress 6.9 compatibility.
* Updated: npm dependencies.

= 1.3 =
* Updated: WooCommerce templates.
* Updated: WordPress 6.3 compatibility.
* Updated: Google Fonts list.

= 1.2.1 =
* Fixed: Square icon displaying on mobile navigation.
* Fixed: Table alignment not working.
* Fixed: Pre-Footer not displaying on homepage.
* Fixed: Post format "Link" incorrect links.
* Updated: Google Fonts list.
* Improved: CSS enhancements.

= 1.2.0 =
* Added: Responsive visibility for Sticky header.
* Fixed: Sticky sidebar option in combination with sticky header.
* Fixed: WooCommerce archive title in breadcrumbs.
* Fixed: SVG Icons not working in Customizer widgets.
* Updated: Google Fonts list.
* Improved: Replaced font icons with SVGs.
* Improved: Performance.
* Improved: CSS enhancements.
* Improved: Accessibility.

= 1.1.6 =
* Fixed: Headings on Block Editor display incorrectly.
* Fixed: Background and Content Background color incorrect for Boxed and Boxed Content layout.
* Fixed: Displaying "Blog" in breadcrumbs on single pages.
* Fixed: Breadcrumbs background displaying featured image on product pages.
* Fixed: Header Cart widget - Variable product displaying wrong price and name.
* Improved: TablePress compatibility.

= 1.1.5 =
* Updated: WooCommerce templates.
* Fixed: Mobile menu visibility.
* Fixed: SSL support for uploads folder.
* Improved: Code formatting.

= 1.1.4 =
* Updated: WordPress 5.5 compatibility.
* Fixed: Range control in Customizer not working.
* Fixed: Page Header background color overlay not working.
* Improved: Block Editor styles to match frontend design.

= 1.1.3 =
* Added: 'sinatra_entry_meta_post_type' filter that allows post meta tags to be displayed on custom post types.
* Added: Sticky header option.
* Added: New Main Footer column layout: 1/3 + 2/3.
* Added: New Main Footer column layout: 2/3 + 1/3.
* Improved: CSS enhancements.

= 1.1.2 =
* Added: Option to control sidebar position on smaller screens.
* Fixed: CSS issue with mobile (hamburger) menu colors.
* Fixed: CSS issue with copyright menu not displaying on mobile devices.
* Fixed: Breadcrumbs Posts page title hard coded to 'Blog' on single pages.
* Updated: Default values for transparent header.
* Updated: Google Fonts list.
* Improved: Block Editor styles to match frontend design.
* Improved: CSS enhancements.

= 1.1.1 =
* Added: Transparent Header - option to set alternative logo.
* Added: Transparent Header - options for logo size and spacing.
* Added: Transparent Header - color options.
* Added: Option to enable or disable Transparent Header on individual posts/pages.
* Fixed: Block Compatibility: Button Block Color Specificity.
* Fixed: Various alignwide and alignfull issues with Block libraries.
* Fixed: Alignfull blocks cover up the post meta.
* Updated: Google Fonts list.
* Improved: Block Editor styles to match frontend design.
* Improved: CSS enhancements.

= 1.1.0 =
* Added: Blog - Horizontal Layout and options to customize the layout.
* Added: Option to choose image size for Blog / Archive layout.
* Added: Breadcrumbs section in Customizer.
* Added: Display options for Breadcrumbs.
* Added: Display options for Hero.
* Added: Display options for Pre Footer Callout.
* Added: Option for Page Header alignment.
* Added: Option for Page Header spacing.
* Added: Option for Page Title layout for Single Post pages.
* Added: Option to display "Shares" in Post Meta Elements when Social Snap is enabled.
* Added: Option to show avatar and icons in post meta in Blog » Blog Page/Archive » Show.
* Added: Option to show last updated date in Blog » Single Post » Post Elements.
* Added: Reset to default values in customizer controls.
* Added: Action before and after hooks for Top Bar, Header and Copyright widgets.
* Added: Theme support for responsive embeds.
* Added: Database Upgrader class.
* Added: Flexibility.js to improve flexbox browser support.
* Fixed: Gallery Block align center not working.
* Fixed: Breadcrumbs and Google Search Console issue.
* Fixed: Hero » Device Visibility not working.
* Fixed: Main Header » Background Image Overlay Color not working.
* Fixed: Main Navigation disappears when screen is resized.
* Fixed: Main Navigation » Typography » Font Size issue.
* Fixed: Footer » Pre Footer » Call to Action » Typography not working.
* Fixed: Mobile menu dissappears when scrolling.
* Fixed: Links in Sinatra widgets always open in same tab.
* Updated: Appearance » Sinatra Theme page.
* Updated: Google Fonts list.
* Improved: Browser compatibility.
* Improved: Responsive styling.
* Improved: CSS enhancements.
* Improved: WPML plugin compatibility.

== Copyright ==

Sinatra WordPress Theme, Copyright (C) 2020-2026 Sinatra Team.
Sinatra is distributed under the terms of the GNU GPL version 2 or later.

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

== Resources ==
Feather Icons, https://feathericons.com/
Copyright (c) 2013-2017 Cole Bemis, MIT License, http://www.opensource.org/licenses/mit-license.php

ImagesLoaded, https://imagesloaded.desandro.com/
Copyright (c) 2019 David DeSandro, MIT License, http://www.opensource.org/licenses/mit-license.php

Breadcrumb Trail, https://github.com/justintadlock/breadcrumb-trail
Copyright (c) 2008-2017 Justin Tadlock, GNU GPL v2 or later License, https://opensource.org/licenses/GPL-2.0

WP Color Picker Alpha, https://github.com/kallookoo/wp-color-picker-alpha
Copyright (c) Sergio GNU GPL v2 or later, https://opensource.org/licenses/GPL-2.0

Normalize.css, https://necolas.github.io/normalize.css/
Copyright (c) Nicolas Gallagher and Jonathan Neal, MIT License, http://www.opensource.org/licenses/mit-license.php

Select2, https://select2.org/
Copyright (c) 2012-2017 Kevin Brown, Igor Vaynberg, and Select2 contributors, MIT License, http://www.opensource.org/licenses/mit-license.php

Screenshot image by Zachary Nelson (StockSnap.io): https://stocksnap.io/photo/QYPJ92FECM
CC0 License, https://creativecommons.org/publicdomain/zero/1.0/
