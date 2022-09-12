<?php

defined( 'ABSPATH' ) || exit;

if ( ! isset( $args['data'] ) ) {
	return;
}

?>

<?php if ( isset( $args['data']['post_link'] ) && $args['data']['post_link'] ) : ?>

	<p class="nextpage-link">
		<?php echo esc_html( $args['shortcode']['attr']['prefix'] ); ?>
		<a href="<?php echo esc_url( $args['data']['post_link'] ); ?>">
			<?php echo esc_html( $args['shortcode']['content'] ); ?>
		</a>
	</p>

<?php elseif ( isset( $args['shortcode']['content'] ) && $args['shortcode']['content'] ) : ?>

	<p class="nextpage-link">
		<span>
			<?php echo esc_html( $args['shortcode']['attr']['prefix'] ); ?>
			<?php echo esc_html( $args['shortcode']['content'] ); ?>
		</span>
	</p>

<?php endif; ?>
