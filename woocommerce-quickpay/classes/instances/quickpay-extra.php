<?php

/**
 * Class WC_QuickPay_Extra
 *
 * Used to add an extra gateway with customizable payment methods.
 * want to offer Dankort-betalinger for NETS customers etc.
 */
class WC_QuickPay_Extra extends WC_QuickPay_Instance {

	public $main_settings = null;

	public function __construct() {
		parent::__construct();

		// Get gateway variables
		$this->id = 'quickpay-extra';

		$this->method_title = 'Quickpay - Extra';

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
			'enabled'        => [
				'title'   => esc_html__( 'Enable', 'woocommerce-quickpay' ),
				'type'    => 'checkbox',
				'label'   => esc_html__( 'Enable Extra QuickPay gateway', 'woocommerce-quickpay' ),
				'default' => 'no'
			],
			'_Shop_setup'    => [
				'type'  => 'title',
				'title' => esc_html__( 'Shop setup', 'woocommerce-quickpay' ),
			],
			'title'          => [
				'title'       => esc_html__( 'Title', 'woocommerce-quickpay' ),
				'type'        => 'text',
				'description' => esc_html__( 'This controls the title which the user sees during checkout.', 'woocommerce-quickpay' ),
				'default'     => esc_html__( 'QuickPay', 'woocommerce-quickpay' )
			],
			'description'    => [
				'title'       => esc_html__( 'Customer Message', 'woocommerce-quickpay' ),
				'type'        => 'textarea',
				'description' => esc_html__( 'This controls the description which the user sees during checkout.', 'woocommerce-quickpay' ),
				'default'     => esc_html__( 'Pay', 'woocommerce-quickpay' )
			],
			'cardtypelock'   => [
				'title'       => esc_html__( 'Payment methods', 'woocommerce-quickpay' ),
				'type'        => 'text',
				'description' => esc_html__( 'Default: creditcard. Type in the cards you wish to accept (comma separated). See the valid payment types here: <b>https://learn.quickpay.net/tech-talk/appendixes/payment-methods/#payment-methods</b>', 'woocommerce-quickpay' ),
				'default'     => 'creditcard',
			],
			'quickpay_icons' => [
				'title'             => esc_html__( 'Credit card icons', 'woocommerce-quickpay' ),
				'type'              => 'multiselect',
				'description'       => esc_html__( 'Choose the card icons you wish to show next to the QuickPay payment option in your shop.', 'woocommerce-quickpay' ),
				'desc_tip'          => true,
				'class'             => 'wc-enhanced-select',
				'css'               => 'width: 450px;',
				'custom_attributes' => [
					'data-placeholder' => esc_html__( 'Select icons', 'woocommerce-quickpay' )
				],
				'default'           => '',
				'options'           => WC_QuickPay_Settings::get_card_icons(),
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
		return $this->s( 'cardtypelock' );
	}

	/**
	 * FILTER: apply_gateway_icons function.
	 *
	 * Sets gateway icons on frontend
	 *
	 * @access public
	 * @return void
	 */
	public function apply_gateway_icons( $icon, $id ) {
		if ( $id == $this->id ) {
			$icon = '';

			$icons = $this->s( 'quickpay_icons' );

			if ( ! empty( $icons ) ) {
				$icons_maxheight = $this->gateway_icon_size();

				foreach ( $icons as $key => $item ) {
					$icon .= $this->gateway_icon_create( $item, $icons_maxheight );
				}
			}
		}

		return $icon;
	}
}
