<?php

use Webkul\Contact\Models\Organization;
use Webkul\Contact\Models\Person;

it('can create a person via the store endpoint', function () {
    $admin = getDefaultAdmin();
    $countBefore = Person::count();
    $suffix = now()->timestamp;

    $response = test()->actingAs($admin)
        ->from(route('admin.contacts.persons.create'))
        ->post(route('admin.contacts.persons.store'), [
            'name' => "Person {$suffix}",
            'emails' => [
                ['value' => "person{$suffix}@example.com", 'label' => 'work'],
            ],
            'contact_numbers' => [
                ['value' => (string) $suffix, 'label' => 'work'],
            ],
        ]);

    $response->assertStatus(302);
    expect(Person::count())->toBe($countBefore + 1);

    $person = Person::orderBy('id', 'desc')->first();
    expect(decrypt($person->getRawOriginal('name'), false))->toBe("Person {$suffix}");
    expect($person->emails)->toBeArray();
    expect($person->emails[0]['value'])->toBe("person{$suffix}@example.com");
});

it('does not create a duplicate organization when organization_id is provided', function () {
    $admin = getDefaultAdmin();
    $suffix = uniqid('test2_');

    test()->actingAs($admin)
        ->from(route('admin.contacts.organizations.create'))
        ->post(route('admin.contacts.organizations.store'), [
            'name' => "Org {$suffix}",
        ]);

    $organization = Organization::orderBy('id', 'desc')->first();
    $orgCountBefore = Organization::count();
    $personCountBefore = Person::count();

    $response = test()->actingAs($admin)
        ->from(route('admin.contacts.persons.create'))
        ->post(route('admin.contacts.persons.store'), [
            'name' => "Person {$suffix}",
            'emails' => [
                ['value' => "person{$suffix}@example.com", 'label' => 'work'],
            ],
            'contact_numbers' => [
                ['value' => "phone_{$suffix}", 'label' => 'work'],
            ],
            'organization_id' => $organization->id,
            'organization_name' => "Org {$suffix}",
        ]);

    $response->assertStatus(302);
    $response->assertSessionHasNoErrors();
    expect(Organization::count())->toBe($orgCountBefore);
    expect(Person::count())->toBe($personCountBefore + 1);

    $person = Person::orderBy('id', 'desc')->first();
    expect($person->organization_id)->toBe($organization->id);
});

it('can create a new organization via organization_name when no organization_id is set', function () {
    $admin = getDefaultAdmin();
    $suffix = uniqid('test3_');

    $orgCountBefore = Organization::count();
    $personCountBefore = Person::count();

    $response = test()->actingAs($admin)
        ->from(route('admin.contacts.persons.create'))
        ->post(route('admin.contacts.persons.store'), [
            'name' => "Person {$suffix}",
            'emails' => [
                ['value' => "person{$suffix}@example.com", 'label' => 'work'],
            ],
            'contact_numbers' => [
                ['value' => "phone_{$suffix}", 'label' => 'work'],
            ],
            'organization_name' => "Quick Org {$suffix}",
        ]);

    $response->assertStatus(302);
    $response->assertSessionHasNoErrors();
    expect(Organization::count())->toBe($orgCountBefore + 1);
    expect(Person::count())->toBe($personCountBefore + 1);

    $organization = Organization::orderBy('id', 'desc')->first();
    expect(decrypt($organization->getRawOriginal('name'), false))->toBe("Quick Org {$suffix}");

    $person = Person::orderBy('id', 'desc')->first();
    expect($person->organization_id)->toBe($organization->id);
});
