<template>
    <v-tabs 
        class="tabs border" 
        color="primary" 
        v-model="tab" 
        @change="$emit('update:value', $event)" 
        background-color="transparent" 
        id="course-manager" 
        active-class="active"
        show-arrows>
        <v-tab v-for="item in tabs"
            :key="item" 
            active-class="active"
            class="primary-font f14 fw600 text-capitalize poppins"
            :disabled="disabled">
            {{item}}
        </v-tab>
    </v-tabs>
</template>

<script>
export default {
    props: {
        tabs: {
            type: Array,
            default: []
        },
        value: {
            type: Number,
            default: 0
        },
        disabled: {
            type: Boolean,
            default: false
        },
    },
    data: () => ({
        tab: 0
    }),
    mounted(){
        this.tab = this.value
    },
    watch: {
        value(val) {
            this.tab = val
        }
    }
}
</script>