<template>
    <section>
        <label class="caption">{{ label }}</label>
        <v-textarea 
            outlined 
            dense 
            :readonly="readonly"
            :disabled="disabled"
            :value="value"
            auto-grow
            rows="3"
            :class="class_ !== '' ? class_ : 'general-custom-field mb-1'"
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
        placeholder: {
            type: String,
            default: ""
        },
        value: {
            type: String,
            default: ""
        },
        error: {
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
        class_ : {
            type: String,
            default: ""
        }
    },
})
</script>