<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    public const METHOD_CASH_ON_DELIVERY = 'cash_on_delivery';

    public const METHOD_VODACOM_LIPA = 'vodacom_lipa';

    public const METHOD_SELCOM = 'selcom';

    public const METHOD_LABELS = [
        self::METHOD_CASH_ON_DELIVERY => 'Cash on delivery',
        self::METHOD_VODACOM_LIPA => 'Vodacom Lipa (M-Pesa)',
        self::METHOD_SELCOM => 'Selcom Pay (Mobile Money)',
    ];

    public const STATUS_PENDING = 'pending';

    public const STATUS_AWAITING_VERIFICATION = 'awaiting_verification';

    public const STATUS_VERIFIED = 'verified';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_REFUNDED = 'refunded';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_AWAITING_VERIFICATION,
        self::STATUS_VERIFIED,
        self::STATUS_REJECTED,
        self::STATUS_REFUNDED,
    ];

    public const STATUS_LABELS = [
        self::STATUS_PENDING => 'Pending',
        self::STATUS_AWAITING_VERIFICATION => 'Awaiting verification',
        self::STATUS_VERIFIED => 'Verified',
        self::STATUS_REJECTED => 'Rejected',
        self::STATUS_REFUNDED => 'Refunded',
    ];

    public const LIPA_NUMBER_PATTERN = '/\A\d{5,9}\z/';

    protected $guarded = ['id'];

    protected $casts = [
        'amount' => 'decimal:2',
        'payload' => 'array',
        'submitted_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    /**
     * Payment settings shared by the checkout, order, and admin screens.
     *
     * @return array<string, string>
     */
    public static function settings(): array
    {
        $stored = BusinessSetting::query()
            ->whereIn('type', [
                'cash_on_delivery',
                'lipa_enabled',
                'lipa_number',
                'lipa_business_name',
                'lipa_instructions',
                'lipa_requires_verification',
                'lipa_webhook_secret',
                'selcom_enabled',
                'selcom_payment_methods',
                'selcom_vendor_id',
                'selcom_api_key',
                'selcom_api_secret',
                'selcom_instructions',
            ])
            ->pluck('value', 'type');

        return [
            'cash_on_delivery' => (string) ($stored->get('cash_on_delivery') ?? '0'),
            'lipa_enabled' => (string) ($stored->get('lipa_enabled') ?? '0'),
            'lipa_number' => (string) ($stored->get('lipa_number') ?? ''),
            'lipa_business_name' => (string) ($stored->get('lipa_business_name') ?? ''),
            'lipa_instructions' => (string) ($stored->get('lipa_instructions') ?? ''),
            'lipa_requires_verification' => (string) ($stored->get('lipa_requires_verification') ?? '1'),
            'lipa_webhook_secret' => (string) ($stored->get('lipa_webhook_secret') ?? ''),
            'selcom_enabled' => (string) ($stored->get('selcom_enabled') ?? '0'),
            'selcom_payment_methods' => (string) ($stored->get('selcom_payment_methods') ?? 'MOBILEMONEY'),
            'selcom_vendor_id' => (string) ($stored->get('selcom_vendor_id') ?? ''),
            'selcom_api_key' => (string) ($stored->get('selcom_api_key') ?? ''),
            'selcom_api_secret' => (string) ($stored->get('selcom_api_secret') ?? ''),
            'selcom_instructions' => (string) ($stored->get('selcom_instructions') ?? ''),
        ];
    }

    /**
     * Payment methods the store can accept right now, keyed by method.
     *
     * @return array<string, string>
     */
    public static function availableMethods(): array
    {
        $settings = self::settings();
        $methods = [];

        if ($settings['cash_on_delivery'] === '1') {
            $methods[self::METHOD_CASH_ON_DELIVERY] = self::METHOD_LABELS[self::METHOD_CASH_ON_DELIVERY];
        }

        if (
            $settings['lipa_enabled'] === '1'
            && preg_match(self::LIPA_NUMBER_PATTERN, $settings['lipa_number']) === 1
        ) {
            $methods[self::METHOD_VODACOM_LIPA] = self::METHOD_LABELS[self::METHOD_VODACOM_LIPA];
        }

        if (
            $settings['selcom_enabled'] === '1'
            && filled($settings['selcom_api_key'])
            && filled($settings['selcom_api_secret'])
            && filled($settings['selcom_vendor_id'])
        ) {
            $methods[self::METHOD_SELCOM] = self::METHOD_LABELS[self::METHOD_SELCOM];
        }

        return $methods;
    }

    /**
     * The merchant account and gateway configuration for Selcom.
     *
     * @return array{enabled: bool, payment_methods: string, vendor_id: string, instructions: string, is_configured: bool}
     */
    public static function selcomAccount(): array
    {
        $settings = self::settings();

        return [
            'enabled' => $settings['selcom_enabled'] === '1',
            'payment_methods' => $settings['selcom_payment_methods'],
            'vendor_id' => $settings['selcom_vendor_id'],
            'instructions' => $settings['selcom_instructions'],
            'is_configured' => filled($settings['selcom_api_key']) && filled($settings['selcom_api_secret']) && filled($settings['selcom_vendor_id']),
        ];
    }

    /**
     * The merchant account a customer pays into for Vodacom Lipa.
     *
     * @return array{number: string, business_name: string, instructions: string, requires_verification: bool}
     */
    public static function lipaAccount(): array
    {
        $settings = self::settings();

        return [
            'number' => $settings['lipa_number'],
            'business_name' => $settings['lipa_business_name'],
            'instructions' => $settings['lipa_instructions'],
            'requires_verification' => $settings['lipa_requires_verification'] === '1',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function methodLabel(): string
    {
        return self::METHOD_LABELS[$this->method] ?? ucfirst(str_replace('_', ' ', $this->method));
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst(str_replace('_', ' ', (string) $this->status));
    }

    public function isVerified(): bool
    {
        return $this->status === self::STATUS_VERIFIED;
    }
}
