<template>
    <v-container class="fade">
        <v-row class="primary-font mb-10 mt-3">
            <v-col cols="12" md="6" class="py-3 f14 d-flex flex-column justify-center">
                <span class="f20 fw600">Frequently Asked Questions</span>
                <v-divider class="l-primary my-5" width="70%" />
                <p>
                    Hello Learners!
                </p>
                <p>
                    We got your initial questions covered. Kindly read the answers provided to list of frequently asked questions below. If in case we failed to foresee your question, rest assured that we will include them to the list from time to time. Help your self on easily getting your question answered by pressing “ctrl + F” and supply the keyword.
                </p>          
            </v-col>
            <v-col cols="12" md="6" class="d-flex align-center justify-center">
                <v-img :src="require('@/assets/images/landing/faq.png')" contain height="300"/>
            </v-col>
        </v-row>

        <section v-for="(section,i) in faq" :key="i" class="primary-font mb-5">
            <div class="l-primary--text fw600 f18 mb-3">
                {{ section.part }}
            </div>
            <v-expansion-panels flat dense> 
                <v-expansion-panel 
                        v-for="(item,j) in section.items"
                        :key="j"
                    >
                    <v-expansion-panel-header class="f17 fw500">
                        {{ item.title }}
                    </v-expansion-panel-header>
                    <v-expansion-panel-content class="px-6 f14">
                        <v-sheet v-html="item.text" ></v-sheet>
                            <ol v-if="item.numberList.length > 0 && item.numberList[0].text !== ''">
                                <li v-for="(listItem, k) in item.numberList" :key="k">
                                    <span v-html="listItem.text"></span>
                                    <ol type="a">
                                        <li v-for="(list, l) in listItem.list" :key="l" v-html="list"></li>
                                    </ol>
                                </li>
                            </ol>
                            <ol v-else type="a">
                                <span v-for="(listItem, k) in item.numberList" :key="k">
                                    <li v-for="(list, l) in listItem.list" :key="l" v-html="list"></li>
                                </span>
                            </ol>
                    </v-expansion-panel-content>
                </v-expansion-panel>
            </v-expansion-panels>
        </section>
        
    </v-container>
</template>

<script>
import faq from '@/constants/faq'


export default {
    name: 'faq',
    data: () => ({
        faq
    }),
}
</script>