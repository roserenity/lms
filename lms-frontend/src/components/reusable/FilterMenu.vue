<template>
    <v-menu ref="menu" offset-y transition="slide-y-transition" bottom duration :close-on-content-click="false" class="">
        <template v-slot:activator="{ on, attrs }">
        <v-btn 
            outlined
            class="text-capitalize poppins fw600 f12 small ma-1 ml-auto"
            v-bind="attrs"
            v-on="on"
            color="primary"
            >
                Filter
            <v-icon small color="primary" right>
                mdi-tune
            </v-icon>
        </v-btn>
    </template>
    <v-card :width="width">
        <v-card-text>
            <slot></slot>
        </v-card-text> 
        <v-divider/>  
        <v-card-text>
        <v-btn small outlined color="primary" block dense class="mt-2" @click="resetFilters">
            RESET FILTERS
        </v-btn>    
        <v-btn small color="primary" block dense class="mt-2" @click="applyFilters">
            APPLY
        </v-btn>
        </v-card-text>
    </v-card>
    </v-menu>
</template>

<script>
export default {
    name: 'FilterMenu',
    props: {
        initialFilter: {
            type: Object,
            default: () => ({})
        },
        width: {
            type: String,
            default: '300'
        }
    },
    data() {
        return {
            filter: { ...this.initialFilter }
        };
    },
    methods: {
        resetFilters() {
            this.filter = { ...this.initialFilter };
            this.$emit('resetFilters');
        },
        applyFilters() {
            this.$refs.menu.isActive = false;
            this.$emit('applyFilters', this.filter);
        }
    }
};
</script>

<style scoped>
.poppins {
    font-family: 'Poppins', sans-serif;
}
</style>
  