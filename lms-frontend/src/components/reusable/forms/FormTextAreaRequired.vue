<template>
    <section>
        <label class="caption">{{ label }}</label>
        <v-textarea 
            v-if="!hasMax"
            outlined 
            dense 
            required
            :readonly="readonly"
            :disabled="disabled"
            :rules="[ v => !!v || 'This is required']"
            :value="value"
            :error-messages="error"
            auto-grow
            rows="3"
            :class="class_ !== '' ? class_ : 'general-custom-field mb-1'"
            @input="$emit('update:value', $event)"
            hide-details="auto"/>
        <v-textarea 
            v-else
            outlined 
            dense 
            required
            :readonly="readonly"
            :disabled="disabled"
            :value="value"
            :error-messages="error"
            auto-grow
            rows="3"
            counter
            :class="class_ !== '' ? class_ : 'general-custom-field mb-1'"
            @input="$emit('update:value', $event)"
            :rules="[v => v === '' || v.length <= 255 || 'Max 255 characters', v => !!v || 'This is required']"
            :maxlength="255"
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
        hasMax: {
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