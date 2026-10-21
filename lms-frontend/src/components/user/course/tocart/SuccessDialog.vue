<template>
     <v-dialog v-model="dialog" persistent width="auto">
            <v-card width="370">
                <section class="d-flex justify-end">
                    <v-btn icon @click="$emit('close')">
                        <v-icon>
                            mdi-close
                        </v-icon>
                    </v-btn>
                </section>
                Processing may take up to 3 working days.
                    
                <v-btn small outlined dense color="primary" @click="$emit('close')">
                    Continue
                </v-btn>
            </v-card>
    </v-dialog>
</template>

<script>
export default {
    props: ["dialog"]
}
</script>