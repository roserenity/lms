<template>
    <section :class="class_ !== '' ? class_ : 'd-flex flex-row align-center col-xl-3 col-lg-4 pa-0'">
        <v-text-field
            :solo="!outlined"
            :outlined="outlined"
            dense
            flat
            class="custom-border general-custom-field f14 fw500"
            hide-details
            prepend-inner-icon="mdi-magnify"
            :placeholder="placeholder"
            :value="value"
            :loading="loading"
            @input="$emit('update:value', $event)"
            :append-icon="value.trim() === '' ? '' : 'mdi-close'"
            @click:append="$emit('clear')"
        />
        <v-btn dense :color="landing ? 'l-primary' : 'primary'" :class="landing ? 'white--text' : ''" class="poppins f12 fw500 px-3" @click="(e) => {e.preventDefault(), $emit('search') }" :disabled="disabled"> Search</v-btn>
    </section>
</template>

<script>
import { defineComponent } from '@vue/composition-api';

export default defineComponent({
    props: {
        placeholder: {
            type: String,
            default: 'Search'
        },
        value: {
            type: String,
            default: ''
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
        landing: {
            default: false,
            type: Boolean
        },
        class_: {
            type: String,
            default: ''
        }
    },
})
</script>