<template>
    <v-menu
        v-model="menu"
        :close-on-content-click="false"
        transition="slide-y-transition"
        offset-y
        min-width="auto"
    >
        <template v-slot:activator="{ on, attrs }">
        <v-text-field
            v-model="date"
            prepend-icon="mdi-calendar"
            dense
            outlined
            hide-details
            :disabled="disabled"
            :placeholder="placeholder"
            :class="`general-custom-field f14 ${class_ === '' ? 'col-4 col-md-3' : class_} `"
            readonly
            :append-icon="val.trim() === '' ? '' : 'mdi-close'"
            @click:append="$emit('apply', '')"
            v-bind="attrs"
            v-on="on"
        ></v-text-field>
        </template>
        <v-date-picker
            v-model="date"
            @input="menu = false"
            :max="max"
            :min="min"
            dense
        ></v-date-picker>
    </v-menu>
</template>

<script>
import { defineComponent } from 'vue';

export default defineComponent({
    props: {
        val: {
            default: '',
            type: String
        },
        class_:{
            default: '',
            type: String
        },
        max: {
            default: '',
            type: String
        },
        min: {
            default: '',
            type: String
        },
        disabled: {
            type: Boolean,
            default: false
        },
        placeholder: {
            default: '',
            type: String
        },
    },
    data: () => ({
        date: '',
        menu: false,
    }),
    mounted(){
        this.date = this.val
    },
    watch: {
        menu(val) {
            if(!val) {
                this.$emit('apply', this.date)
            }
        },
        val(val) {
            this.date = val
        }
    },
})

</script>