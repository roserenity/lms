<template>
    <v-dialog v-model="dialog" max-width="550" persistent eager retain-focus="false">
      <v-card>
            <v-card-title class="d-flex align-center justify-space-between">
                <h4 class="primary--text">IMPORT QUESTIONS</h4>
            </v-card-title>
            <v-card-text>
                <label class="caption">SELECT QUESTION POOL *</label>
                <v-select   
                    :items="question_pools"
                    item-value="id"
                    item-text="name"    
                    outlined
                    dense
                    class="general-custom-field roboto f14 secondary-1--text fw500 mb-5"
                    v-model="ids"
                    required
                    hide-details="auto"
                    :loading="loading"
                    multiple
                    >
                    <template slot="item" slot-scope="data">
                        <v-list-item-content>
                            <v-list-item-title class="roboto f14 fw500" >
                                {{  data.item.name }}
                                <div class="roboto f10 secondary-2--text text-uppercase">
                                    <div v-if="data.item.uploader"> creator: {{ data.item.uploader.full_name }} </div>
                                    <div v-if="data.item.updated_at"> updated {{ $dateFormat.calendar(data.item.updated_at) }} </div>
                                </div>
                            </v-list-item-title>
                        </v-list-item-content>
                    </template>
                </v-select>
            </v-card-text>
            <v-divider class=""/>
            <v-card-actions class="d-flex align-center justify-end">
                <v-btn class="text-capitalize poppins secondary-1--text" text @click="()=>{
                        $emit('close')
                    }">Cancel</v-btn>
                <v-btn :disabled="ids.length === 0" class="text-capitalize poppins" color="primary" width="100" @click="$emit('submit', ids)" :loading="submit_loading">Import</v-btn>
            </v-card-actions>
      </v-card>
    </v-dialog>
</template>

<script>
import Editor from "@tinymce/tinymce-vue";
import { mapActions, mapMutations, mapState } from 'vuex'

export default {
    props: {
        dialog: {
            default: false,
            type: Boolean
        },
        type_of_question_pool: {
            default: 'assessment',
            type: String
        },
        submit_loading:  {
            default: false,
            type: Boolean
        },
    },
    components: {
        editor: Editor,
    },
    data: () => ({
        loading: false,
        ids: []
    }),

    watch: {
        dialog(val) {
            if(val) {
                this.ids = []
                this.getQuestionPools()
            }
        }
    },


    computed: {
        ...mapState('instructor', {
            question_pools: (state) => state.question_pools
        }),
    },

    methods: {
        ...mapActions('instructor', [
            'getQuestionPoolsAction'
        ]),

        ...mapMutations(['alertMutation']),

        getQuestionPools(){
            this.loading = true
            this.getQuestionPoolsAction({ type_of_question_pool: this.type_of_question_pool }).then(() => {
                this.loading = false
            }).catch(() => {
                this.loading = false
            })
        },
    }
}
</script>