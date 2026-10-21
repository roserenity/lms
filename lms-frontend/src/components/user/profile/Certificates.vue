<template>
    <div>
        <SkeletonLoaderCard height="400" v-if="loading && certificates.length === 0"/>
        <v-card class="custom-border border pb-1 fade" v-else>
            <v-card-title class="d-flex flex-row align-center">
                <!-- <v-badge
                    :content="certificates.length"
                    :color="certificates.length === 0 ? 'transparent' : 'primary'"
                    inline
                > -->
                    <v-icon color="primary" class="pr-2">mdi-certificate-outline</v-icon>
                    <div class="f18 primary-font secondary-1--text fw600">My Certificates</div>
                <!-- </v-badge> -->
            </v-card-title>
        <v-progress-linear
            indeterminate
            color="primary"
            rounded
            height="3px"
            v-if="loading"
          ></v-progress-linear>
            <v-divider v-if="certificates.length > 0"></v-divider>
            <v-sheet height="auto" v-if="certificates.length>0" class="d-flex flex-wrap pa-5 fade">
                <CertificateCard v-for="(item, i) in certificates" :key="i" :certificate="item" class="ma-1 my-2" :class="$vuetify.breakpoint.mobile"/>
            </v-sheet>
            <v-sheet
                v-if="!loading && certificates.length === 0"
                color="transparent"
                class="d-flex flex-column align-center justify-center pb-10"
                height="400"
            >
                <div class="">
                <v-img 
                    :max-width="$vuetify.breakpoint.mobile?200:300" 
                    :src="require('@/assets/default/empty_drafts.png')"/>
                </div>
                <h3 class="poppins secondary-1--text fw600 my-5">No Certificates Generated yet</h3>
            </v-sheet>
            <FormPagination 
                :pageCount="pageCount" 
                :page="page"
                :paginate="paginate"
                @page="(e) => {page = e, getCertificates()}" 
                @paginate="(e) => {paginate = e, page = 1, getCertificates()}"/>
        </v-card>
    </div>
</template>

<script>
import { mapState } from 'vuex';
import CertificateCard from './CertificateCard.vue';
import { mapActions, mapMutations } from 'vuex'
import SkeletonLoaderCard from '@/components/reusable/SkeletonLoaderCard.vue'

export default {
    components: {
        CertificateCard,
        SkeletonLoaderCard
    },
    data: () => ({
        pageCount: 1,
        page: 1,
        paginate: '10',
        loading: false
    }),
    computed: {
        ...mapState('usr', {
            certificates: (state) => state.certificates
        })
    },
    
    mounted() {
        this.certificatesMutation([])
        this.getCertificates()
    },

    methods: {
        ...mapActions('usr', ['getCourseCertificatesAction']),
        ...mapMutations('usr', ['certificatesMutation']),
        
        getCertificates() {
            this.loading = true
            this.getCourseCertificatesAction({
                page: this.page,
                paginate: Number(this.paginate)
            }).then(res => {
                this.pageCount = res.last_page
                this.paginate = res.per_page.toString()
                this.page = res.current_page
            }).finally(() => {
                this.loading = false
            })
        }
    }
}
</script>