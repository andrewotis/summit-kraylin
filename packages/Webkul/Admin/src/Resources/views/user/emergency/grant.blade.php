<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.users.emergency.grant-title')
    </x-slot>

    <div class="flex min-h-full flex-col">
        <div class="flex items-center justify-between border-b p-4 dark:border-gray-800">
            <h1 class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('admin::app.users.emergency.grant-title')
            </h1>
        </div>

        <div class="flex flex-1 items-center justify-center p-8">
            <div class="box-shadow w-full max-w-lg rounded-md bg-white p-8 dark:bg-gray-900">
                @if (session('emergency_token'))
                    <div class="mb-6 rounded-md bg-yellow-50 p-4 dark:bg-yellow-900">
                        <p class="mb-2 text-sm font-semibold text-yellow-800 dark:text-yellow-200">
                            @lang('admin::app.users.emergency.token-generated')
                        </p>
                        <p class="select-all rounded bg-white px-3 py-2 font-mono text-sm text-gray-800 dark:bg-gray-900 dark:text-gray-200">
                            {{ session('emergency_token') }}
                        </p>
                        <p class="mt-2 text-xs text-yellow-700 dark:text-yellow-300">
                            @lang('admin::app.users.emergency.token-warning')
                        </p>
                    </div>
                @endif

                <x-admin::form :action="route('admin.emergency.grant')">
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('admin::app.users.emergency.target-email')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="email"
                            class="w-full"
                            id="email"
                            name="email"
                            rules="required|email"
                            :label="trans('admin::app.users.emergency.target-email')"
                        />

                        <x-admin::form.control-group.error control-name="email" />
                    </x-admin::form.control-group>

                    <div class="flex items-center justify-end gap-4">
                        <a
                            href="{{ route('admin.dashboard.index') }}"
                            class="cursor-pointer text-sm font-semibold text-brandColor"
                        >
                            @lang('admin::app.users.emergency.cancel')
                        </a>

                        <button
                            type="submit"
                            class="primary-button"
                        >
                            @lang('admin::app.users.emergency.grant-btn')
                        </button>
                    </div>
                </x-admin::form>
            </div>
        </div>
    </div>
</x-admin::layouts>
