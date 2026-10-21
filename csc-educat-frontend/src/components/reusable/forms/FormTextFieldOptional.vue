<template>
    <section>
        <label class="caption">{{ label }}</label>
        <v-text-field 
            outlined 
            dense 
            :value="value"
            :readonly="readonly"
            :disabled="disabled"
            :placeholder="placeholder"
            :error-messages="error"
            :class="`general-custom-field mb-1 ${class_}`"
            @input="$emit('update:value', $event)"
            hide-details="auto"/>
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
        value: {
            type: String,
            default: ""
        },
        readonly: {
            type: Boolean,
            default: false
        },
        disabled: {
            type: Boolean,
            default: false
        },
        placeholder: {
            type: String,
            default: ""
        },
        class_: {
            type: String,
            default: ""
        },
        error: {
            type: String,
            default: ""
        },
    },
})
</script>