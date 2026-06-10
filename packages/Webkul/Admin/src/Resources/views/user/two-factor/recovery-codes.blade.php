@extends('admin::layouts.master')

@section('page_title')
    @lang('admin::app.user.two-factor.recovery-codes-title')
@stop

@section('content-wrapper')
    <div class="flex min-h-full flex-col">
        <div class="flex items-center justify-between border-b p-4 dark:border-gray-800">
            <h1 class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('admin::app.user.two-factor.recovery-codes-title')
            </h1>
        </div>

        <div class="flex flex-1 items-center justify-center p-8">
            <div class="box-shadow w-full max-w-lg rounded-md bg-white p-8 dark:bg-gray-900">
                <div class="mb-6 text-center">
                    <div class="mb-4 inline-flex h-16 w-16 items-center justify-center rounded-full bg-yellow-100 text-3xl text-yellow-600 dark:bg-yellow-900 dark:text-yellow-300">
                        &#x26A0;
                    </div>

                    <h2 class="text-lg font-semibold text-gray-800 dark:text-white">
                        @lang('admin::app.user.two-factor.recovery-codes-heading')
                    </h2>

                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        @lang('admin::app.user.two-factor.recovery-codes-instruction')
                    </p>
                </div>

                <div class="mb-6 rounded-md border bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800">
                    <div class="grid grid-cols-2 gap-2">
                        @foreach ($codes as $code)
                            <div class="select-all rounded bg-white px-3 py-2 font-mono text-sm text-gray-800 dark:bg-gray-900 dark:text-gray-200">
                                {{ $code }}
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mb-6 rounded-md border border-yellow-300 bg-yellow-50 p-3 text-xs text-yellow-800 dark:border-yellow-700 dark:bg-yellow-900 dark:text-yellow-200">
                    @lang('admin::app.user.two-factor.recovery-codes-warning')
                </div>

                <div class="flex items-center justify-between">
                    <a
                        href="{{ route('admin.account.edit') }}"
                        class="cursor-pointer text-sm font-semibold text-brandColor"
                    >
                        @lang('admin::app.user.two-factor.done')
                    </a>

                    <button
                        type="button"
                        class="primary-button"
                        onclick="document.getElementById('regenerate-form').submit();"
                    >
                        @lang('admin::app.user.two-factor.regenerate')
                    </button>
                </div>

                <form
                    id="regenerate-form"
                    action="{{ route('admin.two-factor.regenerate-recovery-codes') }}"
                    method="POST"
                    class="hidden"
                >
                    @csrf
                    <input type="hidden" name="code" value="" />
                </form>
            </div>
        </div>
    </div>
@stop
