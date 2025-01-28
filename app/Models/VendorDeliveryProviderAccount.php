<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\VendorDeliveryProviderAccount
 *
 * @property int $id
 * @property int $vendor_id
 * @property string $provider_name
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|VendorDeliveryProviderAccount newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VendorDeliveryProviderAccount newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VendorDeliveryProviderAccount query()
 * @method static \Illuminate\Database\Eloquent\Builder|VendorDeliveryProviderAccount whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VendorDeliveryProviderAccount whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VendorDeliveryProviderAccount whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VendorDeliveryProviderAccount whereProviderName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VendorDeliveryProviderAccount whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VendorDeliveryProviderAccount whereVendorId($value)
 *
 * @mixin \Eloquent
 */
class VendorDeliveryProviderAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'data',
        'provider_name',
    ];

    protected $casts = [
        'data' => 'json',
    ];
}
