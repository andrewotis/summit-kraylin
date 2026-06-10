<?php

namespace Webkul\MailingList\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\Contact\Models\PersonProxy;
use Webkul\MailingList\Contracts\Subscriber as SubscriberContract;

class Subscriber extends Model implements SubscriberContract
{
    protected $fillable = [
        'mailing_list_id',
        'person_id',
        'is_subscribed',
    ];

    protected $casts = [
        'is_subscribed' => 'boolean',
    ];

    public function mailingList(): BelongsTo
    {
        return $this->belongsTo(MailingListProxy::modelClass());
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(PersonProxy::modelClass());
    }
}
