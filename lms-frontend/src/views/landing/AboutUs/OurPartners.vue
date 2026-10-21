<template>
    <v-container class="fade">
        <v-row class="primary-font mb-10 mt-3">
            <v-col cols="12" md="7" class="py-3 f14 d-flex flex-column justify-center">
                <span class="f20 fw600">Our Partners</span>
                <v-divider class="l-primary my-5" width="70%" />
                <ul>
                    <li class="my-3" v-for="(item, i) in list" :key="i">
                        {{ item }}
                    </li>
                </ul>
            </v-col>
            <v-col cols="12" md="5" class="d-flex flex-column align-center justify-center">
                <v-img :src="require('@/assets/images/landing/partners-1.png')" contain width="70%"/>
                <v-img :src="require('@/assets/images/landing/partners-2.png')" contain width="70%"/>
            </v-col>
        </v-row>
    </v-container>
</template>

<script>
export default {
    data: () => ({
        list: [
            'Accredited private learning and development institutions',
            'Corporate and public-sector training institutions',
            'Specialist providers offering highly technical training',
            'Local training institutions recognized for meeting global standards of excellence',
            'Universities and colleges recognized as centers of excellence',
            'International institutions that offer training for scholarship or personal advancement'
        ]
    })
}
</script>
