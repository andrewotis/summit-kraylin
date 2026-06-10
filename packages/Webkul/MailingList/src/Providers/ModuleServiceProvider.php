<?php

namespace Webkul\MailingList\Providers;

use Webkul\Core\Providers\BaseModuleServiceProvider;
use Webkul\MailingList\Models\MailingList;
use Webkul\MailingList\Models\Subscriber;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    protected $models = [
        MailingList::class,
        Subscriber::class,
    ];
}
