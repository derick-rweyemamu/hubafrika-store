<?php

namespace App\Models;

use App\Models\Concerns\HasLibraryMedia;
use Database\Factories\BusinessSettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;

class BusinessSetting extends Model implements HasMedia
{
    /** @use HasFactory<BusinessSettingFactory> */
    use HasFactory;

    use HasLibraryMedia;

    protected $table = 'business_settings';

    protected $fillable = [
        'type',
        'value',
        'lang',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('branding')->singleFile();
    }

    /**
     * Branding files (logo, dark logo, favicon) stored as media, with the
     * legacy path kept as a fallback.
     */
    public function imageUrl(): ?string
    {
        return $this->firstMediaUrl('branding') ?? $this->legacyMediaUrl($this->value);
    }
}
