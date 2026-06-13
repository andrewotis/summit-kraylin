<?php

use Webkul\Contact\Models\Organization;

it('can create an organization via the store endpoint', function () {
    $admin = getDefaultAdmin();
    $countBefore = Organization::count();
    $suffix = now()->timestamp;

    $response = test()->actingAs($admin)
        ->from(route('admin.contacts.organizations.create'))
        ->post(route('admin.contacts.organizations.store'), [
            'name' => "Org {$suffix}",
        ]);

    $response->assertStatus(302);
    expect(Organization::count())->toBe($countBefore + 1);

    $organization = Organization::orderBy('id', 'desc')->first();
    expect(decrypt($organization->getRawOriginal('name'), false))->toBe("Org {$suffix}");
});

it('can create an organization with an address attribute', function () {
    $admin = getDefaultAdmin();
    $countBefore = Organization::count();
    $suffix = now()->timestamp;

    $response = test()->actingAs($admin)
        ->from(route('admin.contacts.organizations.create'))
        ->post(route('admin.contacts.organizations.store'), [
            'name' => "Org Address {$suffix}",
            'address' => [
                'address' => '123 Main St',
                'country' => 'US',
                'state' => 'MD',
                'city' => 'Columbia',
                'postcode' => '21044',
            ],
        ]);

    $response->assertStatus(302);
    expect(Organization::count())->toBe($countBefore + 1);

    $organization = Organization::orderBy('id', 'desc')->first();
    expect(decrypt($organization->getRawOriginal('name'), false))->toBe("Org Address {$suffix}");
});
