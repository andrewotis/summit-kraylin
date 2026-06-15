<?php

namespace Webkul\DataTransfer\Helpers\Importers\Persons;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Validator;
use Webkul\Activity\Repositories\ActivityRepository;
use Webkul\Attribute\Repositories\AttributeRepository;
use Webkul\Attribute\Repositories\AttributeValueRepository;
use Webkul\Contact\Models\Person;
use Webkul\Contact\Repositories\PersonRepository;
use Webkul\DataTransfer\Contracts\ImportBatch as ImportBatchContract;
use Webkul\DataTransfer\Helpers\Import;
use Webkul\DataTransfer\Helpers\Importers\AbstractImporter;
use Webkul\DataTransfer\Repositories\ImportBatchRepository;

class Importer extends AbstractImporter
{
    /**
     * Error code for non existing email.
     */
    const ERROR_EMAIL_NOT_FOUND_FOR_DELETE = 'email_not_found_to_delete';

    /**
     * Error code for duplicated email.
     */
    const ERROR_DUPLICATE_EMAIL = 'duplicated_email';

    /**
     * Error code for duplicated phone.
     */
    const ERROR_DUPLICATE_PHONE = 'duplicated_phone';

    /**
     * Permanent entity columns.
     */
    protected array $validColumnNames = [
        'contact_numbers',
        'emails',
        'job_title',
        'name',
        'organization_id',
        'user_id',
    ];

    /**
     * Error message templates.
     */
    protected array $messages = [
        self::ERROR_EMAIL_NOT_FOUND_FOR_DELETE => 'data_transfer::app.importers.persons.validation.errors.email-not-found',
        self::ERROR_DUPLICATE_EMAIL => 'data_transfer::app.importers.persons.validation.errors.duplicate-email',
        self::ERROR_DUPLICATE_PHONE => 'data_transfer::app.importers.persons.validation.errors.duplicate-phone',
    ];

    /**
     * Permanent entity columns.
     *
     * @var string[]
     */
    protected $permanentAttributes = ['emails'];

    /**
     * Permanent entity column.
     */
    protected string $masterAttributeCode = 'unique_id';

    /**
     * Emails storage.
     */
    protected array $emails = [];

    /**
     * Phones storage.
     */
    protected array $phones = [];

    /**
     * Create a new helper instance.
     *
     * @return void
     */
    public function __construct(
        protected ImportBatchRepository $importBatchRepository,
        protected PersonRepository $personRepository,
        protected AttributeRepository $attributeRepository,
        protected AttributeValueRepository $attributeValueRepository,
        protected Storage $personStorage,
    ) {
        parent::__construct(
            $importBatchRepository,
            $attributeRepository,
            $attributeValueRepository,
        );
    }

    /**
     * Initialize Product error templates.
     */
    protected function initErrorMessages(): void
    {
        foreach ($this->messages as $errorCode => $message) {
            $this->errorHelper->addErrorMessage($errorCode, trans($message));
        }

        parent::initErrorMessages();
    }

    /**
     * Validate data.
     */
    public function validateData(): void
    {
        /**
         * Clear stale email map cache before re-validation.
         */
        $cachePath = "imports/email_map_{$this->import->id}.json";

        if (\Illuminate\Support\Facades\Storage::disk('local')->exists($cachePath)) {
            \Illuminate\Support\Facades\Storage::disk('local')->delete($cachePath);
        }

        if ($this->import->action == Import::ACTION_DELETE) {
            $this->personStorage->init();
        }

        parent::validateData();
    }

    /**
     * Validates row.
     */
    public function validateRow(array $rowData, int $rowNumber): bool
    {
        $rowData = $this->parsedRowData($rowData);

        /**
         * If row is already validated than no need for further validation.
         */
        if (isset($this->validatedRows[$rowNumber])) {
            return ! $this->errorHelper->isRowInvalid($rowNumber);
        }

        $this->validatedRows[$rowNumber] = true;

        /**
         * If import action is delete than no need for further validation.
         */
        if ($this->import->action == Import::ACTION_DELETE) {
            foreach ($rowData['emails'] as $email) {
                if (! $this->isEmailExist($email['value'])) {
                    $this->skipRow($rowNumber, self::ERROR_EMAIL_NOT_FOUND_FOR_DELETE, 'email');

                    return false;
                }

                return true;
            }
        }

        /**
         * Validate row data.
         */
        $validator = Validator::make($rowData, [
            ...$this->getValidationRules('persons', $rowData),
            'organization_id' => 'nullable|integer|exists:organizations,id',
            'user_id' => 'required|exists:users,id',
            'contact_numbers' => 'nullable|array',
            'contact_numbers.*.value' => 'nullable|numeric',
            'contact_numbers.*.label' => 'nullable|in:home,work',
            'emails' => 'required|array',
            'emails.*.value' => 'required|email',
            'emails.*.label' => 'nullable|in:home,work',
        ]);

        if ($validator->fails()) {
            $failedAttributes = $validator->failed();

            foreach ($validator->errors()->getMessages() as $attributeCode => $message) {
                $errorCode = array_key_first($failedAttributes[$attributeCode] ?? []);

                $this->skipRow($rowNumber, $errorCode, $attributeCode, current($message));
            }
        }

        /**
         * Check if email is unique.
         */
        if (! empty($emails = $rowData['emails'])) {
            foreach ($emails as $email) {
                if (! in_array($email['value'], $this->emails)) {
                    $this->emails[] = $email['value'];
                } else {
                    $message = sprintf(
                        trans($this->messages[self::ERROR_DUPLICATE_EMAIL]),
                        $email['value']
                    );

                    $this->skipRow($rowNumber, self::ERROR_DUPLICATE_EMAIL, 'email', $message);
                }
            }
        }

        /**
         * Check if phone(s) are unique.
         */
        if (! empty($rowData['contact_numbers'])) {
            foreach ($rowData['contact_numbers'] as $phone) {
                if (! in_array($phone['value'], $this->phones)) {
                    if (! empty($phone['value'])) {
                        $this->phones[] = $phone['value'];
                    }
                } else {
                    $message = sprintf(
                        trans($this->messages[self::ERROR_DUPLICATE_PHONE]),
                        $phone['value']
                    );

                    $this->skipRow($rowNumber, self::ERROR_DUPLICATE_PHONE, 'phone', $message);
                }
            }
        }

        return ! $this->errorHelper->isRowInvalid($rowNumber);
    }

    /**
     * Start the import process.
     */
    public function importBatch(ImportBatchContract $batch): bool
    {
        Event::dispatch('data_transfer.imports.batch.import.before', $batch);

        if ($batch->import->action == Import::ACTION_DELETE) {
            $this->deletePersons($batch);
        } else {
            $this->savePersonData($batch);
        }

        /**
         * Update import batch summary.
         */
        $batch = $this->importBatchRepository->update([
            'state' => Import::STATE_PROCESSED,

            'summary' => [
                'created' => $this->getCreatedItemsCount(),
                'updated' => $this->getUpdatedItemsCount(),
                'deleted' => $this->getDeletedItemsCount(),
            ],
        ], $batch->id);

        Event::dispatch('data_transfer.imports.batch.import.after', $batch);

        return true;
    }

    /**
     * Delete persons from current batch.
     */
    protected function deletePersons(ImportBatchContract $batch): bool
    {
        /**
         * Load person storage with batch emails.
         */
        $emails = collect(Arr::pluck($batch->data, 'emails'))
            ->map(function ($value) {
                $normalized = $this->normalizeField($value);

                return $normalized[0]['value'] ?? null;
            });

        $this->personStorage->load($emails->toArray());

        $idsToDelete = [];

        foreach ($batch->data as $rowData) {
            $rowData = $this->parsedRowData($rowData);

            foreach ($rowData['emails'] as $email) {
                if (! $this->isEmailExist($email['value'])) {
                    continue;
                }

                $idsToDelete[] = $this->personStorage->get($email['value']);
            }
        }

        $idsToDelete = array_unique($idsToDelete);

        $this->deletedItemsCount = count($idsToDelete);

        $this->personRepository->deleteWhere([['id', 'IN', $idsToDelete]]);

        return true;
    }

    /**
     * Cache path for email→ID map between batch requests.
     */
    private function emailMapCachePath(): string
    {
        return "imports/email_map_{$this->import->id}.json";
    }

    /**
     * Save person from current batch.
     */
    protected function savePersonData(ImportBatchContract $batch): bool
    {
        /**
         * Load person storage with batch email.
         * Uses a file-based cache between batch HTTP requests so each
         * batch doesn't re-scan the entire persons table.
         */
        $emails = collect(Arr::pluck($batch->data, 'emails'))
            ->map(function ($value) {
                $normalized = $this->normalizeField($value);

                return $normalized[0]['value'] ?? null;
            });

        $cachePath = $this->emailMapCachePath();

        if (\Illuminate\Support\Facades\Storage::disk('local')->exists($cachePath)) {
            $cached = \Illuminate\Support\Facades\Storage::disk('local')->get($cachePath);

            $this->personStorage->setItems(json_decode($cached, true) ?? []);
        } else {
            $this->personStorage->load($emails->toArray());

            \Illuminate\Support\Facades\Storage::disk('local')->put(
                $cachePath,
                json_encode($this->personStorage->getItems())
            );
        }

        $persons = [];

        $attributeValues = [];

        /**
         * Prepare persons for import.
         */
        foreach ($batch->data as $rowData) {
            $rowData = $this->parsedRowData($rowData);

            $this->preparePersons($rowData, $persons);

            $this->prepareAttributeValues($rowData, $attributeValues);
        }

        $this->savePersons($persons);

        $this->saveAttributeValues($attributeValues);

        return true;
    }

    /**
     * Prepare persons from current batch.
     */
    public function preparePersons(array $rowData, array &$persons): void
    {
        $emails = $this->prepareEmail($rowData['emails']);

        foreach ($emails as $email) {
            $contactValue = ! empty($rowData['contact_numbers']) ? $rowData['contact_numbers'][0]['value'] : '';

            $rowData['unique_id'] = "{$rowData['user_id']}|{$rowData['organization_id']}|{$email}|{$contactValue}";

            if ($this->isEmailExist($email)) {
                $persons['update'][$email] = $rowData;
            } else {
                $persons['insert'][$email] = [
                    ...$rowData,
                    'created_at' => $rowData['created_at'] ?? now(),
                    'updated_at' => $rowData['updated_at'] ?? now(),
                ];
            }
        }
    }

    /**
     * Save persons from current batch.
     */
    public function savePersons(array $persons): void
    {
        $userId = Auth::check() ? Auth::id() : null;

        if (! empty($persons['update'])) {
            $this->updatedItemsCount += count($persons['update']);

            array_walk($persons['update'], fn (&$row) => $this->encryptArrayFields($row));

            $this->personRepository->upsert(
                $persons['update'],
                $this->masterAttributeCode,
            );

            $this->logActivity(array_keys($persons['update']), 'Updated via CSV import', $userId);
        }

        if (! empty($persons['insert'])) {
            $this->createdItemsCount += count($persons['insert']);

            $uniqueIds = array_map(fn ($row) => $row['unique_id'], $persons['insert']);

            array_walk($persons['insert'], fn (&$row) => $this->encryptArrayFields($row));

            $this->personRepository->insert($persons['insert']);

            /**
             * Query only the newly inserted persons by unique_id (not encrypted)
             * instead of reloading all persons from the database.
             */
            $insertedPersons = Person::query()
                ->select(['id', 'emails'])
                ->whereIn('unique_id', $uniqueIds)
                ->get();

            foreach ($insertedPersons as $person) {
                $decoded = collect($person->emails);

                $decoded->each(fn ($email) => $this->personStorage->set($email['value'], $person->id));
            }

            $this->logActivity(array_keys($persons['insert']), 'Created via CSV import', $userId);

            /**
             * Persist the updated email map to cache for the next batch.
             */
            \Illuminate\Support\Facades\Storage::disk('local')->put(
                $this->emailMapCachePath(),
                json_encode($this->personStorage->getItems())
            );
        }
    }

    /**
     * Create a system activity for each person and attach via pivot.
     */
    private function logActivity(array $emails, string $title, ?int $userId): void
    {
        foreach ($emails as $email) {
            $personId = $this->personStorage->get($email);

            if (! $personId) {
                continue;
            }

            $activity = app(ActivityRepository::class)->create([
                'type' => 'system',
                'title' => $title,
                'is_done' => 1,
                'user_id' => $userId,
            ]);

            DB::table('person_activities')->insert([
                'activity_id' => $activity->id,
                'person_id' => $personId,
            ]);
        }
    }

    /**
     * Encrypt array fields (emails, contact_numbers) for direct DB insertion,
     * bypassing Eloquent casts that don't apply with query builder insert()/upsert().
     */
    private function encryptArrayFields(array &$row): void
    {
        foreach (['name', 'emails', 'contact_numbers'] as $field) {
            if (isset($row[$field])) {
                $row[$field] = is_array($row[$field])
                    ? Crypt::encryptString(json_encode($row[$field]))
                    : Crypt::encryptString($row[$field]);
            }
        }
    }

    /**
     * Save attribute values for the person.
     */
    public function saveAttributeValues(array $attributeValues): void
    {
        $personAttributeValues = [];

        foreach ($attributeValues as $email => $attributeValue) {
            foreach ($attributeValue as $attribute) {
                $attribute['entity_id'] = (int) $this->personStorage->get($email);

                $attribute['unique_id'] = implode('|', array_filter([
                    $attribute['entity_id'],
                    $attribute['attribute_id'],
                ]));

                $attribute['entity_type'] = 'persons';

                foreach (['text_value', 'json_value'] as $field) {
                    if (isset($attribute[$field]) && is_array($attribute[$field])) {
                        $attribute[$field] = Crypt::encryptString(json_encode($attribute[$field]));
                    } elseif (isset($attribute[$field]) && $field === 'text_value') {
                        $attribute[$field] = Crypt::encryptString($attribute[$field]);
                    }
                }

                $personAttributeValues[$attribute['unique_id']] = $attribute;
            }
        }

        $this->attributeValueRepository->upsert($personAttributeValues, 'unique_id');
    }

    /**
     * Check if email exists.
     */
    public function isEmailExist(string $email): bool
    {
        return $this->personStorage->has($email);
    }

    /**
     * Prepare attribute values for the person.
     */
    public function prepareAttributeValues(array $rowData, array &$attributeValues): void
    {
        foreach ($rowData as $code => $value) {
            if (is_null($value)) {
                continue;
            }

            $where = ['code' => $code];

            if ($code === 'name') {
                $where['entity_type'] = 'persons';
            }

            $attribute = $this->attributeRepository->findOneWhere($where);

            if (! $attribute) {
                continue;
            }

            $typeFields = $this->personRepository->getModel()::$attributeTypeFields;

            $attributeTypeValues = array_fill_keys(array_values($typeFields), null);

            $emails = $this->prepareEmail($rowData['emails']);

            foreach ($emails as $email) {
                $attributeValues[$email][] = array_merge($attributeTypeValues, [
                    'attribute_id' => $attribute->id,
                    $typeFields[$attribute->type] => $value,
                ]);
            }
        }
    }

    /**
     * Normalize a CSV field that accepts either a JSON array
     * `[{"label":"work","value":"..."}]` or a bare value.
     * Bare values get wrapped with label "work".
     */
    private function normalizeField(?string $value): array
    {
        if (empty($value)) {
            return [];
        }

        $decoded = json_decode($value, true);

        if (is_array($decoded)) {
            return $decoded;
        }

        return [['label' => 'work', 'value' => $value]];
    }

    /**
     * Get parsed email and phone.
     */
    private function parsedRowData(array $rowData): array
    {
        $rowData['emails'] = $this->normalizeField($rowData['emails'] ?? null);

        $rowData['contact_numbers'] = $this->normalizeField($rowData['contact_numbers'] ?? null);

        return $rowData;
    }

    /**
     * Prepare email from row data.
     */
    private function prepareEmail(array $emails): Collection
    {
        return collect($emails)->pluck('value');
    }
}
