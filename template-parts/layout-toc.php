<?php

defined( 'ABSPATH' ) || exit;

if ( ! isset( $args['data'] ) || empty( $args['data'] ) ) {
	return;
}

$open = ( '1' === $args['shortcode']['attr']['open'] ) ? 'open' : '';
$cnum = get_query_var( 'page' ) ? (int) get_query_var( 'page' ) : 1;

?>

<div class="nextpage-link-toc">
	<details <?php echo esc_attr( $open ); ?>>
		<summary><?php echo esc_html( $args['shortcode']['attr']['headline'] ); ?></summary>

		<ul>
		<?php foreach ( $args['data'] as $item ) : ?>
			<li>
			<?php if ( $item['current'] ) : ?>
				<span title="<?php echo esc_html( $item['title'] ); ?>">P<?php echo esc_html( $item['page_number'] ); ?>. <?php echo esc_html( $item['title'] ); ?></span>
			<?php else : ?>
				<a href="<?php echo esc_url( $item['link'] ); ?>" title="<?php echo esc_html( $item['title'] ); ?>">P<?php echo esc_html( $item['page_number'] ); ?>. <?php echo esc_html( $item['title'] ); ?></a>
			<?php endif; ?>
			</li>
		<?php endforeach; ?>
		</ul>
	</details>
</div>
