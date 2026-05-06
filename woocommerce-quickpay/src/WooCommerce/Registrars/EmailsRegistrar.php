<?php
namespace QuickpayPSP\WooCommerce\Registrars;

use QuickpayPSP\WooCommerce\Emails\PaymentLinkEmail;
use QuickpayPSP\WooCommerce\Controllers\EmailsController;

final class EmailsRegistrar {
	private EmailsController $controller;

	public function __construct( EmailsController $controller ) {
		$this->controller = $controller;
	}

	public function register(): void {
		add_filter( 'woocommerce_email_classes', [ $this, 'add_email_classes' ] );
		add_action( 'woocommerce_quickpay_order_action_payment_link_created', [ $this->controller, 'send_customer_payment_link' ], 10, 2 );
		add_action( 'woocommerce_email_before_order_table', [ $this->controller, 'email_instructions' ], 10, 2 );
	}

	/**
	 * @param array<string, \WC_Email> $emails
	 *
	 * @return array<string, \WC_Email>
	 */
	public function add_email_classes( array $emails ): array {
		$emails['woocommerce_quickpay_payment_link'] = new PaymentLinkEmail();
		return $emails;
	}
}
