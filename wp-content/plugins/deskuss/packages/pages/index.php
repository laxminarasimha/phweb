<?php


// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
//////////////////////////////////////////////////////////////
//===========================================================
// SOFTACULOUS PROJECT
//===========================================================
// Inspired by the DESIRE to be the BEST OF ALL
// ----------------------------------------------------------
// Started by: Pulkit and Brijesh
// ----------------------------------------------------------
// Please Read the Terms of use at http://deskuss.com
// ----------------------------------------------------------
//===========================================================
// (c)Softaculous Ltd.
//===========================================================
//////////////////////////////////////////////////////////////

@chdir(dirname(__file__).'/../');

require_once('client.inc.php');
require_once(INCLUDE_DIR.'class.format.php');
require_once(INCLUDE_DIR.'class.page.php');

// Determine the requested page
// - Strip extension
$slug = Format::slugify($dsk->get_path_info());

// Get the part before the first dash
$first_word = explode('-', $slug);
$first_word = $first_word[0];

$pages = Page::objects()->filter(array(
    'name__like' => "$first_word%"
));

$selected_page = null;
foreach ($pages as $P) {
    if (Format::slugify($P->name) == $slug) {
        $selected_page = $P;
        break;
    }
}

if (!$selected_page)
    Http::response(404, __('Page Not Found'));

if (!$selected_page->isActive() || $selected_page->getType() != 'other')
    Http::response(404, __('Page Not Found'));

require deskuss_get_header();

$BUTTONS = false;
require deskuss_load_view('templates/sidebar.tmpl.php');
?>
<div class="main-content">
<?php
print $selected_page->getBodyWithImages();
?>
</div>

<?php
require deskuss_get_footer();
?>
