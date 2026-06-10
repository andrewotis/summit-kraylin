<?php

namespace Webkul\MailingList\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Webkul\MailingList\Contracts\MailingList as MailingListContract;

class MailingList extends Model implements MailingListContract
{
    protected $fillable = [
        'name',
        'description',
    ];

    public function subscribers(): HasMany
    {
        return $this->hasMany(SubscriberProxy::modelClass());
    }
}
