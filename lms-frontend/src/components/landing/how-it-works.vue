<template>
    <v-container class="px-0 my-5">
        <v-row no-gutters>
            <v-col class="px-5" cols="12" md="4">
                <div class="primary-font f25 fw600">
                    How It Works
                </div>
                <div class="f14">
                    Lorem ipsum dolor sit amet consectetur. Dolor auctor vel tristique arcu bibendum lacus pretium.
                </div>
                <v-list dense>
                    <v-list-item v-for="(item, i) in steps" :key="i">
                        <v-list-item-avatar>
                            <v-avatar class="primary-fade">
                                <v-icon 
                                    color="l-primary"
                                    v-text="item.icon">
                                </v-icon>
                            </v-avatar>
                        </v-list-item-avatar>
                        <v-list-item-content>
                            <v-list-item-title v-text="item.text" class="primary-font f16"></v-list-item-title>
                        </v-list-item-content>
                    </v-list-item>
                </v-list>
            </v-col>
            <v-col cols="12" md="8" xs="6">
                <v-img
                    contain
                    :src="require('../assets/images/how-it-works.png')"
                    :lazy-src="require('../assets/images/how-it-works.png')"/>
            </v-col>
        </v-row>
    </v-container>
</template>

<style scoped>
.primary-fade {
    background-color: rgba(181, 34, 16, 0.1);
}
</style>

<script>
import {steps} from '@/constants/steps'

export default {
    name: 'how-it-works',
    data: ()=>({
        steps
    })
}
</script>