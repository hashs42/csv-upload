<script setup>
import {reactive, defineProps, watch, ref} from 'vue'

const emit = defineEmits(['update:file', 'update:remove-media']);

const props = defineProps({
    previousMedia: {
        type: Object,
        default: null,
    },
    name: {
        type: String,
        default: "file-upload",
    },
    maxSize: {
        type: String,
        default: "1MB",
    },
    type: {
        type: Number,
        default: null,
    },
    mimeType: {
        type: String,
        default: "",
    },
    allowRemove: {
        type: Boolean,
        default: false
    }
})

const file = reactive({
    name: null,
    preview: null,
    data: null,
    type: null,
    mimeType: null,
})

const widgetState = reactive({
    showDropArea: false,
    disabled: false
})

const acceptedMimeTypes = {
    [1]: {
        "accept": ".csv",
        "display": "CSV",
    },
}

const isRemoveFile = ref(false)

function removeFile() {
    isRemoveFile.value = true
    emit('update:file', null)
    file.name = null
    file.preview = null
    file.data = null
    file.type = props.type
    file.mimeType = null
    emit('update:remove-media', true)
}

function onUploadChange(e) {
    if (e.target.files.length === 0) {
        emit('update:file', null)
        return
    }
    generatePreview(e.target.files[0])
    emit('update:file', file.data)
}

function generatePreview(upload) {
    file.name = upload.name
    file.data = upload
    file.mimeType = upload.type
    file.type = mimeTypeToIntType(upload.type)
    file.preview = null
    let reader = new FileReader();
    reader.addEventListener("load", () => {
        file.preview = reader.result
    });
    reader.readAsDataURL(upload);
}

function objectIsEmpty(obj) {
    return !obj || Object.keys(obj).length === 0
}

function bootstrap() {
    if (isRemoveFile.value === false) {
        file.name = null
        file.preview = null
        file.data = null
        file.type = props.type
        file.mimeType = null
        if (!objectIsEmpty(props.previousMedia) && props.type === mimeTypeToIntType(props.previousMedia.content_type)) {
            file.name = props.previousMedia.original
            file.preview = props.previousMedia.signed_url
            file.mimeType = props.mimeType
        }
    }
}

bootstrap()

watch(props, () => {
    bootstrap()
})

function dropFiles(e) {
    widgetState.showDropArea = false
    if (e.dataTransfer == null || e.dataTransfer.files.length === 0) {
        emit('update:file', null)
        return
    }
    if ( props.type && mimeTypeToIntType(e.dataTransfer.files[0].type) !== props.type ) {
        return
    }
    generatePreview(e.dataTransfer.files[0])
    emit('update:file', file.data)
}

function mimeTypeToIntType(contentType) {
    switch (contentType.split("/")[0]) {
        case "image":
            return 1
        case "video":
            return 2
        default:
            return 3
    }
}

</script>

<template>
    <div class="relative mt-1 sm:mt-0 sm:col-span-2"
         @dragenter.prevent.stop="widgetState.showDropArea = true"
         @dragover.prevent.stop="widgetState.showDropArea  = true"
         @drop.prevent.stop="dropFiles">
        <div v-if="widgetState.showDropArea" class="dropzone absolute w-full h-full bg-gray-300 opacity-50 z-50 flex flex-column">
            <div class="active-area tex-center m-3 border-box border border-dashed border-blue-600 rounded-lg border-2 flex-1"></div>
        </div>
        <div class="flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">

            <span v-if="props.allowRemove" class="absolute top-6 right-6" aria-hidden="true">
                 <svg v-if="file.preview !== null" @click="removeFile" xmlns="http://www.w3.org/2000/svg"
                      title="Remove File" alt="Remove File" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                      stroke="red"
                      class="w-5 h-5 cursor-pointer rounded-md font-medium text-red-600 hover:text-red-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-red-500">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                </svg>
            </span>
            <div class="space-y-1 text-center">
                <template v-if="file.preview === null">
                    <svg v-if="file.type === 1" xmlns="http://www.w3.org/2000/svg"
                         class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                         stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </template>
                <template v-else>
                    <span v-if="file.type === 1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </span>
                    <component v-if="file.preview" :is="file.data ? 'p' : 'a'" :href="file.preview" target="_blank"
                        :class="['no-underline text-sm', file.data ? 'text-gray-700':'text-blue-700 hover:underline']">
                        {{ file.name ?? 'View' }}
                    </component>
                </template>
                <div class="flex justify-center text-sm text-gray-600">
                    <label :for="name" class="relative block cursor-pointer rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                            Upload File
                    </label>
                    <input :id="name" :name="name" type="file"
                           class="sr-only" @change="onUploadChange"
                           :accept="acceptedMimeTypes[props.type] ? acceptedMimeTypes[props.type].accept : null"/>
                </div>
                <p class="text-xs text-gray-500">
                    {{acceptedMimeTypes[props.type] ? acceptedMimeTypes[props.type].display : 'Upload' }}
                </p>
            </div>
        </div>
    </div>
</template>
