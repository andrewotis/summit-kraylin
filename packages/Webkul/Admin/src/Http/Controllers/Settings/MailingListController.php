<?php

namespace Webkul\Admin\Http\Controllers\Settings;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Event;
use Illuminate\View\View;
use Webkul\Admin\DataGrids\Settings\MailingListDataGrid;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Admin\Http\Requests\MassDestroyRequest;
use Webkul\MailingList\Repositories\MailingListRepository;

class MailingListController extends Controller
{
    public function __construct(protected MailingListRepository $mailingListRepository) {}

    public function index(): View|JsonResponse
    {
        if (request()->isXmlHttpRequest()) {
            return datagrid(MailingListDataGrid::class)->process();
        }

        return view('admin::settings.mailing-lists.index');
    }

    public function store(): JsonResponse
    {
        $this->validate(request(), [
            'name' => 'required|string|max:255|unique:mailing_lists,name',
            'description' => 'nullable|string',
        ]);

        Event::dispatch('settings.mailing_lists.create.before');

        $mailingList = $this->mailingListRepository->create(request()->only(['name', 'description']));

        Event::dispatch('settings.mailing_lists.create.after', $mailingList);

        return response()->json([
            'data' => $mailingList,
            'message' => trans('admin::app.settings.mailing-lists.index.create-success'),
        ]);
    }

    public function edit(int $id): JsonResponse
    {
        $mailingList = $this->mailingListRepository->findOrFail($id);

        return response()->json(['data' => $mailingList]);
    }

    public function update(int $id): JsonResponse
    {
        $this->validate(request(), [
            'name' => 'required|string|max:255|unique:mailing_lists,name,'.$id,
            'description' => 'nullable|string',
        ]);

        Event::dispatch('settings.mailing_lists.update.before', $id);

        $mailingList = $this->mailingListRepository->update(request()->only(['name', 'description']), $id);

        Event::dispatch('settings.mailing_lists.update.after', $mailingList);

        return response()->json([
            'data' => $mailingList,
            'message' => trans('admin::app.settings.mailing-lists.index.update-success'),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->mailingListRepository->findOrFail($id);

        Event::dispatch('settings.mailing_lists.delete.before', $id);

        $this->mailingListRepository->delete($id);

        Event::dispatch('settings.mailing_lists.delete.after', $id);

        return response()->json([
            'message' => trans('admin::app.settings.mailing-lists.index.delete-success'),
        ]);
    }

    public function massDestroy(MassDestroyRequest $massDestroyRequest): JsonResponse
    {
        $indices = $massDestroyRequest->input('indices');

        foreach ($indices as $id) {
            Event::dispatch('settings.mailing_lists.delete.before', $id);

            $this->mailingListRepository->delete($id);

            Event::dispatch('settings.mailing_lists.delete.after', $id);
        }

        return response()->json([
            'message' => trans('admin::app.settings.mailing-lists.index.delete-success'),
        ]);
    }
}
