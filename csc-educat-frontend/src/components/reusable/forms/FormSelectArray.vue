<template>
    <section>
        <label v-if="label !== ''" class="caption">{{ label }}</label>
        <v-select
            :items="items"
            outlined
            dense
            :multiple="multiple"
            :error-messages="error"
            :required="Boolean(required)"
            :rules="Boolean(required) ? [ value => !!value || 'This is required' ] : []"
            hide-details="auto"
            :value="value"
            :loading="loading"
            :placeholder="placeholder"
            :class="class_ !== '' ? class_ : 'general-custom-field f12'"
            @input="$emit('update:value', $event)"
            @change="$emit('change', $event)"
        >
        </v-select>
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
        multiple: {
            type: Boolean,
            default: false
        },
        required: {
            type: Number,
            default: 0
        },
        loading: {
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