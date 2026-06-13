<v-control-tags
    :errors="errors"
    {{ $attributes }}
    v-bind="$attrs"
></v-control-tags>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-control-tags-template"
    >
        <div class="relative">
            <div 
                class="flex min-h-[38px] w-full items-center rounded border border-gray-300 px-2.5 py-1.5 text-sm font-normal text-gray-800 transition-all hover:border-gray-400 dark:border-gray-800 dark:text-white dark:hover:border-gray-400"
                :class="[errors[`temp-${name}`] ? 'border !border-red-600 hover:border-red-600' : '']"
            >
                <ul
                    class="flex flex-wrap items-center gap-1"
                    v-bind="$attrs"
                >
                    <li
                        v-for="(tag, index) in tags"
                        :key="index"
                        class="flex items-center gap-1 rounded-md bg-gray-100 dark:bg-gray-950 ltr:pl-2 rtl:pr-2"
                    >
                        <x-admin::form.control-group.control
                            type="hidden"
                            ::name="name + '[' + index + ']'"
                            ::value="tag"
                        />

                        @{{ tag }}

                        <span
                            class="icon-cross-large cursor-pointer p-0.5 text-xl"
                            @click="removeTag(tag)"
                        ></span>
                    </li>

                    <li :class="['w-full', tags.length && 'mt-1.5']">
                        <v-field
                            v-slot="{ field, errors }"
                            :name="'temp-' + name"
                            v-model="input"
                            :rules="tags.length ? inputRules : [inputRules, rules].filter(Boolean).join('|')"
                            :label="label"
                        >
                            <input
                                type="text"
                                :name="'temp-' + name"
                                v-bind="field"
                                class="w-full dark:!bg-gray-900"
                                :placeholder="placeholder"
                                :label="label"
                                @keydown.enter.prevent="onEnter"
                                @keydown.down.prevent="navigateSuggestions(1)"
                                @keydown.up.prevent="navigateSuggestions(-1)"
                                @keydown.esc="showSuggestions = false"
                                autocomplete="new-email"
                                @blur="onBlur"
                                @focus="onFocus"
                            />
                        </v-field>

                        <template v-if="! tags.length && input != ''">
                            <v-field
                                v-slot="{ field, errors }"
                                :name="name + '[' + 0 +']'"
                                :value="input"
                                :rules="inputRules"
                                :label="label"
                            >
                                <input
                                    type="hidden"
                                    :name="name + '[0]'"
                                    v-bind="field"
                                />
                            </v-field>
                        </template>
                    </li>
                </ul>
            </div>

            <ul
                v-if="suggestionsUrl && showSuggestions && filteredSuggestions.length"
                class="absolute z-10 mt-1 max-h-48 w-full overflow-y-auto rounded-lg border border-gray-300 bg-white py-1 shadow-lg dark:border-gray-700 dark:bg-gray-900"
            >
                <li
                    v-for="(suggestion, index) in filteredSuggestions"
                    :key="index"
                    class="cursor-pointer px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800"
                    :class="{ 'bg-gray-100 dark:bg-gray-800': index === selectedIndex }"
                    @mousedown.prevent="selectSuggestion(suggestion)"
                >
                    @{{ suggestion }}
                </li>
            </ul>
        </div>

        <v-error-message
            :name="'temp-' + name"
            v-slot="{ message }"
        >
            <p
                class="mt-1 text-xs italic text-red-600"
                v-text="message"
            >
            </p>
        </v-error-message>
    </script>

    <script type="module">
        app.component('v-control-tags', {
            template: '#v-control-tags-template',

            props: {
                name: {
                    type: String,
                    required: true,
                },

                label: {
                    type: String,
                    default: '',
                },

                placeholder: {
                    type: String,
                    default: '',
                },

                rules: {
                    type: String,
                    default: '',
                },

                inputRules: {
                    type: String,
                    default: '',
                },

                data: {
                    type: Array,
                    default: () => [],
                },

                errors: {
                    type: Object,
                    default: () => {},
                },

                allowDuplicates: {
                    type: Boolean,
                    default: true,
                },

                suggestionsUrl: {
                    type: String,
                    default: '',
                },
            },

            data() {
                return {
                    tags: this.data ? this.data : [],

                    input: '',

                    suggestions: [],

                    showSuggestions: false,

                    selectedIndex: -1,

                    fetchTimer: null,
                }
            },

            computed: {
                filteredSuggestions() {
                    if (! this.input) {
                        return [];
                    }

                    const q = this.input.toLowerCase().trim();

                    return this.suggestions.filter(s =>
                        s.toLowerCase().includes(q)
                    );
                },
            },

            watch: {
                input() {
                    this.selectedIndex = -1;

                    if (! this.suggestionsUrl || ! this.input.trim()) {
                        this.showSuggestions = false;

                        return;
                    }

                    clearTimeout(this.fetchTimer);

                    this.fetchTimer = setTimeout(() => {
                        this.fetchSuggestions();
                    }, 200);
                },
            },

            methods: {
                fetchSuggestions() {
                    if (! this.input.trim()) {
                        this.suggestions = [];
                        this.showSuggestions = false;

                        return;
                    }

                    this.$axios.get(this.suggestionsUrl, {
                        params: { q: this.input.trim() },
                    }).then(response => {
                        this.suggestions = response.data.data ?? [];
                        this.showSuggestions = this.suggestions.length > 0;
                    }).catch(() => {
                        this.suggestions = [];
                        this.showSuggestions = false;
                    });
                },

                onEnter() {
                    if (this.selectedIndex >= 0 && this.filteredSuggestions[this.selectedIndex]) {
                        this.selectSuggestion(this.filteredSuggestions[this.selectedIndex]);

                        return;
                    }

                    this.addTag();
                },

                navigateSuggestions(direction) {
                    if (! this.filteredSuggestions.length) {
                        return;
                    }

                    this.selectedIndex = Math.max(
                        -1,
                        Math.min(
                            this.filteredSuggestions.length - 1,
                            this.selectedIndex + direction
                        )
                    );
                },

                selectSuggestion(suggestion) {
                    if (
                        ! this.allowDuplicates
                        && this.tags.includes(suggestion)
                    ) {
                        this.input = '';
                        this.showSuggestions = false;

                        return;
                    }

                    this.tags.push(suggestion);

                    this.$emit('tags-updated', this.tags);

                    this.input = '';

                    this.showSuggestions = false;

                    this.selectedIndex = -1;
                },

                addTag() {
                    if (this.errors['temp-' + this.name]) {
                        return;
                    }

                    const tag = this.input.trim();

                    if (! tag) {
                        return;
                    }

                    if (
                        ! this.allowDuplicates
                        && this.tags.includes(tag)
                    ) {
                        this.input = '';

                        return;
                    }

                    this.tags.push(tag);

                    this.$emit('tags-updated', this.tags);

                    this.input = '';
                },

                removeTag: function(tag) {
                    this.tags = this.tags.filter(function (tempTag) {
                        return tempTag != tag;
                    });

                    this.$emit('tags-updated', this.tags);
                },

                onBlur() {
                    setTimeout(() => {
                        this.showSuggestions = false;
                    }, 200);
                },

                onFocus() {
                    if (this.filteredSuggestions.length) {
                        this.showSuggestions = true;
                    }
                },
            }
        });
    </script>
@endpushOnce
