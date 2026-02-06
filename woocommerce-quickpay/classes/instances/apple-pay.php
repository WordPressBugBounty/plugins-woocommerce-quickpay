<?php

class WC_QuickPay_Apple_Pay extends WC_QuickPay_Instance {

	public $main_settings = null;

	public function __construct() {
		parent::__construct();

		// Get gateway variables
		$this->id = 'quickpay_apple_pay';

		$this->method_title = 'Quickpay - Apple Pay';

		$this->setup();

		$this->title       = $this->s( 'title' );
		$this->description = $this->s( 'description' );

		add_filter( 'woocommerce_quickpay_cardtypelock_' . $this->id, [ $this, 'filter_cardtypelock' ] );
		add_filter( 'woocommerce_quickpay_checkout_gateway_icon', [ $this, 'filter_icon' ] );
	}


	/**
	 * init_form_fields function.
	 *
	 * Initiates the plugin settings form fields
	 *
	 * @access public
	 * @return array
	 */
	public function init_form_fields(): void {
		$this->form_fields = [
			'enabled'     => [
				'title'   => esc_html__( 'Enable', 'woocommerce-quickpay' ),
				'type'    => 'checkbox',
				/* translators: 1: name of the payment gateway  */
				'label'   => sprintf( esc_html__( 'Enable %s payment', 'woocommerce-quickpay' ), 'Apple Pay' ),
				'default' => 'no',
				/* translators: 1: name of the browser */
				'description' => sprintf(esc_html__( 'Works only in %s.', 'woocommerce-quickpay' ), 'Safari' )
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
	 * filter_cardtypelock function.
	 *
	 * Sets the cardtypelock
	 *
	 * @access public
	 * @return string
	 */
	public function filter_cardtypelock() {
		return 'apple-pay';
	}

	/**
	 * @param $icon
	 *
	 * @return string
	 */
	public function filter_icon( $icon ) {
		if ( 'apple_pay' === $icon ) {
			$icon = 'apple-pay';
		}

		return $icon;
	}

	/**
	 * @return bool
	 */
	public function is_available() {
		$available = parent::is_available();
		if ( $available && ! WC_QuickPay_Helper::is_browser( 'safari' ) && ! is_admin() ) {
			$available = false;
		}

		return $available;
	}
}
