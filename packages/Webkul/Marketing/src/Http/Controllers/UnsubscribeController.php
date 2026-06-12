<?php

namespace Webkul\Marketing\Http\Controllers;

use Illuminate\Routing\Controller;
use Webkul\MailingList\Repositories\SubscriberRepository;

class UnsubscribeController extends Controller
{
    public function __construct(protected SubscriberRepository $subscriberRepository) {}

    public function __invoke(int $id)
    {
        $signature = request()->query('signature');

        if (! $signature || ! hash_equals($this->signature($id), $signature)) {
            abort(404);
        }

        $subscriber = $this->subscriberRepository->find($id);

        if (! $subscriber) {
            abort(404);
        }

        $this->subscriberRepository->update(['is_subscribed' => false], $id);

        return view('marketing::unsubscribe.confirmed');
    }

    public static function signature(int $id): string
    {
        return hash_hmac('sha256', (string) $id, config('app.key'));
    }
}
