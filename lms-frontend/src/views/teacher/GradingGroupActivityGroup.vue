<template>
    <section>
        <Breadcrumb
            :links="[
                {
                    label: `<  ${group_activity_title} `,
                    link: true,
                    route: (prevRoute && prevRoute.name === 'Instructor Group Activity') ? { name: 'Instructor Group Activity' , params: { id: $route.params.id, group_activity_id: $route.params.group_activity_id }, query: { ...prevRoute.query}} : { name: 'Instructor Group Activity' , params: { id: $route.params.id, group_activity_id: $route.params.group_activity_id }, query: { search: '', page: 1, paginate: 10 }}
                },
                {
                    label: `${group_name}`,
                    link: false,
                    route: null,
                },
            ]"
        />


        <v-sheet
          color="white"
          v-if="group"
          class="roboto mb-5 f14 fw500 secondary-1--text border custom-border" 
        >
            <div class="roboto f13 secondary-2--text fw500 pa-3">QUESTION:</div>

            <v-sheet
                color="transparent"
                v-if="group_activity_question"
                class="roboto mb-2 f14 pa-3 mb-3 fw500 secondary-1--text text-justify" 
                v-html="group_activity_question ? $dompurifier(group_activity_question) : ''"
                style="white-space: pre; text-wrap: wrap;"
            />
            <v-divider />

            <v-row class="pa-3">
                <v-col class="d-flex flex-row align-center">
                    <section style="width: 100%">
                        <label class="caption">MEMBERS</label>
                        <v-text-field 
                            outlined 
                            dense 
                            :value.sync="group.group_activity_has_group_members_count"
                            :readonly="true"
                            :class="`general-custom-field mb-1`"
                            hide-details="auto"/>
                    </section>
                    <v-btn dense icon class="primary--text f12 mt-4 float-right" @click="() => { group_activity_group_members_dialog = true, getGroupMembers() }">
                        <v-icon small>mdi-account-multiple</v-icon>
                    </v-btn>
                </v-col>
                <v-col>
                    <FormSelectObject
                        :label="'STATUS'"
                        :items="status"
                        :value.sync="group.checked"
                        :class_="'f14 '"
                        :loading="statusLoading"
                        @change="(e) => updateStatus(e)"
                    />
                </v-col>
            </v-row>
        </v-sheet>
       

        <LinearProgress v-if="loading"/>
        
        <v-sheet class="custom-border border pt-3" height="100vh">
            <iframe 
                v-if="group"
                :src="group.file_url"
                class="responsive-iframe"
                allowfullscreen
                webkitallowfullscreen="true"
                mozallowfullscreen="true"
                frame-border="none"
                loading="lazy"
            />
        </v-sheet>

        <GroupActivityGroupMembers
            :dialog="group_activity_group_members_dialog"
            :loading="loading1"
            :members="group_activity_group_members"
            @close="group_activity_group_members_dialog = false"
            @getMembers="() => {getGroup(), getGroupMembers()}"
            :group="group"
        />

    </section>
</template>

<script>

import { mapActions, mapState, mapMutations } from 'vuex'
import Breadcrumb from "../../layouts/teacher/InstructorBreadcrumbs.vue";
import GroupActivityGroupMembers from '@/components/teacher/course/GroupActivityGroupMembers.vue';

export default {
    components: { Breadcrumb, GroupActivityGroupMembers },
    data: () => ({
        loading: false,
        loading1: false,
        statusLoading: false,
        group_activity_title: '',
        group_name: '',
        group: null,
        prevRoute: null,
        group_activity_question: '',
        group_activity_group_members: [],
        status: [ 
            { text: 'PENDING', value: 0 },
            { text: 'CHECKED', value: 1 }
        ],
        group_activity_group_members_dialog: false,
        selected: null,
    }),
    mounted(){
        this.getGroup()
    },
    methods: {
        ...mapActions('instructor', ['getGroupActivityGroupAction', 'getGroupActivityGroupStatusAction', 'getGroupActivityGroupMembersAction']),
        
        ...mapMutations(['alertMutation']),

        getGroup(){
            this.loading = true
            this.getGroupActivityGroupAction({group_activity_id: this.$route.params.group_activity_id, group_id: this.$route.params.group_id}).then(res => {
                this.group_activity_title = res.group_activity_title
                this.group_name = res.group_activity_group ? res.group_activity_group.name : ''
                this.group = res.group_activity_group
                this.group_activity_question = res.group_activity_question
            }).catch(() => {
                this.loading = false
                this.alertMutation({
                    show: true,
                    text: "Something went wrong.",
                    type: "error"
                }) 
            }).finally(() => {
                this.loading = false
            })
        },

        updateStatus(val){
            this.statusLoading = true
            this.getGroupActivityGroupStatusAction({group_activity_id: this.$route.params.group_activity_id, group_id: this.$route.params.group_id, checked: val}).then(res => {
                this.getGroup()
                this.alertMutation({
                    show: true,
                    text: "Checked status updated",
                    type: "success"
                }) 
            }).catch(() => {
                this.statusLoading = false
                this.alertMutation({
                    show: true,
                    text: "Something went wrong.",
                    type: "error"
                }) 
            }).finally(() => {
                this.statusLoading = false
            })
        },

        getGroupMembers() {
            this.loading1 = true
            this.getGroupActivityGroupMembersAction(this.group).then(res => {
                this.group_activity_group_members = [...res.group_activity_has_group_members]
                this.loading1 = false
            }).catch(e => {
                console.log(e)
                this.loading1 = false
                this.group_activity_group_members_dialog = false
                this.alertMutation({
                    show: true,
                    text: "Something went wrong.",
                    type: "error"
                }) 
            })
        },

    },
}

</script>
