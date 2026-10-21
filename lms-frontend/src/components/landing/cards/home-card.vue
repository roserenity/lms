<template>
    <v-card tile height="430" style="width: inherit" class="course-box-shadow course-title-div">
        <v-container class="pa-0">
            <v-hover>
                <template v-slot:default="{ hover }">
                    <section class="pointer-cursor">
                        <v-img
                            v-if="!hover"
                            :src="require(`@/assets/images/landing/background/course-frame1.png`)"
                            height="427"
                            class="fade align-start pointer-cursor text-center"
                            gradient="to bottom, rgba(0,0,0,0), rgba(0,0,0,.1)"
                            cover
                            eager
                            transition="slide-x-transition"
                            @click="overlay = !overlay"
                            >
                        </v-img>
                        
                        <!-- <v-slide-y-transition> -->
                            <v-sheet color="transparent" v-if="hover" class="fade mr-3">
                                <v-list class="course-title-div overflow-y-auto scroller w-100" height="427" width="100%">
                                    <v-list-group
                                        v-for="(course, i) in courses"
                                        :key="i"
                                        class="white--text"
                                        :value="i === 0 ? true : false"
                                    >
                                        <template v-slot:appendIcon>
                                            <v-icon color="white">mdi-chevron-up</v-icon>
                                        </template>
                                        <template v-slot:activator>
                                            <v-list-item-content class="white--text">
                                                <v-list-item-title class=" f18" >{{  course.class_name }}</v-list-item-title>
                                            </v-list-item-content>
                                        </template>

                                            <v-list-item
                                                v-for="(item, i) in course.courses"
                                                :key="i"
                                                class="white--text"
                                                link
                                            >
                                                <v-list-item-content class="px-6" @click="$router.push({name: 'Course Learn More', params: { uuid: item.uuid}, query: { timestamp: Date.now() }})">
                                                    <div class="d-flex flex-row justify-space-between">
                                                        <div class="d-flex flex-column">
                                                            <div v-if="item.available_until" class="primary-font f10 py-0 text-left">
                                                                <span v-if="item.available_until.start_date"> from <b>{{ $dateFormat.mmDDyy(item.available_until.start_date) }} </b></span>
                                                                <span v-if="item.available_until.end_date"> until <b>{{ $dateFormat.mmDDyy(item.available_until.end_date) }} </b></span>
                                                            </div>
                                                            <div class="f14 fw500 text-left">
                                                                {{ item.title }}
                                                            </div>
                                                        </div>
                                                        <v-icon color="white">
                                                            mdi-chevron-right
                                                        </v-icon>
                                                    </div>
                                                </v-list-item-content>
                                            </v-list-item>
                                        </v-list-group>
                                    </v-list>
                            </v-sheet>
                            <!-- <v-overlay
                                absolute
                                :class="hover && 'course-box-shadow'"
                                class="white--text d-flex align-start justify-start"
                                opacity="0"
                                >
                                
                            </v-overlay> -->
                        <!-- </v-slide-y-transition> -->
                    </section>
                </template>
            </v-hover>
        </v-container>
            <!-- <v-container fluid class="course_card d-flex flex-row align-center justify-space-between" @click="$router.push({name: 'Course Learn More', params: { uuid: course.uuid}, query: { timestamp: Date.now() }})">
                <div class="d-flex flex-column" width="100vw">
                    <div v-if="course.available_until" class="primary-font f11 py-0 text-left">
                        <span v-if="course.available_until.start_date"> from <b class="primary-font">{{ $dateFormat.mmDDyy(course.available_until.start_date) }} </b></span>
                        <span v-if="course.available_until.end_date"> until <b class="primary-font">{{ $dateFormat.mmDDyy(course.available_until.end_date) }} </b></span>
                    </div>
                    <div class="f16 fw500 text-left">
                        {{ course.title }}
                    </div>
                </div>
                <v-btn icon dense>
                    <v-icon>
                        mdi-chevron-right
                    </v-icon>
                </v-btn>
            </v-container>
            <v-divider v-if="i !== courses.length - 1" class="my-1" style="border: 1px solid white;"/> -->
        <!-- <v-container class="pa-0">
            <v-hover>
                <template v-slot:default="{ hover }">
                    <section class="pointer-cursor">
                        <v-img
                            :src="image"
                            height="480"
                            class="align-start pointer-cursor text-center"
                            gradient="to bottom, rgba(0,0,0,.1), rgba(0,0,0,.5)"
                            cover
                            eager
                            transition="slide-x-transition"
                            @click="overlay = !overlay"
                            >
                            <v-card-title class="white--text course-title-div f16 d-flex justify-center align-center" style="word-break: break-word">{{ title }}</v-card-title>        
                        </v-img>
                        <v-slide-y-transition>
                            <v-overlay
                                v-if="hover"
                                absolute
                                :class="hover && 'course-box-shadow'"
                                class="white--text overflow-y-auto scroller d-flex align-start pa-5 justify-start"
                                opacity="0"
                                zIndex="1"
                                width="250px"
                                >
                                <section v-for="(course, i) in courses" :key="i" >
                                    <v-container fluid class="course_card d-flex flex-row align-center justify-space-between" @click="$router.push({name: 'Course Learn More', params: { uuid: course.uuid}, query: { timestamp: Date.now() }})">
                                        <div class="d-flex flex-column" width="100vw">
                                            <div v-if="course.available_until" class="primary-font f11 py-0 text-left">
                                                <span v-if="course.available_until.start_date"> from <b class="primary-font">{{ $dateFormat.mmDDyy(course.available_until.start_date) }} </b></span>
                                                <span v-if="course.available_until.end_date"> until <b class="primary-font">{{ $dateFormat.mmDDyy(course.available_until.end_date) }} </b></span>
                                            </div>
                                            <div class="f16 fw500 text-left">
                                                {{ course.title }}
                                            </div>
                                        </div>
                                        <v-btn icon dense>
                                            <v-icon>
                                                mdi-chevron-right
                                            </v-icon>
                                        </v-btn>
                                    </v-container>
                                    <v-divider v-if="i !== courses.length - 1" class="my-1" style="border: 1px solid white;"/>
                                </section>
                            </v-overlay>
                        </v-slide-y-transition>
                    </section>
                </template>
            </v-hover>
        </v-container> -->
    </v-card>
</template>

<style scoped>
.course-title-div {
    background-color: rgb(40,47,129);
}
.v-overlay__content {
    width: 100% !important
}
.course_card:hover{
    background-color: rgb(30, 36, 101);
}
</style>

<script>

export default {
    props: {
        image: {
            type: String,
            default: ""
        },
        title: {
            type: String,
            default: ""
        },
        courses: {
            type: Array,
            default: []
        }
    },
    data: () => ({
        hover: false,
        overlay: false,
        active: null
    })
}
</script>