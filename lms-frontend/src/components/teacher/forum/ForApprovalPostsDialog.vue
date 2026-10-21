<template>
    <v-dialog v-model="dialog" max-width="550" persistent eager retain-focus="false">
        <v-card>
            <v-card-title class="d-flex align-center justify-space-between">
                <h4 class="primary--text">View Pending Post</h4>
                <v-spacer/>
                <v-btn icon small @click="$emit('close')">
                    <v-icon>mdi-close</v-icon>
                </v-btn>
            </v-card-title>

            <v-divider />
            <LinearProgress v-if="loading"/>
            
            <div v-else-if="!loading && posts.length === 0" class="roboto f15 fw500 my-5 mx-6 secondary--text">
                No pending posts available
            </div>
            <v-sheet class="pa-5 ma-3 custom-border cursor-pointer" outlined rounded="lg"
                v-for="item in posts"
                :key="item.id"
                :item="item">
                <div class="d-flex align-start mb-5">
                    <v-avatar size="40">
                    <v-img :src="item.user.image?item.user.image.url: require('@/assets/default-photo.png')"
                        v-on:error="require('@/assets/default-photo.png')"/>
                    </v-avatar>
                    <v-sheet class="ml-3 flex-grow-1">
                        <div class="d-flex align-start">
                            <div class="d-flex flex-column">
                            <div class="d-flex flex-row align-start">
                                <div class="d-inline f14 primary--text fw500 roboto mr-1">{{ item.user.full_name }}</div>
                                <div class="d-inline f14 secondary-1--text fw400 roboto mr-2">({{ item.user.email }})</div>
                            </div>
                            <div class="d-flex flex-row mt-1">
                                <v-chip class="poppins f12 fw500 text-wrap py-1 mr-1" height="100%" dense v-if="item.course"> {{ item.course.title }} </v-chip>
                                <v-chip class="poppins f12 fw500 text-wrap py-1" height="100%" dense v-if="topic_categories.find((category) => category.value === item.topic_category)"> {{ topic_categories.find((category) => category.value === item.topic_category).text }} </v-chip>
                            </div>
                            </div>
                            <v-spacer></v-spacer>
                            <div class="d-flex flex-row">
                                <v-btn @click="() => { selected_post = item, selected_post_status = 'APPROVED', updatePost(selected_post) }" text dense class="mr-1 poppins f12 px-2" color="success">
                                    <v-progress-circular
                                        v-if="selected_post && selected_post.id === item.id && selected_post_status == 'APPROVED' && updateLoading"
                                            indeterminate
                                            color="success"
                                            rounded
                                            size="20"
                                    ></v-progress-circular>
                                    <div v-else>
                                        <v-icon small >mdi-check</v-icon>
                                        Approve
                                    </div>
                                </v-btn>
                                <v-btn @click="() => { selected_post = item, selected_post_status = 'DECLINED', updatePost(selected_post) }" text dense color="error" class="poppins f12 px-2">
                                    <v-progress-circular
                                        v-if="selected_post && selected_post.id === item.id && selected_post_status == 'DECLINED' && updateLoading"
                                            indeterminate
                                            color="error"
                                            rounded
                                            size="20"
                                    ></v-progress-circular>                                    
                                    <div v-else>
                                        <v-icon small>mdi-close</v-icon>
                                        Decline
                                    </div>
                                </v-btn>
                            </div>
                        </div>
                    </v-sheet>
                </div>
                <v-divider/>
                <div class="my-5">
                    <h3 class="mx-3">{{ item.title }}</h3>
                    <v-sheet
                    color="transparent"
                    class="roboto mb-2 f14 pa-3 fw500 secondary-1--text text-justify overflow-hidden" 
                    style="white-space: pre; text-overflow: ellipsis; text-wrap: wrap;"
                    v-html="item.content ? $dompurifier(item.content) : ''"
                    height="90"
                    />
                    <div class="d-flex justify-space-between secondary-4--text f12 align-start">
                    <span class="px-3 f12 secondary-2--text roboto" v-if="item.comments_count > 0">{{item.comments_count}} {{ item.comments_count > 1 ? 'Comments' : 'Comment'}}</span>
                    <span class="px-3 f12 secondary-2--text roboto">{{$dateFormat.calendar(item.created_at)}}</span>
                    </div>
                </div>
            </v-sheet>

            <FormPagination 
                :pageCount="pageCount" 
                :page="page"
                :paginate="paginate"
                @page="(e) => {page = e, getData()}" 
                @paginate="(e) => {paginate = e, page = 1, getData()}"/>
        </v-card>
    </v-dialog>
</template>

<script>
import { mapActions, mapMutations } from 'vuex';
import Post from './Post.vue';
import { topic_categories } from '@/constants/forum';

export default {
    props: {
        dialog: {
            default: false,
            type: Boolean
        },
        role: {
          default: 'user',
          type: String
        },
    },

    data: () => ({
        loading: false,
        updateLoading: false,
        totalCount: 0,
        pageCount: 1,
        paginate: '10',
        page: 1,
        posts: [],
        topic_categories,
        selected_post: null,
        selected_post_status: null
    }),

    components: {
        Post
    },

    methods: {
        ...mapMutations(['alertMutation']),
        ...mapActions({
            getPendingForumPosts(dispatch) {
                return dispatch(`${this.role === 'user' ? 'usr' : this.role}/getPendingForumPostsAction`, { status: 'PENDING', paginate: Number(this.paginate), page: this.page });
            },
            updateForumPost(dispatch, payload) {
                return dispatch(`${this.role === 'user' ? 'usr' : this.role}/updateForumPostAction`, payload);
            },
        }),

        getData() {
            this.loading = true
            this.getPendingForumPosts().then(res => {
                this.posts = res.data
                this.page = res.current_page
                this.pageCount = res.last_page
                this.paginate = String(res.per_page)
                this.loading = false
            }).finally(() => {
                this.loading = false
            })
        },

        updatePost(item){
            this.updateLoading = true
            this.updateForumPost({ ...item, status: this.selected_post_status }).then(() => {
                this.updateLoading = false
                this.selected_post = null
                this.alertMutation({
                    show: true,
                    text: `Post successfully ${this.selected_post_status.toLowerCase()}`,
                    type: "success"
                })
                this.selected_post_status = null
                this.getData()
                this.$emit('getData')
            }).catch(e => {
                this.updateLoading = false
                this.selected_post = null
                this.selected_post_status = null
                this.alertMutation({
                    show: true,
                    text: "Something went wrong.",
                    type: "error"
                })
            })
        }
    },

    watch: {
        dialog(val) {
            if(val) {
                this.getData()
            }
        }
    },
}
</script>