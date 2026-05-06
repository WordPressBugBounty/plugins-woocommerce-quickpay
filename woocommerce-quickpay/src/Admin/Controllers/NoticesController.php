<?php

namespace QuickpayPSP\Admin\Controllers;

use QuickpayPSP\Support\Config\GatewaySettingsProvider;
use QuickpayPSP\Utilities\UrlUtils;

class NoticesController {

	private GatewaySettingsProvider $settings;

	public function __construct( GatewaySettingsProvider $settings ) {
		$this->settings = $settings;
	}

	public function bulk_action_payment_link_notice(): void {
		$processed = isset( $_REQUEST['quickpay_bulk_payment_link_processed'] ) ? (int) $_REQUEST['quickpay_bulk_payment_link_processed'] : null;
		$skipped   = isset( $_REQUEST['quickpay_bulk_payment_link_skipped'] ) ? (int) $_REQUEST['quickpay_bulk_payment_link_skipped'] : null;

		if ( null === $processed ) {
			return;
		}

		if ( $processed > 0 ) {
			woocommerce_quickpay_add_admin_notice(
				sprintf(
				/* translators: %d: number of orders */
					_n( 'Payment link created for %d order.', 'Payment links created for %d orders.', $processed, 'woocommerce-quickpay' ),
					$processed
				), 'success'
			);
		}

		if ( $skipped > 0 ) {
			woocommerce_quickpay_add_admin_notice(
				sprintf(
				/* translators: %d: number of orders */
					_n( 'Payment link creation skipped for %d order.', 'Payment link creation skipped for %d orders.', $skipped, 'woocommerce-quickpay' ),
					$skipped
				), 'warning'
			);
		}
	}

	public function maybe_display_mandatory_settings(): void {
		$error_fields = [];

		$mandatory_fields = [
			'quickpay_privatekey' => __( 'Private key', 'woocommerce-quickpay' ),
			'quickpay_apikey'     => __( 'Api User key', 'woocommerce-quickpay' )
		];

		foreach ( $mandatory_fields as $mandatory_field_setting => $mandatory_field_label ) {
			if ( $this->has_empty_mandatory_post_fields( $mandatory_field_setting ) ) {
				$error_fields[] = $mandatory_field_label;
			}
		}

		if ( ! empty( $error_fields ) ) {
			$message = sprintf( '<h2>%s</h2>', esc_html__( "Quickpay for WooCommerce", 'woocommerce-quickpay' ) );
			/* translators: 1: Link to the settings page */
			$message .= sprintf( '<p>%s</p>', sprintf( __( 'You have missing or incorrect settings. Go to the <a href="%s">settings page</a>.', 'woocommerce-quickpay' ), esc_url( UrlUtils::get_settings_page_url() ) ) );
			$message .= '<ul>';
			foreach ( $error_fields as $error_field ) {
				$message .= "<li>" . sprintf( wp_kses( '<strong>%s</strong> is mandatory.', 'woocommerce-quickpay', [ 'strong' ] ), esc_html( $error_field ) ) . "</li>";
			}
			$message .= '</ul>';

			echo wp_kses_post( sprintf( '<div class="%s">%s</div>', 'notice notice-error', $message ) );
		}

	}

	/**
	 * Logic wrapper to check if some of the mandatory fields are empty on post request.
	 *
	 * @param $settings_field
	 *
	 * @return bool
	 */
	private function has_empty_mandatory_post_fields( $settings_field ): bool {
		$post_key    = 'woocommerce_quickpay_' . $settings_field;
		$setting_key = $this->settings->get( $settings_field );

		return empty( $_POST[ $post_key ] ) && empty( $setting_key );
	}
}
