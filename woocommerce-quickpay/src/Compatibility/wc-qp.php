<?php

/**
 * Legacy compatibility layer.
 *
 * A large ecosystem (themes/extensions/snippets) expects the global WC_QP() helper
 * and a handful of legacy classes (WC_QuickPay_Log, WC_QuickPay_Subscription, etc.).
 *
 * The legacy class names are registered as deprecated aliases in class-aliases.php,
 * pointing to their namespaced equivalents in the QuickpayPSP\Compatibility namespace.
 */

if ( ! function_exists( 'WC_QP' ) ) {
	/**
	 * Backwards compatible accessor.
	 *
	 * @deprecated Use the plugin's service container or GatewaySettingsProvider directly.
	 *
	 * @return WC_QP_Legacy_Facade
	 */
	function WC_QP(): WC_QP_Legacy_Facade {
		static $instance = null;
		if ( null === $instance ) {
			$instance = new WC_QP_Legacy_Facade();
		}

		return $instance;
	}
}
