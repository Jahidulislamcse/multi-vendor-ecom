<?php

namespace App\Data\Dto;

use AhmedAliraqi\LaravelMediaUploader\Entities\TemporaryFile;
use App\Constants\LivestreamStatuses;
use Carbon\Carbon;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\Validation\AfterOrEqual;
use Spatie\LaravelData\Attributes\Validation\Enum;
use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\LaravelData\Optional;

#[MapName(SnakeCaseMapper::class)]
class UpdateLivestremData extends Data
{
    public function __construct(
        public Optional|string $title,
        #[AfterOrEqual('today')]
        public Optional|Carbon|null $scheduledTime,
        #[Exists(TemporaryFile::class, 'token')]
        public Optional|string $thumbnailPicture,
        #[Enum(LivestreamStatuses::class)]
        public LivestreamStatuses|Optional $status,
    ) {
    }
}
