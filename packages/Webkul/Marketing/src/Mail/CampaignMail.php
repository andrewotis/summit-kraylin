<?php

namespace Webkul\Marketing\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Webkul\Attribute\Repositories\AttributeRepository;
use Webkul\Contact\Contracts\Person;
use Webkul\Marketing\Contracts\Campaign;
use Webkul\Marketing\Http\Controllers\UnsubscribeController;

class CampaignMail extends Mailable
{
    public function __construct(
        public string $email,
        public Campaign $campaign,
        public ?Person $person = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            to: [
                new Address($this->email),
            ],
            subject: $this->replacePlaceholders($this->campaign->subject),
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: $this->replacePlaceholders($this->campaign->email_template->content),
        );
    }

    private function replacePlaceholders(string $content): string
    {
        if ($this->person) {
            $attributeRepository = app(AttributeRepository::class);

            $attributes = $attributeRepository->findByField('entity_type', 'persons');

            foreach ($attributes as $attribute) {
                $value = '';

                if (isset($this->person->{$attribute->code})) {
                    $personValue = $this->person->{$attribute->code};

                    if (in_array($attribute->type, ['email', 'phone']) && is_array($personValue)) {
                        $labels = [];

                        foreach ($personValue as $item) {
                            $labels[] = ($item['value'] ?? '').' ('.($item['label'] ?? '').')';
                        }

                        $value = implode(', ', $labels);
                    } elseif ($attribute->type === 'address' && is_array($personValue)) {
                        $value = ($personValue['address'] ?? '').'<br>'
                               .($personValue['postcode'] ?? '').' '.($personValue['city'] ?? '').'<br>'
                               .($personValue['state'] ?? '').'<br>'
                               .($personValue['country'] ?? '');
                    } else {
                        $value = $personValue;
                    }
                }

                $content = strtr($content, [
                    '{%persons.'.$attribute->code.'%}' => $value,
                    '{% persons.'.$attribute->code.' %}' => $value,
                ]);
            }
        }

        $unsubscribeUrl = $this->unsubscribeUrl();

        $content = strtr($content, [
            '{%unsubscribe_url%}' => $unsubscribeUrl,
            '{% unsubscribe_url %}' => $unsubscribeUrl,
        ]);

        return $content;
    }

    private function unsubscribeUrl(): string
    {
        if (! $this->person || ! $this->campaign->mailing_list_id) {
            return '#';
        }

        $subscriber = $this->person->subscribers()
            ->where('mailing_list_id', $this->campaign->mailing_list_id)
            ->first();

        if (! $subscriber) {
            return '#';
        }

        return route('marketing.unsubscribe', [
            'id' => $subscriber->id,
            'signature' => UnsubscribeController::signature($subscriber->id),
        ]);
    }
}
