<template>
    <v-dialog transition="slide-y-transition" v-model="dialog" fullscreen hide-overlay>
        <v-overlay>
            <v-card color="transparent" flat min-width="320" max-width="500" max-height="90vh">
                <div class="d-flex flex-row">
                    <v-spacer />
                    <v-btn class="ml-auto" icon @click="$emit('close')">
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </div>
                <v-img class="fade" :src="imageUrl" contain ></v-img>
            </v-card>
        </v-overlay>
    </v-dialog>
  </template>
  
<script>
export default {
    props: {
        imageUrl: {
            type: String,
            default: ''
        },
        dialog: {
            type: Boolean,
            default: false
        }
    }
};
</script>
  