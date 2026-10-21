<template>
    <v-btn
        :outlined="outlined"
        color="primary"
        class="poppins f12 fw600 text-capitalize"
        @click="$emit('click')"
        dense
        :loading="loading"
        :disabled="disabled"
        :block="block"
    >
        {{ label }}
        <v-icon small right v-if="icon">
            {{ icon }}
        </v-icon>
    </v-btn>
</template>

<script>
export default {
    props: {
        label: {
            type: String,
            default: ""
        },
        outlined: {
            type: Boolean,
            default: false
        },
        loading: {
            type: Boolean,
            default: false
        },
        disabled: {
            type: Boolean,
            default: false
        },
        icon: {
            type: String || null,
            default: null
        },
        block: {
            type: Boolean,
            default: false
        }
    },
}
</script>
