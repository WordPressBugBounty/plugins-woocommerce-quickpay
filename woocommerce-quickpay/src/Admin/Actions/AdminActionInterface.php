<?php

namespace QuickpayPSP\Admin\Actions;

interface AdminActionInterface {
	public function action(): string;

	public function register(): void;
}
