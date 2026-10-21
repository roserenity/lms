<template>
    <v-dialog persistent v-model="dialog" max-width="600">
        <v-card class="pa-5 border">
            <h4 class="primary--text poppins">Profile Update Requested</h4>
            <div class="d-flex flex-row align-center f14 pa-3 fw500">
                Your profile information maybe incomplete or outdated. An administrator has requested that you review and update your details to ensure your account remains accurate and active.
            </div>
            <ButtonPrimary
                :label="'Update Now'"
                class='mt-3'
                @click="$emit('proceed')"
                :loading="loading"
                :block="true"
            />
        </v-card>
    </v-dialog>
</template>


<script>

export default {
    props: {
        dialog: {
            type: Boolean, 
            default: false
        },
        loading: {
            type: Boolean, 
            default: false
        },
    },
}

</script>