<template>
    <v-dialog
        persistent
        v-model="privacy"
        max-width="400">
        <v-card class="text-center pb-5">
            <section class="d-flex flex-row">
                <v-spacer/>
                <v-btn icon dense class="float-right" @click="$emit('close')">
                    <v-icon>
                        mdi-close
                    </v-icon>
                </v-btn>
            </section>
            <v-card-title class="primary-font l-primary--text fw600 d-flex align-center justify-center text-center f22">DATA PRIVACY STATEMENT</v-card-title>
            <v-card-subtitle class="py-3 f16 fw500 mt-1">
                Aster Academy recognizes
                its responsibilities under applicable data privacy laws, with respect to the data it collects, record, organize, update, use, consolidate or
                destruct from the registered participants. The personal data obtained from this Form will
                be reflected in any of the course materials and will only be accessed by Aster Academy
                authorized personnel.
            </v-card-subtitle>
        </v-card>
    </v-dialog>
</template>

<script>
export default {
    props: {
        privacy: Boolean,
    }
}
</script>