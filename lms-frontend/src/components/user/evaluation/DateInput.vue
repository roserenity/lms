<template>
    <v-menu
        ref="menu"
        v-model="menu"
        :close-on-content-click="false"
        transition="scale-transition"
        offset-y
        min-width="auto"
        :return-value.sync="date"
        :disabled="preview"
    >
        <template v-slot:activator="{ on, attrs }">
            <v-text-field
                v-model="date"
                placeholder="mm/dd/yyyy"
                prepend-icon="mdi-calendar"
                v-bind="attrs"
                v-on="on"
                class=" col-sm-6 f12 poppins"
                :readonly="preview"
            ></v-text-field>
        </template>
        <v-date-picker
            color="primary"
            v-model="date"
            show-adjacent-months>
            <v-spacer></v-spacer>
            <v-btn
                text
                color="primary"
                @click="menu = false"
            >
                Cancel
            </v-btn>
            <v-btn
                text
                color="primary"
                @click="$refs.menu.save(date)"
            >
                OK
            </v-btn>
        </v-date-picker>
    </v-menu>
</template>

<script>
export default {
    props: ['index', 'preview', 'answer'],
    data: () => ({
        menu: null,
        date: null,
    }),
    mounted() {
        if(this.preview) {
            this.date = this.answer
        }
    },
    watch: {
        date(e) {
            this.$emit('saveAnswer', {index: this.index, answer: e})
        }
    }
}
</script>