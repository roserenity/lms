<template>
    <div :class="`d-flex ${wrap ? 'flex-column' : '' } align-center justify-end`" style="width: inherit">
        <div class="d-flex align-center justify-end ml-auto my-2" style="width: 25px">
            <FormLabel :label="'SHOW:'" class="mr-1 f10"/>
            <FormSelectArray 
                :items="['5', '10', '15']"
                :value.sync="paginate_"
                :loading="loading"
                :class_="' f11 general-custom-field pagination-select'"
            />
        </div>
        <v-pagination
            dense
            :value="page"
            :length="pageCount"
            :total-visible="5"
            :class="wrap ? 'ml-auto' : ''"
            color="primary"
            flat
            elevation="0"
            class="f10 my-2"
            @input="$emit('page', $event)"
        >
        </v-pagination>
    </div>
</template>

<style>
.pagination-select.v-text-field--outlined fieldset {
    height: 38px !important;
    top: -2px !important;
}
</style>

<script>
import { defineComponent } from 'vue';

export default defineComponent({
    props: {
        wrap: {
            type: Boolean,
            default: false
        },
        pageCount: {
            type: Number,
            default: 1
        },
        loading: {
            type: Boolean,
            default: false
        },
        paginate: {
            type: String,
            default: '10'
        },
        page: {
            type: Number,
            default: 1
        }
    },
    data: () => ({
        paginate_: '10'
    }),
    watch: {
        paginate_(val) {
            this.$emit('paginate', val)
        },
        paginate(val) {
            this.paginate_ = val
        },
    },
    mounted(){
        this.paginate_ = this.paginate
    }
})
</script>