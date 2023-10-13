<template>
    <div>
        <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
            <h1 class="mt-0 text-2xl font-medium text-gray-900">
                CSV Upload
            </h1>

            <div class="mt-5 mb-5">
                <so-upload v-model:file="form.upload" :type="1"/>

                <p class="text-red-500 mt-2" v-if="form.errors.upload || form.errors[0]">
                    {{ form.errors.upload }}
                </p>

                <p class="text-green-500 mt-2" v-if="msg">
                    {{ msg }}
                </p>

                <div class="text-right">
                    <PrimaryButton class="mt-2" @click.prevent="submitForm()"
                                   :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                        Upload
                    </PrimaryButton>
                </div>
            </div>

            <div v-if="userUploads.length > 0">
                <div class="relative flex py-5 items-center">
                    <div class="flex-grow border-t border-gray-400"></div>
                    <span class="flex-shrink mx-4 text-gray-400">Uploads</span>
                    <div class="flex-grow border-t border-gray-400"></div>
                </div>

                <div class="px-4 sm:px-6 lg:px-8">
                    <div class="mt-8 flow-root">
                        <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                            <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                                <table class="min-w-full divide-y divide-gray-300">
                                    <thead>
                                    <tr>
                                        <th scope="col"
                                            class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                            Time
                                        </th>
                                        <th scope="col"
                                            class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                            Filename
                                        </th>
                                        <th scope="col"
                                            class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                            Status
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 bg-white">
                                    <tr v-for="upload in userUploads" :key="upload.id">
                                        <td class="whitespace-nowrap py-5 pl-4 pr-3 text-sm sm:pl-0">
                                            <div class="flex items-center">
                                                <div class="ml-4">
                                                    <div class="font-medium text-gray-900">
                                                        {{ getHumanReadableTime(upload.created_at) }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-5 text-sm text-gray-500">
                                            <div class="text-gray-900">{{ upload.filename }}</div>
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-5 text-sm text-gray-500">
                                        <span class="inline-flex items-center rounded-md
                                            px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset
                                            ring-green-600/20" :class="getStatusClass(upload.status)"
                                            v-if="visible">
                                            {{ getStatusText(upload.status) }}
                                        </span>
                                        <span class="inline-flex items-center rounded-md
                                            px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset
                                            ring-green-600/20" :class="uploadStatusClass"
                                                  v-else>
                                            {{ uploadStatus }}
                                        </span>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout.vue"
import SoUpload from "@/Components/UploadWidget.vue"
import PrimaryButton from '@/Components/PrimaryButton.vue';
import moment from 'moment'

export default {

    components: {
        AppLayout,
        SoUpload,
        PrimaryButton,
        moment
    },

    data() {
        return {
            form: this.$inertia.form({
                upload: null,
                processing: false
            }),
            previousMedia: null,
            uploadedFile: null,
            msg: null,
            visible: true,
            uploadStatus: null,
            uploadStatusClass: null
        }
    },

    computed: {
        getStatusClass() {
            return (status) => {
                switch (status) {
                    case 1:
                        return 'bg-yellow-50 text-yellow-700 ring-yellow-600/20';
                    case 2:
                        return 'bg-orange-50 text-orange-700 ring-orange-600/20';
                    case 3:
                        return 'bg-red-50 text-red-700 ring-red-600/20';
                    case 4:
                        return 'bg-green-50 text-green-700 ring-green-600/20';
                    default:
                        return '';
                }
            };
        },
        getStatusText() {
            return (status) => {
                switch (status) {
                    case 1:
                        return 'Pending';
                    case 2:
                        return 'Processing';
                    case 3:
                        return 'Failed';
                    case 4:
                        return 'Completed';
                    default:
                        return 'Unknown Status';
                }
            };
        },
    },

    methods: {
        submitForm() {
            this.msg = ''
            this.form.processing = true
            this.form.post(this.route('csv.upload'), {
                preserveState: false,
                onSuccess: () => {
                    this.form.processing = false
                    this.form.reset()
                    this.uploadedFile = null
                    this.msg = 'Successfully Uploaded!'
                },
                onError: () => {
                    this.form.processing = false
                },
            })
        },
        getHumanReadableTime(timestamp) {
            return moment(timestamp).fromNow();
        },

        startListening() {
            Echo.private(`csv-upload.${this.user.id}`)
                .listen('.csv-upload.created', e => {
                    this.visible = false;
                    switch (e.status) {
                        case 1:
                            this.uploadStatus = 'Pending'
                            this.uploadStatusClass = 'bg-yellow-50 text-yellow-700 ring-yellow-600/20';
                            break;
                        case 2:
                            this.uploadStatus = 'Processing'
                            this.uploadStatusClass = 'bg-orange-50 text-orange-700 ring-orange-600/20';
                            break;
                        case 3:
                            this.uploadStatus = 'Failed'
                            this.uploadStatusClass = 'bg-red-50 text-red-700 ring-red-600/20';
                            break;
                        case 4:
                            this.uploadStatus = 'Completed'
                            this.uploadStatusClass = 'bg-green-50 text-green-700 ring-green-600/20';
                            break;
                        default:
                            return '';
                    }
                })
        },

        stopListening() {
            Echo.private(`csv-upload.${this.user.id}`)
                .stopListening('.csv-upload.created')
            Echo.leave(`csv-upload.${this.user.id}`)
            Echo.leaveChannel(`csv-upload.${this.user.id}`)
        },

    },

    props: {
        userUploads: Array,
        user: Object,
    },

    mounted() {
        this.startListening()
    },

    beforeUnmount() {
        this.stopListening()
    },

    setup() {
        return {}
    },
}

</script>
