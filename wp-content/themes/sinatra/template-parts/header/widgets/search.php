<?php
/**
 * The template for displaying theme header search widget.
 *
 * @package     Sinatra
 * @author      Sinatra Team <hello@sinatrawp.com>
 * @since       1.0.0
 */

?>

<div aria-haspopup="true">
	<a href="#" class="si-search">
		<?php echo sinatra()->icons->get_svg( 'search', array( 'aria-label' => esc_html__( 'Search', 'sinatra' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</a><!-- END .si-search -->

	<div class="si-search-simple si-search-container dropdown-item">
		<?php
		get_search_form(
			array(
				'aria_label'     => esc_attr__( 'Site Search', 'sinatra' ),
				'sinatra_style'  => 'header-widget',
			)
		);
		?>
	</div><!-- END .si-search-simple -->
</div>
