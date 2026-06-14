<?php

namespace Webkul\Admin\Http\Controllers\Settings;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Event;
use Illuminate\View\View;
use Webkul\Admin\DataGrids\Settings\SubscriberDataGrid;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Admin\Http\Requests\MassDestroyRequest;
use Webkul\Contact\Repositories\PersonRepository;
use Webkul\MailingList\Repositories\MailingListRepository;
use Webkul\MailingList\Repositories\SubscriberRepository;

class SubscriberController extends Controller
{
    public function __construct(
        protected SubscriberRepository $subscriberRepository,
        protected MailingListRepository $mailingListRepository,
        protected PersonRepository $personRepository,
    ) {}

    public function toggle(int $mailingListId, int $id): JsonResponse
    {
        $subscriber = $this->subscriberRepository->findOrFail($id);

        $subscriber = $this->subscriberRepository->update([
            'is_subscribed' => ! $subscriber->is_subscribed,
        ], $id);

        $message = $subscriber->is_subscribed
            ? trans('admin::app.settings.mailing-lists.subscribers.subscribed-success')
            : trans('admin::app.settings.mailing-lists.subscribers.unsubscribed-success');

        return response()->json([
            'message' => $message,
        ]);
    }

    public function massToggle(MassDestroyRequest $massDestroyRequest): JsonResponse
    {
        $indices = $massDestroyRequest->input('indices');

        foreach ($indices as $id) {
            $subscriber = $this->subscriberRepository->find($id);

            if (! $subscriber) {
                continue;
            }

            $this->subscriberRepository->update([
                'is_subscribed' => ! $subscriber->is_subscribed,
            ], $id);
        }

        return response()->json([
            'message' => trans('admin::app.settings.mailing-lists.subscribers.mass-toggle-success'),
        ]);
    }

    public function index(int $mailingListId): View|JsonResponse
    {
        $mailingList = $this->mailingListRepository->findOrFail($mailingListId);

        if (request()->isXmlHttpRequest()) {
            return datagrid(SubscriberDataGrid::class)->process();
        }

        return view('admin::settings.mailing-lists.subscribers', compact('mailingList'));
    }

    public function store(int $mailingListId): JsonResponse
    {
        $this->validate(request(), [
            'person_id' => 'required|integer|exists:persons,id',
        ]);

        $mailingList = $this->mailingListRepository->findOrFail($mailingListId);

        $person = $this->personRepository->findOrFail(request('person_id'));

        $existing = $this->subscriberRepository->findWhere([
            'mailing_list_id' => $mailingList->id,
            'person_id' => $person->id,
        ])->first();

        if ($existing) {
            return response()->json([
                'message' => trans('admin::app.settings.mailing-lists.subscribers.already-exists'),
            ], 422);
        }

        Event::dispatch('settings.subscribers.create.before');

        $subscriber = $this->subscriberRepository->create([
            'mailing_list_id' => $mailingList->id,
            'person_id' => $person->id,
            'is_subscribed' => true,
        ]);

        Event::dispatch('settings.subscribers.create.after', $subscriber);

        return response()->json([
            'data' => $subscriber,
            'message' => trans('admin::app.settings.mailing-lists.subscribers.create-success'),
        ]);
    }

    public function edit(int $mailingListId, int $id): JsonResponse
    {
        $subscriber = $this->subscriberRepository->with('person')->findOrFail($id);

        return response()->json([
            'data' => [
                'id' => $subscriber->id,
                'person_id' => $subscriber->person_id,
                'person' => $subscriber->person ? [
                    'id' => $subscriber->person->id,
                    'name' => $subscriber->person->name,
                ] : null,
            ],
        ]);
    }

    public function update(int $mailingListId, int $id): JsonResponse
    {
        $this->validate(request(), [
            'person_id' => 'required|integer|exists:persons,id',
        ]);

        $subscriber = $this->subscriberRepository->findOrFail($id);

        $existing = $this->subscriberRepository->findWhere([
            'mailing_list_id' => $mailingListId,
            'person_id' => request('person_id'),
        ])->first();

        if ($existing && $existing->id !== $subscriber->id) {
            return response()->json([
                'message' => trans('admin::app.settings.mailing-lists.subscribers.already-exists'),
            ], 422);
        }

        Event::dispatch('settings.subscribers.update.before', $id);

        $subscriber = $this->subscriberRepository->update([
            'person_id' => request('person_id'),
        ], $id);

        Event::dispatch('settings.subscribers.update.after', $subscriber);

        return response()->json([
            'data' => $subscriber,
            'message' => trans('admin::app.settings.mailing-lists.subscribers.update-success'),
        ]);
    }

    public function destroy(int $mailingListId, int $id): JsonResponse
    {
        $this->subscriberRepository->findOrFail($id);

        Event::dispatch('settings.subscribers.delete.before', $id);

        $this->subscriberRepository->delete($id);

        Event::dispatch('settings.subscribers.delete.after', $id);

        return response()->json([
            'message' => trans('admin::app.settings.mailing-lists.subscribers.delete-success'),
        ]);
    }

    public function massDestroy(MassDestroyRequest $massDestroyRequest): JsonResponse
    {
        $indices = $massDestroyRequest->input('indices');

        foreach ($indices as $id) {
            Event::dispatch('settings.subscribers.delete.before', $id);

            $this->subscriberRepository->delete($id);

            Event::dispatch('settings.subscribers.delete.after', $id);
        }

        return response()->json([
            'message' => trans('admin::app.settings.mailing-lists.subscribers.delete-success'),
        ]);
    }
}
