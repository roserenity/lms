<template>
  <v-dialog v-model="dialog" max-width="550" persistent eager retain-focus="false">
    <v-card>
      <v-form
        ref="form"
        v-model="valid">
        <v-card-title class="d-flex align-center justify-space-between">
          <h4 class="primary--text">{{ selected_forum_post ? 'Update' : 'Post' }} Topic</h4>
        </v-card-title>
        <v-card-text>
          <label class="caption">COURSE</label>
          <v-select   
            :items="courses"
            item-value="id"
            item-text="title"    
            outlined
            dense
            class="general-custom-field roboto f14 secondary-1--text fw500"
            v-model="form.course_id"
            hide-details="auto"
          >
            <template slot="item" slot-scope="data">
              <v-list-item-content>
                <v-list-item-title class="roboto f14 fw500" >
                  <div class="roboto f10">
                    {{  data.item.type_of_learning }}
                    <span v-if="data.item.available_until">
                        &nbsp; | &nbsp; 
                        <span v-if="data.item.available_until.start_date"> {{ $dateFormat.mmDDyy(data.item.available_until.start_date) }} </span>
                        <span v-if="data.item.available_until.end_date"> until {{ $dateFormat.mmDDyy(data.item.available_until.end_date) }} </span>
                    </span>
                  </div>
                  {{  data.item.title }}
                </v-list-item-title>
              </v-list-item-content>
            </template>
            <template slot="selection" slot-scope="data" >
              <div class="roboto f14 fw500 my-1">
                <div class="roboto f10">
                  {{  data.item.type_of_learning }} &nbsp;
                </div>
                {{  data.item.title }}
              </div>
            </template>
          </v-select>
          <FormSelectObject
              :label="'TOPIC CATEGORY'"
              :items="topic_categories.filter((i) => i.text != 'All')"
              :value.sync="form.topic_category"
              :required="true"
              :error="errors && errors.topic_category"
              :class_="'f14'"
          />
          <FormTextAreaRequired
            :label="'TITLE *'"
            :value.sync="form.title"
            :error="errors && errors.title"
            :hasMax="true"
          />
          <label class="caption">CONTENT</label>
          <RichTextEditor v-if="showMce"
            :rules="rules"
            :value="form.content"
            @setValue="(e) => {form.content = e}"
            class="mb-5"
          />
          <circular v-else />
          <v-alert
            v-if="errors && errors.content"
            type="error"
            transition="v-scroll-y-transition"
            dense
            outlined
            class="mt-3 f13"
          >
            Content is required
          </v-alert>

          <label class="caption">ATTACHMENTS</label>
          <div class="mb-5">
            <v-chip 
                v-for="(item, i) in form.attachments"
                class="ma-1"
                color="primary"
                :key="i"
                small
                outlined
                :close="!selected_forum_post"
                @click:close="remove(i)"
                >
              <v-icon small left>
                  mdi-image
              </v-icon>
                {{ selected_forum_post ? item.file_name : item.name }}
            </v-chip>
          </div>
          <FormUploadFile 
            v-if="!selected_forum_post"
            :accepted_types="'.jpg, .jpeg, .png, .txt, .doc, .docx, .ppt, .pptx, .pdf'"
            :accept="''"
            :accepted_types_list="[
                'image/jpeg',
                'image/png',
                'text/plain',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'application/pdf'
            ]"
            :multiple="true"
            @acceptFile="acceptFile"/>

          <v-alert
            v-if="err !== ''"
            type="error"
            transition="v-scroll-y-transition"
            dense
            outlined
            class="mt-3 f13"
          >
            {{  err }}
          </v-alert>

        </v-card-text>
        <v-divider class=""/>
        <v-card-actions class="d-flex align-center justify-end">
          <v-btn class="text-capitalize poppins secondary-1--text" text @click="()=>{
            this.$refs.form.reset()
            $emit('close')
          }">Cancel</v-btn>
          <v-btn class="text-capitalize poppins" color="primary" width="100" @click="submit()" :loading="loading">{{ selected_forum_post ? 'Update' : 'Post' }}</v-btn>
        </v-card-actions>
      </v-form>
    </v-card>
  </v-dialog>
</template>

<script>
import Editor from "@tinymce/tinymce-vue";
import { mapActions, mapMutations, mapState } from 'vuex'
import { topic_categories } from '@/constants/forum';

export default {
  props: {
    dialog : {
      default : false,
      type: Boolean,
    },
    selected_forum_post: {
      type: Object || null,
      default: null
    },
    courses: {
      type: Array,
      default: []
    }
  },
  components: {
    editor: Editor,
  },
  data: () => ({
    valid: true,
    rules: [ v => !!v || 'This is required' ],
    course_id: '',
    errors: {},
    loading: false,
    component_key: 1,
    alert: false,
    showMce: true,
    mce_key: `${process.env.VUE_APP_TINYMCE_KEY}`,
    form: {
      course_id: null,
      title: '',
      topic_category: 'general_discussion',
      content: '',
      attachments: [],
    },
    topic_categories,
    courses: [],
    err: ''
  }),

  watch: {
    dialog(val) {
      if(val) {
        this.errors = {}
        this.form = {
          course_id: null,
          title: '',
          topic_category: 'general_discussion',
          content: '',
          attachments: []
        }
        this.showMce = false;

        if(this.selected_forum_post) {
          this.form = {...this.selected_forum_post}
        }

        this.$nextTick(() => {
          setTimeout(() => {
            this.showMce = true;
          }, 2000);
        });
      }

      this.loading = false
    }
  },

  methods: {
    ...mapActions({
        postForumPost(dispatch, payload) {
          return dispatch(`${this.user.role.toLowerCase() === 'user' ? 'usr' : this.user.role.toLowerCase()}/postForumPostAction`, payload);
        },
        updateForumPost(dispatch, payload) {
          return dispatch(`${this.user.role.toLowerCase() === 'user' ? 'usr' : this.user.role.toLowerCase()}/updateForumPostAction`, payload);
        },
    }),
    ...mapMutations(['alertMutation']),
    
    remove(i){
        this.form.attachments.splice(i, 1)
    },

    acceptFile(e) {
      this.err = ''
      console.log(e)
      console.log(this.form)
      if(this.form.attachments.length === 5) {
        this.err = 'You could only attach up to 5 files'
      } else {
        this.form.attachments.push(e)
      }
    },

    submit() {

      if(this.$refs.form.validate()){
        this.errors = {}

        if(!this.selected_forum_post) {
          this.loading = true

          let formData = new FormData()
          formData.append('course_id', this.form.course_id)
          formData.append('title', this.form.title)
          formData.append('topic_category', this.form.topic_category)
          formData.append('content', this.form.content)
          formData.append('user_id', this.user.id)
          formData.append('status', this.user.role === 'USER' ? 'PENDING' : 'APPROVED')
          formData.append("_method", 'POST');

          this.form.attachments.forEach((item, i) => {
            formData.append(`attachments[${i}]`, item)
          });

          this.postForumPost(formData).then(res => {
            this.loading = false
            this.$emit('posted')
            this.alertMutation({
              show: true,
              text: this.user.role === 'USER' ? "Your post is now pending approval" : "Your post has been posted successfully",
              type: "success"
            })
          })
          .catch(errs => {
            this.errors = errs
            this.loading = false
          })
        } else {
          this.loading = true
          
          let formData = new FormData()
          formData.append('id', this.selected_forum_post.id)
          formData.append('course_id', this.form.course_id)
          formData.append('title', this.form.title)
          formData.append('topic_category', this.form.topic_category)
          formData.append('content', this.form.content)
          formData.append('user_id', this.user.id)
          formData.append("_method", 'PUT');

          this.updateForumPost({ id: this.selected_forum_post.id, form: formData}).then(res => {
            this.loading = false
            this.$emit('posted')
            this.alertMutation({
              show: true,
              text: "Topic has been updated",
              type: "success"
            })
          })
          .catch(errs => {
            this.errors = errs
            this.loading = false
          })
        }
      }
    }
  },

  computed: {
    ...mapState({
        user: (state) => state.user,
    }),
  }
}
</script>