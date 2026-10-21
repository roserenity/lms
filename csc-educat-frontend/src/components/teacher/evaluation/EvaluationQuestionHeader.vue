<template>
        <section class="d-flex flex-wrap ">
            <div class="col-xl-6 col-lg-8">
                <FormTextAreaRequired 
                    :label="'QUESTION *'"
                    :value.sync="evaluation.question"
                    :disabled="$isReadOnly.isReadOnly(status, permission)"
                />
            </div>
            <div class="col-xl-6 col-lg-4">
                <label class="caption mb-2">TYPE *</label>
                <!-- v-model="item.type_of_question" -->
                <v-select
                    dense
                    outlined
                    hide-details
                    :items="survey_question_types"
                    v-model="evaluation.type_of_question"
                    class="general-custom-field secondary-1--text f14 fw600 roboto"
                    @change="$emit('setSurveyType', {id: evaluation.id, type: $event, panel_key: panel_key})"
                    :disabled="$isReadOnly.isReadOnly(status, permission)"
                >
                    <template slot="item" slot-scope="data">
                        <v-list-item-icon class="roboto f14 fw500" >
                                <v-icon>
                                    {{ data.item.icon }}
                                </v-icon>
                            </v-list-item-icon>
                        <v-list-item-content>
                            <v-list-item-title class="roboto f14 fw500" >
                                <span class="text-capitalize">{{ data.item.text }}</span>
                            </v-list-item-title>
                        </v-list-item-content>
                    </template>
                    <template slot="selection" slot-scope="data" >
                        <div class="roboto f14 fw500 my-1">
                            <span class="text-capitalize">{{ data.item.text }}</span>
                        </div>
                    </template>
                </v-select>
            </div>
        </section>
</template>

<script>
import { survey_question_types } from "@/shared/questionTypes.js";

export default {
    props: ['evaluation', 'panel_key', "status", "permission"],
    data: () => ({
        loading: false,
        showMce: true,
        survey_question_types,
        survey_question_type: null
    }),
    methods: {
        // updateEvaluation(e) {
        //     this.question.question = e
        // }
    },
    mounted() {
    //   this.$nextTick(() => {
    //     setTimeout(() => {
    //       this.showMce = true;
    //     }, 2000);
    //   });
    },
}
</script>