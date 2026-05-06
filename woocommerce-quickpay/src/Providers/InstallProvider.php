<?php
namespace QuickpayPSP\Providers;

use QuickpayPSP\Admin\Actions\RunUpgraderAction;
use QuickpayPSP\Admin\Support\Installer;
use QuickpayPSP\Admin\Support\InstallState;
use QuickpayPSP\Admin\Controllers\InstallController;
use QuickpayPSP\Admin\ViewRenderer;
use QuickpayPSP\Admin\Updates\Update43;
use QuickpayPSP\Admin\Updates\Update46;
use QuickpayPSP\QuickPay\Api\ApiClientFactory;
use QuickpayPSP\Support\Services;
use QuickpayPSP\WooCommerce\Logging\WooCommerceLogger;

/**
 * Registers installation and upgrade services within the Application Layer.
 *
 * Covers:
 * - Install state tracking (version option, maintenance flag)
 * - The Installer, which runs sequential upgrade routines on activation/update
 * - The InstallController, which drives the admin install/upgrade UI
 * - The RunUpgraderAction AJAX action that triggers the upgrader manually
 *
 * Note: this provider intentionally does not implement ServiceProviderInterface
 * because it requires an additional `$plugin_base_path` argument at registration time.
 */
class InstallProvider {
	public function register( Services $services, string $plugin_base_path ): void {
		$this->register_state( $services );
		$this->register_installer( $services, $plugin_base_path );
		$this->register_controller( $services );
		$this->register_actions( $services );
	}

	/**
	 * Install state.
	 *
	 * Tracks the currently installed plugin version and whether the site is
	 * in maintenance mode during an upgrade. Used by the Installer to decide
	 * which upgrade routines need to run.
	 */
	private function register_state( Services $services ): void {
		$services->set( 'install/state', function () {
			return new InstallState(
				'woocommerce_quickpay_version',
				'woocommerce_quickpay_maintenance',
				'0'
			);
		} );
	}

	/**
	 * Installer.
	 *
	 * Wires together the install state, the current plugin version, and the
	 * ordered map of upgrade routines. Each key is the version that introduced
	 * the migration; routines are executed in order when the stored version
	 * is behind the current one.
	 */
	private function register_installer( Services $services, string $plugin_base_path ): void {
		$services->set( 'install/installer', function ( $c ) use ( $plugin_base_path ) {
			return new Installer(
				$c->get_as( InstallState::class, 'install/state' ),
				defined( 'WCQP_VERSION' ) ? WCQP_VERSION : '0',
				[
					'4.3' => new Update43(),
					'4.6' => new Update46(
						$c->get_as( ApiClientFactory::class, 'quickpay/api/factory' ),
						$c->get_as( WooCommerceLogger::class, 'logger' )
					),
				],
				$plugin_base_path
			);
		} );
	}

	/**
	 * Install controller.
	 *
	 * Drives the admin install/upgrade UI. Depends on the Installer to check
	 * upgrade status and the ViewRenderer to output the relevant admin views.
	 */
	private function register_controller( Services $services ): void {
		$services->set( 'install/controller', function ( $c ) {
			return new InstallController(
				$c->get_as( Installer::class, 'install/installer' ),
				$c->get_as( InstallState::class, 'install/state' ),
				$c->get_as( ViewRenderer::class, 'wordpress/view' )
			);
		} );
	}

	/**
	 * Install AJAX actions.
	 *
	 * Registers the action that allows an admin to manually trigger the
	 * upgrade process from the WordPress admin area.
	 */
	private function register_actions( Services $services ): void {
		$services->set( 'install/action/run-upgrader', function ( $c ) {
			return new RunUpgraderAction(
				$c->get_as( Installer::class, 'install/installer' )
			);
		} );
	}
}
