<template>
  <v-dialog
    v-model="dialog"
    max-width="550"
    persistent
    eager
    retain-focus="false"
  >
    <v-card>
      <v-form ref="form" v-model="valid">
        <v-card-title class="d-flex align-center justify-space-between">
          <div class="poppins f16 fw600 primary--text">
            {{ action == 'create' ? 'Create' : 'Update'}} Course Group
          </div>
        </v-card-title>
        <v-card-text>
            <label v-if="action == 'create' && course_id === null" class="caption">COURSES</label>
            <v-autocomplete
                v-if="action == 'create' && course_id === null"
                outlined 
                dense 
                :items="courses"
                v-model="form.course_id"
                :filter="filterData"
                :loading="loading"
                color="primary"
                auto-select-first
                clearable
                placeholder="Search course"
                item-text="title"
                item-value="id"
                hide-details="auto"
                class="roboto f14"
            >
                <template v-slot:item="{item, attrs, on}">
                    <v-list-item v-on="on" v-bind="attrs">
                        <v-list-item-content dense>
                            <v-list-item-title class="roboto">
                                <div class="f10 poppins secondary-2--text text-uppercase ">
                                    {{ item.status }}ED &nbsp; | &nbsp; {{  item.type_of_learning }} &nbsp; | &nbsp; {{  item.price == 0 ? 'FREE' : item.price }} 
                                    <span v-if="item.available_until">
                                        &nbsp; |
                                        <span v-if="item.available_until.start_date"> {{ $dateFormat.mmDDyy(item.available_until.start_date) }} </span>
                                        <span v-if="item.available_until.end_date"> until {{ $dateFormat.mmDDyy(item.available_until.end_date) }} </span>
                                    </span>
                                </div>
                                <span class="text-capitalize f14">{{item.title}}</span>
                            </v-list-item-title>
                        </v-list-item-content>
                    </v-list-item>
                </template>
            </v-autocomplete>
            <FormTextFieldRequired
                :label="'GROUP NAME *'"
                :value.sync="form.name"
                :error="errors && errors.name"
            />
        </v-card-text>
        <v-divider class="" />
        <v-card-actions class="d-flex align-center">
            <v-spacer />
            <div class="d-flex flex-row">
                <v-btn
                    class="text-capitalize poppins secondary-1--text"
                    text
                    @click="
                    () => {
                        this.$refs.form.reset();
                        $emit('close');
                    }
                    "
                    dense
                    >Cancel</v-btn
                >
                <v-btn
                    class="text-capitalize poppins"
                    color="primary"
                    width="100"
                    @click="submit"
                    :loading="createLoading"
                    :disabled="!form.course_id && !form.title"
                    dense
                    >Save</v-btn
                >
            </div>
        </v-card-actions>
      </v-form>
    </v-card>
  </v-dialog>
</template>

<script>
import { mapActions, mapMutations, mapState } from "vuex";

export default {
  props: {
    action: {
      default: 'create',
      type: String
    },
    dialog: {
      default: false,
      type: Boolean,
    },
    loading: {
      default: false,
      type: Boolean,
    },
    selected_forum_post: {
      type: Object || null,
      default: null,
    },
    group: {
      default: null,
      type: Object || null
    },
    course_id: {
      default: null,
      type: Number || null
    }
  },
  data: () => ({
    form: {
        course_id: null,
        name: '',
        sub_groups: []
    },
    valid: true,
    createLoading: false
  }),

  watch: {
    dialog(val) {
      if(val) {
        this.form = { ...this.group, sub_groups: [] }
        if(this.course_id) {
          this.form.course_id = this.course_id
        }
      }
    }
  },

  methods: {
    ...mapActions('instructor', [
        'createCourseGroupAction',
        'updateCourseGroupAction'
    ]),
    ...mapMutations(["alertMutation", "errorsMutation"]),


    filterData(item, queryText) {
        return item.title.toLowerCase().includes(queryText.toLowerCase())
    },

    submit() {
        if(this.$refs.form.validate()){
          if(this.action === 'create') {
            this.createLoading = true;
            this.createCourseGroupAction(this.form).then(() => {
                this.createLoading = false;
                this.reset()
                this.alertMutation({
                    show: true,
                    text: "Course group created successfully",
                    type: "success",
                });
                this.$emit('getData');
                this.$emit('close');
            }).catch(() => {
                this.createLoading = false;
                this.alertMutation({
                    show: true,
                    text: "Something went wrong",
                    type: "error",
                });
            })
          } else {
            this.createLoading = true;
            this.updateCourseGroupAction(this.form).then(() => {
                this.createLoading = false;
                this.reset()
                this.alertMutation({
                    show: true,
                    text: "Course group updated successfully",
                    type: "success",
                });
                this.$emit('getData');
                this.$emit('close');
            }).catch(() => {
                this.createLoading = false;
                this.alertMutation({
                    show: true,
                    text: "Something went wrong",
                    type: "error",
                });
            })

          }
        }
    },

    reset() {
      this.form = {
        name: '',
        sub_groups: []
      }
    }
  },

  computed: {
    ...mapState({
        user: (state) => state.user,
        errors: (state) => state.errors,
    }),
    
    ...mapState("instructor", {
      courses: (state) => state.courses,
    }),
  },
};
</script>
