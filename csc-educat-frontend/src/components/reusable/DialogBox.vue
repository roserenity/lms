<template>
    <v-dialog persistent v-model="dialog" max-width="400">
        <v-card>
            <!-- <section>
                <v-spacer />
                <v-btn icon @click="$emit('close')" class="ml-auto">
                    <v-icon>
                        mdi-close
                    </v-icon>
                </v-btn>
            </section> -->
            <section class="d-flex flex-column align-center pt-5">
                <h4 class="primary--text poppins fw600 mb-3">{{ title }}</h4>
                <div class="poppins f12 secondary--text">{{ subtext }}</div>
            </section>

            <!-- buttons -->

            <section class="secondary--text poppins d-flex flex-row pa-3">
                <v-btn text class="poppins f13 fw500 text-capitalize" :loading="loading_btn1" @click="$emit('btn_1')">
                    {{ btn1 }}
                </v-btn>
                <v-spacer></v-spacer>
                <ButtonPrimary 
                    @click="$emit('btn_2')"
                    :label="btn2"
                    :loading="loading_btn2"
                />
            </section>
        </v-card>
    </v-dialog>
</template>

<script>
export default {
    props: {
        dialog: Boolean,
        title: String,
        subtext: String,
        btn1: String,
        btn2: String,
        loading_btn1: {
            type: Boolean,
            default: false
        },
        loading_btn2: {
            type: Boolean,
            default: false
        }
    }
}
</script>