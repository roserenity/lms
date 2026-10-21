<template>
    <v-slide-x-reverse-transition>
        <v-card v-if="activity_log" class="pa-5 custom-border border" :class="!popup && 'mt-2'">
            <div class="d-flex flex-row align-center mb-3">
                <div class="secondary-2--text f16 mr-auto fw600">
                    Activity Log
                </div>
                
                <v-btn v-if="!popup" icon small right @click="$emit('close')">
                    <v-icon size="18">mdi-close</v-icon>
                </v-btn>
            </div>
            <div>
                <v-sheet v-for="(log, i) in logs" :key="i" class="px-3 py-2 my-2 poppins d-flex flex-row align-center " color="secondary-5" rounded>
                    <div class="f10 d-flex flex-column mr-3">
                        <span>
                            {{ log.date }}
                        </span>
                        <span>
                            {{ log.time }}
                        </span>
                    </div>
                    <v-divider inset class="ml-0"></v-divider>
                    <div class="f10 d-flex flex-column ml-3">
                        {{ log.user }}
                    </div>
                </v-sheet>
            </div>
        </v-card>
    </v-slide-x-reverse-transition>
</template>
<script>
export default {
    props: ['activity_log', 'popup'],
    data: () => ({
        logs: [
            {
                date: 'December 25, 2022',
                time: '1:37 pm',
                user: 'John Doe'
            },
            {
                date: 'December 25, 2022',
                time: '1:37 pm',
                user: 'John Doe'
            },
        ]
    })
}
</script>