<?php

namespace Webkul\MailingList\Repositories;

use Webkul\Core\Eloquent\Repository;

class SubscriberRepository extends Repository
{
    public function model(): string
    {
        return 'Webkul\MailingList\Contracts\Subscriber';
    }
}
