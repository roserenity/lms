<template>
    <section>
        <label class="caption mb-2 align-start" :class="status === 'PUBLISH' ? 'mx-8' : 'mx-6'">ATTACHED IMAGES</label>
        <div v-if="images.length === 0 && !imageLoading" :class="status === 'PUBLISH' ? 'mx-8' : 'mx-6'" class="secondary-2--text poppins fw500 f12"> No images attached </div>
        <v-slide-group
            v-model="model"
            show-arrows
            class="pa-3"
            >
            <v-slide-item
                v-for="(img, i) in images"
                :key="img.id"
                v-slot="{ active, toggle }"
            >
                    <v-badge
                        icon="mdi-close"
                        color="secondary"
                        @click.native="removeImage({id: img.id, index: i})"
                        class="ma-3 cursor-pointer"
                        overlap>
                        <v-img
                            :src="img.url"
                            contain
                            max-width="150"
                            class="pointer-default"
                        />
                    </v-badge>
            </v-slide-item>
            <circular v-if="imageLoading" class="my-auto ml-3" />
        </v-slide-group>
        <v-divider />
        <input
            type="file"
            ref="image"
            class="d-none"
            accept=".jpeg,.png,.jpg"
            v-on:change="acceptInput"
        />
        <v-container fluid class="d-flex flex-wrap align-center" v-if="!$isReadOnly.isReadOnly(status, permission)">
            <v-btn
                v-if="type_of_question === 'multiple_choice' || type_of_question === 'checkbox' || type_of_question === 'dropdown'"
                dense
                text class="f11 text-capitalize"
                @click="$emit('addChoice', { id: question.id, name: null })">
                <v-icon class="mr-1"> mdi-plus </v-icon>
                Add Choice
            </v-btn>
            <v-btn
                dense
                text class="f11 text-capitalize poppins"
                :color="setColorRequireImage"
                @click="$refs.image.click()">
                <v-icon class="mr-1" :color="setColorRequireImage"> mdi-image </v-icon>
                Insert Image
            </v-btn>
            <section class="f12 text-capitalize poppins d-flex flex-row align-center fw500">
                <v-switch
                    v-model="question.is_require_to_answer"
                    hide-details
                    dense
                    class="mt-0 pr-0"
                ></v-switch>
                Require Answer
            </section>
        </v-container>
        <RadioButtons 
            :question="question" 
            :length="data_length(question.id)" 
            v-if="type_of_question === 'multiple_choice' || type_of_question === 'dropdown'" 
            @removeChoice="removeChoice" 
            :preview="$isReadOnly.isReadOnly(status, permission)" 
            :choices="question.data"
            :disabled="true"/>

        <Checkboxes 
            :question="question" 
            :length="data_length(question.id)" 
            v-if="type_of_question === 'checkbox'" 
            @removeChoice="removeChoice" 
            :preview="$isReadOnly.isReadOnly(status, permission)" 
            :choices="question.data"
            :disabled="true"/>

        <LinearScale :index="index" v-if="type_of_question === 'linear_scale'" :preview="$isReadOnly.isReadOnly(status, permission)" :question="question" @changeChoice="changeChoice"/>
        <Grid v-if="type_of_question === 'multiple_choice_grid' || type_of_question === 'checkbox_grid'" :question="question" :type_of_question="type_of_question" :preview="$isReadOnly.isReadOnly(status, permission)" @addChoice="addChoice" @removeChoice="removeChoice"/>
    </section>
</template>

<script>
import { mapState, mapActions } from 'vuex'
import RadioButtons from '../questiontypes/RadioButtons.vue'
import Checkboxes from '../questiontypes/Checkboxes.vue'
import LinearScale from '../questiontypes/LinearScale.vue'
import Grid from '../questiontypes/Grid.vue'

export default {
    props: ['id', 'type_of_question', 'question', 'index', "status", "permission"],
    components: {
        RadioButtons,
        Checkboxes,
        LinearScale,
        Grid
    },
    data: () => ({
        btn_group: [],
        imageRequire: false,
        images: [],
        imageLoading: true,
        model: null
    }),
    mounted() {
        this.imageLoading = true
        this.getImages()
    },
    watch: {
        type_of_question(val) {
            if(['multiple_choice', 'checkbox', 'dropdown'].includes(val)){
                this.$emit('addChoice', { id: this.question.id, name: null })
            }
        }
    },
    methods: {
        ...mapActions('instructor', [
            'addCourseEvaluationQuestionImage',
            'deleteCourseEvaluationQuestionImage',
            'addModuleEvaluationQuestionImage',
            'deleteModuleEvaluationQuestionImage',
        ]),
        
        removeChoice(e) {
            this.$emit('removeChoice', e)
        },
        changeChoice(e){
            this.$emit('changeChoice', e)
        },
        addChoice(e){
            this.$emit('addChoice', e)
        },
        data_length(id){
            return Object.keys(this.questions.find(item => item.id == id).data).length
        },
        acceptInput(index) {
            if(this.$refs.image.files[0]) {
                const reader = new FileReader()
                reader.onload = (e) => {
                    this.imageLoading = true
                    let _form = new FormData();

                    _form.append("_method", 'POST');
                    _form.append("id", this.question.id);
                    _form.append("image", this.$refs.image.files[0]);

                    if(this.question.hasOwnProperty('module_evaluation_id')) {
                        this.addModuleEvaluationQuestionImage({id: this.question.id, form: _form}).then(() => {
                            this.getImages()
                        })
                    } else {
                        this.addCourseEvaluationQuestionImage({id: this.question.id, form: _form}).then(() => {
                            this.getImages()
                        })
                    }
                }
                reader.readAsDataURL(this.$refs.image.files[0])
            }
        },

        getImages(){
            if(this.question.hasOwnProperty('module_evaluation_id')) {
                this.$api.get(`/instructor/module/evaluation/question/${this.question.id}/image`).then(res => {
                    this.images = res.data.data[0]?.image
                    this.imageLoading = false
                })
            } else {
                this.$api.get(`/instructor/course/evaluation/question/${this.question.id}/image`).then(res => {
                    this.images = res.data.data[0].image
                    this.imageLoading = false
                })
            }
        },

        removeImage(e){
            if(this.question.hasOwnProperty('module_evaluation_id')) {
                this.deleteModuleEvaluationQuestionImage(e.id).then(() => {
                    this.images.splice(e.index, 1)
                })
            } else {
                this.deleteCourseEvaluationQuestionImage(e.id).then(() => {
                    this.images.splice(e.index, 1)
                })
            }
        }
    },
    computed: {
        setColorRequireImage(){
            if(this.btn_group.includes(0)) {
                return 'primary'
            } else 'secondary-1--text'
        },

        ...mapState('instructor', {
            questions: (state) => state.questions
        }),
    }
}
</script>