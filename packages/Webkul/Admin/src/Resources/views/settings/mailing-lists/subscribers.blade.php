<x-admin::layouts>
    <!-- Page Title -->
    <x-slot:title>
        @lang('admin::app.settings.mailing-lists.subscribers.title', ['name' => $mailingList->name])
    </x-slot>

    <div class="flex flex-col gap-4">
        <div class="scroll-reactive-sticky sticky top-[60px] z-[1000] flex items-center justify-between rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm shadow-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
            <div class="flex flex-col gap-2">
                {!! view_render_event('admin.settings.mailing_lists.subscribers.index.breadcrumbs.before') !!}

                <x-admin::breadcrumbs name="settings.mailing_lists.subscribers" :entity="$mailingList" />

                {!! view_render_event('admin.settings.mailing_lists.subscribers.index.breadcrumbs.after') !!}

                <div class="text-xl font-bold dark:text-gray-300">
                    @lang('admin::app.settings.mailing-lists.subscribers.title', ['name' => $mailingList->name])
                </div>
            </div>

            <div class="flex items-center gap-x-2.5">
                <div class="flex items-center gap-x-2.5">
                    {!! view_render_event('admin.settings.mailing_lists.subscribers.index.create_button.before') !!}

                    @if (bouncer()->hasPermission('settings.other_settings.mailing_lists.subscribers.create'))
                        <button
                            type="button"
                            class="primary-button"
                            @click="$refs.subscriberSettings.openModal()"
                        >
                            @lang('admin::app.settings.mailing-lists.subscribers.create-btn')
                        </button>

                        <button
                            type="button"
                            class="transparent-button"
                            @click="$refs.subscriberSettings.openBulkModal()"
                        >
                            @lang('admin::app.settings.mailing-lists.subscribers.bulk-add-btn')
                        </button>

                        <button
                            type="button"
                            class="transparent-button"
                            @click="$refs.subscriberSettings.subscribeAll()"
                        >
                            @lang('admin::app.settings.mailing-lists.subscribers.subscribe-all-btn')
                        </button>
                    @endif

                    {!! view_render_event('admin.settings.mailing_lists.subscribers.index.create_button.after') !!}
                </div>
            </div>
        </div>

        <v-subscriber-settings
            ref="subscriberSettings"
            :search-url="'{{ route('admin.contacts.persons.search', ['exclude_subscribers_of' => $mailingList->id]) }}'"
        >
            <x-admin::shimmer.datagrid />
        </v-subscriber-settings>
    </div>

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="subscriber-settings-template"
        >
            {!! view_render_event('admin.settings.mailing_lists.subscribers.index.datagrid.before') !!}

            <span class="mb-2 flex cursor-pointer items-center gap-x-2 text-sm text-gray-600 dark:text-gray-300" @click="toggleUnsubscribed">
                <span
                    :class="['rounded-md text-2xl', showUnsubscribed ? 'icon-checkbox-select text-brandColor' : 'icon-checkbox-outline text-gray-500']"
                ></span>
                Show unsubscribed
            </span>

            <x-admin::datagrid
                :src="route('admin.settings.mailing_lists.subscribers.index', $mailingList->id)"
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

                            <p>@{{ record.id }}</p>

                            <p>@{{ record.person_name }}</p>

                            <p>@{{ record.person_emails }}</p>

                            <p>@{{ record.is_subscribed }}</p>

                            <p>@{{ record.created_at }}</p>

                            <div class="flex justify-end">
                                <a @click="performAction(record.actions.find(action => action.index === 'toggle'))">
                                    <span
                                        :class="record.actions.find(action => action.index === 'toggle')?.icon"
                                        class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 max-sm:place-self-center"
                                    >
                                    </span>
                                </a>

                                <a @click="selectedSubscriber=true; editModal(record.actions.find(action => action.index === 'edit')?.url)">
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
                                        <a @click="performAction(record.actions.find(action => action.index === 'toggle'))">
                                            <span
                                                :class="record.actions.find(action => action.index === 'toggle')?.icon"
                                                class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 max-sm:place-self-center"
                                            >
                                            </span>
                                        </a>

                                        <a @click="selectedSubscriber=true; editModal(record.actions.find(action => action.index === 'edit')?.url)">
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

            {!! view_render_event('admin.settings.mailing_lists.subscribers.index.datagrid.after') !!}

            {!! view_render_event('admin.settings.mailing_lists.subscribers.index.form.before') !!}

            <x-admin::form
                v-slot="{ meta, errors, handleSubmit }"
                as="div"
                ref="modalForm"
            >
                <form @submit="handleSubmit($event, updateOrCreate)">
                    {!! view_render_event('admin.settings.mailing_lists.subscribers.index.create_form_controls.before') !!}

                    {!! view_render_event('admin.settings.mailing_lists.subscribers.index.form.modal.before') !!}

                    <x-admin::modal ref="subscriberUpdateAndCreateModal">
                        <x-slot:header>
                            <p class="text-lg font-bold text-gray-800 dark:text-white">
                                @{{
                                    selectedSubscriber
                                    ? "@lang('admin::app.settings.mailing-lists.subscribers.edit.title')"
                                    : "@lang('admin::app.settings.mailing-lists.subscribers.create.title')"
                                }}
                            </p>
                        </x-slot>

                        <x-slot:content>
                            {!! view_render_event('admin.settings.mailing_lists.subscribers.index.content.before') !!}

                            <x-admin::form.control-group.control
                                type="hidden"
                                name="id"
                            />

                            {!! view_render_event('admin.settings.mailing_lists.subscribers.index.form.form_controls.person.before') !!}

                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('admin::app.settings.mailing-lists.subscribers.create.contact')
                                </x-admin::form.control-group.label>

                                <x-admin::lookup
                                    ::src="searchUrl"
                                    name="person_id"
                                    ::value="selectedPerson"
                                    rules="required"
                                    :label="trans('admin::app.settings.mailing-lists.subscribers.create.contact')"
                                    :placeholder="trans('admin::app.settings.mailing-lists.subscribers.create.contact')"
                                    @on-selected="onPersonSelected"
                                />

                                <x-admin::form.control-group.error control-name="person_id" />
                            </x-admin::form.control-group>

                            {!! view_render_event('admin.settings.mailing_lists.subscribers.index.form.form_controls.person.after') !!}
                        </x-slot>

                        <x-slot:footer>
                            {!! view_render_event('admin.settings.mailing_lists.subscribers.index.form.form_controls.save_button.before') !!}

                            <x-admin::button
                                button-type="submit"
                                class="primary-button justify-center"
                                :title="trans('admin::app.settings.mailing-lists.subscribers.create.save-btn')"
                                ::loading="isProcessing"
                                ::disabled="isProcessing"
                            />

                            {!! view_render_event('admin.settings.mailing_lists.subscribers.index.form.form_controls.save_button.after') !!}
                        </x-slot>
                    </x-admin::modal>

                    {!! view_render_event('admin.settings.mailing_lists.subscribers.index.form.modal.after') !!}
                </form>
            </x-admin::form>

            {!! view_render_event('admin.settings.mailing_lists.subscribers.index.form.after') !!}

            {!! view_render_event('admin.settings.mailing_lists.subscribers.index.bulk_form.before') !!}

            <x-admin::form
                v-slot="{ meta, errors, handleSubmit }"
                as="div"
                ref="bulkModalForm"
            >
                <form @submit="handleSubmit($event, bulkAdd)">
                    <x-admin::modal ref="bulkAddModal">
                        <x-slot:header>
                            <p class="text-lg font-bold text-gray-800 dark:text-white">
                                @lang('admin::app.settings.mailing-lists.subscribers.bulk-add-title')
                            </p>
                        </x-slot>

                        <x-slot:content>
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('admin::app.settings.mailing-lists.subscribers.bulk-add-search')
                                </x-admin::form.control-group.label>

                                <x-admin::lookup
                                    ::src="searchUrl"
                                    name="person_search_id"
                                    ::value="null"
                                    :label="trans('admin::app.settings.mailing-lists.subscribers.create.contact')"
                                    :placeholder="trans('admin::app.settings.mailing-lists.subscribers.create.contact')"
                                    @on-selected="addPerson"
                                />
                            </x-admin::form.control-group>

                            <div v-if="selectedPeople.length" class="mt-4 rounded-lg border border-gray-200 dark:border-gray-700">
                                <div
                                    v-for="(person, index) in selectedPeople"
                                    class="flex items-center justify-between border-b border-gray-100 px-3 py-2 last:border-b-0 dark:border-gray-700"
                                >
                                    <span class="text-sm text-gray-700 dark:text-gray-300">@{{ person.name }}</span>
                                    <span
                                        class="icon-delete cursor-pointer rounded p-1 text-lg text-gray-400 hover:bg-gray-100 hover:text-red-500 dark:hover:bg-gray-800"
                                        @click="removePerson(index)"
                                    >
                                    </span>
                                </div>
                                <div class="px-3 py-2 text-xs text-gray-400">
                                    @{{ selectedPeople.length }} @lang('admin::app.settings.mailing-lists.subscribers.bulk-add-selected')
                                </div>
                            </div>
                        </x-slot>

                        <x-slot:footer>
                            <x-admin::button
                                button-type="submit"
                                class="primary-button justify-center"
                                title="Add Selected People"
                                ::loading="isBulkProcessing"
                                ::disabled="isBulkProcessing || !selectedPeople.length"
                            />
                        </x-slot>
                    </x-admin::modal>
                </form>
            </x-admin::form>

            {!! view_render_event('admin.settings.mailing_lists.subscribers.index.bulk_form.after') !!}
        </script>

        <script type="module">
            app.component('v-subscriber-settings', {
                template: '#subscriber-settings-template',

                props: ['searchUrl'],

                    data() {
                        return {
                            isProcessing: false,

                            isBulkProcessing: false,

                            selectedSubscriber: false,

                            selectedPerson: null,

                            selectedPeople: [],

                            showUnsubscribed: false,
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
                        this.selectedSubscriber = false;

                        this.selectedPerson = null;

                        this.$refs.subscriberUpdateAndCreateModal.toggle();
                    },

                    toggleUnsubscribed() {
                        this.showUnsubscribed = !this.showUnsubscribed;

                        let params = {};

                        if (this.showUnsubscribed) {
                            params.show_unsubscribed = 1;
                        }

                        this.$refs.datagrid.get(params);
                    },

                    onPersonSelected(person) {
                        this.selectedPerson = person;
                    },

                    updateOrCreate(params, {resetForm, setErrors}) {
                        this.isProcessing = true;

                        let url = params.id
                            ? "{{ route('admin.settings.mailing_lists.subscribers.update', [$mailingList->id, ':subscriberId']) }}".replace(':subscriberId', params.id)
                            : "{{ route('admin.settings.mailing_lists.subscribers.store', $mailingList->id) }}";

                        this.$axios.post(url, {
                            person_id: this.selectedPerson?.id || params.person_id,
                            _method: params.id ? 'put' : 'post'
                        }, {
                            headers: {
                                'Content-Type': 'multipart/form-data',
                            }
                        }).then(response => {
                            this.isProcessing = false;

                            this.$refs.subscriberUpdateAndCreateModal.toggle();

                            this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });

                            this.$refs.datagrid.get();

                            resetForm();

                            this.selectedPerson = null;
                        }).catch(error => {
                            this.isProcessing = false;

                            if (error.response.status === 422) {
                                setErrors(error.response.data.errors);
                            }
                        });
                    },

                    openBulkModal() {
                        this.selectedPeople = [];

                        this.$refs.bulkAddModal.toggle();
                    },

                    addPerson(person) {
                        if (!this.selectedPeople.some(p => p.id === person.id)) {
                            this.selectedPeople.push(person);
                        }
                    },

                    removePerson(index) {
                        this.selectedPeople.splice(index, 1);
                    },

                    bulkAdd(params, {resetForm}) {
                        this.isBulkProcessing = true;

                        this.$axios.post("{{ route('admin.settings.mailing_lists.subscribers.bulk_store', $mailingList->id) }}", {
                            person_ids: this.selectedPeople.map(p => p.id),
                        }, {
                            headers: {
                                'Content-Type': 'multipart/form-data',
                            }
                        }).then(response => {
                            this.isBulkProcessing = false;

                            this.$refs.bulkAddModal.toggle();

                            this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });

                            this.$refs.datagrid.get();

                            resetForm();

                            this.selectedPeople = [];
                        }).catch(error => {
                            this.isBulkProcessing = false;

                            if (error.response.status === 422) {
                                this.$emitter.emit('add-flash', { type: 'error', message: error.response.data.message });
                            }
                        });
                    },

                    subscribeAll() {
                        if (!confirm('Are you sure you want to add all contacts to this mailing list?')) {
                            return;
                        }

                        this.isBulkProcessing = true;

                        this.$axios.post("{{ route('admin.settings.mailing_lists.subscribers.subscribe_all', $mailingList->id) }}", {}, {
                            headers: {
                                'Content-Type': 'multipart/form-data',
                            }
                        }).then(response => {
                            this.isBulkProcessing = false;

                            this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });

                            this.$refs.datagrid.get();
                        }).catch(error => {
                            this.isBulkProcessing = false;
                        });
                    },

                    editModal(url) {
                        this.$axios.get(url)
                            .then(response => {
                                let data = response.data.data;

                                this.selectedPerson = data.person;

                                this.$refs.modalForm.setValues({
                                    id: data.id,
                                    person_id: data.person_id,
                                });

                                this.$refs.subscriberUpdateAndCreateModal.toggle();
                            })
                            .catch(error => {});
                    },
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>
