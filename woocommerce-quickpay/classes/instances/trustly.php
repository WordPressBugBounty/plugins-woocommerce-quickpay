<?php

class WC_QuickPay_Trustly extends WC_QuickPay_Instance {

	public $main_settings = null;

	public function __construct() {
		parent::__construct();

		// Get gateway variables
		$this->id = 'trustly';

		$this->method_title = 'Quickpay - Trustly';

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
				'label'   => esc_html__( 'Enable Trustly payment', 'woocommerce-quickpay' ),
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
				'default'     => esc_html__( 'Trustly', 'woocommerce-quickpay' )
			],
			'description' => [
				'title'       => esc_html__( 'Customer Message', 'woocommerce-quickpay' ),
				'type'        => 'textarea',
				'description' => esc_html__( 'This controls the description which the user sees during checkout.', 'woocommerce-quickpay' ),
				'default'     => esc_html__( 'Pay with Trustly', 'woocommerce-quickpay' )
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
		return 'trustly';
	}
}
