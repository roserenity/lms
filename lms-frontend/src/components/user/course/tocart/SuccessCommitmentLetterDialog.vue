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
               <div class="my-2">
                Your registration for the course <b>{{ course.title }}</b> will proceed temporarily. Please ensure that payment is settled to generate your COC.
               </div>
                
               <div class="my-2">
                    Please review the Payment Reminders to guide you on completing the payment process.
               </div>
               <v-btn small outlined dense color="primary" @click="$emit('close')">
                   Continue
               </v-btn>
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
        course: {
            default: null,
            type: Object || null
        },
   }
}
</script>