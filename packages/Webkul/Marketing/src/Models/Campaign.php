<?php

namespace Webkul\Marketing\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\EmailTemplate\Models\EmailTemplateProxy;
use Webkul\MailingList\Models\MailingListProxy;
use Webkul\Marketing\Contracts\Campaign as CampaignContract;

class Campaign extends Model implements CampaignContract
{
    protected $table = 'marketing_campaigns';

    protected $fillable = [
        'name',
        'subject',
        'status',
        'sent_at',
        'marketing_template_id',
        'marketing_event_id',
        'mailing_list_id',
        'spooling',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function email_template()
    {
        return $this->belongsTo(EmailTemplateProxy::modelClass(), 'marketing_template_id');
    }

    public function event()
    {
        return $this->belongsTo(EventProxy::modelClass(), 'marketing_event_id');
    }

    public function mailingList()
    {
        return $this->belongsTo(MailingListProxy::modelClass(), 'mailing_list_id');
    }
}
