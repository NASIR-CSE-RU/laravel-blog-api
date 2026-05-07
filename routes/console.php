<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('app:passport-init', function (ClientRepository $clients) {
    $provider = config('auth.guards.api.provider', 'users');

    $publicKey = Passport::keyPath('oauth-public.key');
    $privateKey = Passport::keyPath('oauth-private.key');

    if (! file_exists($publicKey) || ! file_exists($privateKey)) {
        $this->call('passport:keys');
    }

    try {
        $clients->personalAccessClient($provider);
        $this->components->info("Passport personal access client already exists for '{$provider}'.");
    } catch (\RuntimeException) {
        $clients->createPersonalAccessGrantClient(
            config('app.name').' Personal Access Client',
            $provider
        );

        $this->components->info("Passport personal access client created for '{$provider}'.");
    }
})->purpose('Ensure Passport keys and personal access client exist');
