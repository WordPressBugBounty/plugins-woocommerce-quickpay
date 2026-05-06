<?php

namespace QuickpayPSP\Admin\Support;

class InstallState {
	/**
	 * @var string
	 */
	private $version_option;

	/**
	 * @var string
	 */
	private $maintenance_option;

	/**
	 * @var string
	 */
	private $default_version;

	public function __construct( $version_option, $maintenance_option, $default_version ) {
		$this->version_option     = (string) $version_option;
		$this->maintenance_option = (string) $maintenance_option;
		$this->default_version    = (string) $default_version;
	}

	/**
	 * @return string
	 */
	public function get_db_version() {
		return (string) get_option( $this->version_option, $this->default_version );
	}

	/**
	 * @param string $version
	 *
	 * @return void
	 */
	public function set_db_version( $version ) {
		delete_option( $this->version_option );
		add_option( $this->version_option, (string) $version );
	}

	/**
	 * @return bool
	 */
	public function is_maintenance_enabled() {
		return (bool) get_option( $this->maintenance_option, false );
	}

	/**
	 * @return void
	 */
	public function enable_maintenance() {
		add_option( $this->maintenance_option, true, '', 'yes' );
	}

	/**
	 * @return void
	 */
	public function disable_maintenance() {
		delete_option( $this->maintenance_option );
	}
}
