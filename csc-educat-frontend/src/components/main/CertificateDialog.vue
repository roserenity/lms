<template>
    <v-dialog v-model="dialog"
        :width="$vuetify.breakpoint.mobile ? '80vw' : '40vw'">
        <v-card>
            <v-card-text v-if="loading" class="text-center d-flex flex-column align-center justify-center">
                <v-card-title class="poppins primary--text text-center"> Generating Certificate... </v-card-title>
                <v-card-text class="poppins secondary--text">Please wait and do not close the page.</v-card-text>
                <v-progress-linear
                    indeterminate
                    color="primary"
                    rounded
                    height="5px"
                ></v-progress-linear>
            </v-card-text>
            <section v-if="!loading" class="fw600 text-center d-flex flex-column align-center poppins justify-center success--text py-1 mx-1">
                <v-btn icon @click="$emit('close')" class="ml-auto" dense>
                    <v-icon>mdi-close</v-icon>
                </v-btn>
                <div class="container-frame">
                    <iframe 
                        class="responsive-iframe"
                        :src="`${url}`">
                    </iframe>
                </div>
            </section>
        </v-card>
    </v-dialog>
</template>

<script>
    export default {
        props: ['dialog', 'loading', 'error', 'url'],
    }
</script>

<style scoped>
    .container-frame {
    position: relative;
    overflow: hidden;
    width: 100%; /* 16:9 Aspect Ratio (divide 9 by 16 = 0.5625) */
    }

    .responsive-iframe {
    height: 500px;
    }

    @media only screen and (max-width: 768px){
    .responsive-iframe {
        height: 300px;
        width: 100%;
    }
    }

</style>