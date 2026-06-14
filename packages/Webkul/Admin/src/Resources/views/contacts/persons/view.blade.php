<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.contacts.persons.view.title', ['name' => strip_tags($person->name)])
    </x-slot>

    <!-- Content -->
    <div class="flex gap-4 max-lg:flex-wrap">
        <!-- Left Panel -->
        {!! view_render_event('admin.contact.persons.view.left.before', ['person' => $person]) !!}

        <div class="max-lg:min-w-full max-lg:max-w-full [&>div:last-child]:border-b-0 lg:sticky lg:top-[73px] flex min-w-[394px] max-w-[394px] flex-col self-start rounded-lg border border-gray-300 bg-white dark:border-gray-800 dark:bg-gray-900">
            <!-- Person Information -->
            <div class="flex w-full flex-col gap-2 border-b border-gray-300 p-4 dark:border-gray-800">
                <!-- Breadcrumbs -->
                <div class="flex items-center justify-between">
                    <x-admin::breadcrumbs
                        name="contacts.persons.view"
                        :entity="$person"
                    />
                </div>

                {!! view_render_event('admin.contact.persons.view.tags.before', ['person' => $person]) !!}

                <!-- Tags -->
                <x-admin::tags
                    :attach-endpoint="route('admin.contacts.persons.tags.attach', $person->id)"
                    :detach-endpoint="route('admin.contacts.persons.tags.detach', $person->id)"
                    :added-tags="$person->tags"
                />

                {!! view_render_event('admin.contact.persons.view.tags.after', ['person' => $person]) !!}

                
                <!-- Title -->
                <div class="mb-4 flex flex-col gap-0.5">
                    {!! view_render_event('admin.contact.persons.view.title.before', ['person' => $person]) !!}

                    <h3 class="text-lg font-bold dark:text-white">
                        {{ $person->name }}
                    </h3>

                    <p class="dark:text-white">
                        {{ $person->job_title }}
                    </p>

                    {!! view_render_event('admin.contact.persons.view.title.after', ['person' => $person]) !!}
                </div>
                
                <!-- Activity Actions -->
                <div class="flex flex-wrap gap-2">
                    {!! view_render_event('admin.contact.persons.view.actions.before', ['person' => $person]) !!}

                    <!-- Mail Activity Action -->
                    <x-admin::activities.actions.mail
                        :entity="$person"
                        entity-control-name="person_id"
                    />

                    <!-- File Activity Action -->
                    <x-admin::activities.actions.file
                        :entity="$person"
                        entity-control-name="person_id"
                    />

                    <!-- Note Activity Action -->
                    <x-admin::activities.actions.note
                        :entity="$person"
                        entity-control-name="person_id"
                    />

                    <!-- Activity Action -->
                    <x-admin::activities.actions.activity
                        :entity="$person"
                        entity-control-name="person_id"
                    />

                    {!! view_render_event('admin.contact.persons.view.actions.after', ['person' => $person]) !!}
                </div>
            </div>

            <!-- Person Attributes -->
            @include ('admin::contacts.persons.view.attributes')

            <!-- Contact Organization -->
            @include ('admin::contacts.persons.view.organization')
        </div>

        {!! view_render_event('admin.contact.persons.view.left.after', ['person' => $person]) !!}

        <!-- Right Panel -->
        <div class="flex w-full flex-col gap-4 rounded-lg">
            {!! view_render_event('admin.contact.persons.view.right.before', ['person' => $person]) !!}

            <!-- Stages Navigation -->
            <x-admin::activities
                :endpoint="route('admin.contacts.persons.activities.index', $person->id)"
                :extraTypes="[['name' => 'mailing_lists', 'label' => trans('admin::app.contacts.persons.view.mailing-lists.title')]]"
            >
                <x-slot:mailing_lists>
                    <v-person-mailing-lists person-id="{{ $person->id }}" />
                </x-slot:mailing_lists>
            </x-admin::activities>

            {!! view_render_event('admin.contact.persons.view.right.after', ['person' => $person]) !!}
        </div>
    </div>

    @pushOnce('scripts', 'v-person-mailing-lists')
        <script type="text/x-template" id="v-person-mailing-lists-template">
            <div class="p-4">
                <div v-if="isLoading" class="flex items-center justify-center py-8">
                    <x-admin::spinner />
                </div>

                <template v-else>
                    <div
                        v-for="list in mailingLists"
                        class="flex items-center justify-between border-b border-gray-200 px-4 py-3 last:border-b-0 dark:border-gray-800"
                    >
                        <div class="flex flex-col gap-0.5">
                            <p class="font-medium dark:text-white">
                                @{{ list.name }}
                            </p>

                            <p
                                v-if="list.description"
                                class="text-sm text-gray-500 dark:text-gray-400"
                            >
                                @{{ list.description }}
                            </p>
                        </div>

                        <button
                            type="button"
                            class="rounded-md px-3 py-1.5 text-sm font-medium transition-all"
                            :class="{
                                'bg-brandColor text-white': ! list.subscribed,
                                'bg-gray-200 text-gray-600 dark:bg-gray-700 dark:text-gray-300': list.subscribed,
                                'cursor-not-allowed opacity-50': toggling[list.id],
                            }"
                            :disabled="toggling[list.id]"
                            @click="toggle(list)"
                        >
                            <template v-if="toggling[list.id]">
                                <x-admin::spinner />
                            </template>

                            <template v-else>
                                <template v-if="list.subscribed">
                                    @lang('admin::app.components.activities.index.unsubscribe')
                                </template>

                                <template v-else>
                                    @lang('admin::app.components.activities.index.subscribe')
                                </template>
                            </template>
                        </button>
                    </div>

                    <div
                        v-if="! mailingLists.length"
                        class="py-8 text-center text-gray-400 dark:text-gray-500"
                    >
                        @lang('admin::app.contacts.persons.view.mailing-lists.no-lists')
                    </div>
                </template>
            </div>
        </script>

        <script type="module">
            app.component('v-person-mailing-lists', {
                template: '#v-person-mailing-lists-template',

                props: {
                    personId: {
                        type: [String, Number],
                        required: true,
                    },
                },

                data() {
                    return {
                        mailingLists: [],
                        isLoading: false,
                        toggling: {},
                    };
                },

                mounted() {
                    this.get();
                },

                methods: {
                    get() {
                        this.isLoading = true;

                        this.$axios.get("{{ route('admin.contacts.persons.mailing_lists.index', 'PERSON_ID') }}".replace('PERSON_ID', this.personId))
                            .then(response => {
                                this.mailingLists = response.data;

                                this.isLoading = false;
                            })
                            .catch(error => {
                                this.isLoading = false;
                            });
                    },

                    toggle(list) {
                        this.toggling[list.id] = true;

                        if (list.subscribed) {
                            this.$axios.delete("{{ route('admin.contacts.persons.mailing_lists.destroy', ['id' => 'PERSON_ID', 'mailingListId' => 'LIST_ID']) }}"
                                    .replace('PERSON_ID', this.personId)
                                    .replace('LIST_ID', list.id))
                                .then(response => {
                                    list.subscribed = false;

                                    this.toggling[list.id] = false;

                                    this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });
                                })
                                .catch(error => {
                                    this.toggling[list.id] = false;
                                });
                        } else {
                            this.$axios.post("{{ route('admin.contacts.persons.mailing_lists.store', ['id' => 'PERSON_ID', 'mailingListId' => 'LIST_ID']) }}"
                                    .replace('PERSON_ID', this.personId)
                                    .replace('LIST_ID', list.id))
                                .then(response => {
                                    list.subscribed = true;

                                    list.subscriber_id = response.data.subscriber_id;

                                    this.toggling[list.id] = false;

                                    this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });
                                })
                                .catch(error => {
                                    this.toggling[list.id] = false;
                                });
                        }
                    },
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>
