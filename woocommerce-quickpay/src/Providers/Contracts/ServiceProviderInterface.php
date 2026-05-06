<?php

namespace QuickpayPSP\Providers\Contracts;

use QuickpayPSP\Support\Services;

interface ServiceProviderInterface {

	public function register( Services $services ): void;
}
