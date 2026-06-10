@extends('admin::layouts.master')

@section('page_title')
    @lang('admin::app.user.two-factor.setup-title')
@stop

@section('content-wrapper')
    <div class="flex min-h-full flex-col">
        <div class="flex items-center justify-between border-b p-4 dark:border-gray-800">
            <h1 class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('admin::app.user.two-factor.setup-title')
            </h1>
        </div>

        <div class="flex flex-1 items-center justify-center p-8">
            <div class="box-shadow w-full max-w-lg rounded-md bg-white p-8 dark:bg-gray-900">
                <div class="mb-6 text-center">
                    <div class="mb-4 inline-flex h-16 w-16 items-center justify-center rounded-full bg-blue-100 text-3xl text-blue-600 dark:bg-blue-900 dark:text-blue-300">
                        &#x1F510;
                    </div>

                    <h2 class="text-lg font-semibold text-gray-800 dark:text-white">
                        @lang('admin::app.user.two-factor.setup-heading')
                    </h2>

                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        @lang('admin::app.user.two-factor.setup-instruction')
                    </p>
                </div>

                <div class="mb-6 flex justify-center">
                    <img
                        src="https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl={{ urlencode($qrCodeUrl) }}"
                        alt="QR Code"
                        class="rounded-lg border p-2 dark:border-gray-700"
                        width="200"
                        height="200"
                    />
                </div>

                <div class="mb-6 rounded-md bg-gray-50 p-4 dark:bg-gray-800">
                    <p class="mb-2 text-xs font-medium text-gray-500 dark:text-gray-400">
                        @lang('admin::app.user.two-factor.manual-entry')
                    </p>
                    <p class="select-all rounded bg-white px-3 py-2 font-mono text-sm text-gray-800 dark:bg-gray-900 dark:text-gray-200" id="secret-key">
                        {{ $secret }}
                    </p>
                </div>

                <x-admin::form :action="route('admin.two-factor.enable')">
                    <input type="hidden" name="secret" value="{{ $secret }}" />

                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('admin::app.user.two-factor.verify-code')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            class="w-full"
                            id="code"
                            name="code"
                            rules="required|size:6"
                            :label="trans('admin::app.user.two-factor.verify-code')"
                            :placeholder="trans('admin::app.user.two-factor.verify-code')"
                            inputmode="numeric"
                            pattern="[0-9]*"
                        />

                        <x-admin::form.control-group.error control-name="code" />
                    </x-admin::form.control-group>

                    <div class="flex items-center justify-end gap-4">
                        <a
                            href="{{ route('admin.account.edit') }}"
                            class="cursor-pointer text-sm font-semibold text-brandColor"
                        >
                            @lang('admin::app.user.two-factor.cancel')
                        </a>

                        <button
                            type="submit"
                            class="primary-button"
                        >
                            @lang('admin::app.user.two-factor.enable')
                        </button>
                    </div>
                </x-admin::form>
            </div>
        </div>
    </div>
@stop


