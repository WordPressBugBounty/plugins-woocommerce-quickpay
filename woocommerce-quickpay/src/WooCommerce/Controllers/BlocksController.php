<?php

namespace QuickpayPSP\WooCommerce\Controllers;

use QuickpayPSP\WooCommerce\Support\GatewayUtils;

class BlocksController {

	/**
	 * @return void
	 */
	public function maybe_apply_block_styles(): void {
		if ( wp_script_is( 'quickpay-blocks-integration', 'enqueued' ) ) {
			// Enqueue the stylesheet only if the JavaScript file is enqueued
			wp_enqueue_style( 'quickpay-blocks-integration-styles' );
		}
	}

	/**
	 * @return array
	 */
	public function get_plugin_settings_data(): array {
		return [
			'gateways' => array_values( array_map( static fn( $gateway ) => $gateway->id, GatewayUtils::get_plugin_gateways() ) ),
		];
	}
}
