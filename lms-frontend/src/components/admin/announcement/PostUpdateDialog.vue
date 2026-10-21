<template>
    <v-dialog v-model="dialog" max-width="550" persistent eager retain-focus="false">
      <v-card v-if="announcement">
        <v-form
          ref="form"
          v-model="valid">
          <v-card-title class="d-flex align-center justify-space-between">
            <h4 class="primary--text">Update Announcement</h4>
          </v-card-title>
          <v-card-text>
            <alert 
              v-if="msg.show"
              :show = "msg.show"
              :text = "msg.text"
              :type = "msg.type"
            />
            <label class="poppins f12 secondary--text mx-1">COURSE *</label>
            <v-select   
              :items="courses"
              item-value="id"
              item-text="title"    
              outlined
              dense
              hide-details="auto"
              class="general-custom-field roboto f14 secondary-1--text fw500 mb-5"
              v-model="course_id"
              required
              disabled
              :rules="rules"
            >
            </v-select>
            <label class="poppins f12 secondary--text mx-1">CONTENT *</label>
            <RichTextEditor v-if="showMce"
              :rules="rules"
              :value="content"
              @setValue="(e) => {content = e}"
            />
            <circular v-else />
            <v-alert
              :value="alert"
              type="error"
              transition="v-scroll-y-transition"
              dense
              outlined
              class="mt-3 f13"
            >
              Announcement content is required
            </v-alert>
          </v-card-text>
          <v-divider class=""/>
          <v-card-actions class="d-flex align-center justify-end">
            <v-btn class="text-capitalize poppins secondary-1--text" text @click="()=>{
              this.$refs.form.reset()
              $emit('close')
            }">Cancel</v-btn>
            <v-btn class="text-capitalize poppins" color="primary" width="100" @click="update" :loading="loading">Update</v-btn>
          </v-card-actions>
        </v-form>
      </v-card>
    </v-dialog>
  </template>
  
  <script>
  import { mapActions, mapMutations } from "vuex";
  import Editor from "@tinymce/tinymce-vue";
  
  export default {
    props:['dialog', 'user', 'courses', 'announcement'],
    components: {
      editor: Editor,
    },
    data: () => ({
        valid: true,
        rules: [ v => !!v || 'Course is required' ],
        course_id: '',
        content: '',
        allow_comments: false,
        errors: [],
        loading: false,
        showMce: false,
        alert: false,
        mce_key: `${process.env.VUE_APP_TINYMCE_KEY}`,
        msg: {
            show: false,
            text: '',
            type: ''
        }
    }),
  
    watch: {
      dialog(val) {
        if(val) {
            this.msg = {
                show: false,
                text: '',
                type: ''
            }
            this.showMce = false;
            this.content = ''
            this.$nextTick(() => {
                setTimeout(() => {
                this.showMce = true;
                }, 2000);
            });
            this.course_id = this.announcement.course_id,
            this.content = this.announcement.content
        }
      }
    },
  
    beforeMount(){
    },
  
    methods: {
        ...mapActions('admin', ['updateAnnouncementAction']),
        ...mapMutations(['alertMutation']),

        update() {
            if(this.$refs.form.validate()){
              this.msg = {
                show: false,
                text: '',
                type: ''
              }
              this.loading = true
              this.updateAnnouncementAction({ id: this.announcement.id, content: this.content, allow_comments: this.announcement.allow_comments }).then(() => {
                  this.msg = {
                      show: true,
                      text: 'Announcement successfully updated.',
                      type: "success"
                  }
                  this.loading = false
                  this.$emit('posted')
              }).catch(() => {
                this.msg = {
                    show: true,
                    text: 'Something went wrong',
                    type: "error"
                }
                this.loading = false
              })
            }
        }
    },
  }
  </script>