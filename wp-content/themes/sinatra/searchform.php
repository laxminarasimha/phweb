<?php
/**
 * The template for displaying search form.
 *
 * @package     Sinatra
 * @author      Sinatra Team <hello@sinatrawp.com>
 * @since       1.0.0
 */

/**
 * Do not allow direct script access.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Support for custom search post type.
$sinatra_post_type = apply_filters( 'sinatra_search_post_type', 'all' );
$sinatra_post_type = 'all' !== $sinatra_post_type ? '<input type="hidden" name="post_type" value="' . esc_attr( $sinatra_post_type ) . '" />' : '';

$sinatra_search_args  = isset( $args ) && is_array( $args ) ? $args : array();
$sinatra_search_style = isset( $sinatra_search_args['sinatra_style'] ) ? $sinatra_search_args['sinatra_style'] : 'default';
$sinatra_aria_label   = isset( $sinatra_search_args['aria_label'] ) && '' !== $sinatra_search_args['aria_label'] ? $sinatra_search_args['aria_label'] : esc_attr__( 'Enter search keywords', 'sinatra' );

if ( 'header-widget' === $sinatra_search_style ) :
	?>
	<form role="search" aria-label="<?php echo esc_attr( $sinatra_aria_label ); ?>" method="get" class="si-search-form search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
		<label class="si-form-label">
			<span class="screen-reader-text"><?php esc_html_e( 'Search for:', 'sinatra' ); ?></span>
			<input type="search" class="si-input-search search-field" placeholder="<?php esc_attr_e( 'Search', 'sinatra' ); ?>" value="<?php echo esc_attr( get_query_var( 's' ) ); ?>" name="s" autocomplete="off">
		</label>
		<?php echo $sinatra_post_type; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php sinatra_animated_arrow( 'right', 'submit', true ); ?>
	</form>
	<?php
else :
	?>
	<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
		<div>
			<input type="search" class="search-field" aria-label="<?php echo esc_attr( $sinatra_aria_label ); ?>" placeholder="<?php esc_attr_e( 'Search', 'sinatra' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>" name="s" />
			<?php echo $sinatra_post_type; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

			<button role="button" type="submit" class="search-submit" aria-label="<?php esc_attr_e( 'Search', 'sinatra' ); ?>">
				<?php echo sinatra()->icons->get_svg( 'search', array( 'aria-hidden' => 'true' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</button>
		</div>
	</form>
	<?php
endif;
?>
