<?php

namespace Webkul\Admin\Http\Controllers\Contact\Persons;

use Illuminate\Http\JsonResponse;
use Webkul\Activity\Repositories\ActivityRepository;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Contact\Repositories\PersonRepository;
use Webkul\MailingList\Repositories\MailingListRepository;
use Webkul\MailingList\Repositories\SubscriberRepository;

class MailingListController extends Controller
{
    public function __construct(
        protected PersonRepository $personRepository,
        protected MailingListRepository $mailingListRepository,
        protected SubscriberRepository $subscriberRepository,
        protected ActivityRepository $activityRepository,
    ) {}

    public function index(int $personId): JsonResponse
    {
        $person = $this->personRepository->findOrFail($personId);

        $mailingLists = $this->mailingListRepository->all();

        $subscribers = $person->subscribers->keyBy('mailing_list_id');

        $result = $mailingLists->map(function ($list) use ($subscribers) {
            $subscriber = $subscribers->get($list->id);

            return [
                'id' => $list->id,
                'name' => $list->name,
                'description' => $list->description,
                'subscribed' => $subscriber && $subscriber->is_subscribed,
                'subscriber_id' => $subscriber?->id,
            ];
        });

        return response()->json($result);
    }

    public function store(int $personId, int $mailingListId): JsonResponse
    {
        $person = $this->personRepository->findOrFail($personId);

        $mailingList = $this->mailingListRepository->findOrFail($mailingListId);

        $subscriber = $this->subscriberRepository->findWhere([
            'mailing_list_id' => $mailingListId,
            'person_id' => $personId,
        ])->first();

        if ($subscriber) {
            $this->subscriberRepository->update([
                'is_subscribed' => true,
            ], $subscriber->id);
        } else {
            $subscriber = $this->subscriberRepository->create([
                'mailing_list_id' => $mailingListId,
                'person_id' => $personId,
                'is_subscribed' => true,
            ]);
        }

        $activity = $this->activityRepository->create([
            'type' => 'system',
            'title' => trans('admin::app.contacts.persons.view.mailing-lists.subscribed', ['list' => $mailingList->name]),
            'is_done' => 1,
            'user_id' => auth()->id(),
        ]);

        $person->activities()->attach($activity->id);

        return response()->json([
            'message' => trans('admin::app.contacts.persons.view.mailing-lists.subscribed-success', ['list' => $mailingList->name]),
            'subscriber_id' => $subscriber->id,
        ]);
    }

    public function destroy(int $personId, int $mailingListId): JsonResponse
    {
        $person = $this->personRepository->findOrFail($personId);

        $mailingList = $this->mailingListRepository->findOrFail($mailingListId);

        $subscriber = $this->subscriberRepository->findWhere([
            'mailing_list_id' => $mailingListId,
            'person_id' => $personId,
        ])->first();

        if ($subscriber) {
            $this->subscriberRepository->update([
                'is_subscribed' => false,
            ], $subscriber->id);

            $activity = $this->activityRepository->create([
                'type' => 'system',
                'title' => trans('admin::app.contacts.persons.view.mailing-lists.unsubscribed', ['list' => $mailingList->name]),
                'is_done' => 1,
                'user_id' => auth()->id(),
            ]);

            $person->activities()->attach($activity->id);
        }

        return response()->json([
            'message' => trans('admin::app.contacts.persons.view.mailing-lists.unsubscribed-success', ['list' => $mailingList->name]),
        ]);
    }
}
