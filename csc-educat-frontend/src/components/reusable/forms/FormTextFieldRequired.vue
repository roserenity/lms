<template>
    <section>
        <label class="caption">{{ label }}</label>
        <v-text-field 
            v-if="type !== 'number'"
            outlined 
            dense 
            required
            :type="type"
            :rules="[ v => !!v || 'This is required', v => !!v.trim() || 'This is required']"
            :value="value"
            :error-messages="error"
            :disabled="disabled"
            :readonly="readonly"
            @input="$emit('update:value', $event)"
            :class="`general-custom-field mb-1 ${class_}`"
            hide-details="auto"/>
        <v-text-field 
            v-else
            outlined 
            dense 
            required
            :type="type"
            :rules="[ v => !!v || 'This is required', v => !!v.trim() || 'This is required']"
            :value="value"
            :error-messages="error"
            :disabled="disabled"
            :readonly="readonly"
            @input="$emit('update:value', $event)"
            :class="`general-custom-field mb-1 ${class_}`"
            :min="min"
            hide-details="auto"/>
    </section>
</template>

<script>
import { defineComponent } from '@vue/composition-api';
import { min } from 'moment';

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
        error: {
            type: String,
            default: ""
        },
        type: {
            type: String,
            default: "text"
        },
        disabled: {
            type: Boolean,
            default: false
        },
        readonly: {
            type: Boolean,
            default: false
        },
        class_: {
            type: String,
            default: ""
        },
        min: {
            type: Number,
            default: 0
        },
    },
})
</script>