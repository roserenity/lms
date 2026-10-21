<template>
    <v-btn 
        :loading="loading" 
        color="primary" 
        right 
        outlined 
        :disabled="disabled"
        class="poppins f12 fw500" 
        @click="$emit('click')">
        Export
        <v-icon right small>
            mdi-export-variant
        </v-icon>
    </v-btn>
</template>

<script>
export default {
    props: {
        loading: {
            type: Boolean,
            default: false
        },
        disabled: {
            type: Boolean,
            default: false
        }
    },
}
</script>