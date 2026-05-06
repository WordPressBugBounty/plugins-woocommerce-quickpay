<?php

namespace QuickpayPSP\QuickPay\Api;

use QuickpayPSP\Support\Config\GatewaySettingsProvider;
use QuickpayPSP\Support\Http\HttpClientInterface;

final class ApiClientFactory {
	private HttpClientInterface $http;
	private GatewaySettingsProvider $settings;

	public function __construct( HttpClientInterface $http, GatewaySettingsProvider $settings ) {
		$this->http     = $http;
		$this->settings = $settings;
	}

	public function base(): QuickPayClient {
		return new QuickPayClient( $this->http, $this->settings );
	}

	public function payment( $resource_data = null ): PaymentClient {
		return new PaymentClient( $this->http, $this->settings, $resource_data );
	}

	public function subscription( $resource_data = null ): SubscriptionClient {
		return new SubscriptionClient( $this->http, $this->settings, $resource_data );
	}
}
