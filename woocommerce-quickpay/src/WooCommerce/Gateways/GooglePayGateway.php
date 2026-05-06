<?php

namespace QuickpayPSP\WooCommerce\Gateways;

use QuickpayPSP\Support\Dependencies;

class GooglePayGateway extends BaseGateway {

	public const ID = 'quickpay_google_pay';

	public function __construct() {
		$this->method_title = 'Quickpay - Google Pay';

		$this->supports_products();
		$this->supports_subscriptions();

		parent::__construct();
	}

	public function init_form_fields(): void {
		$this->form_fields = [
			'enabled'     => [
				'title'       => esc_html__( 'Enable', 'woocommerce-quickpay' ),
				'type'        => 'checkbox',
				/* translators: 1: name of the payment gateway  */
				'label'       => sprintf( esc_html__( 'Enable %s payment', 'woocommerce-quickpay' ), 'Apple Pay' ),
				'default'     => 'no',
				/* translators: 1: name of the browser */
				'description' => sprintf( esc_html__( 'Works only in %s.', 'woocommerce-quickpay' ), 'Safari' )
			],
			'_Shop_setup' => [
				'type'  => 'title',
				'title' => esc_html__( 'Shop setup', 'woocommerce-quickpay' ),
			],
			'title'       => [
				'title'       => esc_html__( 'Title', 'woocommerce-quickpay' ),
				'type'        => 'text',
				'description' => esc_html__( 'This controls the title which the user sees during checkout.', 'woocommerce-quickpay' ),
				'default'     => esc_html__( 'Apple Pay', 'woocommerce-quickpay' )
			],
			'description' => [
				'title'       => esc_html__( 'Customer Message', 'woocommerce-quickpay' ),
				'type'        => 'textarea',
				'description' => esc_html__( 'This controls the description which the user sees during checkout.', 'woocommerce-quickpay' ),
				/* translators: 1: name of the payment gateway */
				'default'     => sprintf( esc_html__( 'Pay with %s', 'woocommerce-quickpay' ), 'Apple Pay' )
			],
		];
	}

	/**
	 * @return false
	 */
	public function is_available() {
		$available = parent::is_available();
		if ( $available && ! Dependencies::is_browser( 'chrome' ) && ! is_admin() ) {
			$available = false;
		}

		return $available;
	}

	public function default_card_type_lock(): string {
		return 'google-pay';
	}

	protected function icon_slugs(): array {
		return [ 'google-pay' ];
	}

}
