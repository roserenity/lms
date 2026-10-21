<template>
    <v-dialog v-model="dialog" max-width="550" persistent eager retain-focus="false">
        <v-card>
            <v-card-title class="d-flex align-center justify-space-between">
                <h4 class="primary--text">View Pending Posts</h4>
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

            <Post 
                v-for="item in posts"
                :key="item.id"
                :item="item"
                @delete="(id) => { $emit('remove', id) }"
                @update="(item) => { $emit('update', item) }"
                @getPost="() => { getPost() }"
                :role="role"
                @getData="getData()"
                class="ma-3"
            />

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
import { mapActions } from 'vuex';
import Post from './Post.vue';

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
        totalCount: 0,
        pageCount: 1,
        paginate: '10',
        page: 1,
        posts: []
    }),

    components: {
        Post
    },

    methods: {
        ...mapActions({
            getPendingForumPosts(dispatch) {
                return dispatch(`${this.role === 'user' ? 'usr' : this.role}/pendingForumPostsAction`, { status: 'PENDING', paginate: Number(this.paginate), page: this.page });
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