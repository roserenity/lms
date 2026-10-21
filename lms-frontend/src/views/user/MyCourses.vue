<template>
    <section>
        <v-card class="custom-border border pa-3">
            <v-card-text>
                <div class="poppins fw600 secondary-1--text f18 mb-3">My Courses</div> 

                <div class=" d-flex flex-wrap justify-space-between align-end f18 secondary-1--text mb-3">
                    <SearchBar 
                        :placeholder="'Search course'" 
                        :outlined="true" 
                        :value.sync="search" 
                        @clear="clearSearch"
                        @search="onSearch"
                        class="my-1"/>
                    
                    <div class="d-flex justify-end align-end ml-auto my-1">
                        <section style="width:150px">
                            <FormSelectObject
                            :placeholder="'Type of Learning'"
                            :items="type_of_learning"
                            :value.sync="filter1.type_of_learning"
                            :class="'mx-1'"
                            />
                        </section>
                        <section style="width:150px">
                            <FormSelectObject
                                :items="categories"
                                :value.sync="filter1.classes"
                                :loading="loading1"
                                class="mx-1"
                            />
                        </section>

                        <v-btn 
                            text 
                            dense
                            icon
                            @click="gallery =  true"
                            class="hidden-sm-and-down mx-1"
                        >
                            <v-icon size="22" :class="gallery ? 'primary--text' : 'secondary-2--text'">
                            mdi-view-gallery-outline
                            </v-icon>
                        </v-btn>
                        <v-btn 
                            text 
                            dense
                            icon
                            @click="gallery = false"
                            class="hidden-sm-and-down"
                        >
                            <v-icon size="22" :class="!gallery ? 'primary--text' : 'secondary-2--text'">
                            mdi-format-list-bulleted
                            </v-icon>
                        </v-btn>

                        <v-btn 
                            text 
                            dense
                            icon
                            @click="() => { filter1.availability_date_sort = null, filter1.course_name_sort = (filter1.course_name_sort === 'ASC' ? 'DESC' : 'ASC')}"
                            class="hidden-sm-and-down"
                        >
                            <v-icon size="22" class="primary--text" v-if="filter1.course_name_sort === 'DESC'" :class="filter1.course_name_sort ? 'primary--text' : 'secondary-2--text'">
                                mdi-sort-alphabetical-ascending
                            </v-icon>
                            <v-icon size="22"  class="primary--text" v-if="filter1.course_name_sort === 'ASC'" :class="filter1.course_name_sort ? 'primary--text' : 'secondary-2--text'">
                                mdi-sort-alphabetical-descending
                            </v-icon>
                            <v-icon size="22"  v-if="!filter1.course_name_sort">
                                mdi-sort-alphabetical-variant
                            </v-icon>
                        </v-btn>
                        <v-btn 
                            text 
                            dense
                            icon
                            @click="() => { filter1.course_name_sort = null, filter1.availability_date_sort = (filter1.availability_date_sort === 'ASC' ? 'DESC' : 'ASC') }"
                            class="hidden-sm-and-down"
                        >
                            <v-icon size="22" class="primary--text" v-if="filter1.availability_date_sort === 'DESC'" :class="filter1.availability_date_sort ? 'primary--text' : 'secondary-2--text'">
                                mdi-sort-calendar-ascending
                            </v-icon>
                            <v-icon size="22"  class="primary--text" v-if="filter1.availability_date_sort === 'ASC'" :class="filter1.availability_date_sort ? 'primary--text' : 'secondary-2--text'">
                                mdi-sort-calendar-descending
                            </v-icon>
                            <v-icon size="22" v-if="!filter1.availability_date_sort">
                                mdi-sort-calendar-ascending
                            </v-icon>
                        </v-btn>
                    </div>
                </div>
                <section v-if="totalCount !== 0" class="text-right">
                    <FormLabel :label="`${totalCount} result/s`"/>
                </section>

                <LinearProgress 
                    v-if="loading"
                    class="mt-3"
                />

                <v-sheet v-if="courses.length>0" class="d-flex my-4" :class="[gallery ? 'flex-wrap' : 'flex-column']">
                    <CardGallery 
                        v-for="(item,i) in courses"
                        v-if="gallery"
                        :key="i"
                        :data="item"
                        :progress="getProgress(item)"
                        class="ma-1"
                    />
                    <CardList 
                        v-for="(item,i) in courses"
                        v-if="!gallery"
                        :key="i"
                        :data="item"
                        :progress="getProgress(item)"
                        class="my-1"
                    />
                </v-sheet>
                <v-sheet
                    v-if="!loading && courses.length === 0"
                    color="transparent"
                    class="d-flex flex-column align-center justify-center pb-10 fade"
                    height="400"
                >
                    <div v-if="search === ''">
                        <v-img 
                            :max-width="$vuetify.breakpoint.mobile?200:300" 
                            :src="require('@/assets/default/empty_drafts.png')"/>
                        <h3 class="poppins secondary-1--text fw600 my-5">Nothing in Courses</h3>
                    </div>
                    <h3 v-else class="text-center poppins secondary-1--text fw600 my-5">No results found.</h3>
                </v-sheet>
                
                <FormPagination 
                    v-if="totalCount > 0"
                    :pageCount="pageCount" 
                    :page="page"
                    :paginate="paginationPaginate"
                    @page="onPageChange" 
                    @paginate="onPaginateChange"/>
            </v-card-text>
        </v-card>
    </section>
</template>

<script>
import { mapActions, mapState, mapMutations } from "vuex"
import searchAndPaginateMixin from '@/mixins/searchAndPaginateMixin';
import CardGallery from '@/components/user/course/CardGallery.vue';
import CardList from '@/components/user/course/CardList.vue';

export default {
    mixins: [searchAndPaginateMixin],
    components: {
        CardGallery,
        CardList,
    },
    data: () => ({
        loading: false,
        loading1: false,
        filter1: {
            classes: [],
            course_name_sort: 'ASC',
            availability_date_sort: null,
            type_of_learning: '',
        },
        gallery: true,
        categories: [
            { text: 'All', value: []}
        ],
        SORT_BY: [
            { text: 'A to Z', value: 'ASC' },
            { text: 'Z to A', value: 'DESC' },
        ],
        totalCount: 0,
        type_of_learning: [
            { text: 'All', value: '' },
            { text: 'E-Learning', value: 'E-LEARNING' },
            { text: 'Blended', value: 'BLENDED' },
            { text: 'Microlearning', value: 'MICROLEARNING' },
        ],
    }),
    mounted() {
        //this.loading = true

        window.addEventListener('resize', this.onResize, {passive: true})
        this.onResize()

        this.getData()
        
        this.getClassCategory()
    }, 
    computed: {
        ...mapState('usr', {
            courses: (state) => state.courses,
            classes: (state) => state.classes
        }),

        paginationPaginate(){
            return String(this.paginate)
        }
    },
    methods: {
        ...mapActions(['otherCoursesAction']),
        ...mapMutations(['alertMutation']),
        ...mapActions('usr', ['getClassCategoryAction', 'getCoursesAction']),

        getData() {
            if(!this.loading) {
                this.loading = true
                this.getCoursesAction({...this.filter1, paginate: Number(this.paginate), page: this.page, search: this.search}).then((res)=>{
                    this.paginate = String(res.per_page)
                    this.page = res.current_page
                    this.pageCount = res.last_page
                    this.totalCount = res.total
                    this.loading = false
                }).catch(() =>{
                    this.loading = false
                })
            }
        },
        
        onResize(){
            if(this.$vuetify.breakpoint.smAndDown || this.$vuetify.breakpoint.mobile){
                this.gallery = false
            }
        },

        getProgress(item){
            let _progress = 0
            if(item.type_of_learning === 'BLENDED'){
                _progress = this.$userProgressBlended(item)
            } else {
                _progress = this.$userProgressElearning(item)
            }
            return Number.isNaN(_progress) ? 0 :_progress
        },

        getClassCategory() {
            this.loading1 = true
            this.getClassCategoryAction().then(res => {
                res.forEach((item) => {
                    this.categories.push({
                        text: item.class_name,
                        value: item.class_code,
                    })
                })
            }).finally(() => {
                this.loading1 = false
            })
        },
    },
    watch: {
        filter1: {
            handler(val) {
                this.getData()
            },
            deep: true
        },
    }
}
</script>