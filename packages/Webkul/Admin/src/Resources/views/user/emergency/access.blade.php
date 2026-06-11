<x-admin::layouts.anonymous>
    <x-slot:title>
        @lang('admin::app.users.emergency.access-title')
    </x-slot>

    <div class="flex h-[100vh] flex-col items-center justify-center gap-10">
        <div class="flex flex-col items-center gap-5">
            @if ($logo = core()->getConfigData('general.general.admin_logo.logo_image'))
                <img
                    class="h-14 w-[280px]"
                    src="{{ Storage::url($logo) }}"
                    alt="{{ config('app.name') }}"
                />
            @else
                <img
                    class="w-max"
                    src="{{ vite()->asset('images/logo.svg') }}"
                    alt="{{ config('app.name') }}"
                />
            @endif

            <div class="box-shadow flex min-w-[300px] flex-col rounded-md bg-white dark:bg-gray-900">
                <x-admin::form :action="route('admin.emergency.access')">
                    <p class="p-4 text-xl font-bold text-gray-800 dark:text-white">
                        @lang('admin::app.users.emergency.access-title')
                    </p>

                    <div class="border-y p-4 dark:border-gray-800">
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('admin::app.users.login.email')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="email"
                                class="w-[254px] max-w-full"
                                id="email"
                                name="email"
                                rules="required|email"
                                :label="trans('admin::app.users.login.email')"
                            />

                            <x-admin::form.control-group.error control-name="email" />
                        </x-admin::form.control-group>

                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('admin::app.users.emergency.token')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                class="w-[254px] max-w-full"
                                id="token"
                                name="token"
                                rules="required"
                                :label="trans('admin::app.users.emergency.token')"
                            />

                            <x-admin::form.control-group.error control-name="token" />
                        </x-admin::form.control-group>
                    </div>

                    <div class="flex items-center justify-between p-4">
                        <a
                            class="cursor-pointer text-xs font-semibold leading-6 text-brandColor"
                            href="{{ route('admin.session.create') }}"
                        >
                            @lang('admin::app.users.emergency.back-to-login')
                        </a>

                        <button
                            class="primary-button"
                            aria-label="{{ trans('admin::app.users.emergency.access-btn') }}"
                        >
                            @lang('admin::app.users.emergency.access-btn')
                        </button>
                    </div>
                </x-admin::form>
            </div>
        </div>
    </div>
</x-admin::layouts.anonymous>
