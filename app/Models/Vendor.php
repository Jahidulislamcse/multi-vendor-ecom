<?php

namespace App\Models;

use AhmedAliraqi\LaravelMediaUploader\Entities\Concerns\HasUploader;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MannikJ\Laravel\Wallet\Traits\HasWallet;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * App\Models\Vendor
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $description
 * @property string $address
 * @property string $contact_email
 * @property string $contact_phone
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $followers
 * @property-read int|null $followers_count
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Product> $products
 * @property-read int|null $products_count
 * @property-read \App\Models\User|null $user
 *
 * @method static \Database\Factories\VendorFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Vendor newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Vendor newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Vendor query()
 * @method static \Illuminate\Database\Eloquent\Builder|Vendor whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vendor whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vendor whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vendor whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vendor whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vendor whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vendor whereUserId($value)
 *
 * @property int|null $city_id
 * @property int|null $area_id
 * @property int|null $zone_id
 * @property int|null $store_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\VendorDeliveryProviderAccount> $deliveryProviderAccounts
 * @property-read int|null $delivery_provider_accounts_count
 * @property-read mixed $balance
 * @property-read \MannikJ\Laravel\Wallet\Models\Wallet $wallet
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \MannikJ\Laravel\Wallet\Models\Transaction> $walletTransactions
 * @property-read int|null $wallet_transactions_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Vendor whereAreaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vendor whereCityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vendor whereContactEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vendor whereContactPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vendor whereStoreId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vendor whereZoneId($value)
 *
 * @mixin \Eloquent
 */
class Vendor extends Model implements HasMedia
{
    use HasFactory, HasUploader, HasWallet, InteractsWithMedia;

    protected $fillable = [
        'user_id',
        'name',
        'address',
        'description',
        'contact_email',
        'contact_phone',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // public function products()
    // {
    //     return $this->hasMany(Product::class);
    // }

    public function deliveryProviderAccounts()
    {
        return $this->hasMany(VendorDeliveryProviderAccount::class);
    }

    public function followers()
    {
        return $this->belongsToMany(User::class, VendorFollower::getModel()->getTable())->using(VendorFollower::class);
    }
}
