<template>
    <v-card class="custom-border border pa-5 fade">
        <section class="d-flex flex-row mb-5 ml-2">
            <section>
                <h4 class="poppins secondary-1--text">
                    Word Restrictions
                </h4>   
            </section>
        </section>
        <LinearProgress v-if="loading"/>
        
        <h5 v-if="form.length === 0 && !loading" class="text-center poppins secondary-1--text fw600 my-5">No restricted words found.</h5>

        <section v-for="(item, i) in form" :key="i" class="d-flex flex-row">
            <FormTextFieldOptional
                :class_="'f12'"
                :value.sync="item.word"
                class="flex-grow-1"
            />
            <v-btn icon :disabled="deleteLoading" :loading="deleteLoading && selected === item.id" @click="(e) => { item.id === -1 ? (selected = -1, deleteWordLocal(i)) : selected = item.id, deleteWord() }">
                <v-icon size="18">mdi-trash-can-outline</v-icon>
            </v-btn>
        </section>
        <section class="d-flex justify-space-between mt-3">
            <ButtonPrimary
                @click="addNewWord"
                :label="'Add New Word'"
                :icon="'mdi-plus'"
                class="ml-1 f12"
            />
            <section class="d-flex flex-row" v-if="form.length !== 0" >
                <!-- <ButtonPrimary
                    @click=""
                    :label="'Save'"
                    class="ml-1 f12"
                /> -->
                <ButtonPrimary
                    @click="save"
                    :label="'Save'"
                    class="ml-1 f12"
                    :loading="saveLoading"
                />
            </section>
        </section>                      
        <FormPagination 
            v-if="banned_words.length !== 0 && !loading"
            :pageCount="pageCount" 
            :page="page"
            :paginate="paginationPaginate"
            @page="onPageChange" 
            @paginate="onPaginateChange"/>
    </v-card>
</template>


<script>
import { mapActions, mapState, mapMutations } from 'vuex';
import searchAndPaginateMixin from '@/mixins/searchAndPaginateMixin';

export default {
    data: () => ({
        loading: false,
        saveLoading: false,
        deleteLoading: false,
        form: [],
        totalCount: 0,
        pageCount: 1,
        selected: null
    }),
    mixins: [searchAndPaginateMixin],
    methods: {
        ...mapMutations(['alertMutation']),
        ...mapActions('admin', ['getBannedWordsAction', 'saveBannedWordsAction', 'deleteBannedWordAction']),

        getData(){
            if(!this.loading) {
                this.loading = true
                this.getBannedWordsAction({ paginate: Number(this.paginate), page: this.page, search: this.search }).then((res) => {
                    this.pageCount = res.last_page
                    this.paginate = String(res.per_page)
                    this.page = res.current_page
                    this.totalCount = res.total
                }).finally(()  => {  this.loading = false })
            }
        },

        save(){
            this.saveLoading = true
            this.saveBannedWordsAction({ words: this.form }).then(() => {
                this.saveLoading = false
                this.alertMutation({
                    show: true,
                    text: 'Banned words has been updated',
                    type: "success"
                })
                this.getData()
            }).catch(() => {
                this.saveLoading = false
                this.alertMutation({
                    show: true,
                    text: 'Something went wrong.',
                    type: "error"
                })
            })
        },

        deleteWord() {
            if(this.selected  !== -1) {
                this.deleteLoading = true
                this.deleteBannedWordAction(this.selected).then(() => {
                    this.deleteLoading = false
                    this.alertMutation({
                        show: true,
                        text: 'Banned word has been successfully deleted',
                        type: "success"
                    })
                    this.getData()
                }).catch(() => {
                    this.deleteLoading = false
                    this.alertMutation({
                        show: true,
                        text: 'Something went wrong.',
                        type: "error"
                    })
                })
            }
        },

        deleteWordLocal(val) {
            console.log(val)
            console.log(this.form)
            this.form.splice(val, 1)
        },

        addNewWord(){
            this.form.push({ id: -1, word: '' })
        },

    },
    computed: {
        ...mapState('admin', {
            banned_words: (state) => state.banned_words
        }),
  
        itemsPerPage(){
            return Number(this.paginate)
        },

        paginationPaginate(){
            return String(this.paginate)
        }
    },
    watch: {
        banned_words(val) {
            if(val.length > 0) {
                this.form = [...val]
            }
        }
    }
}
</script>