<template>
    <v-card class="d-flex flex-row justify-space-between custom-border" elevation="2">
        <section class="cursor-pointer" @click="$router.push({name: 'User Course Details', params: {id: data.uuid}})">
            <v-img
                v-if="data.image"
                width="80"
                height="100%"
                :src="data.image ? data.image.url : require('@/assets/images/default-course-bg.png')"
                :lazy-src="data.image ? data.image.url : require('@/assets/images/default-course-bg.png')"
                v-on:error="data.image = null"
                class="rounded-l"/>
            <v-sheet 
                v-if="!data.image"
                width="80"
                height="100%"
                :color="!data.image ? (data.course_card ? data.course_card.color : selectedColor)  : 'primary'" 
                :style="gallery && 'height: 150px'" 
                class="hover rounded-l" >
                <v-menu offset-y v-if="!data.image ">
                    <template v-slot:activator="{ on, attrs }">
                    <v-btn
                        icon
                        :color="gallery ? 'white' : 'gray'"
                        class="float-right"
                        v-bind="attrs"
                        v-on="on"
                        dense
                    >
                        <v-icon >
                        mdi-dots-vertical
                        </v-icon>
                    </v-btn>
                    </template>
                    <v-list>
                        <v-list-item>
                        <v-menu right offset-x :close-on-content-click="menu">
                            <template v-slot:activator="{ on, attrs }">
                                <v-btn left text v-bind="attrs" v-on="on" class="text-capitalize poppins f12">
                                Change Color
                                <v-icon right>mdi-chevron-right</v-icon>
                                </v-btn>
                            </template>
                            <v-sheet width="250">
                                <v-color-picker
                                hide-canvas
                                hide-inputs
                                hide-sliders
                                hide-mode-switch
                                class="pa-2"
                                mode="rgba"
                                show-swatches
                                swatches-max-height="150"
                                v-model="selectedColor"
                                @update:color="changeColor"
                                />
                            </v-sheet>
                            </v-menu>
                        </v-list-item>
                        <v-list-item>
                        <v-btn text class="text-capitalize poppins f12" @click="changeFavorite">
                            Mark as {{ favorite ? 'Unfavorite' : 'Favorite' }}
                            <v-icon color="yellow" right v-if="favorite">
                            mdi-star
                            </v-icon>
                            <v-icon color="yellow" right v-else>
                            mdi-star-outline
                            </v-icon>
                        </v-btn>
                        </v-list-item>
                    </v-list>
                </v-menu>
            </v-sheet>
        </section>
        <section class="d-flex flex-column flex-grow-1 cursor-pointer" @click="$router.push({name: 'User Course Details', params: {id: data.uuid}})" >
            <v-card-subtitle class="poppins fw600 pb-2 break-word">
                <div class="f10 poppins secondary-2--text text-uppercase ">
                    {{ data.training_hours }} {{ data.training_hours > 1 ? 'hours' : 'hour' }}
                    <span v-if="data.modules_count > 0"> • {{ data.modules_count }} {{ data.modules_count > 1 ? 'modules' : 'module'}} </span>
                    <span v-if="data.zoom_meetings_count > 0"> • {{ data.zoom_meetings_count }} {{ data.zoom_meetings_count > 1 ? 'live sessions' : 'live session'}} </span>
                    <span v-if="data.available_until">
                        &nbsp; <span v-if="data.available_until.start_date || data.available_until.end_date"> | </span>&nbsp; 
                        <span v-if="data.available_until.start_date"> {{ $dateFormat.mmDDyy(data.available_until.start_date) }} </span>
                        <span v-if="data.available_until.end_date"> until {{ $dateFormat.mmDDyy(data.available_until.end_date) }} </span>
                    </span>
                </div>
                {{ data.title ? data.title : 'Untitled' }}
                
                <div class="poppins fw600 text-left f10" v-if="data.course_code">
                    {{ data.course_code }}
                </div>
                <!-- {{ data.title.length > 35 ? `${data.title.slice(0, 31)}...` : data.title}} -->
            </v-card-subtitle>
            <!-- <div class="poppins fw600 truncate d-inline-block mx-2 mt-2 mb-1" style="width: 37vw; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                {{ data.title ? data.title : 'Untitled'}}
            </div> -->
            <!-- <div class="poppins fw600 py-0 secondary-2--text f10 mx-2 mb-2">
                {{ data.training_hours }} {{ data.training_hours > 1 ? 'hours' : 'hour' }} • {{ data.modules_count ? data.modules_count : 0 }} {{ data.modules_count > 1 ? 'modules' : 'module'}} 
            </div> -->
            <div class="poppins fw600 pb-3 d-flex flex-column mx-2">
                <v-progress-linear 
                    height="5"
                    :value="progress"
                    rounded
                    color="success"
                    background-color="grey lighten-3"
                ></v-progress-linear>
                <!-- <b class="poppins secondary-1--text fw600 f11 my-1">
                    {{ progress }}% Complete
                </b> -->
                <section class="d-flex flex-row mt-3">
                    <v-spacer />
                    <v-btn dense flat class="poppins text-capitalize" small style="width: 150px;" color="primary" @click="$router.push({name: 'User Course Details', params: {id: data.uuid}})">
                        {{ progress === 0 ? 'Start Course' : progress === 100 ? 'Completed' : 'Continue'}}
                    </v-btn>
                </section>
            </div>
        </section>
    </v-card>
</template>

<script>
import { mapActions} from 'vuex'

export default {
    props: ['data', 'progress', 'gallery'],
    data:() => ({
        menu: false,
        favorite: false,
        selectedColor: '#1565C0FF',
        asset_path: `${process.env.VUE_APP_ASSET_PATH}`,
    }),
    methods: {
        ...mapActions('usr', ['updateCourseColor', 'updateCourseFavorite', 'updateCourseFavorite']),

        changeColor() {
        if (this.data.course_card) {
            this.data.course_card.color = this.selectedColor
        }
        
        this.updateCourseColor({
            course_id: this.data.id,
            color: this.selectedColor
        })
        },

        changeFavorite() {
        this.favorite = !this.favorite

        this.updateCourseFavorite({
            course_id: this.data.id,
            is_favorite: this.favorite
        })
        }
    },
}
</script>