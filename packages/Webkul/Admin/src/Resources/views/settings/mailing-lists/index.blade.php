<x-admin::layouts>
    <!-- Page Title -->
    <x-slot:title>
        @lang('admin::app.settings.mailing-lists.index.title')
    </x-slot>

    <div class="flex flex-col gap-4">
        <!-- Header section -->
        <div class="scroll-reactive-sticky sticky top-[60px] z-[1000] flex items-center justify-between rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm shadow-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
            <div class="flex flex-col gap-2">
                {!! view_render_event('admin.settings.mailing_lists.index.breadcrumbs.before') !!}

                <x-admin::breadcrumbs name="settings.mailing_lists" />

                {!! view_render_event('admin.settings.mailing_lists.index.breadcrumbs.after') !!}

                <div class="text-xl font-bold dark:text-gray-300">
                    @lang('admin::app.settings.mailing-lists.index.title')
                </div>
            </div>

            <div class="flex items-center gap-x-2.5">
                <div class="flex items-center gap-x-2.5">
                    {!! view_render_event('admin.settings.mailing_lists.index.create_button.before') !!}

                    @if (bouncer()->hasPermission('settings.other_settings.mailing_lists.create'))
                        <button
                            type="button"
                            class="primary-button"
                            @click="$refs.mailingListSettings.openModal()"
                        >
                            @lang('admin::app.settings.mailing-lists.index.create-btn')
                        </button>
                    @endif

                    {!! view_render_event('admin.settings.mailing_lists.index.create_button.after') !!}
                </div>
            </div>
        </div>

        <v-mailing-list-settings ref="mailingListSettings">
            <x-admin::shimmer.datagrid />
        </v-mailing-list-settings>
    </div>

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="mailing-list-settings-template"
        >
            {!! view_render_event('admin.settings.mailing_lists.index.datagrid.before') !!}

            <x-admin::datagrid
                :src="route('admin.settings.mailing_lists.index')"
                ref="datagrid"
            >
                <template #body="{
                    isLoading,
                    available,
                    applied,
                    selectAll,
                    sort,
                    performAction
                }">
                    <template v-if="isLoading">
                        <x-admin::shimmer.datagrid.table.body />
                    </template>

                    <template v-else>
                        <div
                            v-for="record in available.records"
                            class="row grid items-center gap-2.5 border-b px-4 py-4 text-gray-600 transition-all hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:bg-gray-950 max-lg:hidden"
                            :style="`grid-template-columns: repeat(${gridsCount}, minmax(0, 1fr))`"
                        >
                            <p>@{{ record.id }}</p>

                            <p>@{{ record.name }}</p>

                            <p>@{{ record.description }}</p>

                            <div class="flex justify-end">
                                <a :href="record.actions.find(action => action.index === 'subscribers')?.url">
                                    <span
                                        :class="record.actions.find(action => action.index === 'subscribers')?.icon"
                                        class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 max-sm:place-self-center"
                                    >
                                    </span>
                                </a>

                                <a @click="selectedList=true; editModal(record.actions.find(action => action.index === 'edit')?.url)">
                                    <span
                                        :class="record.actions.find(action => action.index === 'edit')?.icon"
                                        class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 max-sm:place-self-center"
                                    >
                                    </span>
                                </a>

                                <a @click="performAction(record.actions.find(action => action.index === 'delete'))">
                                    <span
                                        :class="record.actions.find(action => action.index === 'delete')?.icon"
                                        class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 max-sm:place-self-center"
                                    >
                                    </span>
                                </a>
                            </div>
                        </div>

                        <div
                            class="hidden border-b px-4 py-4 text-black dark:border-gray-800 dark:text-gray-300 max-lg:block"
                            v-for="record in available.records"
                        >
                            <div class="mb-2 flex items-center justify-between">
                                <div class="flex w-full items-center justify-between gap-2">
                                    <p v-if="available.massActions.length">
                                        <label :for="`mass_action_select_record_${record[available.meta.primary_column]}`">
                                            <input
                                                type="checkbox"
                                                :name="`mass_action_select_record_${record[available.meta.primary_column]}`"
                                                :value="record[available.meta.primary_column]"
                                                :id="`mass_action_select_record_${record[available.meta.primary_column]}`"
                                                class="peer hidden"
                                                v-model="applied.massActions.indices"
                                            >
    
                                            <span class="icon-checkbox-outline peer-checked:icon-checkbox-select cursor-pointer rounded-md text-2xl text-gray-500 peer-checked:text-brandColor">
                                            </span>
                                        </label>
                                    </p>

                                    <div
                                        class="flex w-full items-center justify-end"
                                        v-if="available.actions.length"
                                    >
                                        <a :href="record.actions.find(action => action.index === 'subscribers')?.url">
                                            <span
                                                :class="record.actions.find(action => action.index === 'subscribers')?.icon"
                                                class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 max-sm:place-self-center"
                                            >
                                            </span>
                                        </a>

                                        <a @click="selectedList=true; editModal(record.actions.find(action => action.index === 'edit')?.url)">
                                            <span
                                                :class="record.actions.find(action => action.index === 'edit')?.icon"
                                                class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 max-sm:place-self-center"
                                            >
                                            </span>
                                        </a>

                                        <a @click="performAction(record.actions.find(action => action.index === 'delete'))">
                                            <span
                                                :class="record.actions.find(action => action.index === 'delete')?.icon"
                                                class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 max-sm:place-self-center"
                                            >
                                            </span>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="grid gap-2">
                                <template v-for="column in available.columns">
                                    <div class="flex flex-wrap items-baseline gap-x-2">
                                        <span class="text-slate-600 dark:text-gray-300" v-html="column.label + ':'"></span>
                                        <span class="break-words font-medium text-slate-900 dark:text-white" v-html="record[column.index]"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </template>
            </x-admin::datagrid>

            {!! view_render_event('admin.settings.mailing_lists.index.datagrid.after') !!}

            {!! view_render_event('admin.settings.mailing_lists.index.form.before') !!}

            <x-admin::form
                v-slot="{ meta, errors, handleSubmit }"
                as="div"
                ref="modalForm"
            >
                <form @submit="handleSubmit($event, updateOrCreate)">
                    {!! view_render_event('admin.settings.mailing_lists.index.create_form_controls.before') !!}

                    {!! view_render_event('admin.settings.mailing_lists.index.form.modal.before') !!}

                    <x-admin::modal ref="listUpdateAndCreateModal">
                        <x-slot:header>
                            <p class="text-lg font-bold text-gray-800 dark:text-white">
                                @{{
                                    selectedList
                                    ? "@lang('admin::app.settings.mailing-lists.index.edit.title')"
                                    : "@lang('admin::app.settings.mailing-lists.index.create.title')"
                                }}
                            </p>
                        </x-slot>

                        <x-slot:content>
                            {!! view_render_event('admin.settings.mailing_lists.index.content.before') !!}

                            <x-admin::form.control-group.control
                                type="hidden"
                                name="id"
                            />

                            {!! view_render_event('admin.settings.mailing_lists.index.form.form_controls.name.before') !!}

                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('admin::app.settings.mailing-lists.index.create.name')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    id="name"
                                    name="name"
                                    rules="required|min:0|max:50"
                                    :label="trans('admin::app.settings.mailing-lists.index.create.name')"
                                    :placeholder="trans('admin::app.settings.mailing-lists.index.create.name')"
                                />

                                <x-admin::form.control-group.error control-name="name" />
                            </x-admin::form.control-group>

                            {!! view_render_event('admin.settings.mailing_lists.index.form.form_controls.name.after') !!}

                            {!! view_render_event('admin.settings.mailing_lists.index.form.form_controls.description.before') !!}

                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('admin::app.settings.mailing-lists.index.create.description')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="textarea"
                                    id="description"
                                    name="description"
                                    rules="max:250"
                                    :label="trans('admin::app.settings.mailing-lists.index.create.description')"
                                    :placeholder="trans('admin::app.settings.mailing-lists.index.create.description')"
                                />

                                <x-admin::form.control-group.error control-name="description" />
                            </x-admin::form.control-group>

                            {!! view_render_event('admin.settings.mailing_lists.index.form.form_controls.description.after') !!}
                        </x-slot>

                        <x-slot:footer>
                            {!! view_render_event('admin.settings.mailing_lists.index.form.form_controls.save_button.before') !!}

                            <x-admin::button
                                button-type="submit"
                                class="primary-button justify-center"
                                :title="trans('admin::app.settings.mailing-lists.index.create.save-btn')"
                                ::loading="isProcessing"
                                ::disabled="isProcessing"
                            />

                            {!! view_render_event('admin.settings.mailing_lists.index.form.form_controls.save_button.after') !!}
                        </x-slot>
                    </x-admin::modal>

                    {!! view_render_event('admin.settings.mailing_lists.index.form.modal.after') !!}
                </form>
            </x-admin::form>

            {!! view_render_event('admin.settings.mailing_lists.index.form.after') !!}
        </script>

        <script type="module">
            app.component('v-mailing-list-settings', {
                template: '#mailing-list-settings-template',

                data() {
                    return {
                        isProcessing: false,

                        selectedList: false,
                    };
                },

                computed: {
                    gridsCount() {
                        let count = this.$refs.datagrid.available.columns.length;

                        if (this.$refs.datagrid.available.actions.length) {
                            ++count;
                        }

                        if (this.$refs.datagrid.available.massActions.length) {
                            ++count;
                        }

                        return count;
                    },
                },

                methods: {
                    openModal() {
                        this.selectedList=false;

                        this.$refs.listUpdateAndCreateModal.toggle();
                    },

                    updateOrCreate(params, {resetForm, setErrors}) {
                        this.isProcessing = true;

                        this.$axios.post(params.id ? "{{ route('admin.settings.mailing_lists.update', ':id') }}".replace(':id', params.id) : "{{ route('admin.settings.mailing_lists.store') }}", {
                            ...params,
                            _method: params.id ? 'put' : 'post'
                        }, {
                            headers: {
                                'Content-Type': 'multipart/form-data',
                            }
                        }).then(response => {
                            this.isProcessing = false;

                            this.$refs.listUpdateAndCreateModal.toggle();

                            this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });

                            this.$refs.datagrid.get();

                            resetForm();
                        }).catch(error => {
                            this.isProcessing = false;

                            if (error.response.status === 422) {
                                setErrors(error.response.data.errors);
                            }
                        });
                    },

                    editModal(url) {
                        this.$axios.get(url)
                            .then(response => {
                                this.$refs.modalForm.setValues(response.data.data);

                                this.$refs.listUpdateAndCreateModal.toggle();
                            })
                            .catch(error => {});
                    },
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>
