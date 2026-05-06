<?php
namespace QuickpayPSP\Providers;

use QuickpayPSP\Admin\Actions\ClearCacheAction;
use QuickpayPSP\Admin\Actions\EmptyLogsAction;
use QuickpayPSP\Admin\Actions\ManagePaymentAction;
use QuickpayPSP\Admin\Actions\PingAction;
use QuickpayPSP\Admin\Actions\PrivateKeyAction;
use QuickpayPSP\Admin\Controllers\InstallController;
use QuickpayPSP\Admin\Controllers\NoticesController;
use QuickpayPSP\Admin\Controllers\PluginController;
use QuickpayPSP\Admin\Registrars\AllowedRedirectHostsRegistrar;
use QuickpayPSP\Admin\Registrars\AdminAjaxRegistrar;
use QuickpayPSP\Admin\Registrars\AdminNoticesRegistrar;
use QuickpayPSP\Admin\UserCapabilities;
use QuickpayPSP\Admin\ViewRenderer;
use QuickpayPSP\Providers\Contracts\ServiceProviderInterface;
use QuickpayPSP\Support\Services;

/**
 * Registers WordPress-related services within the application.
 *
 * Covers:
 * - Core WordPress utilities (redirect hosts, user capabilities, view rendering)
 * - Admin AJAX actions (individual actions + aggregated collection + registrar)
 * - Admin notices and plugin controller
 *
 * This provider acts as the Integration Layer adapter between WordPress and
 * the Application Layer — no business logic should live here.
 */
class WordPressProvider implements ServiceProviderInterface {
	public function register( Services $services ): void {
		$this->register_core( $services );
		$this->register_admin_ajax( $services );
		$this->register_admin( $services );
	}

	/**
	 * Core WordPress utilities.
	 *
	 * Registers foundational services used across the plugin:
	 * allowed redirect hosts for external URL validation, user capability
	 * checks for access control, and the view renderer for template output.
	 */
	private function register_core( Services $services ): void {
		$services->set( 'wordpress/allowed_redirects', static function () {
			return new AllowedRedirectHostsRegistrar( [
				'payment.quickpay.net',
			] );
		} );

		$services->set( 'wordpress/user_caps', UserCapabilities::class );

		$services->set( 'wordpress/view', ViewRenderer::class );
	}

	/**
	 * Admin AJAX actions.
	 *
	 * Registers each individual AJAX action as a named service, then
	 * aggregates them into a collection and binds the registrar responsible
	 * for hooking them into WordPress (wp_ajax_*).
	 */
	private function register_admin_ajax( Services $services ): void {
		// Individual AJAX actions
		$services->set( 'wordpress/admin-ajax/action/clear-cache', static fn( Services $s ) => new ClearCacheAction(
			$s->get_as( UserCapabilities::class, 'wordpress/user_caps' )
		) );

		$services->set( 'wordpress/admin-ajax/action/empty-logs', static fn( Services $s ) => new EmptyLogsAction(
			$s->get_as( UserCapabilities::class, 'wordpress/user_caps' ),
			$s->get( 'logger' )
		) );

		$services->set( 'wordpress/admin-ajax/action/manage-payment', static fn( Services $s ) => new ManagePaymentAction(
			$s->get_as( UserCapabilities::class, 'wordpress/user_caps' ),
			$s->get( 'woocommerce/subscriptions' ),
			$s->get( 'quickpay/api/factory' )
		) );

		$services->set( 'wordpress/admin-ajax/action/ping', static fn( Services $s ) => new PingAction(
			$s->get( 'http/client' ),
			$s->get( 'gateway/settings_provider' )
		) );

		$services->set( 'wordpress/admin-ajax/action/private-key', static fn( Services $s ) => new PrivateKeyAction(
			$s->get( 'http/client' ),
			$s->get( 'gateway/settings_provider' )
		) );

		// Aggregated collection of all registered AJAX actions
		$services->set( 'wordpress/admin-ajax/actions', static fn( Services $s ) => [
			$s->get( 'wordpress/admin-ajax/action/clear-cache' ),
			$s->get( 'wordpress/admin-ajax/action/empty-logs' ),
			$s->get( 'wordpress/admin-ajax/action/manage-payment' ),
			$s->get( 'wordpress/admin-ajax/action/ping' ),
			$s->get( 'wordpress/admin-ajax/action/private-key' ),
		] );

		// Registrar that hooks all AJAX actions into WordPress
		$services->set( 'wordpress/admin-ajax/registrar', static fn( Services $s ) => new AdminAjaxRegistrar(
			$s->get( 'wordpress/admin-ajax/actions' )
		) );
	}

	/**
	 * Admin notices and plugin controller.
	 *
	 * Registers the admin notices registrar (responsible for displaying
	 * configuration warnings and install prompts) and the plugin controller
	 * that handles plugin-level admin page routing.
	 */
	private function register_admin( Services $services ): void {
		$services->set( 'wordpress/admin-notices/registrar', static fn( Services $s ) => new AdminNoticesRegistrar(
			new NoticesController( $s->get( 'gateway/settings_provider' ) ),
			$s->get_as( InstallController::class, 'install/controller' )
		) );

		$services->set( 'wordpress/plugin/controller', PluginController::class );
	}
}
