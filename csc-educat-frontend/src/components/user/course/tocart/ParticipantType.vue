<template>
    <v-card class="overflow-x-hidden pa-5">
        <section class="d-flex flex-wrap justify-space-between align-center mb-5">
            <section class="primary-font primary--text fw600 f17">
                Type of Participant
            </section>
            <section class="text-right">
                <v-btn icon @click="$emit('close')">
                    <v-icon small>
                        mdi-close
                    </v-icon>
                </v-btn>
            </section>
        </section>

        <FormSelectObject
            :value.sync="type_of_participant"
            :items="type_of_participants"
        />

        <div class="error--text f11" v-if="type_of_participant === ''">Please pick the type of participant from the options.</div>

        <alert 
            v-if="error"
            :show = "true"
            :text = "error"
            :type = "'error'"
        />

        <v-row dense class="mt-5">
            <v-col>
                <v-btn block dense text @click="$emit('back')" class="poppins">
                    Back
                </v-btn>
            </v-col>
            <v-col>
                <v-btn primary block dense color="primary" :disabled="type_of_participant === ''" class="poppins"
                    v-if="(course.is_free || course.price <= 0) && !['', 'regular_payee_hrm'].includes(type_of_participant)" @click="() => { $emit('setParticipantType', type_of_participant), $emit('enroll')}"
                    :loading="loading">
                    Enroll
                </v-btn>
                <!-- <v-btn v-else-if="paymentType === 'online_portal' && type_of_participant !== 'regular_payee_hrm'" primary block dense color="primary" :disabled="type_of_participant === ''" class="poppins"
                    @click="() => { $emit('setParticipantType', type_of_participant), $emit('proceedToOnlinePortal')}" :loading="loading">
                    Proceed to Payment Portal
                </v-btn> -->
                <v-btn v-else primary block dense color="primary" :disabled="type_of_participant === ''" class="poppins"
                    @click="() => { $emit('setParticipantType', type_of_participant), $emit('continue')}">
                    Continue
                </v-btn>
            </v-col>
        </v-row>
    </v-card>
</template>

<script>

export default {
    props: {
        course: {
            default: null,
            type: Object || null
        },
        loading: {
            default: false,
            type: Boolean
        },
        paymentType: {
            default: '',
            type: Boolean,
        },
        error: {
            default: null,
            type: String || null,
        }
    },
    data: () => ({
        type_of_participant: '',
        type_of_participants: [
            { text: '-', value: '' },
            { text: 'INTERNAL EMPLOYEE', value: 'internal_employee' },
            { text: 'REGULAR PAYEE', value: 'regular_payee' },
            { text: 'REGULAR PAYEE (Partner Awardee)', value: 'regular_payee_hrm' },
        ]
    }),
}
</script>