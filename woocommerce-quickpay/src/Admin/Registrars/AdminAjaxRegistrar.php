<?php

namespace QuickpayPSP\Admin\Registrars;

use QuickpayPSP\Admin\Actions\AdminActionInterface;

final class AdminAjaxRegistrar {
	/** @var AdminActionInterface[] */
	private array $actions;

	/**
	 * @param AdminActionInterface[] $actions
	 */
	public function __construct( array $actions ) {
		$this->actions = $actions;
	}

	public function register(): void {
		foreach ( $this->actions as $action ) {
			$action->register();
		}
	}
}
