<template>
    <SkeletonLoaderCard height="400" v-if="loading"/>
    <v-card class="custom-border border pa-5 fade primary-font f13" v-else>
        <v-row dense class="mb-1">
            <v-col cols="4" sm="2" class="fw500 secondary-1--text">TYPE OF FIELD</v-col>
            <v-col class="fw500 secondary-1--text">FIELD LABEL</v-col>
        </v-row>
        <LinearProgress v-if="updateLoading" />
        <v-divider/>
        <v-row dense v-for="(item) in defaults" :key="item.type_of_field" class="mt-3">
            <v-col cols="4" sm="2" class="secondary-1--text">
                <FormSelectObject
                    :items="field_types"
                    :value.sync="item.type_of_field"
                    :disabled="true"
                />
            </v-col>
            <v-col class="secondary-1--text">
			    <FormTextFieldOptional
                    :class_="'f12'"
                    :value="item.field_label"
                    :disabled="true"
                />
            </v-col>
        </v-row>
        <v-row dense v-for="(item, i) in module_fields" :key="i" class="mt-3">
            <v-col cols="4" sm="2" class="secondary-1--text">
                <FormSelectObject
                    :items="field_types"
                    :value.sync="item.input_field_type"
                    :disabled="true"
                />
            </v-col>
            <v-col class="secondary-1--text d-flex flex-row">
                <div style="flex-grow: inherit !important;">
                    <FormTextFieldOptional
                        :class_="'f12'"
                        :value="item.input_field_label"
                        :disabled="true"
                    />
                    <div v-if="item.input_field_type === 'dropdown'" class="ml-1">
                        <FormLabel :label="'OPTIONS:'"/>
                        <div v-for="(option, i) in JSON.parse(item.data)" :key="i" class="ml-3 mb-1">
                            {{ option }}
                        </div>
                    </div>
                </div>
                <v-btn @click="() => { action = 'edit', selectedField = item, addFieldDialog = true}" icon dense>
                    <v-icon size="16" >mdi-pencil-outline</v-icon>
                </v-btn>
                <v-btn @click="() => { selectedField = item, deleteDialog = true}" icon dense>
                    <v-icon size="16" >mdi-trash-can-outline</v-icon>
                </v-btn>
            </v-col>
        </v-row>
        <section class="mt-2 d-flex flex-row">
            <ButtonPrimary :icon="'mdi-plus'" :label="'Add Field'" :outlined="true" @click="() => { action = 'add', selectedField = null, addFieldDialog = true }"/>
        </section>

        <DeleteDialog 
            :dialog="deleteDialog"
            type="Module Field"
            :loading="deleteLoading"
            @close="deleteDialog=false"
            @delete="deleteField"
        />
        <AddField :action="action" :dialog="addFieldDialog" @close="() => { selectedField = null, updateLoading = false, addFieldDialog = false, getFields() }" :field="selectedField"/>
    </v-card>
</template>

<script>
import SkeletonLoaderCard from '@/components/reusable/SkeletonLoaderCard.vue'
import { mapActions, mapState, mapMutations } from 'vuex';
import AddField from '@/components/admin/module_settings/AddField.vue';

export default {
    components: {
        SkeletonLoaderCard,
        AddField
    },
    data: () => ({
        defaults: [
            { type_of_field: 'text_field', field_label: 'MODULE TITLE' },
            { type_of_field: 'text_field', field_label: 'MODULE DESCRIPTION' }
        ],
        loading: false,
        updateLoading: false,
        deleteLoading: false,
        field_types: [
            { text: 'TEXT AREA', value: 'text_area'},
            { text: 'TEXT FIELD', value: 'text_field'},
            { text: 'NUMBER', value: 'number'},
            { text: 'CALENDAR', value: 'calendar'},
            { text: 'DROPDOWN', value: 'dropdown'},
        ],
        addFieldDialog: false,
        deleteDialog: false,
        action: 'add',
        selectedField: null
    }),
    mounted(){
        this.loading = true
        this.getFields()
    }, 
    methods: {
        ...mapMutations(['alertMutation']),
        ...mapActions('admin', ['getModuleFieldsAction', 'addCustomModuleFields', 'deleteModuleFieldsAction']),

        getFields(){
            this.getModuleFieldsAction().catch(() => {
                this.alertMutation({
                    show: true,
                    text: 'Something went wrong',
                    type: 'error'
                })
            }).finally(() => {
                this.updateLoading = false
                this.loading = false
            })
        },

        deleteField(){
            this.deleteLoading = true
            this.deleteModuleFieldsAction(this.selectedField.id).then(() => {
                this.deleteDialog = false
                this.deleteLoading = false
                this.alertMutation({
                    show: true,
                    text: 'Field Deleted Successfully.',
                    type: 'success'
                })
                this.updateLoading = true
                this.getFields()
            }).catch(() => {
                this.deleteDialog = false
                this.deleteLoading = false
                this.alertMutation({
                    show: true,
                    text: 'Something went wrong',
                    type: 'error'
                })
            })
        },

    },
    computed: {
        ...mapState('admin', {
            module_fields: (state) => state.module_fields
        })
    }
}

</script>
