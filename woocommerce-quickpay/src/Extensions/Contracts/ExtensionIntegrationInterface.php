<?php

namespace QuickpayPSP\Extensions\Contracts;

interface ExtensionIntegrationInterface {
	public function is_available(): bool;

	public function register(): void;
}
