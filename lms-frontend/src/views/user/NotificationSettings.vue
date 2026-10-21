<template>
    <section>
        <v-sheet min-height="85vh" max-height="85vh" class="d-flex align-stretch flex-column">
            <div class="ma-5">
                <h3 class="poppins">Notification</h3>
            </div>
            <v-container>
                <v-row class="mx-5">
                    <v-col v-for="(item, i) in headers" :key="i" :lg="item.lg"  class="text-center">
                        {{ item.text }}
                    </v-col>
                </v-row>
                <v-row v-for="(item, i) in settings" :key="i"  class="mx-5">
                    <v-col lg="6">
                        <div class="f14 fw600 poppins">
                            {{ item.text }}
                        </div>
                        <div class="secondary-4--text f12">
                            {{ item.subText }}
                        </div>
                    </v-col>
                    <v-col class="d-flex justify-center">
                        <v-switch
                            v-model="item.browser"/>
                    </v-col>
                    <v-col class="d-flex justify-center">
                        <v-switch
                            v-model="item.email"/>
                    </v-col>
                </v-row>
            </v-container>
            <v-divider class="mt-auto"/>
            <div class="ma-5">
                <v-btn color="primary" class="poppins text-capitalize fw600">Save changes</v-btn>
                <v-btn color="secondary-4" text class="poppins text-capitalize fw600">Cancel</v-btn>
            </div>
        </v-sheet>
    </section>
  </template>

<script>
    export default {
        data: () => ({
            headers: [
                {
                    text: '',
                    lg: 6
                },
                {
                    text: 'Browser',
                    lg: 3
                },
                {
                    text: 'Email',
                    lg: 3
                },
            ],
            settings: [
                {
                    text: 'Assessment',
                    subText: 'To Do and upcoming assessments',
                    browser: false,
                    email: false,
                },
                {
                    text: 'Announcement',
                    subText: 'Replies on my posts',
                    browser: false,
                    email: false,
                },
                {
                    text: 'Announcement',
                    subText: 'Posts from my instructors',
                    browser: false,
                    email: false,
                },
                {
                    text: 'Inbox',
                    subText: 'New messages',
                    browser: false,
                    email: false,
                },
            ]
        })
    }
</script>