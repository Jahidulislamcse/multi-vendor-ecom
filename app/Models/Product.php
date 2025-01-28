<?php

namespace App\Models;

use AhmedAliraqi\LaravelMediaUploader\Entities\Concerns\HasUploader;
use App\Constants\ProductStatuses;
use Database\Factories\ProductFactory;
use Illuminate\Support\Collection;
use Lunar\Base\Casts\AsAttributeData;
use Lunar\Models\Product as LunarProduct;

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
 *
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
 *
 * @mixin \Eloquent
 */
class Product extends LunarProduct
{
    use HasUploader;

    protected $casts = [
        'attribute_data' => AsAttributeData::class,

        'status' => ProductStatuses::class,
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Return a new factory instance for the model.
     */
    protected static function newFactory(): ProductFactory
    {
        return ProductFactory::new();
    }
}
