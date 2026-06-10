<?php

namespace Webkul\Admin\DataGrids\Settings;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class MailingListDataGrid extends DataGrid
{
    public function prepareQueryBuilder(): Builder
    {
        $queryBuilder = DB::table('mailing_lists')
            ->addSelect(
                'mailing_lists.id',
                'mailing_lists.name',
                'mailing_lists.description'
            );

        $this->addFilter('id', 'mailing_lists.id');

        return $queryBuilder;
    }

    public function prepareColumns(): void
    {
        $this->addColumn([
            'index' => 'id',
            'label' => trans('admin::app.settings.mailing-lists.index.datagrid.id'),
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'name',
            'label' => trans('admin::app.settings.mailing-lists.index.datagrid.name'),
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'description',
            'label' => trans('admin::app.settings.mailing-lists.index.datagrid.description'),
            'type' => 'string',
            'sortable' => false,
        ]);
    }

    public function prepareActions(): void
    {
        if (bouncer()->hasPermission('settings.other_settings.mailing_lists.edit')) {
            $this->addAction([
                'index' => 'subscribers',
                'icon' => 'icon-user',
                'title' => trans('admin::app.settings.mailing-lists.index.datagrid.view-subscribers'),
                'method' => 'GET',
                'url' => fn ($row) => route('admin.settings.mailing_lists.subscribers.index', $row->id),
            ]);
        }

        if (bouncer()->hasPermission('settings.other_settings.mailing_lists.edit')) {
            $this->addAction([
                'index' => 'edit',
                'icon' => 'icon-edit',
                'title' => trans('admin::app.settings.mailing-lists.index.datagrid.edit'),
                'method' => 'GET',
                'url' => fn ($row) => route('admin.settings.mailing_lists.edit', $row->id),
            ]);
        }

        if (bouncer()->hasPermission('settings.other_settings.mailing_lists.delete')) {
            $this->addAction([
                'index' => 'delete',
                'icon' => 'icon-delete',
                'title' => trans('admin::app.settings.mailing-lists.index.datagrid.delete'),
                'method' => 'DELETE',
                'url' => fn ($row) => route('admin.settings.mailing_lists.delete', $row->id),
            ]);
        }
    }

    public function prepareMassActions(): void
    {
        $this->addMassAction([
            'icon' => 'icon-delete',
            'title' => trans('admin::app.settings.mailing-lists.index.datagrid.delete'),
            'method' => 'POST',
            'url' => route('admin.settings.mailing_lists.mass_delete'),
        ]);
    }
}
