<template>
    <section>
        <v-row no-gutters>
            <v-col cols="9">
                <section class="description_div roboto f14 fw500 secondary-1--text" >
                    <b>{{module.title ? module.title : 'Untitled' }}</b> 
                </section>
            </v-col>
            <v-col cols="3">
                <section class="d-flex flex-row">
                    <v-icon size="18" class="float-right ml-auto" v-if="module.module_is_locked || (module.validity === 'lock' ? ( index > 0 ? !prev_module_done : true) : false)"> mdi-lock-outline </v-icon>
                    <v-badge
                        bordered
                        size='10'
                        inline
                        :class="!(module.module_is_locked || (module.validity === 'lock' ? ( index > 0 ? !prev_module_done : true) : false)) && 'ml-auto'"
                        :color="moduleIsDone ? 'success' : 'warning'"
                    />
                </section>
            </v-col>
        </v-row>    
        
        <div class="description_div f10 secondary-2--text mt-1 mb-3 " v-if="module.module_is_locked && module.validity === 'date'">
          This module will be accessible
          <template v-if="dateFormattedAvailableFrom"> from {{ dateFormattedAvailableFrom }}</template>
          <template v-if="dateFormattedAvailableTo"> until {{ dateFormattedAvailableTo }}</template>
        </div>
        <section v-if="module.description" class="description_div f12 secondary-2--text mt-5" v-html="module.description ? $dompurifier(module.description) : ''"></section>
        <section v-if="module.contact_person" class="description_div f12 secondary-2--text mt-5"> 
            <div class="fw600 mb-1">
                CONTACT PERSON
            </div>{{ module.contact_person  }} </section>
        <section v-if="module.version" class="description_div f12 secondary-2--text mt-5"> 
            <div class="fw600 mb-1">
                VERSION
            </div>{{ module.version  }} </section>
        <section v-if="module.language && module.language.length > 0" class="description_div f12 secondary-2--text mt-5">
            <div class="fw600 mb-1">
                LANGUAGE
            </div>
            {{ Array.isArray(module.language) ? module.language.join(', ') : module.language }} </section>
        <section v-if="module.data" class="description_div f12 secondary-2--text"> 
            <div v-for="field in custom_module_fields" :key="field.id" class="mt-5">
                <div v-if="module.data[field.input_field_name]" :style="{ whiteSpace: field.input_field_type === 'text_area' ? 'pre-line' : 'normal'}">
                    <div class="fw600 mb-1">
                        {{ field.input_field_label }} 
                    </div>
                    {{ Array.isArray(module.data[field.input_field_name]) ? module.data[field.input_field_name].join(', ') : module.data[field.input_field_name] }}
                </div>
            </div>
        </section>

    </section>
</template>

<style scoped>

.description_div {
  white-space: normal !important;
  word-wrap: break-word !important;
}

</style>

<script>
import moment from 'moment'

export default {
    props: ['module', 'index', 'prev_module_done', 'custom_module_fields'],

    computed: {
        moduleIsDone() {
            if(!this.module) return false

            return [this.module.module_topic_is_done, this.module.module_subtopic_is_done, this.module.module_assessment_is_done, this.module.module_evaluation_is_done].every((item) => item === true)
        
        },
        
        dateFormattedAvailableFrom() {
            return this.module.available_from ? moment(this.module.available_from).format('LL') : ''
        },

        dateFormattedAvailableTo() {
            return this.module.available_to ? moment(this.module.available_to).format('LL') : ''
        },
    }
}

</script>