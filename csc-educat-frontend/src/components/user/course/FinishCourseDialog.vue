<template>
    <v-dialog max-width="400" v-model="dialog" class="poppins">
        <v-card class="pa-8">
            <h1 class="primary--text text-center">Congratulations!</h1>
            <h4 class="secondary-2--text text-center">You've reach the end of this course.</h4>
        </v-card>
    </v-dialog>
</template>

<script>
export default {
    props: ['dialog'],
}
</script>