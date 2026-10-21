<template>
    <section>
        <v-card class="custom-border border pa-5">
            <div class="d-flex flex-row justify-space-between mb-3">
                <section class="f12 d-flex flex-row align-center fw500">
                    <v-switch
                        v-model="form.show"
                        hide-details
                        dense
                        class="mt-0 pr-0"
                        @change="changeShow"
                    ></v-switch>
                    Show
                </section>
                <div>
                    <ButtonPrimary 
                        @click="previewDialog = true"
                        :label="'Preview'"
                        :outlined="true"
                        class="mr-1"
                    />
                    <ButtonPrimary 
                        @click="documents ? updateTerms() : createTerms()"
                        :label="'Update terms'"
                        :loading="saveLoading"
                    />
                </div>
            </div>
            <TermsAndConditions :dialog="previewDialog" :content="form.content" @accept="previewDialog = false" :closeButton="true"/>
            <LinearProgress v-if="loading" class="mb-3"/>
            <editor
                v-model="form.content"
                class="tiny"
                :api-key="mce_key"
                :init="{
                    height: 400,
                    menubar: false,
                    plugins: [
                        'advlist autolink lists link image charmap print preview anchor searchreplace',
                        'insertdatetime media paste help wordcount',
                    ],
                    toolbar:
                        'formatselect | bold italic backcolor | \
                        alignleft aligncenter alignright alignjustify | \
                        bullist numlist outdent indent | removeformat | help',
                    content_style:
                    'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }',
                }"
                />
        </v-card>
        
        <div class="my-10 secondary--text poppins f12 font-italic text-capitalize" v-if="documents">
            Saved {{ getUpdatedAt }} 
        </div>
    </section>
</template>

<script>
import TermsAndConditions from "@/layouts/TermsAndConditions.vue";
import Editor from "@tinymce/tinymce-vue";
import { mapMutations, mapActions, mapState } from 'vuex'

export default {
    components: {
        editor: Editor,
        TermsAndConditions
    },
    mounted() {
      this.$nextTick(() => {
        setTimeout(() => {
          this.showMce = true;
        }, 2000);
      });

      this.loading = true
      this.getTerms()
    },
    computed: {
        ...mapState('admin', {
            documents: (state) => state.documents
        }),
        
        getUpdatedAt(){
            this
            return this.$dateFormat.calendar(this.documents.updated_at)
        },
    },
    methods: {
        ...mapActions('admin', [
            'getDocumentAction', 'updateDocumentAction', 'createDocumentAction'
        ]),
        ...mapMutations(['alertMutation']),

        changeShow(){
            this.loading = true
            this.updateTerms()
        }, 

        updateTerms(){
            if(!this.saveLoading) {
                this.saveLoading = true
                this.updateDocumentAction({...this.form, _method: 'PUT'}).then(() => {
                    this.saveLoading = false
                    this.alertMutation({
                        show: true,
                        text: `Successfully updated`,
                        type: "success"
                    })
                    this.getTerms()
                }).catch(e => {
                    console.log(e)
                    this.saveLoading = false
                    this.alertMutation({
                        show: true,
                        text: `Something went wrong.`,
                        type: "error"
                    })
                })
            }
        },

        createTerms(){
            if(!this.saveLoading) {
                this.saveLoading = true
                this.createDocumentAction(this.form).then(() => {
                    this.saveLoading = false
                    this.loading = false
                    this.alertMutation({
                        show: true,
                        text: `Successfully updated`,
                        type: "success"
                    })
                }).catch(e => {
                    console.log(e)
                    this.saveLoading = false
                    this.loading = false
                    this.alertMutation({
                        show: true,
                        text: `Something went wrong.`,
                        type: "error"
                    })
                })
            }
        },

        getTerms(){
            this.getDocumentAction().then(res => {
                this.loading = false

                if(res) {
                    this.form.id = res.id
                    this.form.show = Boolean(res.show)
                    this.form.content = res.content
                }
            }).catch(e => {
                console.log(e)
                this.loading = false
                this.alertMutation({
                    show: true,
                    text: `Something went wrong.`,
                    type: "error"
                })
            })
        },
        
    },
    data: () => ({
      showMce: false,
      loading: false,
      saveLoading: false,
      mce_key: `${process.env.VUE_APP_TINYMCE_KEY}`,
      form: {
        show: false,
        content: ''
      },
      previewDialog: false
    }),
}

</script>