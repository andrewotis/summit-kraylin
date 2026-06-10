<?php

namespace Webkul\Admin\DataGrids\Settings;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class SubscriberDataGrid extends DataGrid
{
    public function prepareQueryBuilder(): Builder
    {
        $mailingListId = request()->route('id');

        $queryBuilder = DB::table('subscribers')
            ->leftJoin('persons', 'subscribers.person_id', '=', 'persons.id')
            ->addSelect(
                'subscribers.id',
                'subscribers.mailing_list_id',
                'subscribers.person_id',
                'persons.name as person_name',
                'persons.emails as person_emails',
                'subscribers.is_subscribed',
                'subscribers.created_at'
            )
            ->where('subscribers.mailing_list_id', $mailingListId);

        $this->addFilter('id', 'subscribers.id');

        return $queryBuilder;
    }

    public function prepareColumns(): void
    {
        $this->addColumn([
            'index' => 'id',
            'label' => trans('admin::app.settings.mailing-lists.subscribers.datagrid.id'),
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'person_name',
            'label' => trans('admin::app.settings.mailing-lists.subscribers.datagrid.name'),
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'person_emails',
            'label' => trans('admin::app.settings.mailing-lists.subscribers.datagrid.email'),
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
            'closure' => function ($row) {
                if (! $row->person_emails) {
                    return '';
                }

                $emails = json_decode($row->person_emails, true);

                if (! is_array($emails)) {
                    return $row->person_emails;
                }

                $addresses = array_map(function ($email) {
                    return $email['value'] ?? $email;
                }, $emails);

                return implode(', ', $addresses);
            },
        ]);

        $this->addColumn([
            'index' => 'is_subscribed',
            'label' => trans('admin::app.settings.mailing-lists.subscribers.datagrid.subscribed'),
            'type' => 'boolean',
            'searchable' => false,
            'filterable' => true,
            'sortable' => true,
            'closure' => function ($row) {
                return $row->is_subscribed
                    ? trans('admin::app.settings.mailing-lists.subscribers.datagrid.yes')
                    : trans('admin::app.settings.mailing-lists.subscribers.datagrid.no');
            },
        ]);

        $this->addColumn([
            'index' => 'created_at',
            'label' => trans('admin::app.settings.mailing-lists.subscribers.datagrid.created-at'),
            'type' => 'string',
            'searchable' => false,
            'filterable' => true,
            'sortable' => true,
        ]);
    }

    public function prepareActions(): void
    {
        if (bouncer()->hasPermission('settings.other_settings.mailing_lists.subscribers.edit')) {
            $this->addAction([
                'index' => 'edit',
                'icon' => 'icon-edit',
                'title' => trans('admin::app.settings.mailing-lists.subscribers.datagrid.edit'),
                'method' => 'GET',
                'url' => fn ($row) => route('admin.settings.mailing_lists.subscribers.edit', [$row->mailing_list_id, $row->id]),
            ]);
        }

        if (bouncer()->hasPermission('settings.other_settings.mailing_lists.subscribers.delete')) {
            $this->addAction([
                'index' => 'delete',
                'icon' => 'icon-delete',
                'title' => trans('admin::app.settings.mailing-lists.subscribers.datagrid.delete'),
                'method' => 'DELETE',
                'url' => fn ($row) => route('admin.settings.mailing_lists.subscribers.delete', [$row->mailing_list_id, $row->id]),
            ]);
        }
    }

    public function prepareMassActions(): void
    {
        $this->addMassAction([
            'icon' => 'icon-delete',
            'title' => trans('admin::app.settings.mailing-lists.subscribers.datagrid.delete'),
            'method' => 'POST',
            'url' => route('admin.settings.mailing_lists.subscribers.mass_delete', request()->route('id')),
        ]);
    }
}
