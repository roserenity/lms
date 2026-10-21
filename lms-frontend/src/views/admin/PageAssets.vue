<template>
    <v-card class="custom-border border pa-5 fade">
        <Tabs :value.sync="tab" :tabs="items"/>
        
        <v-tabs-items v-model="tab" style="background-color: transparent"  id="main-container" >
            <v-tab-item v-for="(item, i) in items" :key="i" class="">
                <Advisories v-if="item === 'Advisories'"  />
                <LandingPage v-if="item === 'Landing Page'"  />
            </v-tab-item>
        </v-tabs-items>
    </v-card>
</template>

<script>

import Advisories from './page_assets/Advisories';
import LandingPage from './page_assets/LandingPage.vue';

export default {
    components: { 
        Advisories,
        LandingPage
    },
    mounted(){
    },
    data: () => ({
        items: ['Landing Page', 'Advisories'],
        tab: 0,
    }),

    methods: {
    },
    computed: {
    },
}

</script>