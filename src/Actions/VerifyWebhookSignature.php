<?php

namespace Dystcz\LunarPaypal\Actions;

use Dystcz\LunarPaypal\Exceptions\InvalidWebhookSignatureException;
use Http;
use Illuminate\Support\Facades\Config;
use Srmklive\PayPal\Services\PayPal;

class VerifyWebhookSignature
{
    public function __invoke(array $body, array $headers): void
    {
        $requestBody = [
            'auth_algo' => $headers['paypal-auth-algo'],
            'cert_url' => $headers['paypal-cert-url'],
            'transmission_id' => $headers['paypal-transmission-id'],
            'transmission_sig' => $headers['paypal-transmission-sig'],
            'transmission_time' => $headers['paypal-transmission-time'],
            'webhook_id' => Config::get('lunar.paypal.webhook.id'),
            'webhook_event' => $body,
        ];

        $response = Http::withHeaders([
            'Authorization' => $this->getAuthorization(),
        ])
            ->asJson()
            ->post($this->getUrl(), $requestBody);

        $result = $response->json();

        if (! isset($result['verification_status'])) {
            throw new InvalidWebhookSignatureException($result['error'].': '.$result['error_description']);
        }

        if ($result['verification_status'] !== 'SUCCESS') {
            throw new InvalidWebhookSignatureException('Verification failed');
        }
    }

    protected function getAuthorization(): string
    {
        $provider = new PayPal(Config::get('lunar.paypal'));
        $response = $provider->getAccessToken();

        return "{$response['token_type']} {$response['access_token']}";
    }

    protected function getUrl(): string
    {
        if (Config::get('lunar.paypal.mode') === 'sandbox') {
            return 'https://api-m.sandbox.paypal.com/v1/notifications/verify-webhook-signature';
        }

        return 'https://api-m.paypal.com/v1/notifications/verify-webhook-signature';
    }
}
