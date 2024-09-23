<?php
/**
 * Store a message to display in WP admin.
 *
 * @param string The message to display
 *
 * @since 4.9.4
 */
function woocommerce_quickpay_add_admin_notice( $message, $notice_type = 'success' ) {

	$notices = get_transient( '_wcqp_admin_notices' );

	if ( false === $notices ) {
		$notices = [];
	}

	$notices[ $notice_type ][] = $message;

	set_transient( '_wcqp_admin_notices', $notices, 60 * 60 );
}

/**
 * Store a message to display in WP admin.
 *
 * @param $message
 * @param string $notice_type
 *
 * @since 4.9.4
 */
function woocommerce_quickpay_add_runtime_error_notice( $error ) {

	$errors = get_transient( '_wcqp_admin_runtime_errors' );

	if ( false === $errors ) {
		$errors = [];
	}

	$errors[] = $error;

	set_transient( '_wcqp_admin_runtime_errors', $errors, 0 );
}

/**
 * Delete any admin notices we stored for display later.
 *
 * @since 2.0
 */
function woocommere_quickpay_clear_admin_notices() {
	delete_transient( '_wcqp_admin_notices' );
}

/**
 * Delete any admin notices we stored for display later.
 *
 * @since 2.0
 */
function woocommere_quickpay_clear_runtime_error_notices() {
	delete_transient( '_wcqp_admin_runtime_errors' );
}

/**
 * Display any notices added with @param bool $clear
 *
 * @see woocommerce_quickpay_add_admin_notice()
 *
 * This method is also hooked to 'admin_notices' to display notices there.
 *
 * @since 2.0
 */
function woocommere_quickpay_display_admin_notices( $clear = true ) {

	$notices = get_transient( '_wcqp_admin_notices' );

	if ( false !== $notices && ! empty( $notices ) ) {


		if ( ! empty( $notices['success'] ) ) {
			array_walk( $notices['success'], 'esc_html' );
			echo '<div class="notice notice-info"><p>' . wp_kses_post( implode( "</p>\n<p>", $notices['success'] ) ) . '</p></div>';
		}

		if ( ! empty( $notices['error'] ) ) {
			array_walk( $notices['error'], 'esc_html' );
			echo '<div class="notice notice-error"><p>' . wp_kses_post( implode( "</p>\n<p>", $notices['error'] ) ) . '</p></div>';
		}
	}

	if ( false !== $clear ) {
		woocommere_quickpay_clear_admin_notices();
	}
}

add_action( 'admin_notices', 'woocommere_quickpay_display_admin_notices', 100 );

/**
 * Display any notices added with @param bool $clear
 *
 * @see woocommerce_quickpay_add_admin_notice()
 *
 * This method is also hooked to 'admin_notices' to display notices there.
 *
 * @since 2.0
 */
function woocommere_quickpay_display_dismissible_admin_notices( $clear = true ) {

	$notices = get_transient( '_wcqp_admin_runtime_errors' );

	if ( false !== $notices && ! empty( $notices ) ) {
		if ( ! empty( $notices ) ) {
			array_walk( $notices, 'esc_html' );
			echo '<div class="wcqp-notice notice notice-error is-dismissible">';
			printf( '<h3>%s</h3>', __( 'Quickpay - Payment related problems registered' ) );
			echo '<p>' . wp_kses_post( implode( "</p>\n<p>", $notices ) ) . '</p>';
			echo '</div>';
		}
	}
}

add_action( 'admin_notices', 'woocommere_quickpay_display_dismissible_admin_notices', 100 );

/**
 * Endpoint to flush the persisted errors
 */
function woocommerce_quickpay_ajax_flush_runtime_errors() {
	if ( current_user_can( 'manage_woocommerce' ) ) {
		delete_transient( '_wcqp_admin_runtime_errors' );
	}
}

add_action( 'wp_ajax_woocommerce_quickpay_flush_runtime_errors', 'woocommerce_quickpay_ajax_flush_runtime_errors' );
