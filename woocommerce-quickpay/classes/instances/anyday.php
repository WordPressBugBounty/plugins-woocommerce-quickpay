<?php

class WC_QuickPay_Anyday extends WC_QuickPay_Instance {

	public $main_settings = null;

	public function __construct() {
		parent::__construct();

		// Get gateway variables
		$this->id = 'quickpay_anyday';

		$this->method_title = 'Quickpay - Anyday';

		$this->setup();

		$this->title       = $this->s( 'title' );
		$this->description = $this->s( 'description' );

		add_filter( 'woocommerce_quickpay_cardtypelock_' . $this->id, [ $this, 'filter_cardtypelock' ] );
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
				/* translators: 1: name of the payment gateway */
				'label'   => sprintf( esc_html__( 'Enable %s payment', 'woocommerce-quickpay' ), 'Anyday' ),
				'default' => 'no'
			],
			'_Shop_setup' => [
				'type'  => 'title',
				'title' => esc_html__( 'Shop setup', 'woocommerce-quickpay' ),
			],
			'title'       => [
				'title'       => esc_html__( 'Title', 'woocommerce-quickpay' ),
				'type'        => 'text',
				'description' => esc_html__( 'This controls the title which the user sees during checkout.', 'woocommerce-quickpay' ),
				'default'     => esc_html__( 'Anyday', 'woocommerce-quickpay' )
			],
			'description' => [
				'title'       => esc_html__( 'Customer Message', 'woocommerce-quickpay' ),
				'type'        => 'textarea',
				'description' => esc_html__( 'This controls the description which the user sees during checkout.', 'woocommerce-quickpay' ),
				/* translators: 1: name of the payment gateway */
				'default'     => sprintf( esc_html__( 'Pay with %s', 'woocommerce-quickpay' ), 'Anyday' )
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
		return 'anyday-split';
	}

	public function is_available() {
		$available = parent::is_available();

		if ( $available && ( $cart = WC()->cart ) ) {
			$cart_total = (float) $cart->get_total( 'edit' );

			$cart_min = 1;
			$cart_max = 30000;

			if ( ! ( $cart_total >= $cart_min && $cart_total <= $cart_max ) || 'DKK' !== strtoupper( get_woocommerce_currency() ) ) {
				$available = false;
			}
		}

		return $available;
	}
}
