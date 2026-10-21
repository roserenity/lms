<template>
    <div>
        <section class="d-flex flex-row justify-space-between mb-2 ml-2">
            <section>
                <h4 class="poppins secondary-1--text">
                    Forum
                </h4>   
                <FormLabel :label="`Posted from ${$dateFormat.MMYY(filter.posted_from)} to ${$dateFormat.MMYY(filter.posted_to)}`" class="mb-3"/>
            </section>
            <section class="ml-auto">
                <FilterMenu
                    :initialFilter="filter"
                    @resetFilters="resetFilters"
                    @applyFilters="() => { page = 1, updateQuery() }"
                >
                <section>
                    
                    <FormSelectObject
                        :label="'TOPIC CATEGORY'"
                        :items="topic_categories"
                        :value.sync="filter.topic_category"
                        :required="true"
                        :error="error && error.topic_category"
                        :class_="'f14'"
                    />
                    <label class="caption">COURSE</label>
                    <v-select   
                        :items="courses"
                        item-value="id"
                        item-text="title"    
                        outlined
                        dense
                        class="general-custom-field roboto f14 secondary-1--text fw500 mb-3"
                        v-model="filter.course_id"
                        multiple
                        hide-details="auto"
                    >
                    </v-select>
                    <FormLabel class="mb-1" :label="'POSTING DATE (YYYY-MM)'" />
                    <label class="caption">FROM:</label>
                    <FormMonthPicker 
                        :val="filter.posted_from"
                        :class_="'col-12'"
                        :max="maxDate"
                        @setMonth="(e) => { selectedPostFromMonth = e }"
                        @setYear="(e) => { selectedPostFromYear = e }"
                        @apply="applyDate()"/>
                    <FormLabel :label="'TO:'" class="mt-3"/>
                    <FormMonthPicker 
                        :val="filter.posted_to"
                        :class_="'col-12'"
                        :min="minDate"
                        @setMonth="(e) => { selectedPostToMonth = e }"
                        @setYear="(e) => { selectedPostToYear = e}"
                        @apply="applyDate()"/>
                </section>
                </FilterMenu>
                
                <ButtonPrimary
                    v-if="role === 'admin'"
                    @click="$router.push({ name: 'Admin Forum Restrictions', params: { search: '', page: 1, paginate: 10 } })"
                    :label="'Word Restrictions'"
                    :icon="'mdi-message-off-outline'"
                    :outlined="true"
                    class="ml-1 f12"
                />

                <ButtonPrimary
                    v-if="role === 'user'"
                    @click="() => { pendingPostsDialog = true }"
                    :label="'View Pending Posts'"
                    :icon="'mdi-clock-outline'"
                    :outlined="true"
                    class="ml-1 f12"
                />
                <ButtonPrimary
                    v-else
                    @click="() => { forApprovalPostsDialog = true }"
                    :label="'View For Approval Posts'"
                    :icon="'mdi-clock-outline'"
                    :outlined="true"
                    class="ml-1 f12"
                />

                <ButtonPrimary
                    @click="() => { selected_forum_post_to_update = null , showPostDialog = true }"
                    :label="'Post Topic'"
                    :icon="'mdi-plus'"
                    class="ml-1 f12"
                />
            </section>
        </section>
        
        <LinearProgress v-if="loading"/>

        <v-card v-if="forum_posts.length == 0 && !loading" class="border d-flex flex-column align-center justify-center pb-10 text-center" height="60vh">
            <div class="">
                <v-img 
                max-width="300" 
                contain
                :src="require('@/assets/default/empty_drafts.png')" />
            </div>
            <h3 class="poppins secondary-1--text fw600 my-5 text-center">No posts available yet</h3>
        </v-card>

        <v-row dense v-else>
            <v-col
                :cols="(!$vuetify.breakpoint.smAndDown && selected_forum_post_id) ? '4' : '12'">
                <section :style="(!$vuetify.breakpoint.smAndDown && selected_forum_post_id) ? 'overflow-y: auto;' : ''">
                    <div
                        v-for="item in forum_posts"
                        :key="item.id"
                        @click="() => { 
                            selected_forum_post = item, 
                            selected_forum_post_id = item.id, 
                            getPost() 
                            $router.push({ query: { ...$route.query, forum_post_comment_id: null, forum_post_id: item.id } })
                        }"
                        class="mx-1">
                            <Post 
                                :id="`comment-${item.id}`"
                                :item="item"
                                @delete="(id) => { selected_forum_post_to_delete = id, deleteDialog = true }"
                                @update="(item) => { selected_forum_post_to_update = item, showPostDialog = true }"
                                @getPost="() => { getPost() }"
                                :role="role"
                                @getData="getData()"
                                @removeSelectedForumPostId="selected_forum_post_id = null"
                                :showFull="false"
                            />
                    </div>
                </section>
                          
                <FormPagination 
                    v-if="forum_posts.length==0 && !loading"
                    :pageCount="pageCount" 
                    :page="page"
                    :paginate="paginationPaginate"
                    @page="onPageChange" 
                    @paginate="onPaginateChange"/>
            </v-col>
            <v-col v-if="$vuetify.breakpoint.smAndDown ? selected_forum_post_id : selected_forum_post_id" 
                :cols="!$vuetify.breakpoint.smAndDown ? '8' : '12'">      
                <LinearProgress v-if="viewLoading"/>
                <Post 
                    :item="selected_forum_post"
                    :showComment="true"
                    @getPost="() => { getPost() }"
                    :selected_forum_post_id="selected_forum_post_id"
                    :selected_forum_post_comment_id="selected_forum_post_comment_id"
                    @delete="(id) => { selected_forum_post_to_delete = id, deleteDialog = true }"
                    @update="(item) => { selected_forum_post_to_update = item, showPostDialog = true }"
                    :role="role"
                    @getData="getData()"
                    @removeSelectedForumPostId="selected_forum_post_id = null"
                    :showFull="true"
                />
            </v-col>
        </v-row>
        <PostDialog 
            :dialog="showPostDialog" 
            :selected_forum_post="selected_forum_post_to_update"
            :courses="courses"
            @posted="() => {  showPostDialog = false, getData(), selected_forum_post.id === selected_forum_post_to_update.id ? getPost() : '', selected_forum_post_to_update = null }"
            @close="() => { selected_forum_post_to_update = null, showPostDialog = false }"/>

        <DeleteDialog 
            :dialog="deleteDialog"
            type="forum discussion"
            :loading="deleteLoading"
            @close="deleteDialog=false"
            @delete="deletePost"
        />

        <PendingPostsDialog 
            ref="pendingPostDialog"
            :dialog="pendingPostsDialog"
            @remove="(id) => { selected_forum_post_to_delete = id, deleteDialog = true }"
            @update="(item) => { selected_forum_post_to_update = item, showPostDialog = true }"
            @close="pendingPostsDialog=false"
            :role="role"
        />

        <ForApprovalPostsDialog 
            ref="forApprovalPostsDialog"
            :dialog="forApprovalPostsDialog"
            @close="forApprovalPostsDialog=false"
            @getData="getData()"
            :role="role"
        />
    </div>
</template>

<script>
import { mapActions, mapState, mapMutations } from 'vuex';
import { topic_categories } from '@/constants/forum';
import searchAndPaginateMixin from '@/mixins/searchAndPaginateMixin';
import PostDialog from '@/components/teacher/forum/PostDialog.vue';
import Post from '@/components/teacher/forum/Post.vue';
import PendingPostsDialog from '@/components/teacher/forum/PendingPostsDialog.vue';
import ForApprovalPostsDialog from '@/components/teacher/forum/ForApprovalPostsDialog.vue';

var CryptoJS = require("crypto-js");

export default {
    components: {
        PostDialog,
        Post,
        PendingPostsDialog,
        ForApprovalPostsDialog
    },
    mixins: [searchAndPaginateMixin],
    data: () => ({
        topic_categories,
        minDate: '',
        maxDate: '',
        loading: false,
        courseLoading: false,
        viewLoading: false, 
        deleteLoading: false,
        showPostDialog: false,
        deleteDialog: false,
        pageCount: 0,
        totalCount: 0,
        pendingPostsDialog: false,
        forApprovalPostsDialog: false,
        selected_forum_post: null,
        selected_forum_post_id: null,
        selected_forum_post_comment_id: null,
        selected_forum_post_to_delete: null, 
        selected_forum_post_to_update: null, 
        selectedPostFromMonth: `${new Date().getFullYear()}`,
        selectedPostFromYear: `${new Date().getMonth() + 1}`,
        selectedPostToMonth: `${new Date().getMonth() + 1}`,
        selectedPostToYear: `${new Date().getFullYear()}`,
        courses: []
    }),

    mounted(){
        this.filter = {
            course_id: [],
            topic_category: '',
            posted_from: `${new Date().getFullYear()}-1`,
            posted_to: `${new Date().getFullYear()}-12`,
        },

        this.courseLoading = true

        this.getTenantCoursesAction().then(res => {
            this.courses = res
        }).finally(() => {
            this.courseLoading = false
        })

        
        if(this.$route.query.course_id) {
            this.$route.query.course_id.forEach((id) => {
                if(!this.filter['course_id']) {
                this.filter['course_id'] = [parseInt(id)];
                } else {
                this.filter['course_id'].push(parseInt(id));
                }
            })
        } 

        if(this.$route.query.forum_post_id) {
            this.selected_forum_post_id = Number(this.$route.query.forum_post_id)
            this.getPost()
        }

        if(this.$route.query.forum_post_comment_id) {
            this.selected_forum_post_comment_id = Number(this.$route.query.forum_post_comment_id)
        
        }
        
        this.updateQuery()
        
        this.getData()
    },

    computed: {
        ...mapState({
            user: (state) => state.user,
        }),

        forum_posts() {
            return this.$store.state[this.role === 'user' ? 'usr' : this.role]?.forum_posts || []; // Dynamic access
        },
        
        paginationPaginate(){
            return String(this.paginate)
        },

        role() {
            return CryptoJS.AES.decrypt(localStorage.getItem('current'), 'lms-role').toString(CryptoJS.enc.Utf8) 
        }
    },

    methods: {
        ...mapMutations(['alertMutation']),
        ...mapActions(['getTenantCoursesAction']),
        ...mapActions({
            getForumPosts(dispatch) {
                return dispatch(`${this.role === 'user' ? 'usr' : this.role}/getForumPostsAction`, {...this.filter, paginate: Number(this.paginate), page: this.page, status: 'APPROVED' });
            },
            getForumPost(dispatch) {
                return dispatch(`${this.role === 'user' ? 'usr' : this.role}/getForumPostAction`, this.selected_forum_post_id);
            },
            deleteForumPost(dispatch) {
                return dispatch(`${this.role === 'user' ? 'usr' : this.role}/deleteForumPostAction`, this.selected_forum_post_to_delete);
            }
        }),

        applyDate(){
            this.filter = {
                ...this.filter,
                posted_to: `${this.selectedPostToYear}-${this.selectedPostToMonth}`,
                posted_from: `${this.selectedPostFromYear}-${this.selectedPostFromMonth}`,
            }

            this.minDate = this.filter.posted_from
            this.maxDate = this.filter.posted_to

            this.getData()
        },

        getData() {
            this.loading = true
            this.getForumPosts().then(res => {
                this.filter.course_id = []
                if(this.$route.query.course_id) {
                    this.$route.query.course_id.forEach((id) => {
                    if(!this.filter['course_id']) {
                        this.filter['course_id'] = [parseInt(id)];
                    } else {
                        this.filter['course_id'].push(parseInt(id));
                    }
                    })
                } 
                this.pageCount = res.last_page
                this.paginate = String(res.per_page)
                this.page = res.current_page
                this.totalCount = res.total
                this.loading = false

                if(this.pendingPostsDialog) {
                    this.$refs.pendingPostDialog.getData()
                }
            }).finally(() => {
                this.loading = false
            })
        },
        
        deletePost() {
            this.deleteLoading = true
            this.deleteForumPost().then(() => {
                this.deleteLoading = false
                this.deleteDialog = false
                this.alertMutation({
                    show: true,
                    text: 'Post successfully deleted.',
                    type: "success"
                })
                if(this.selected_forum_post_to_delete === this.selected_forum_post_id) {
                    this.selected_forum_post_id = null
                    this.selected_forum_post = null
                }
                this.selected_forum_post_to_delete = null
                this.getData()
            }).catch(() => {
                this.deleteLoading = false
                this.deleteDialog = false
                this.alertMutation({
                    show: true,
                    text: 'Something went wrong',
                    type: "error"
                })
            })
        },

        getPost() {
            this.viewLoading = true
            this.getForumPost().then(res => {
                this.selected_forum_post = res
                this.viewLoading = false
                this.viewLoading = false
            }).catch(res => {
                if(res.message === 'No forum post found.') {
                    this.alertMutation({
                        show: true,
                        text: 'Post not found',
                        type: "error"
                    })
                    this.selected_forum_post = null
                    this.selected_forum_post_id = null
                } else {
                    this.alertMutation({
                        show: true,
                        text: 'Something went wrong',
                        type: "error"
                    })
                }
                this.viewLoading = false
            })
        },

        resetFilters(){
            this.filter = {
                course_id: [],
                topic_category: '',
                posted_from: `${new Date().getFullYear()}-${(new Date().getMonth() + 1)}`,
                posted_to: `${new Date().getFullYear()}-${(new Date().getMonth() + 1)}`
            }
        },
    },
    watch: {
        selected_forum_post_comment_id(val) {
            if(val) {
                setTimeout(() => {
                    console.log(val)
                    this.$vuetify.goTo(`#comment-${val}`, { offset: 30})
                }, 2000)
            }
        },
        '$route.query': {
            handler(newQuery) {
                if(newQuery.forum_post_id) {
                    this.selected_forum_post_id = Number(newQuery.forum_post_id)
                    this.getPost()
                }

                if(newQuery.forum_post_comment_id) {
                    this.selected_forum_post_comment_id = Number(newQuery.forum_post_comment_id)
                
                }
            },
            immediate: true,
            deep: true
        }
    },
}
</script>
