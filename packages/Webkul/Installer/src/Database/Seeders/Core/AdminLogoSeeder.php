<?php

namespace Webkul\Installer\Database\Seeders\Core;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Webkul\Core\Models\CoreConfig;

class AdminLogoSeeder extends Seeder
{
    public function run($parameters = [])
    {
        $sourceDir = __DIR__.'/../../../../../Admin/src/Resources/assets/images';

        $images = [
            'logo.svg',
            'favicon.ico',
            'dark-logo.svg',
            'mobile-light-logo.svg',
            'mobile-dark-logo.svg',
        ];

        foreach ($images as $name) {
            if (Storage::exists("configuration/$name")) {
                continue;
            }

            $source = "$sourceDir/$name";
            if (file_exists($source)) {
                Storage::put("configuration/$name", file_get_contents($source));
            }
        }

        $logoFile = $this->findFirstInStorage('configuration/logo.');
        $faviconFile = $this->findFirstInStorage('configuration/favicon.');

        CoreConfig::updateOrCreate(
            ['code' => 'general.general.admin_logo.logo_image'],
            ['value' => $logoFile ?: 'configuration/logo.svg']
        );

        CoreConfig::updateOrCreate(
            ['code' => 'general.general.admin_logo.favicon_image'],
            ['value' => $faviconFile ?: 'configuration/favicon.ico']
        );
    }

    private function findFirstInStorage(string $prefix): ?string
    {
        $files = Storage::files(dirname($prefix));

        $prefixBasename = basename($prefix);

        foreach ($files as $file) {
            if (str_starts_with(basename($file), $prefixBasename)) {
                return $file;
            }
        }

        return null;
    }
}
