<x-admin::layouts.anonymous>
    <x-slot:title>
        @lang('admin::app.user.two-factor.challenge-title')
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
                <x-admin::form :action="route('admin.two-factor.verify')">
                    <p class="p-4 text-xl font-bold text-gray-800 dark:text-white">
                        @lang('admin::app.user.two-factor.challenge-title')
                    </p>

                    <div class="border-y p-4 dark:border-gray-800">
                        <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">
                            @lang('admin::app.user.two-factor.challenge-instruction')
                        </p>

                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('admin::app.user.two-factor.code')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                class="w-[254px] max-w-full"
                                id="code"
                                name="code"
                                rules="required"
                                :label="trans('admin::app.user.two-factor.code')"
                                :placeholder="trans('admin::app.user.two-factor.code')"
                                autocomplete="one-time-code"
                                inputmode="numeric"
                                pattern="[0-9]*"
                            />

                            <x-admin::form.control-group.error control-name="code" />
                        </x-admin::form.control-group>
                    </div>

                    <div class="flex items-center justify-between p-4">
                        <a
                            class="cursor-pointer text-xs font-semibold leading-6 text-brandColor"
                            href="{{ route('admin.two-factor.logout') }}"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                        >
                            @lang('admin::app.user.two-factor.cancel')
                        </a>

                        <button
                            class="primary-button"
                            aria-label="@lang('admin::app.user.two-factor.verify')"
                        >
                            @lang('admin::app.user.two-factor.verify')
                        </button>
                    </div>
                </x-admin::form>

                <form
                    id="logout-form"
                    action="{{ route('admin.two-factor.logout') }}"
                    method="POST"
                    class="hidden"
                >
                    @csrf
                </form>
            </div>
        </div>
    </div>
</x-admin::layouts.anonymous>
