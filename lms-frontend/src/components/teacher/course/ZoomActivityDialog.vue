<template>
    <v-dialog persistent v-model="dialog" max-width="530">
        <v-card rounded="lg" class="pa-5">
            <div class="poppins f16 fw600 d-flex flex-row justify-space-between align-center">
                <div class="primary-text">
                    {{ selected_zoom_meeting_attendee?.user.full_name }}
                </div>
                <v-btn icon small @click="$emit('close')">
                    <v-icon size="18">mdi-close</v-icon>
                </v-btn>
            </div>
            <v-divider class="mt-3"/>
            <LinearProgress v-if="loading"/>

            <div class=" mt-3"></div>
            <label class="caption">SCORE</label>
            <v-text-field 
                outlined 
                dense 
                hide-details
                min="0"
                type="number"
                v-model="form.score"
                class="secondary-1--text f12 fw600 poppins general-custom-field mb-1"
            />
            <FormTextAreaOptional
                :label="'COMMENT'"
                :value.sync="form.comment"/>

            <v-card-actions class="d-flex align-center justify-end">
                <v-btn class="text-capitalize poppins" text @click="$emit('close')" :disabled="!saveLoading">Cancel</v-btn>
                <v-btn class="text-capitalize poppins" color="primary" @click="save" :loading="saveLoading">Save</v-btn>
            </v-card-actions>
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
        selected_zoom_meeting_id: {
            type: null || Number,
            default: null
        },
        selected_zoom_meeting_attendee: {
            type: Object || null,
            default: null
        }
    },

    watch: {
        selected_zoom_meeting_attendee(val) {
            if(val) {
                this.loading = true
                this.getMeetingParticipantAction({zoom_meeting_id: this.selected_zoom_meeting_id, user_id: val.id}).then(res => {
                    if(res) {
                        if(res.zoom_meeting_activity) {
                            this.form = {
                                score: res.zoom_meeting_activity.score,
                                comment: res.zoom_meeting_activity.comment,
                            }
                        }
                    }
                    this.loading = false
                }).catch(() => {
                    this.loading = false
                })
            } else {
                this.form = {
                    score: '',
                    comment: ''
                }
            }
        }
    },

    methods: {
        ...mapActions('instructor', ['getMeetingParticipantAction', 'postMeetingParticipantScoreAndCommentAction']),
        ...mapMutations(['alertMutation']),

        save(){
            this.saveLoading = true
            this.postMeetingParticipantScoreAndCommentAction({zoom_meeting_id: this.selected_zoom_meeting_id, user_id: this.selected_zoom_meeting_attendee.user_id, score: this.form.score ? Number(this.form.score) : null, comment: this.form.comment}).then(() => {
                this.saveLoading = false
                this.alertMutation({
                    show: true,
                    text: "Successfully updated participant activity",
                    type: "success"
                })
                this.$emit('close')
            }).catch(() => {
                this.saveLoading = false
                this.alertMutation({
                    show: true,
                    text: "Something went wrong.",
                    type: "error"
                })
                this.$emit('close')
            })
        }
    },

    data: () => ({
        form: {
            score: '',
            comment: ''
        },
        loading: false,
        saveLoading: false
    })
};
</script>

<style scoped>
.centered-input >>> input {
    text-align: center;
}
</style>
  