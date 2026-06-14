<?php

namespace Webkul\Admin\DataGrids\Settings\Marketing;

use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class CampaignDatagrid extends DataGrid
{
    /**
     * Prepare query builder.
     */
    public function prepareQueryBuilder()
    {
        $queryBuilder = DB::table('marketing_campaigns')
            ->leftJoin('marketing_events', 'marketing_campaigns.marketing_event_id', '=', 'marketing_events.id')
            ->leftJoin('email_templates', 'marketing_campaigns.marketing_template_id', '=', 'email_templates.id')
            ->leftJoin('mailing_lists', 'marketing_campaigns.mailing_list_id', '=', 'mailing_lists.id')
            ->addSelect(
                'marketing_campaigns.id',
                'marketing_campaigns.name',
                'marketing_campaigns.subject',
                'marketing_campaigns.status',
                'marketing_campaigns.mailing_list_id',
                'marketing_campaigns.marketing_template_id',
                'marketing_campaigns.marketing_event_id',
                'marketing_events.name as event_name',
                'marketing_events.date as event_date',
                'email_templates.name as email_template_name',
                'mailing_lists.name as mailing_list_name',
                DB::raw('CASE
                    WHEN marketing_campaigns.mailing_list_id IS NOT NULL
                    THEN (SELECT COUNT(*) FROM subscribers WHERE subscribers.mailing_list_id = marketing_campaigns.mailing_list_id AND subscribers.is_subscribed = 1)
                    ELSE (SELECT COUNT(*) FROM persons)
                END as recipients_count'),
            );

        $this->addFilter('id', 'marketing_campaigns.id');
        $this->addFilter('name', 'marketing_campaigns.name');
        $this->addFilter('subject', 'marketing_campaigns.subject');
        $this->addFilter('status', 'marketing_campaigns.status');
        $this->addFilter('event_name', 'marketing_events.name');
        $this->addFilter('email_template_name', 'email_templates.name');
        $this->addFilter('mailing_list_name', 'mailing_lists.name');

        return $queryBuilder;
    }

    /**
     * Add columns.
     *
     * @return void
     */
    public function prepareColumns()
    {
        $this->addColumn([
            'index' => 'id',
            'label' => trans('admin::app.settings.marketing.campaigns.index.datagrid.id'),
            'type' => 'string',
            'sortable' => true,
            'searchable' => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index' => 'name',
            'label' => trans('admin::app.settings.marketing.campaigns.index.datagrid.name'),
            'type' => 'string',
            'sortable' => true,
            'searchable' => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index' => 'subject',
            'label' => trans('admin::app.settings.marketing.campaigns.index.datagrid.subject'),
            'type' => 'string',
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'event_name',
            'label' => trans('admin::app.settings.marketing.campaigns.index.datagrid.event'),
            'type' => 'string',
            'sortable' => true,
            'searchable' => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index' => 'email_template_name',
            'label' => trans('admin::app.settings.marketing.campaigns.index.datagrid.email-template'),
            'type' => 'string',
            'sortable' => true,
            'searchable' => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index' => 'mailing_list_name',
            'label' => trans('admin::app.settings.marketing.campaigns.index.datagrid.mailing-list'),
            'type' => 'string',
            'sortable' => true,
            'searchable' => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index' => 'recipients_count',
            'label' => trans('admin::app.settings.marketing.campaigns.index.datagrid.recipients'),
            'type' => 'string',
            'sortable' => false,
        ]);

        $this->addColumn([
            'index' => 'status',
            'label' => trans('admin::app.settings.marketing.campaigns.index.datagrid.status'),
            'type' => 'string',
            'sortable' => true,
        ]);
    }

    /**
     * Prepare actions.
     */
    public function prepareActions()
    {
        $this->addAction([
            'index' => 'subscribers',
            'icon' => 'icon-user',
            'title' => trans('admin::app.settings.marketing.campaigns.index.datagrid.subscribers'),
            'method' => 'GET',
            'url' => fn ($row) => $row->mailing_list_id
                ? route('admin.settings.mailing_lists.subscribers.index', $row->mailing_list_id)
                : '#',
        ]);

        if (bouncer()->hasPermission('settings.automation.campaigns.edit')) {
            $this->addAction([
                'index' => 'edit',
                'icon' => 'icon-edit',
                'title' => trans('admin::app.settings.marketing.campaigns.index.datagrid.edit'),
                'method' => 'GET',
                'url' => fn ($row) => route('admin.settings.marketing.campaigns.edit', $row->id),
            ]);
        }

        if (bouncer()->hasPermission('settings.automation.campaigns.delete')) {
            $this->addAction([
                'index' => 'delete',
                'icon' => 'icon-delete',
                'title' => trans('admin::app.settings.marketing.campaigns.index.datagrid.delete'),
                'method' => 'DELETE',
                'url' => fn ($row) => route('admin.settings.marketing.campaigns.delete', $row->id),
            ]);
        }
    }

    /**
     * Prepare mass actions.
     */
    public function prepareMassActions(): void
    {
        if (bouncer()->hasPermission('settings.automation.campaigns.mass_delete')) {
            $this->addMassAction([
                'icon' => 'icon-delete',
                'title' => trans('admin::app.settings.marketing.campaigns.index.datagrid.delete'),
                'method' => 'POST',
                'url' => route('admin.settings.marketing.campaigns.mass_delete'),
            ]);
        }
    }
}
