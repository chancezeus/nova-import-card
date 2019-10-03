<template>
    <card class="flex flex-col h-auto import-card">
        <div class="px-3 py-3">
            <h1 class="text-xl font-light">{{ __("Import :name", {name: this.card.resourceLabel}) }}</h1>
            <form @submit.prevent="processImport" ref="form">
                <div class="py-4">
                    <span class="form-file mr-4">
                        <input
                            ref="fileField"
                            class="form-file-input"
                            type="file"
                            :id="inputName"
                            :name="inputName"
                            :disabled="working"
                            accept=".csv,.xlsx,.ods"
                            @change="fileChange"
                        />
                        <label :for="inputName" class="form-file-btn btn btn-default btn-primary">
                            {{ __('Choose File') }}
                        </label>
                    </span>
                    <span :class="fileName ? 'text-gray-25' : 'text-gray-50'">
                        {{ fileName || __('No file selected') }}
                    </span>
                </div>

                <div class="py-4" v-show="working" ref="progressBarContainer"></div>

                <div class="flex">
                    <div v-if="errors">
                        <p class="text-danger mb-1" v-for="error in errors">{{error[0]}}</p>
                    </div>
                    <button
                        :disabled="working || !file"
                        type="submit"
                        class="btn btn-default btn-primary ml-auto mt-auto"
                    >
                        <loader v-if="working" width="30"></loader>
                        <span v-else>{{__('Import')}}</span>
                    </button>
                </div>
            </form>
        </div>
    </card>
</template>

<script>
    import {merge} from 'lodash';
    import {Circle, Line, SemiCircle} from 'progressbar.js';

    export default {
        props: ['card', 'resource', 'resourceName'],

        data() {
            return {
                fileName: '',
                file: null,
                working: false,
                errors: null,
            };
        },

        mounted: function () {
            const options = merge({
                strokeWidth: 4,
                easing: 'easeInOut',
                duration: 1000,
                color: '#ffea82',
                trailColor: '#eeeeee',
                trailWidth: 1,
                svgStyle: null,
                text: {
                    style: {
                        color: '#999999',
                        textAlign: 'center',
                        position: 'relative',
                        padding: 0,
                        margin: 0,
                        transform: 0,
                    },
                    autoStyleContainer: false,
                },
            }, this.card.options);

            options.step = (state, bar) => {
                bar.setText(`${Math.round(bar.value() * 100)} %`);

                if (options.animateColor) {
                    bar.path.setAttribute('stroke', state.color);
                }
            };

            let ProgressBarConstructor;
            switch (options.type) {
                case 'circle':
                    ProgressBarConstructor = Circle;
                    break;
                case 'semi-circle':
                    ProgressBarConstructor = SemiCircle;
                    break;
                default:
                    ProgressBarConstructor = Line;
            }

            this.progressBar = new ProgressBarConstructor(this.$refs.progressBarContainer, options);
        },

        methods: {
            fileChange() {
                if (this.$refs.fileField.files.length) {
                    this.fileName = this.$refs.fileField.files[0].name.match(/[^\\/]*$/)[0];
                    this.file = this.$refs.fileField.files[0];
                }
            },
            processImport() {
                if (!this.file) {
                    return;
                }

                this.progressBar.set(0);
                this.working = true;

                let formData = new FormData();
                formData.append('file', this.file);

                if (this.resource && this.resource.id && this.resource.id.value) {
                    formData.append('resource_id', this.resource.id.value);
                    formData.append('resource_type', this.resourceName);
                }

                Nova.request()
                    .post(
                        `/nova-vendor/sparclex/nova-import-card/endpoint/${this.card.resource}`,
                        formData
                    )
                    .then(({data}) => {
                        this.$toasted.success(data.message);

                        if (data.queued) {
                            return this.updateProgress(data.status_id)
                                .then(() => this.requestCompleted());
                        }

                        this.requestCompleted();
                    })
                    .catch(reason => {
                        const response = reason instanceof Error ? reason.response : reason;

                        this.errors = null;

                        if (response && response.data) {
                            const data = response.data;

                            if ('status' in data && data.status === 'failed' && data.output) {
                                this.$toasted.error(data.output.message);

                                return;
                            }

                            if (data.danger) {
                                this.$toasted.error(data.danger);

                                return;
                            }

                            if (data.errors) {
                                this.errors = data.errors;

                                return;
                            }
                        }

                        if (reason instanceof Error && reason.message) {
                            this.$toasted.error(reason.message);

                            return;
                        }

                        this.$toasted.error(this.__('An unknown error occurred'));
                    })
                    .finally(() => {
                        this.working = false;
                    });
            },
            updateProgress(status_id) {
                return new Promise((resolve, reject) => {
                    setTimeout(() => {
                        Nova.request()
                            .get(`/nova-vendor/sparclex/nova-import-card/endpoint/${this.card.resource}/progress/${status_id}`)
                            .then(response => {
                                const data = response.data;

                                this.progressBar.animate(
                                    data.progress_now > 0 && data.progress_max > 0
                                        ? data.progress_now / data.progress_max
                                        : 0
                                );

                                if (!data || data.status === 'failed') {
                                    reject(response);

                                    return;
                                }

                                if (data.status !== 'finished') {
                                    this.updateProgress(status_id).then(resolve, reject);

                                    return;
                                }

                                resolve();
                            }, reject);
                    }, 1000);
                });
            },
            requestCompleted() {
                if (this.$parent.$parent.$parent.$parent.getResources) {
                    this.$parent.$parent.$parent.$parent.getResources();
                } else {
                    this.$parent.$parent.$parent.$parent.getResource();
                }

                this.errors = null;
                this.file = null;
                this.fileName = '';
                this.$refs.form.reset();
            }
        },

        computed: {
            firstError() {
                return this.errors ? this.errors[Object.keys(this.errors)[0]][0] : null;
            },

            inputName() {
                return `file-import-input-${this.card.resource}`;
            },
        },
    };
</script>
