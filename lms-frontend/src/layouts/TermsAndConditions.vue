<template>
    <v-dialog persistent v-model="dialog" max-width="600">
        <v-card class="pa-5 border">
            <h4 class="primary--text poppins">Terms and Conditions</h4>
            <v-sheet
                color="transparent"
                class="roboto f14 pa-3 fw500" 
                v-html="content ? $dompurifier(content) : ''"
                style="white-space: pre; text-wrap: wrap;"
            />
            <div class="d-flex flex-row align-center">
                <v-checkbox v-model="accept" />
                <label class="caption">By checking this box, you confirm that you have read and agree to our Terms and Conditions.</label>
            </div>
            <ButtonPrimary
                :label="closeButton ? 'Close' : 'Proceed'"
                class='mt-3'
                :disabled="!accept"
                @click="$emit('accept')"
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
        content: {
            type: String,
            default: ''
        },
        loading: {
            type: Boolean, 
            default: false
        },
        closeButton: {
            type: Boolean, 
            default: false
        },
    },
    data: () => ({
        accept: false
    })
}

</script>