<template>
    <div>
        <SkeletonLoaderCard height="400" v-if="loading"/>

        <v-card class="custom-border border fade" v-if="!loading">
            <v-card-title class="d-flex align-center flex-wrap justify-space-between">
                <section class="d-flex flex-row align-center">
                    <v-icon color="primary" class="pr-2">mdi-account-circle-outline</v-icon>
                    <div class="f18 primary-font secondary-1--text fw600">Personal Information</div>
                </section>
                <section class="d-flex flex-column text-right ml-auto my-1">
                    <FormLabel  :label="'Enrollee ID: '" class="text-capitalize"/>
                    <div class="f18 primary-font secondary-1--text fw600">{{ user?.enrollee_id }}</div>
                </section>
            </v-card-title>
            <v-divider></v-divider>
            <section class="d-flex align-center justify-center my-5">
                <v-avatar  size="100"> 
                    <v-img :src="user.image ? user.image.url : require('@/assets/default-photo.png')" v-on:error="user.image = null" />
                </v-avatar>
            </section>
            <section class="pa-5">
                <v-row no-gutters>
                    <v-col cols="12" md="6">
                        <v-row no-gutters v-for="(item, i) in info1" :key="i">
                            <v-col cols="6">
                                <FormLabel  :label="item.replaceAll('_', ' ')" class="text-capitalize"/>
                            </v-col>
                            <v-col>
                                <span class="f15 fw500" v-if="item === 'name'">{{ user.first_name ? user.first_name : 'User' }} {{ user.middle_name ? $userFormat.middle_initial(user.middle_name) : '' }} {{ user.last_name ? user.last_name : 'Account' }} {{ user.suffix ? user.suffix : '' }}</span>
                                <span class="f15 fw500" v-else>{{  user[item] }}</span>
                            </v-col>
                        </v-row>
                    </v-col>
                    <v-col cols="12" md="6" v-for="(tbl,  j) in registration_tables" :key="j">
                        <v-row no-gutters v-for="(column, i) in tbl.custom_registration_fields" :key="i">
                            <v-col cols="6">
                                <FormLabel  :label="`${column.input_field_label}`" class="text-capitalize"/>
                            </v-col>
                            <v-col>
                                <span class="f15 fw500">{{  user.data[tbl.title][column.input_field_name]}}</span>
                            </v-col>
                        </v-row>
                    </v-col>
                </v-row>
            </section>
        </v-card>
    </div>
</template>

<script>
import { defineComponent } from 'vue';
import { mapState, mapActions } from 'vuex'
import SkeletonLoaderCard from '@/components/reusable/SkeletonLoaderCard.vue'

export default defineComponent({
    components: {
        SkeletonLoaderCard
    },
    computed: {
        ...mapState({
            user: (state) => state.user,
            registration_tables: (state) => state.registration_tables,
        }),
    },
    data:() => ({
        info1: ['first_name', 'middle_name', 'last_name', 'suffix', 'email'],
        info2: [''],
        loading: false
    }),
    methods: {
       ...mapActions(['getRegistrationTables']),
    },
    mounted() {
        this.loading = true
        this.getRegistrationTables().then(res => {
            let _data = {}
            this.registration_tables.forEach(tbl => {
                tbl.custom_registration_fields.forEach(column => {
                    if (!_data[tbl.title]) {
                        _data[tbl.title] = {};
                    }
                    _data[tbl.title][column.input_field_name] = '';
                    this.loading = false
                })
            })
            this.user.data = Object.assign({}, { ..._data, ...this.user.data });

            this.loading = false
        }).finally(() => {
            this.loading = false
        })
    }
})
</script>