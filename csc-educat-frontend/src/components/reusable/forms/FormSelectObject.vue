<template>
    <section>
        <label class="caption">{{ label }}</label>
        <v-select 
            :items="items" 
            item-text="text"
            item-value="value"
            outlined dense 
            :value="value"
            hide-details="auto"
            :error-messages="error"
            :class="`general-custom-field f12 ${class_}`"
            :required="required"
            :placeholder="placeholder"
            :loading="loading"
            :disabled="disabled"
            :multiple="multiple"
            :rules="Boolean(required) ? [ value => !!value || 'This is required' ] : []"
            @input="$emit('update:value', $event)"
            @change="$emit('change', $event)"/>
    </section>
</template>

<script>
import { defineComponent } from '@vue/composition-api';

export default defineComponent({
    props: {
        label: {
            type: String,
            default: ""
        },
        items: {
            type: Array,
            default: []
        },
        value: {
            type: String,
            default: ""
        },
        placeholder: {
            type: String,
            default: ""
        },
        error: {
            type: String,
            default: ""
        },
        loading: {
            type: Boolean,
            default: false
        },
        required: {
            type: Boolean,
            default: false
        },
        multiple: {
            type: Boolean,
            default: false
        },
        disabled: {
            type: Boolean,
            default: false
        },
        class_: {
            type: String,
            default: ""
        },
    },
})
</script>