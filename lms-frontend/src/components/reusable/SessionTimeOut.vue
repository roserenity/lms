<template>
    <v-dialog persistent v-model="modal" max-width="350">
        <v-card class="pa-5">
            <h3 class="primary--text poppins">Session Timeout</h3>
            <FormLabel :label="`You're being timed out due to inactivity. Please choose to stay signed in to log off. Otherwise, you will be logged off automatically.`" />
            <v-divider class="my-3" />
            <section class="d-flex flex-row justify-end align-center">
                <v-btn dense small outlined color="secondary-2" class="mr-1" @click="logout()" :loading="loading">Log off</v-btn>
                <v-btn dense small outlined color="primary" @click="modal = false, resetSessionTimer()">Stay Signed In ({{ counter }})</v-btn>
            </section>
        </v-card>
    </v-dialog>
</template>

<script>
import { defineComponent, onMounted, ref } from '@vue/composition-api'
import { mapActions, mapMutations } from 'vuex';

export default defineComponent({
    data: () => ({
        modal: false,
        sessionTimeoutDuration: 900000, //in milliseconds
        sessionTimer: null,
        lastActivityTime:  Date.now(),
        loading: false,
        counter: 15,
        intervalTimer: null
    }),
    methods: {
        ...mapActions(['logoutAction']),
        ...mapMutations(['signoutMutation']),

        logout() {
            this.loading = true
            this.logoutAction().catch(() => this.signoutMutation())
        },

        logoutUser(){
            this.modal = true;
        },
        resetSessionTimer(){
            clearTimeout(this.sessionTimer);
            this.sessionTimer = setTimeout(this.logoutUser, this.sessionTimeoutDuration);
        },
        onUserActivity(){
            this.lastActivityTime = Date.now();
            this.resetSessionTimer();
        }
    },
    mounted() {
        document.addEventListener("mousemove", this.onUserActivity);
        document.addEventListener("keypress", this.onUserActivity);
        this.resetSessionTimer()
    },
    watch:  {
        modal(val) {
            let _count = 15
            if(val) {
                this.intervalTimer = setInterval(() => {
                    this.counter = _count--;

                    if (_count < 1) {
                        clearInterval(this.intervalTimer);
                        this.loading = false
                        this.logout()
                    }
                    
                }, 1000);
            } else  {
                this.counter = 15
                clearInterval(this.intervalTimer)
            }
        }
    }
})
</script>