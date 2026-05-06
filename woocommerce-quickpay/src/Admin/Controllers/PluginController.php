<?php
namespace QuickpayPSP\Admin\Controllers;

/**
 * Handles plugin-level admin integrations such as update notices and action links.
 */
class PluginController {

	/**
	 * Shows an upgrade notice below the plugin in the plugins list.
	 *
	 * @param array $args
	 */
	public function in_plugin_update_message( array $args ): void {
		$transient_name = 'wcqp_upgrade_notice_' . $args['Version'];
		$upgrade_notice = get_transient( $transient_name );

		if ( false === $upgrade_notice ) {
			$response = wp_remote_get( 'https://plugins.svn.wordpress.org/woocommerce-quickpay/trunk/README.txt' );

			if ( ! is_wp_error( $response ) && ! empty( $response['body'] ) ) {
				$upgrade_notice = $this->parse_update_notice( $response['body'] );
				set_transient( $transient_name, $upgrade_notice, DAY_IN_SECONDS );
			}
		}

		echo wp_kses_post( $upgrade_notice );
	}

	/**
	 * Adds a Settings link to the plugin action links.
	 *
	 * @param array $links
	 *
	 * @return array
	 */
	public function add_action_links( array $links ): array {
		$settings_url = admin_url( 'admin.php?page=wc-settings&tab=checkout&section=quickpay' );

		return array_merge(
			[ '<a href="' . esc_url( $settings_url ) . '">' . esc_html__( 'Settings', 'woocommerce-quickpay' ) . '</a>' ],
			$links
		);
	}

	/**
	 * Parses the upgrade notice from the README.txt file.
	 *
	 * @param string $content
	 *
	 * @return string
	 */
	private function parse_update_notice( string $content ): string {
		$matches        = null;
		$regexp         = '~==\s*Upgrade Notice\s*==\s*=\s*(.*)\s*=(.*)(=\s*' . preg_quote( WCQP_VERSION, '/' ) . '\s*=|$)~Uis';
		$upgrade_notice = '';

		if ( preg_match( $regexp, $content, $matches ) ) {
			$version = trim( $matches[1] );
			$notices = (array) preg_split( '~[\r\n]+~', trim( $matches[2] ) );

			if ( version_compare( WCQP_VERSION, $version, '<' ) ) {
				$upgrade_notice .= '<div class="wc_plugin_upgrade_notice">';

				foreach ( $notices as $line ) {
					$upgrade_notice .= wp_kses_post( preg_replace( '~\[([^\]]*)\]\(([^\)]*)\)~', '<a href="${2}">${1}</a>', $line ) );
				}

				$upgrade_notice .= '</div> ';
			}
		}

		return wp_kses_post( $upgrade_notice );
	}
}
