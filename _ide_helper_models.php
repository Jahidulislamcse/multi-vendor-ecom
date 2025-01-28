<?php

// @formatter:off
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * App\Models\Livestream
 *
 * @property int $id
 * @property string $title
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Product> $products
 * @property-read int|null $products_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\ModelStatus\Status> $statuses
 * @property-read int|null $statuses_count
 * @property-read string $status
 * @property-read \App\Models\Vendor|null $vendor
 * @method static \Illuminate\Database\Eloquent\Builder|Livestream currentStatus(...$names)
 * @method static \Database\Factories\LivestreamFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Livestream newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Livestream newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Livestream otherCurrentStatus(...$names)
 * @method static \Illuminate\Database\Eloquent\Builder|Livestream query()
 * @method static \Illuminate\Database\Eloquent\Builder|Livestream whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Livestream whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Livestream whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Livestream whereUpdatedAt($value)
 * @property int $vendor_id
 * @property \Illuminate\Support\Carbon|null $scheduled_time
 * @property \Illuminate\Support\Carbon|null $started_at
 * @property \Illuminate\Support\Carbon|null $ended_at
 * @property int|null $total_duration
 * @method static \Illuminate\Database\Eloquent\Builder|Livestream whereEndedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Livestream whereScheduledTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Livestream whereStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Livestream whereTotalDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Livestream whereVendorId($value)
 * @mixin \Eloquent
 */
	class Livestream extends \Eloquent implements \Spatie\MediaLibrary\HasMedia {}
}

namespace App\Models{
/**
 * App\Models\LivestreamProduct
 *
 * @property int $product_id
 * @property int $livestream_id
 * @method static \Illuminate\Database\Eloquent\Builder|LivestreamProduct newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LivestreamProduct newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LivestreamProduct query()
 * @method static \Illuminate\Database\Eloquent\Builder|LivestreamProduct whereLivestreamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LivestreamProduct whereProductId($value)
 * @mixin \Eloquent
 */
	class LivestreamProduct extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Product
 *
 * @property int $id
 * @property int|null $brand_id
 * @property int $product_type_id
 * @property ProductStatuses $status
 * @property Collection $attribute_data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int $vendor_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Lunar\Models\ProductAssociation> $associations
 * @property-read int|null $associations_count
 * @property-read \Lunar\Models\Brand|null $brand
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Lunar\Models\Channel> $channels
 * @property-read int|null $channels_count
 * @property-read \Kalnoy\Nestedset\Collection<int, \Lunar\Models\Collection> $collections
 * @property-read int|null $collections_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Lunar\Models\CustomerGroup> $customerGroups
 * @property-read int|null $customer_groups_count
 * @property-read \Lunar\Models\Url|null $defaultUrl
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $images
 * @property-read int|null $images_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Lunar\Models\ProductAssociation> $inverseAssociations
 * @property-read int|null $inverse_associations_count
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Lunar\Models\Price> $prices
 * @property-read int|null $prices_count
 * @property-read \Lunar\Models\ProductType $productType
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Lunar\Models\Tag> $tags
 * @property-read int|null $tags_count
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Media|null $thumbnail
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Lunar\Models\Url> $urls
 * @property-read int|null $urls_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Lunar\Models\ProductVariant> $variants
 * @property-read int|null $variants_count
 * @property-read \App\Models\Vendor|null $vendor
 * @method static Builder|Product channel(\Lunar\Models\Channel|\Traversable|array|null $channel = null, ?\DateTime $startsAt = null, ?\DateTime $endsAt = null)
 * @method static Builder|Product customerGroup(\Lunar\Models\CustomerGroup|\Traversable|array|null $customerGroup = null, ?\DateTime $startsAt = null, ?\DateTime $endsAt = null)
 * @method static \Lunar\Database\Factories\ProductFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Product newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Product newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Product onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Product query()
 * @method static Builder|Product status(string $status)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereAttributeData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereBrandId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereProductTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereVendorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Product withoutTrashed()
 * @mixin \Eloquent
 */
	class Product extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\User
 *
 * @property-read int|null $vendors_count
 * @property int $id
 * @property string|null $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Lunar\Models\Customer> $customers
 * @property-read int|null $customers_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Lunar\Models\Order> $orders
 * @property-read int|null $orders_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read int|null $roles_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Vendor> $vendors
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User permission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User role($roles, $guard = null)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 * @property string|null $phone_number
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 * @property-read int|null $media_count
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePhoneNumber($value)
 * @mixin \Eloquent
 * @property mixed|null $password
 * @property \App\Constants\GenderTypes|null $gender
 * @property string|null $address
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 */
	class User extends \Eloquent implements \Spatie\MediaLibrary\HasMedia, \Illuminate\Contracts\Auth\MustVerifyEmail {}
}

namespace App\Models{
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
 * @mixin \Eloquent
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
 * @method static \Illuminate\Database\Eloquent\Builder|Vendor whereAreaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vendor whereCityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vendor whereContactEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vendor whereContactPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vendor whereStoreId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vendor whereZoneId($value)
 */
	class Vendor extends \Eloquent implements \Spatie\MediaLibrary\HasMedia {}
}

namespace App\Models{
/**
 * App\Models\VendorDeliveryProviderAccount
 *
 * @property int $id
 * @property int $vendor_id
 * @property string $provider_name
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|VendorDeliveryProviderAccount newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VendorDeliveryProviderAccount newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VendorDeliveryProviderAccount query()
 * @method static \Illuminate\Database\Eloquent\Builder|VendorDeliveryProviderAccount whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VendorDeliveryProviderAccount whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VendorDeliveryProviderAccount whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VendorDeliveryProviderAccount whereProviderName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VendorDeliveryProviderAccount whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VendorDeliveryProviderAccount whereVendorId($value)
 */
	class VendorDeliveryProviderAccount extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\VendorFollower
 *
 * @property int $user_id
 * @property int $vendor_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|VendorFollower newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VendorFollower newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VendorFollower query()
 * @method static \Illuminate\Database\Eloquent\Builder|VendorFollower whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VendorFollower whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VendorFollower whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VendorFollower whereVendorId($value)
 * @mixin \Eloquent
 */
	class VendorFollower extends \Eloquent {}
}

namespace App\Webhooks\Livekit{
/**
 * App\Webhooks\Livekit\LivekitWebhookCall
 *
 * @method static \Illuminate\Database\Eloquent\Builder|LivekitWebhookCall newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LivekitWebhookCall newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LivekitWebhookCall query()
 * @mixin \Eloquent
 */
	class LivekitWebhookCall extends \Eloquent {}
}

