<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * App\Models\VendorFollower
 *
 * @property int $user_id
 * @property int $vendor_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|VendorFollower newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VendorFollower newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VendorFollower query()
 * @method static \Illuminate\Database\Eloquent\Builder|VendorFollower whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VendorFollower whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VendorFollower whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VendorFollower whereVendorId($value)
 *
 * @mixin \Eloquent
 */
class VendorFollower extends Pivot
{
    protected $table = 'vendor_followers';
}
