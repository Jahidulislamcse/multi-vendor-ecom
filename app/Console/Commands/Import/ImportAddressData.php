<?php

namespace App\Console\Commands\Import;

use DB;
use Illuminate\Console\Command;
use Lunar\Models\Country;

class ImportAddressData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-address-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Address data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Importing Countries and States');

        $existing = Country::pluck('iso3');

        /**
         * Here we are using Http over Https due to some environments not having
         * the latest CA Authorities installed, causing an SSL exception to be thrown.
         */
        $countries = file_get_contents(implode(DIRECTORY_SEPARATOR, [__DIR__, 'address-data.json']));
        $countries = json_decode($countries, false);

        $newCountries = collect($countries)->filter(function ($country) use ($existing) {
            return ! $existing->contains($country->iso3);
        });

        if (! $newCountries->count()) {
            $this->info('There are no new countries to import');

            return Command::SUCCESS;
        }

        DB::transaction(function () use ($newCountries) {
            $this->withProgressBar($newCountries, function ($country) {
                $model = Country::create([
                    'name' => $country->name,
                    'iso3' => $country->iso3,
                    'iso2' => $country->iso2,
                    'phonecode' => $country->phone_code,
                    'capital' => $country->capital,
                    'currency' => $country->currency,
                    'native' => $country->native,
                    'emoji' => $country->emoji,
                    'emoji_u' => $country->emojiU,
                ]);

                $states = collect($country->states)->map(function ($state) {
                    return [
                        'name' => $state->name,
                        'code' => $state->state_code,
                    ];
                });

                $model->states()->createMany($states->toArray());
            });
        });

        $this->line('');

        return Command::SUCCESS;
    }
}
