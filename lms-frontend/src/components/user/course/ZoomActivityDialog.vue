<template>
    <v-dialog persistent v-model="dialog" max-width="530">
        <v-card rounded="lg" class="pa-5">
            <div class="poppins f16 fw600 d-flex flex-row justify-space-between align-center">
                <div class="primary-text">
                    {{  selected_zoom_meeting?.user.full_name }}
                </div>
                <v-btn icon small @click="$emit('close')">
                    <v-icon size="18">mdi-close</v-icon>
                </v-btn>
            </div>
            <v-divider class="mt-3"/>

            <div class=" mt-3"></div>
            <div class="f15 mb-3">
                <label class="caption">SCORE:</label> {{ selected_zoom_meeting?.zoom_meeting_activity.score }}
            </div>
            <label class="caption">COMMENT:</label>
            <p class="f15">
                {{ selected_zoom_meeting?.zoom_meeting_activity.comment }}
            </p>
        </v-card>
    </v-dialog>
</template>

<script>
import { mapMutations, mapActions } from "vuex";

export default {
    props: {
        dialog: {
            type: Boolean,
            default: false,
        },
        selected_zoom_meeting: {
            type: Object || null,
            default: null
        },
    },
};
</script>