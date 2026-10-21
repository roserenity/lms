<template>
  <v-dialog persistent v-model="dialog" max-width="550">
    <v-card>
      <v-card-title class="d-flex align-center justify-space-between ma-0 pa-0">
        <div class="poppins primary--text px-5 py-3 fw600 f14">
          {{ action === 'mine' ? 'Move' : 'Duplicate and Move' }} Assessment
        </div>
        <v-btn icon @click="$emit('close')" class="mr-2">
          <v-icon size="20">mdi-close</v-icon>
        </v-btn>
      </v-card-title>
      <v-card-text>
        <FormLabel
          :label="'ASSESSMENT TITLE'"
        />
        <v-text-field
          dense
          outlined
          class="general-custom-field secondary-1--text f14 fw600 roboto mb-2"
          hide-details
          v-model="data.title"
          v-on:input="updateAssessment(data)"
        />
        <FormLabel
          :label="'COURSES'"
        />
        
        <v-text-field
            dense
            outlined
            class="general-custom-field secondary-1--text f14 fw600 roboto mb-2"
            prepend-inner-icon="mdi-magnify"
            placeholder="Search Course"
            hide-details
            v-model="search"
            :loading="courseLoading"
          />
        <v-list dense v-if="items.length > 0">
          <v-list-item-group v-model="selected" active-class="active border">
            <v-list-group
              v-for="item in items"
              :key="item.id"
              no-action
            >
              <template v-slot:activator>
                <v-list-item-content>
                  <div class="roboto f10 secondary-2--text text-uppercase">
                    <span class="poppins fw600 text-left f10" v-if="item.course_code">
                      {{ item.course_code }} 
                    </span>
                    {{  item.type_of_learning }} &nbsp; | &nbsp; {{ item.is_free ? 'FREE' : `PHP ${item.price ? item.price : 0}` }}
                    <span v-if="item.available_until">
                        &nbsp; | &nbsp; 
                        <span v-if="item.available_until.start_date"> {{ $dateFormat.mmDDyy(item.available_until.start_date) }} </span>
                        <span v-if="item.available_until.end_date"> until {{ $dateFormat.mmDDyy(item.available_until.end_date) }} </span>
                    </span>
                  </div>
                  <v-list-item-title v-text="item.title ? item.title : 'Untitled'"></v-list-item-title>
                </v-list-item-content>
              </template>

              <v-list-group
                v-for="_module in item.modules"
                :key="_module.id"
                no-action
                sub-group
                dense
              >
              <template v-slot:activator>
                <v-list-item-content  @click="setSelected(_module)">
                  <FormLabel
                    :label="'MODULE'"
                  />
                  <v-list-item-title v-text="_module.title ? _module.title : 'Untitled'"></v-list-item-title>
                </v-list-item-content>
                <v-list-item-action v-if="selected_ && selected_.uuid === _module.uuid" >
                  <v-icon size="20" color="primary">mdi-check</v-icon>
                </v-list-item-action>
              </template>
              <!-- <template v-slot:activator="{ props, active }">
                <v-list-item-content @click.stop="setSelected(_module)">
                  <FormLabel :label="'MODULE'" />
                  <v-list-item-title>  {{ _module.title ? _module.title : 'Untitled' }}erte </v-list-item-title>
                  <v-list-item-action>
                    <v-icon v-if="active" size="20" color="primary">mdi-check</v-icon>
                  </v-list-item-action>
                </v-list-item-content>
              </template> -->


                <v-list
                  v-if="_module.topics.length > 0"
                  v-for="_topic in _module.topics"
                  :key="_topic.id"
                  dense
                >
                  <v-list-item :key="_topic.id" :value="_topic.title" @click="setSelected(_topic)">
                    <template v-slot:default="{ active }">
                      <v-list-item-content class="ml-9">
                        <FormLabel
                          :label="'TOPIC'"
                        />
                        <v-list-item-title v-text="_topic.title ? _topic.title : 'Untitled'"
                        :class="active ? 'primary--text' : ''"></v-list-item-title>
                      </v-list-item-content>
                      <v-list-item-action v-if="selected_ && selected_.uuid === _topic.uuid">
                        <v-icon size="20" color="primary">{{
                          active ? "mdi-check" : ""
                        }}</v-icon>
                      </v-list-item-action>
                    </template>
                  </v-list-item>
                  <v-list-item
                    v-if="_topic.sub_topics.length > 0"
                    v-for="sub_topic in _topic.sub_topics"
                    :key="sub_topic.id"
                    :value="sub_topic.title"
                    @click="setSelected(sub_topic)"
                  >
                    <template v-slot:default="{ active }">
                      <v-list-item-content class="ml-13">
                        <FormLabel
                          :label="'SUBTOPIC'"
                        />
                        <v-list-item-title
                        :class="active ? 'primary--text' : ''">{{ sub_topic.title ? sub_topic.title : 'Untitled' }}</v-list-item-title>
                      </v-list-item-content>
                      <v-list-item-action v-if="selected_ && selected_.uuid === sub_topic.uuid">
                        <v-icon size="20" color="primary">{{
                          active ? "mdi-check" : ""
                        }}</v-icon>
                      </v-list-item-action>
                    </template>
                  </v-list-item>
                  
                </v-list>
                
                <v-list-item v-if="_module.topics.length === 0">
                    <v-list-item-title> No topics available </v-list-item-title>
                </v-list-item>
              </v-list-group>
            </v-list-group>
          </v-list-item-group>
        </v-list>
        <div class="text-center poppins f14 secondary-2--text fw500 my-5" v-else>
          No course found
        </div>
      </v-card-text>
      <v-card-actions class="d-flex align-center justify-end mx-2">
        <div class="d-flex align-center">
          <v-btn @click="$emit('close')" text class="poppins text-capitalize f13"
            >Cancel</v-btn
          >
          <v-btn
            color="primary"
            class="poppins text-capitalize f13"
            @click="move"
            :loading="loading"
            :disabled="!(!!selected_)"
            >Move</v-btn
          >
        </div>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
import { mapActions, mapMutations } from "vuex";
export default {
  props: ["dialog", "data", "courses", "action", "courseLoading"],
  data: () => ({
    selected: [],
    selected_: null,
    loading: false,
    items: [],
    search: ''
  }),
  mounted(){
    this.items = [...this.courses]
  },
  watch: {
    dialog(val) {
      if(!val) {
        this.selected = []
        this.selected_ = null
        this.items = [...this.courses]
      }
      this.search = ''
    },
    search(val){
      if (val == '') {
        this.items = [...this.courses]
      } else {
        this.items = this.items.filter(course => String(`${course.title} ${course.course_code}`).toLowerCase().search(val.toLowerCase()) !== -1)
      }
    },
    courses(val){
      this.items = [...this.courses]
    }
  },
  methods: {
    ...mapActions("instructor", [
      "updateAssessmentAction",
      "moveAssessmentAction",
      "moveAssessmentSharedWithMeAction",
      "showSharedWithMeAction",
    ]),
    ...mapMutations(["alertMutation"]),

    setSelected(item) {
      // if(!this.selected) {
      //   this.selected_ = null
      // } else {
        if(this.selected_ === item){
          this.selected_ = null
        } else {
          this.selected_ = item
        }
        
      //}
    },

    updateAssessment(data) {
      this.action == "mine" ? this.updateAssessmentAction(data) : "";
    },

    move() {
      this.loading = true;
      if (this.action == "mine") {
        let params = {
          assessment_id: this.data.id,
          course_id: this.selected_.course_id,
        }

        if(this.selected_.hasOwnProperty('module_id')) {
          params = {
            ...params,
            module_id: this.selected_.module_id,
            topic_or_sub_topic_id: this.selected_.id,
            is_topic: this.selected_.hasOwnProperty('sub_topics')
          }
        } else {
          params = {
            ...params,
            module_id: this.selected_.id,
          }
        }
        this.moveAssessmentAction(params).then(() => {
          this.loading = false;
          this.selected = [];
          this.selected_ = null
          this.alertMutation({ show: true, text: "Assessment Successfully Moved!", type: "success" });
          this.$emit('getMyAssessments')
          this.$emit("close");
        }).catch(() => {
          this.loading = false;
          this.selected_ = null
          this.$emit("close");
          this.alertMutation({ show: true, text: "Something went wrong", type: "error" });
        })
      } else {
        let params = {
          assessment_id: this.data.id,
          course_id: this.selected_.course_id,
        }
        if(this.selected_.hasOwnProperty('module_id')) {
          params = {
            ...params,
            module_id: this.selected_.module_id,
            topic_or_sub_topic_id: this.selected_.id,
            is_topic: this.selected_.hasOwnProperty('sub_topics'),
            title: this.data.title,
          }
        } else {
          params = {
            ...params,
            module_id: this.selected_.id,
            title: this.data.title,
          }
        }
        this.moveAssessmentSharedWithMeAction(params).then(() => {
          this.loading = false;
          this.selected = [];
          this.alertMutation({ show: true, text: "Assessment has been Moved", type: "success" });
          this.$emit('getMyAssessments')
          this.$emit("close");
        }).catch(() => {
          this.loading = false;
          this.$emit("close");
          this.alertMutation({ show: true, text: "Something went wrong", type: "error" });
        })
      }
    },
  },
};
</script>

<style>
.active {
  background-color: transparent !important;
  border-radius: 5px !important;
}

.inner_p > p {
    margin-bottom: 2px !important;
    margin-top: 2px !important;
}
</style>