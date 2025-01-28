<?php

namespace App\Jobs;

use App\Constants\SupportedShippingMethods;
use App\Data\Dto\Pathao\CreateNewStoreRequestData;
use App\Data\Dto\Pathao\CreateNewStoreResponseData;
use App\Facades\Pathao;
use App\Models\Vendor;
use App\Models\VendorDeliveryProviderAccount;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\Attributes\WithoutRelations;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SubmitVendorDataToPathaoToCreateStore implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        #[WithoutRelations]
        public readonly Vendor $vendor
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        /** @var VendorDeliveryProviderAccount */
        $pathaoDeliveryAccount = $this->vendor->deliveryProviderAccounts()->where('provider_name', SupportedShippingMethods::PATHAO->value)->first();

        /** @var CreateNewStoreResponseData */
        $result = Pathao::createNewStore(new CreateNewStoreRequestData(
            name: $this->vendor->name,
            contactName: $this->vendor->name,
            contactNumber: $this->vendor->contact_phone,
            secondaryContact: null,
            address: $this->vendor->address,
            cityId: $pathaoDeliveryAccount->data['city']['id'],
            areaId: $pathaoDeliveryAccount->data['area']['id'],
            zoneId: $pathaoDeliveryAccount->data['zone']['id'],
        ));

        $pathaoDeliveryAccount->update([
            'data' => $pathaoDeliveryAccount->data + [
                'store' => [
                    'id' => $result->data->storeId,
                    'name' => $result->data->storeName,
                ],
            ],
        ]);
    }
}
