=== SocialFeeds ===
Contributors: softaculous
Tags: social feeds, instagram feed, youtube feed, social media, youtube videos
Requires at least: 5.0
Tested up to: 7.1
Requires PHP: 7.2
Stable tag: 1.0.9
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

YouTube feeds for WordPress with simple Setup and Settings options.

== Description ==

SocialFeeds is a lightweight and easy-to-use WordPress plugin designed to showcase Instagram and YouTube content directly on your website. With quick setup and shortcode support, you can display social media feeds anywhere on your site and keep your content fresh and engaging.

You can find our official documentation at [https://socialfeeds.org/docs](https://socialfeeds.org/docs). We are also active in our community support forums on wordpress.org if you are one of our free users. Our Premium Support Ticket System is at [https://softaculous.deskuss.com](https://softaculous.deskuss.com)

[Home Page](https://socialfeeds.org "SocialFeeds Homepage") | [Support](https://softaculous.deskuss.com "SocialFeeds Support") | [Documents](http://socialfeeds.org/docs "Documents")

== SocialFeeds YouTube Free Features ==

* **Display YouTube Channel Videos** – Show videos directly from your YouTube channel on your website.
* **Quick and Easy Feed Setup** – Easily create and manage your YouTube feed from the WordPress dashboard.
* **Video Information Display** – Display video title, description, play icon, lazy loading, and click-to-play functionality.
* **Hover Effects** – Apply hover animation and visual effects to video items for better user interaction.
* **Custom Feed Header** – Display a header with channel name, logo, description, custom logo, and banner image.
* **YouTube Subscribe Button** – Add a YouTube subscribe button in the feed header to increase channel subscribers.
* **Load More Videos** – Allow users to load additional videos using a convenient Load More button.
* **Basic Layout and Style Customization** – Customize basic layout and styling options to match your website design.
* **Fully Responsive Design** – Feeds automatically adjust to desktop, tablet, and mobile devices and work with most WordPress themes.

== Upgrade to SocialFeeds PRO for More Power ==

Unlock advanced capabilities with **SocialFeeds PRO**, such as:

* **Multiple Account Support** – Connect and manage multiple YouTube channels and Instagram accounts.
* **Advanced Video Details** – Display video duration, publish date, view count, like count, and comment count.
* **Multiple YouTube Feed Types** – Show Channel Feeds, Playlist Feeds, Search Feeds, Single Videos, and Live Stream Videos.
* **Advanced Feed Design Customization** – Customize colors, fonts, spacing, layouts, and styling for YouTube feeds.
* **Instagram Feed Layouts** – Display Instagram feeds using Grid, Carousel, and Masonry layouts.
* **Responsive Instagram Columns** – Control the number of columns for desktop, tablet, and mobile devices.
* **Post Limits and Loading Control** – Set limits on the number of Instagram posts displayed and configure loading behavior.
* **Customizable Instagram Header** – Customize header position, profile avatar, bio, follower count, and media count.
* **Enhanced Instagram Post Display** – Show captions, likes, comments, reels, icons, and play modes.
* **Instagram Hover Effects** – Enable hover states and interaction effects for Instagram posts.
* **Instagram Follow Button** – Add and customize a follow button to grow your Instagram audience.
* **Load More Button Customization** – Control the behavior and design of the Instagram Load More button.
* **Post Sorting Options** – Sort Instagram posts by newest, most liked, or random order.
* **Layout Spacing and Aspect Ratio Control** – Adjust spacing between posts and control the media aspect ratio.
* **Live Shortcode Preview** – Display a live preview of the feed using the Preview Block inside the editor.

== Why Use SocialFeeds? ==

- Increase visitor engagement with live social media content
- Promote your YouTube and Instagram profiles directly on your website
- Improve website appearance with modern and responsive feed layouts
- Easy integration with shortcodes

= Third Party API usage =

1. YouTube Search API: This plugin uses the YouTube Data API to retrieve publicly available YouTube videos based on search queries configured by the user. When enabled, the plugin sends the search term and the YouTube API key provided by the user to Google servers. The API returns public video data such as titles, descriptions, thumbnails, and video IDs.

2. YouTube Channels API: This plugin uses the YouTube Data API to retrieve publicly available information about a YouTube channel. When enabled, the plugin sends the channel ID and the YouTube API key provided by the user to Google servers. The API returns public channel data such as channel name, description, thumbnails, statistics, and other public metadata.

Service Provider: Google LLC (YouTube Data API v3)

Terms of Service: https://developers.google.com/youtube/terms/api-services-terms-of-service  
Privacy Policy: https://policies.google.com/privacy

== Installation ==

**Automatic Installation**
1. Go to WordPress Admin → Plugins → Add New
2. Search for **SocialFeeds**
3. Click **Install Now**, then **Activate**
4. Open SocialFeeds from the dashboard
5. Connect your YouTube or Instagram account
6. Add feeds using shortcodes

**Shortcodes**

Instagram Feed:
[socialfeeds id="1" platform="instagram"]

YouTube Feed:
[socialfeeds id="2" platform="youtube"]

**Manual Installation**
1. Download the plugin ZIP file
2. Go to Plugins → Add New → Upload Plugin
3. Upload `socialfeeds.zip`
4. Install and activate the plugin

== Frequently Asked Questions ==

= Can I display multiple feeds? =
Yes. The Pro version allows multiple feeds and accounts.

= Can I customize layouts and styles? =
Yes. Basic customization is available in the free version, with advanced controls in Pro.

= Is SocialFeeds compatible with all themes? =
Yes. SocialFeeds works with all standard WordPress themes.

== Screenshots ==

1. SocialFeeds Dashboard
2. YouTube Feeds Type
3. YouTube Customize
4. Instagram Accounts
5. Instagram Customize
6. All Feeds

== Start Using SocialFeeds ==

Install SocialFeeds today to display your Instagram photos and YouTube videos on your WordPress website and keep your content always up to date.


== Changelog ==

= 1.0.9 =

* [Task] Improved security checks for plugin updates by verifying plugin signatures before upgrading.

= 1.0.8 =

* [Security-Fix] There were security issues related to YouTube API requests and AJAX endpoints that have been fixed, reported by Averon Averenkov (averonsec.com).
* [Security-Fix] There were multiple XSS vulnerabilities in the admin and frontend interfaces that have been fixed, reported by Averon Averenkov (averonsec.com).
* [Security-Fix] There was an issue related to the handling of YouTube API credentials that has been fixed, reported by Averon Averenkov (averonsec.com).
* [Security-Fix] There was an issue related to URL escaping for YouTube thumbnails that has been fixed, reported by Averon Averenkov (averonsec.com).
* [Pro-Improvement] Added an option to set content alignment for Instagram feeds.
* [Bug-Fix] Fixed an issue where Google Reviews were not being translated.
* [Task] Tested with WordPress 7.1.

= 1.0.7 =

* [Pro-Feature] Added Google Reviews integration.

= 1.0.6 =

* [Pro-Feature] Added Facebook feed integration.

= 1.0.5 =

* [Pro-Feature] Added support for hashtag posts and hashtag posts for Instagram.

= 1.0.4 =

* [Improvement] You can now modify the feed name from the Customize tab.
* [Bug-Fix] Fixed an issue where YouTube hover effects were not working in the List and Carousel layouts. 
* [Bug-Fix] Fixed an issue where the video play mode was not applied to newly loaded videos after clicking Load More.

= 1.0.3 =

* [Feature] Added option to customize the feed name.

= 1.0.2 =

* [Pro-Feature] Added YouTube and Instagram Gutenberg blocks.
* [Bug-Fix] Fixed an issue where the YouTube subscribe text could not be customized in the preview.
* [Bug-Fix] Fixed an issue with deleting Instagram feeds.

= 1.0.1 =

* [Bug Fix] The modal close button was not working this has been fixed.
* [Task] Added a notice for the YouTube API key.

= 1.0.0 =

* Initial release.