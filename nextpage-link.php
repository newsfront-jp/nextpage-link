<?php
/**
 * Plugin Name: Nextpage Link
 * Plugin URI: https://www.newsfront.jp/wordpress/plugins/nextpage-link/
 * Description: A link to the next page can be displayed using a shortcode.
 * Version: 1.0.1
 * Author: NewsFront Corporation
 * Author URI: https://www.newsfront.jp
 * License: GPLv2 or later
 * Text Domain: nextpage-link
 *
 * @package WordPress
 */

namespace NF\NextpageLink;

defined( 'ABSPATH' ) || exit;

/**
 * Nextpage Link class
 */
class Nextpage_Link {
	/**
	 * Post data.
	 *
	 * @since 1.0.0
	 * @var   array
	 */
	private $data = array();

	public function __construct() {
		add_action( 'init', array( $this, 'register_shortcode' ) );
	}

	/**
	 * Register shortcode
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function register_shortcode() {
		add_shortcode( 'nextpage_link', array( $this, 'do_nextpage_link' ) );
		add_shortcode( 'nextpage_link_toc', array( $this, 'do_nextpage_link_toc' ) );
	}

	/**
	 * Do shortcode
	 *
	 * @since  1.0.0
	 * @param  array       $attr    Attributes of the shortcode.
	 * @param  string|null $content Contents of the shortcode.
	 * @return mixed
	 */
	public function do_nextpage_link( $attr, $content ) {
		if ( ! is_singular() ) {
			return;
		}

		$attr = shortcode_atts(
			array(
				'id'             => '0',
				'type'           => 'pagination',
				'layout'         => '',
				'in_same_term'   => '0',
				'excluded_terms' => '',
				'previous'       => '0',
				'taxonomy'       => 'category',
				'prefix'         => __( 'Next:', 'nextpage-link' ),
			),
			(array) $attr
		);

		$attr = apply_filters( 'nextpage_link/shortcode_atts', $attr );

		$this->data = array(
			'post_ID'    => false,
			'post_link'  => false,
			'post_title' => false,
		);

		if ( ! empty( $attr['id'] ) ) {
			// use post ID.
			$this->set_data_by_id( $attr['id'] );
		} elseif ( 'nextpost' === $attr['type'] ) {
			// Adjacent post.
			$this->set_data_from_adjacent_post( $attr );
		} elseif ( 'pagination' === $attr['type'] ) {
			// Current page.
			$this->set_data_from_pagination();
		}

		// Set template args.
		$args = array(
			'data'      => $this->data,
			'shortcode' => array(
				'attr'    => $attr,
				'content' => str_replace( '%post_title%', $this->data['post_title'], $content ),
			),
		);

		do_action( 'nextpage_link' );

		return $this->get_template_part( $attr['layout'], $args );
	}

	/**
	 * Do shortcode for the table of contents
	 *
	 * @since  1.0.0
	 * @param  array $attr Attributes of the shortcode.
	 * @return mixed
	 */
	public function do_nextpage_link_toc( $attr ) {
		if ( ! is_singular() ) {
			return;
		}

		$attr = shortcode_atts(
			array(
				'open'     => '0',
				'headline' => __( 'Table of contents', 'nextpage-link' ),
				'p1_title' => '',
			),
			(array) $attr
		);

		$attr = apply_filters( 'nextpage_link_toc/shortcode_atts', $attr );

		global $post;

		if ( ! isset( $post->post_content ) || empty( $post->post_content ) ) {
			return;
		}

		// Extracting shortcodes
		// See: https://developer.wordpress.org/reference/functions/get_shortcode_regex/
		$pattern = get_shortcode_regex( array( 'nextpage_link' ) );
		$total   = preg_match_all( '/' . $pattern . '/s', $post->post_content, $matches );

		if ( false === $total || ! isset( $matches[5] ) || empty( $matches[5] ) ) {
			return;
		}

		$pnum = 2;
		$cnum = get_query_var( 'page' ) ? (int) get_query_var( 'page' ) : 1;

		// Set template args.
		$args = array(
			'data'      => array(),
			'shortcode' => array(
				'attr'    => $attr,
				'content' => null,
			),
		);

		// First page.
		if ( ! empty( $attr['p1_title'] ) ) {
			$args['data'][] = array(
				'current'     => ( 1 === $cnum ),
				'page_number' => 1,
				'title'       => str_replace( '%post_title%', get_the_title(), $attr['p1_title'] ),
				'link'        => get_permalink(),
			);
		}

		// After page 2.
		foreach ( $matches[5] as $title ) {
			$link = $this->get_nextpage_link( $post, $pnum );

			if ( empty( $title ) ) {
				$title = sprintf(
					/* translators: 1: Page number */
					__( 'Go to Page %d', 'nextpage-link' ),
					$pnum
				);
			}

			if ( ! empty( $link ) ) {
				$args['data'][] = array(
					'current'     => ( $pnum === $cnum ),
					'page_number' => $pnum,
					'title'       => $title,
					'link'        => $link,
				);
			}

			$pnum++;
		}

		do_action( 'nextpage_link_toc' );

		return empty( $args['data'] ) ? null : $this->get_template_part( 'toc', $args );
	}

	/**
	 * Set data by post ID
	 *
	 * @since  1.0.0
	 * @param  int $id Post ID.
	 * @return void
	 */
	private function set_data_by_id( $id ) {
		if ( 'publish' !== get_post_status( $id ) ) {
			return;
		}

		$this->data['post_ID']    = $id;
		$this->data['post_link']  = apply_filters( 'nextpage_link/post_link_by_id', get_permalink( $id ), $id );
		$this->data['post_title'] = get_the_title( $id );
	}

	/**
	 * Set data from adjacent post
	 *
	 * @param  array $attr Shortcode attributes.
	 * @return void
	 */
	private function set_data_from_adjacent_post( $attr ) {
		$previous       = ( '1' === $attr['previous'] );
		$in_same_term   = ( '1' === $attr['in_same_term'] );
		$excluded_terms = explode( ',', trim( $attr['excluded_terms'] ) );

		$post = get_adjacent_post( $in_same_term, $excluded_terms, $previous, $attr['taxonomy'] );

		if ( ! isset( $post->ID ) ) {
			return;
		}

		$this->data['post_ID']    = $post->ID;
		$this->data['post_link']  = apply_filters( 'nextpage_link/post_link_form_adjacent_post', get_permalink( $post->ID ), $post );
		$this->data['post_title'] = get_the_title( $post->ID );
	}

	/**
	 * Set data from pagination
	 *
	 * @since  1.0.0
	 * @return void
	 */
	private function set_data_from_pagination() {
		global $post, $page;

		$this->data['post_ID']    = $post->ID;
		$this->data['post_link']  = $this->get_nextpage_link( $post, $page + 1 );
		$this->data['post_title'] = get_the_title( $post->ID );
	}

	/**
	 * Returns a link to the specified page
	 *
	 * @since  1.0.0
	 * @param  WP_POST $post     Post data.
	 * @param  integer $nextpage Next page number.
	 * @return bool|string
	 */
	private function get_nextpage_link( $post, $nextpage = 2 ) {
		global $numpages, $multipage, $wp_rewrite;

		$link  = false;
		$_link = get_permalink();

		if ( ! $multipage || $nextpage > $numpages || ! isset( $post->ID ) ) {
			return false;
		}

		if ( ! get_option( 'permalink_structure' ) || in_array( $post->post_status, array( 'draft', 'pending' ), true ) ) {
			$link = add_query_arg( 'page', $nextpage, $_link );
		} elseif ( 'page' === get_option( 'show_on_front' ) && $post->ID == get_option( 'page_on_front' ) ) {
			$link = trailingslashit( $_link ) . user_trailingslashit( "{$wp_rewrite->pagination_base}/{$nextpage}", 'single_paged' );
		} else {
			$link = trailingslashit( $_link ) . user_trailingslashit( $nextpage, 'single_paged' );
		}

		if ( is_preview() ) {
			$query_args = array();

			if ( ( 'draft' !== $post->post_status ) && isset( $_GET['preview_id'], $_GET['preview_nonce'] ) ) {
				$options = array(
					'options' => array(
						'default'   => 0,
						'min_range' => 1,
					),
				);

				$query_args['preview_id']    = filter_var( wp_unslash( $_GET['preview_id'] ), FILTER_VALIDATE_INT, $options );
				$query_args['preview_nonce'] = sanitize_key( wp_unslash( $_GET['preview_nonce'] ) );
			}

			$link = get_preview_post_link( $post, $query_args, $link );
		}

		return apply_filters( 'nextpage_link/post_link_from_pagination', $link );
	}

	/**
	 * Check load template
	 *
	 * @since  1.0.0
	 * @param  string $layout Template name.
	 * @return mixed
	 */
	private function get_template( $layout ) {
		$layout    = wp_basename( $layout, '.php' );
		$theme_dir = get_stylesheet_directory();
		$templates = array(
			$theme_dir . '/template-parts/nextpage-link/layout-' . $layout . '.php',
			$theme_dir . '/template-parts/nextpage-link/layout.php',
			__DIR__ . '/template-parts/layout-' . $layout . '.php',
			__DIR__ . '/template-parts/layout.php',
		);

		if ( 'toc' === $layout ) {
			$templates = array(
				$theme_dir . '/template-parts/nextpage-link/layout-toc.php',
				__DIR__ . '/template-parts/layout-toc.php',
			);
		}

		foreach ( $templates as $t ) {
			if ( file_exists( $t ) ) {
				return $t;
				break;
			}
		}

		return false;
	}

	/**
	 * Returns output data molded by template
	 *
	 * @since  1.0.0
	 * @param  string $layout Template name.
	 * @param  array  $args   Data for template.
	 * @return mixed
	 */
	private function get_template_part( $layout, $args ) {
		$template = $this->get_template( $layout );

		if ( false === $template ) {
			if ( is_user_logged_in() && current_user_can( 'administrator' ) ) {
				return '<p>' . esc_html__( 'Error: Template not found.', 'nextpage-link' ) . '</p>';
			}

			return;
		}

		ob_start();
		load_template( $template, false, $args );
		$output = ob_get_contents();
		ob_end_clean();

		return $output;
	}
}

new Nextpage_Link();
