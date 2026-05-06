<?php

namespace QuickpayPSP\Admin\Support;

use QuickpayPSP\Admin\Support\InstallState;

class Installer {

	/**
	 * @var InstallState
	 */
	private $install_state;

	/**
	 * @var string
	 */
	private $plugin_version;

	/**
	 * @var array<string,string>
	 */
	private $updates;

	/**
	 * @var string
	 */
	private $base_path;

	public function __construct( InstallState $install_state, string $plugin_version, array $updates, $base_path ) {
		$this->install_state  = $install_state;
		$this->plugin_version = (string) $plugin_version;
		$this->updates        = $updates;
		$this->base_path      = rtrim( (string) $base_path, '/\\' ) . DIRECTORY_SEPARATOR;
	}

	/**
	 * @return string
	 */
	public function get_db_version() {
		return $this->install_state->get_db_version();
	}

	/**
	 * @param string|null $version
	 *
	 * @return bool
	 */
	public function is_update_required( $version = null ) {
		if ( $version === null ) {
			$version = $this->get_db_version();
		}

		foreach ( $this->updates as $update_version => $file ) {
			if ( version_compare( (string) $version, (string) $update_version, '<' ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @return void
	 */
	public function run_updates() {
		if ( function_exists( 'session_write_close' ) ) {
			@session_write_close();
		}

		$this->install_state->enable_maintenance();

		foreach ( $this->updates as $version => $update_callback ) {
			if ( version_compare( $this->get_db_version(), (string) $version, '<' ) ) {
				if ( is_string( $update_callback ) ) {
					$path = $this->base_path . ltrim( (string) $update_callback, '/\\' );
					if ( file_exists( $path ) ) {
						include $path;
					}
				} elseif ( is_callable( $update_callback ) ) {
					$update_callback();
				}

				$this->install_state->set_db_version( (string) $version );
			}
		}

		$this->install_state->set_db_version( $this->plugin_version );
		$this->install_state->disable_maintenance();
	}
}
