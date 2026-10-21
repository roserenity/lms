<template>
    <v-card class="custom-border d-flex flex-column justify-space-between" width="250" elevation="2">
        <section class="cursor-pointer" @click="$router.push({name: 'User Course Details', params: {id: data.uuid}})">
            <img
                v-if="data.image"
                width="250"
                height="132"
                :src="data.image ? data.image.url : require('@/assets/images/default-course-bg.png')"
                :lazy-src="data.image ? data.image.url : require('@/assets/images/default-course-bg.png')"
                v-on:error="data.image = null"
                class="rounded-t"/>
            <v-sheet 
                v-if="!data.image"
                width="250"
                height="132"
                :color="!data.image ? (data.course_card ? data.course_card.color : selectedColor)  : 'primary'" 
                class="hover border-top" >
                <v-menu offset-y v-if="!data.image ">
                    <template v-slot:activator="{ on, attrs }">
                    <v-btn
                        icon
                        color="white"
                        class="float-right"
                        v-bind="attrs"
                        v-on="on"
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
            
            <v-card-subtitle class="poppins fw600 pb-0 break-word">
                <div class="f10 poppins secondary-2--text text-uppercase ">
                    {{ data.type_of_learning }}
                </div>
                {{ data.title ? data.title : 'Untitled' }}
                
                <div class="poppins fw600 text-left f10" v-if="data.course_code">
                    {{ data.course_code }}
                </div>
                <!-- {{ data.title.length > 35 ? `${data.title.slice(0, 31)}...` : data.title}} -->
            </v-card-subtitle>
            <v-card-subtitle class="poppins fw600 py-0 secondary-2--text f11">
                {{ data.training_hours }} {{ data.training_hours > 1 ? 'hours' : 'hour' }}
                <span v-if="data.modules_count > 0"> • {{ data.modules_count }} {{ data.modules_count > 1 ? 'modules' : 'module'}} </span>
                <span v-if="data.zoom_meetings_count > 0"> • {{ data.zoom_meetings_count }} {{ data.zoom_meetings_count > 1 ? 'live sessions' : 'live session'}} </span>
                <span v-if="data.available_until">
                    &nbsp; <span v-if="data.available_until.start_date || data.available_until.end_date"> | </span>&nbsp; 
                    <span v-if="data.available_until.start_date"> {{ $dateFormat.mmDDyy(data.available_until.start_date) }} </span>
                    <span v-if="data.available_until.end_date"> until {{ $dateFormat.mmDDyy(data.available_until.end_date) }} </span>
                </span>
            </v-card-subtitle>
        </section>
        <section class="cursor-pointer" @click="$router.push({name: 'User Course Details', params: {id: data.uuid}})">
            <v-container class="mx-1">
                <v-progress-linear 
                    height="8"
                    :value="progress"
                    rounded
                    color="success"
                    background-color="grey lighten-3"
                ></v-progress-linear>
                <b class="poppins secondary-1--text fw600 f13 my-2">
                    {{ progress }}% Complete
                </b>
            </v-container>
            <v-btn block tile flat class="py-5 poppins f12 rounded-b text-capitalize" color="primary" @click="$router.push({name: 'User Course Details', params: {id: data.uuid}})">
                {{ progress === 0 ? 'Start Course' : progress === 100 ? 'Completed' : 'Continue Course'}}
            </v-btn>
        </section>
    </v-card>
</template>

<script>
import { mapActions} from 'vuex'

export default {
    props: ['data', 'progress'],
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