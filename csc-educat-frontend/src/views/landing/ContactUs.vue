<template>
    <v-container class="mb-10 fade">
        <v-row align="center">
            <v-col cols="12" md="6" class=" px-8 f14 d-flex flex-column justify-center">
                  <Contact />
            </v-col>
            <v-col cols="12" md="6" class="d-flex flex-column primary-font px-8">
                    <v-card class="border box-shadow-rd">
                        <iframe src="https://www.google.com/maps?q=Greenwich&output=embed" width="100%" height="300" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade" class="mb-5"></iframe>

                        <v-list dense>
                            <v-list-item v-for="(item, i) in contacts" :key="i">
                                <v-list-item-icon>
                                    <v-icon color="l-primary">
                                        {{ item.icon }}
                                    </v-icon>
                                </v-list-item-icon>
                                <v-list-item-subtitle v-if="item.icon === 'mdi-email'">
                                    <a href="mailto:hello@example.com" class=" l-primary--text">{{ item.text }}</a>
                                </v-list-item-subtitle>
                                <v-list-item-subtitle v-else>
                                    {{ item.text }}
                                </v-list-item-subtitle>
                            </v-list-item>
                        </v-list>
                    </v-card>
            </v-col>
        </v-row>
    </v-container>
</template>

<script>
import Contact from '@/components/landing/forms/Contact.vue';

export default {
    components: {
        Contact
    },
    data: () => ({
        contacts: [
            {
                icon: 'mdi-map-marker',
                text: '123 Learning Avenue, Example City'
            },
            {
                icon: 'mdi-phone',
                text: '(000) 000-0000'
            },
            {
                icon: 'mdi-phone',
                text: '(000) 000-0001'
            },
            {
                icon: 'mdi-email',
                text: 'hello@example.com'
            },
            // {
            //     icon: 'mdi-web',
            //     text: '#'
            // },
        ]
    })
}
</script>