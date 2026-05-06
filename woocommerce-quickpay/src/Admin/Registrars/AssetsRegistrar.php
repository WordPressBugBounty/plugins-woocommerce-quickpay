<?php

namespace QuickpayPSP\Admin\Registrars;

use QuickpayPSP\Admin\Support\AssetEnqueuer;

/**
 * Handles the registration of assets in WordPress.
 *
 * This class registers scripts and styles for both the frontend and backend of a WordPress site.
 * It utilizes WordPress action hooks to enqueue stylesheets and backend JavaScript when appropriate.
 */
class AssetsRegistrar {
	public function register(): void {
		add_action( 'wp_enqueue_scripts', [ AssetEnqueuer::class, 'enqueue_stylesheet' ] );
		add_action( 'admin_enqueue_scripts', [ AssetEnqueuer::class, 'enqueue_javascript_backend' ] );
		add_action( 'init', [ AssetEnqueuer::class, 'load_i18n' ] );
	}
}
