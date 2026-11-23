<?php

namespace App\Services\Selcom;

use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SelcomClient
{
    public const SANDBOX_URL = 'https://apigwtest.selcommobile.com';

    public const PRODUCTION_URL = 'https://apigw.selcommobile.com';

    public function __construct(
        protected ?string $apiKey = null,
        protected ?string $apiSecret = null,
        protected ?string $vendorId = null,
        protected ?string $mode = null,
    ) {
        $settings = Payment::settings();
        $this->apiKey = $apiKey ?? ($settings['selcom_api_key'] ?? '');
        $this->apiSecret = $apiSecret ?? ($settings['selcom_api_secret'] ?? '');
        $this->vendorId = $vendorId ?? ($settings['selcom_vendor_id'] ?? '');
        $this->mode = $mode ?? ($settings['selcom_mode'] ?? 'sandbox');
    }

    public function baseUrl(): string
    {
        return $this->mode === 'live' ? self::PRODUCTION_URL : self::SANDBOX_URL;
    }

    public function isConfigured(): bool
    {
        return filled($this->apiKey) && filled($this->apiSecret) && filled($this->vendorId);
    }

    public function isSimulationMode(): bool
    {
        return $this->mode === 'simulation' || ! $this->isConfigured();
    }

    /**
     * Compute Selcom APIGW authentication headers.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, string>
     */
    public function computeHeaders(array $data, ?string $timestamp = null): array
    {
        $timestamp = $timestamp ?? now()->toIso8601String();
        $signedFields = implode(',', array_keys($data));

        $signData = 'timestamp='.$timestamp;
        foreach ($data as $key => $value) {
            $signData .= '&'.$key.'='.(is_bool($value) ? ($value ? 'true' : 'false') : (string) $value);
        }

        $digest = base64_encode(hash_hmac('sha256', $signData, (string) $this->apiSecret, true));

        return [
            'Authorization' => 'SELCOM '.base64_encode((string) $this->apiKey),
            'Digest-Method' => 'HS256',
            'Digest' => $digest,
            'Timestamp' => $timestamp,
            'Signed-Fields' => $signedFields,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    /**
     * Verify incoming webhook callback signature from Selcom.
     *
     * @param  array<string, mixed>  $payload
     */
    public function verifyWebhookSignature(
        array $payload,
        string $receivedDigest,
        string $timestamp,
        string $signedFields
    ): bool {
        if ($this->isSimulationMode()) {
            return true;
        }

        $fields = explode(',', $signedFields);
        $signData = 'timestamp='.$timestamp;

        foreach ($fields as $field) {
            $field = trim($field);
            if (array_key_exists($field, $payload)) {
                $signData .= '&'.$field.'='.$payload[$field];
            }
        }

        $computedDigest = base64_encode(hash_hmac('sha256', $signData, (string) $this->apiSecret, true));

        return hash_equals($computedDigest, $receivedDigest);
    }

    /**
     * Name Lookup for mobile money wallet (M-Pesa, Tigo Pesa, Airtel Money, Halopesa).
     *
     * @return array{result: string, name: string, phone: string, network: string, message?: string}
     */
    public function nameLookup(string $phone, string $network = 'VODACOM'): array
    {
        $normalizedPhone = $this->normalizePhone($phone);

        if ($this->isSimulationMode()) {
            $mockNames = [
                'VODACOM' => 'AMINA BAKARI (VODACOM M-PESA)',
                'AIRTEL' => 'JUMA KASSIM (AIRTEL MONEY)',
                'TIGO' => 'BARAKA ELIUD (TIGO PESA)',
                'HALOPESA' => 'NEEMA MWITA (HALOPESA)',
            ];

            return [
                'result' => 'SUCCESS',
                'name' => $mockNames[strtoupper($network)] ?? 'VERIFIED CUSTOMER ('.strtoupper($network).')',
                'phone' => $normalizedPhone,
                'network' => strtoupper($network),
                'message' => 'Simulated name lookup successful',
            ];
        }

        $transId = (string) Str::uuid();
        $params = [
            'utilitycode' => strtoupper($network),
            'utilityref' => $normalizedPhone,
            'transid' => $transId,
        ];

        try {
            $headers = $this->computeHeaders($params);
            $response = Http::withHeaders($headers)
                ->timeout(15)
                ->get($this->baseUrl().'/v1/walletcashin/namelookup', $params);

            if ($response->successful()) {
                $body = $response->json();
                $name = $body['data'][0]['name'] ?? $body['name'] ?? null;

                if ($name !== null) {
                    return [
                        'result' => 'SUCCESS',
                        'name' => (string) $name,
                        'phone' => $normalizedPhone,
                        'network' => strtoupper($network),
                    ];
                }
            }

            Log::warning('Selcom name lookup returned unsuccessful response', [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);

            return [
                'result' => 'FAIL',
                'name' => '',
                'phone' => $normalizedPhone,
                'network' => strtoupper($network),
                'message' => $response->json('message') ?? 'Unable to lookup account name with provider.',
            ];
        } catch (\Throwable $e) {
            Log::error('Selcom name lookup exception: '.$e->getMessage());

            return [
                'result' => 'FAIL',
                'name' => '',
                'phone' => $normalizedPhone,
                'network' => strtoupper($network),
                'message' => 'Network error during name lookup: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Create minimal checkout order.
     *
     * @param  array{
     *     order_id: string,
     *     buyer_email: string,
     *     buyer_name: string,
     *     buyer_phone: string,
     *     amount: float|int,
     *     currency?: string,
     *     no_of_items?: int,
     *     redirect_url: string,
     *     cancel_url: string,
     *     webhook: string
     * }  $params
     * @return array{result: string, resultcode: string, message: string, payment_gateway_url?: string, reference?: string}
     */
    public function createOrderMinimal(array $params): array
    {
        $payload = [
            'vendor' => (string) $this->vendorId,
            'order_id' => (string) $params['order_id'],
            'buyer_email' => (string) $params['buyer_email'],
            'buyer_name' => (string) $params['buyer_name'],
            'buyer_phone' => $this->normalizePhone((string) $params['buyer_phone']),
            'amount' => (int) round((float) $params['amount']),
            'currency' => $params['currency'] ?? 'TZS',
            'no_of_items' => (int) ($params['no_of_items'] ?? 1),
            'payment_methods' => 'ALL',
            'redirect_url' => base64_encode($params['redirect_url']),
            'cancel_url' => base64_encode($params['cancel_url']),
            'webhook' => base64_encode($params['webhook']),
        ];

        if ($this->isSimulationMode()) {
            $mockRef = 'SELCOM-SIM-'.strtoupper(Str::random(10));

            return [
                'result' => 'SUCCESS',
                'resultcode' => '000',
                'message' => 'Simulated order created successfully',
                'reference' => $mockRef,
                'payment_gateway_url' => $params['redirect_url'].(str_contains($params['redirect_url'], '?') ? '&' : '?').'selcom_simulated=1&reference='.$mockRef,
            ];
        }

        try {
            $headers = $this->computeHeaders($payload);
            $response = Http::withHeaders($headers)
                ->timeout(20)
                ->post($this->baseUrl().'/v1/checkout/create-order-minimal', $payload);

            $data = $response->json();

            if ($response->successful() && ($data['result'] ?? '') === 'SUCCESS') {
                $gatewayUrl = $data['data'][0]['payment_gateway_url'] ?? $data['payment_gateway_url'] ?? null;
                $reference = $data['data'][0]['reference'] ?? $data['reference'] ?? null;

                return [
                    'result' => 'SUCCESS',
                    'resultcode' => $data['resultcode'] ?? '000',
                    'message' => $data['message'] ?? 'Order created successfully',
                    'payment_gateway_url' => $gatewayUrl,
                    'reference' => $reference,
                ];
            }

            Log::error('Selcom create order minimal failed', [
                'payload' => $payload,
                'response' => $data,
                'status' => $response->status(),
            ]);

            return [
                'result' => 'FAIL',
                'resultcode' => $data['resultcode'] ?? '999',
                'message' => $data['message'] ?? 'Payment gateway returned an error.',
            ];
        } catch (\Throwable $e) {
            Log::error('Selcom create order exception: '.$e->getMessage());

            return [
                'result' => 'FAIL',
                'resultcode' => '500',
                'message' => 'Connection failed with Selcom: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Query order status directly from Selcom.
     *
     * @return array<string, mixed>
     */
    public function queryOrderStatus(string $orderId): array
    {
        if ($this->isSimulationMode()) {
            return [
                'result' => 'SUCCESS',
                'resultcode' => '000',
                'order_status' => 'COMPLETED',
                'message' => 'Simulated order status: COMPLETED',
            ];
        }

        $params = ['order_id' => $orderId];

        try {
            $headers = $this->computeHeaders($params);
            $response = Http::withHeaders($headers)
                ->timeout(15)
                ->get($this->baseUrl().'/v1/checkout/order-status', $params);

            return $response->json() ?? [];
        } catch (\Throwable $e) {
            Log::error('Selcom query order status error: '.$e->getMessage());

            return [
                'result' => 'FAIL',
                'resultcode' => '500',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Normalize Tanzania phone numbers to 255XXXXXXXXX format.
     */
    public function normalizePhone(string $phone): string
    {
        $clean = preg_replace('/[^\d]/', '', $phone);

        if (str_starts_with($clean, '0') && strlen($clean) === 10) {
            return '255'.substr($clean, 1);
        }

        if (str_starts_with($clean, '255') && strlen($clean) === 12) {
            return $clean;
        }

        return $clean;
    }
}
