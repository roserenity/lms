<template>
    <v-menu
            ref="menu"
            v-model="menu"
            :close-on-content-click="false"
            transition="scale-transition"
            offset-y
            max-width="290px"
            min-width="auto"
        >
        <template v-slot:activator="{ on, attrs }">
            <v-text-field
                v-model="selectedDate"
                prepend-inner-icon="mdi-calendar"
                readonly
                dense
                outlined
                :class="`general-custom-field f14 ${class_ === '' ? 'col-4 col-md-3' : class_} `"
                hide-details="auto"
                v-bind="attrs"
                v-on="on">
            </v-text-field>
        </template>

        <v-date-picker
            v-model="date"
            type="month"
            no-title
            scrollable
            :max="max"
            :min="min"
        >
            <v-spacer></v-spacer>
            <v-btn
                text
                small
                color="primary"
                class="poppins"
                @click="cancel"
            >
                Cancel
            </v-btn>
            <v-btn
                text
                small
                color="primary"
                class="poppins"
                @click="save"
            >
                OK
            </v-btn>
        </v-date-picker>
    </v-menu>
</template>

<script>
import { defineComponent } from 'vue';

export default defineComponent({
    props: {
        val: {
            default: `${new Date().toISOString().substr(0, 7)}`,
            type: String
        },
        class_:{
            default: '',
            type: String
        },
        max:{
            default: '',
            type: String
        },
        min:{
            default: '',
            type: String
        }
    },
    data: () => ({
        date: new Date().toISOString().substr(0, 7),
        menu: false,
    }),
    methods: {
        cancel(){
            var currentDate = new Date(this.val)
            currentDate.setMonth(currentDate.getMonth() + 1);

            this.date = currentDate.toISOString().substr(0, 7)
            this.menu = false
        },
        save(){
            this.menu = false
            this.$emit('setYear', this.date.split('-')[0])
            this.$emit('setMonth', this.date.split('-')[1])
            this.$emit('apply')
        }
    },
    computed: {
        selectedDate: {
            get() {
                return this.$dateFormat.MMYY(this.val)
            }, 
            set(val) {
                this.date = this.$dateFormat.MMYY(val)
                return this.$dateFormat.MMYY(val)
            }
        }
    },
})

</script>