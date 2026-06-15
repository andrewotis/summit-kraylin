<?php

namespace Webkul\DataTransfer\Helpers\Importers\Persons;

use Webkul\Contact\Models\Person;
use Webkul\Contact\Repositories\PersonRepository;

class Storage
{
    const CHUNK_SIZE = 200;

    /**
     * Items contains email as key and product information as value.
     */
    protected array $items = [];

    /**
     * Columns which will be selected from database.
     */
    protected array $selectColumns = [
        'id',
        'emails',
    ];

    /**
     * Create a new helper instance.
     *
     * @return void
     */
    public function __construct(protected PersonRepository $personRepository) {}

    /**
     * Initialize storage.
     */
    public function init(): void
    {
        $this->items = [];

        $this->load();
    }

    /**
     * Load the Emails.
     *
     * Emails are encrypted in the database, so we must load all records
     * and match in PHP after Eloquent decrypts them.
     */
    public function load(array $emails = []): void
    {
        $emailSet = ! empty($emails) ? array_flip($emails) : null;

        Person::query()->select($this->selectColumns)->chunk(self::CHUNK_SIZE, function ($persons) use ($emailSet) {
            foreach ($persons as $person) {
                $personEmails = collect($person->emails);

                if ($emailSet === null) {
                    $personEmails->each(fn ($email) => $this->set($email['value'], $person->id));
                } else {
                    $personEmails
                        ->filter(fn ($email) => isset($emailSet[$email['value']]))
                        ->each(fn ($email) => $this->set($email['value'], $person->id));
                }
            }
        });
    }

    /**
     * Get email information.
     */
    public function set(string $email, int $id): self
    {
        $this->items[$email] = $id;

        return $this;
    }

    /**
     * Set all items at once (from cache).
     */
    public function setItems(array $items): self
    {
        $this->items = $items;

        return $this;
    }

    /**
     * Get all items.
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * Check if email exists.
     */
    public function has(string $email): bool
    {
        return isset($this->items[$email]);
    }

    /**
     * Get email information.
     */
    public function get(string $email): ?int
    {
        if (! $this->has($email)) {
            return null;
        }

        return $this->items[$email];
    }

    /**
     * Is storage is empty.
     */
    public function isEmpty(): int
    {
        return empty($this->items);
    }
}
