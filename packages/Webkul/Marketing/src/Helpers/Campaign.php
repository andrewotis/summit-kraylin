<?php

namespace Webkul\Marketing\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Webkul\Contact\Repositories\PersonRepository;
use Webkul\Marketing\Mail\CampaignMail;
use Webkul\Marketing\Repositories\CampaignRepository;
use Webkul\Marketing\Repositories\EventRepository;

class Campaign
{
    public function __construct(
        protected EventRepository $eventRepository,
        protected CampaignRepository $campaignRepository,
        protected PersonRepository $personRepository,
    ) {}

    public function process(): void
    {
        $campaigns = $this->campaignRepository->getModel()
            ->leftJoin('marketing_events', 'marketing_campaigns.marketing_event_id', 'marketing_events.id')
            ->leftJoin('email_templates', 'marketing_campaigns.marketing_template_id', 'email_templates.id')
            ->select('marketing_campaigns.*')
            ->where('marketing_campaigns.status', 1)
            ->whereNull('marketing_campaigns.sent_at')
            ->where(function ($query) {
                $query->where('marketing_events.date', Carbon::now()->format('Y-m-d'))
                    ->orWhereNull('marketing_events.date');
            })
            ->get();

        foreach ($campaigns as $campaign) {
            $campaign->load('mailingList');

            $persons = $this->getPersons($campaign);

            foreach ($persons as $person) {
                if (! $person->emails) {
                    continue;
                }

                foreach (data_get($person->emails, '*.value') as $email) {
                    Mail::queue(new CampaignMail($email, $campaign, $person));
                }
            }

            $this->campaignRepository->update([
                'sent_at' => now(),
            ], $campaign->id);
        }
    }

    private function getPersons($campaign): iterable
    {
        if ($campaign->mailing_list_id && $campaign->mailingList) {
            return $campaign->mailingList->subscribers()
                ->where('is_subscribed', true)
                ->with('person')
                ->get()
                ->pluck('person')
                ->filter();
        }

        return $this->personRepository->all();
    }
}
