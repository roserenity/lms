<template>
    <v-dialog persistent width="500" v-model="dialog">
        <v-card class="overflow-x-hidden pa-5">
            <section class="d-flex flex-row align-start justify-space-between align-center mb-5">
                <section class="primary-font primary--text fw600 f17">
                    Would you like to receive a Physical Copy or a Soft Copy of your Official Receipt?
                </section>
                <section class="text-right">
                    <v-btn icon @click="$emit('close')">
                        <v-icon small>
                            mdi-close
                        </v-icon>
                    </v-btn>
                </section>
            </section>
            <v-row dense v-if="!choice">
                <v-col>
                    <v-btn dense block text class="poppins" @click="$emit('proceed', { deliveryOption: null , deliveryAddress: null })">
                        NO
                    </v-btn>
                </v-col>
                <v-col>
                    <v-btn primary block dense color="primary" class="poppins" @click="choice = 'yes'">
                        YES
                    </v-btn>
                </v-col>
            </v-row>
            <div v-if="choice">
                <i class="f12">
                    <div class="mt-3">
                        Please note that Physical Copies of the Official Receipt will be delivered via our courier partner. Regardless of whether you choose Pick-Up or Delivery, you will be responsible for the corresponding courier/shipping fee.
                    </div>
                </i>
                
                <div class="d-flex flex-row justify-center align-center mt-3 fw600">
                    <v-chip class="d-flex flex-column align-center pa-5 ma-1" outlined :color="deliveryOption === 'email' ? 'primary' : ''" @click="deliveryOption = 'email'">
                        <v-icon x-large class="mb-2 mr-1">
                            mdi-send-outline
                        </v-icon>
                        Soft Copy
                    </v-chip>
                    <v-chip class="d-flex flex-column align-center pa-5 ma-1" outlined :color="deliveryOption === 'pick_up' ? 'primary' : ''" @click="deliveryOption = 'pick_up'">
                        <v-icon x-large class="mb-2 mr-1">
                            mdi-package-variant-closed
                        </v-icon>
                        Pick Up
                    </v-chip>
                    <v-chip class="d-flex flex-column align-center pa-5 ma-1" outlined :color="deliveryOption === 'courier' ? 'primary' : ''" @click="deliveryOption = 'courier'">
                        <v-icon x-large class="mb-2 mr-1">
                            mdi-truck-fast-outline
                        </v-icon>
                        Courier
                    </v-chip>
                </div>
                
                <FormTextFieldRequired
                    class="mt-3"
                    v-if="deliveryOption"
                    :label="deliveryOption === 'pick_up' ? 'Please enter the exact address of your preferred courier branch for pick-up:' : (deliveryOption === 'courier' ? 'Please enter the exact delivery address where you would like the Official Receipt to be sent:' : 'Please enter a valid email address, as the Official Receipt will be sent to this email.')"
                    :value.sync="deliveryAddress"
                    :error="errors && errors.delivery_address"
                />

                <v-row dense class="mt-3">
                    <v-col>
                        <v-btn dense block text class="poppins" @click="() => { deliveryOption = null , deliveryAddress = null, choice = null }">
                            Back
                        </v-btn>
                    </v-col>
                    <v-col>
                        <v-btn primary block dense color="primary" class="poppins" :loading="loading" :disabled="!(deliveryOption && deliveryAddress)" @click="() =>$emit('proceed', { deliveryOption: deliveryOption , deliveryAddress: deliveryAddress })">
                            Proceed
                        </v-btn>
                    </v-col>
                </v-row>
            </div>
        </v-card>
    </v-dialog>
</template>

<script>

export default {
    props: {
        loading: {
            type: Boolean,
            default: false
        },
        dialog: {
            type: Boolean,
            default: false
        },
        errors: {
            type: Array || null,
            default: null
        }
    },
    data: () => ({
        choice: null,
        deliveryOption: null,
        deliveryAddress: null
    })
}
</script>