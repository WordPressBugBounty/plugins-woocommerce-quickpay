<?php

namespace QuickpayPSP\WooCommerce\Controllers;

use QuickpayPSP\Support\Config\GatewaySettingsProvider;
use WC_Order;

final class EmailsController {

	private GatewaySettingsProvider $settings;

	public function __construct( GatewaySettingsProvider $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Adds custom instructions text to the order confirmation email.
	 *
	 * @param WC_Order $order
	 * @param bool     $sent_to_admin
	 */
	public function email_instructions( WC_Order $order, bool $sent_to_admin ): void {
		if ( $order->get_payment_method() !== 'quickpay' || $sent_to_admin ) {
			return;
		}

		if ( ! in_array( $order->get_status(), [ 'processing', 'completed' ], true ) ) {
			return;
		}

		$instructions = $this->settings->get( 'instructions' );
		if ( $instructions ) {
			echo wp_kses_post( wpautop( wptexturize( $instructions ) ) );
		}
	}

	/**
	 * Triggers the customer payment link email.
	 *
	 * @param string $payment_link
	 * @param WC_Order $order
	 */
	public function send_customer_payment_link( string $payment_link, WC_Order $order ): void {
		$mailer = wc()->mailer();
		$emails = $mailer->get_emails();

		/** @var \QuickpayPSP\WooCommerce\Emails\PaymentLinkEmail $mail */
		$mail = $emails['woocommerce_quickpay_payment_link'] ?? null;

		if ( $mail ) {
			$mail->trigger( $payment_link, $order );
		}
	}
}
