<template>
  <v-dialog v-model="dialog" max-width="550" persistent eager retain-focus="false">
    <v-card>
      <v-form
        ref="form"
        v-model="valid">
        <v-card-title class="d-flex align-center justify-space-between">
          <h4 class="primary--text">Post Announcement</h4>
        </v-card-title>
        <v-card-text>
          <label class="caption">COURSE *</label>
          <v-select   
            :items="courses"
            item-value="id"
            item-text="title"    
            outlined
            dense
            class="general-custom-field roboto f14 secondary-1--text fw500 mb-5"
            v-model="course_id"
            required
            :rules="rules"
            hide-details="auto"
          >
            <template slot="item" slot-scope="data">
              <v-list-item-content>
                <v-list-item-title class="roboto f14 fw500" >
                  <div class="roboto f10 secondary-2--text text-uppercase">
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
                  {{  data.item.type_of_learning }}
                  <span v-if="data.item.available_until">
                      &nbsp; | &nbsp; 
                      <span v-if="data.item.available_until.start_date"> {{ $dateFormat.mmDDyy(data.item.available_until.start_date) }} </span>
                      <span v-if="data.item.available_until.end_date"> until {{ $dateFormat.mmDDyy(data.item.available_until.end_date) }} </span>
                  </span>
                </div>
                {{  data.item.title }}
              </div>
            </template>
          </v-select>
          <!-- <FormTextAreaRequired 
            :label="'CONTENT *'"
            :value.sync="content"
            :error="errors.content"
          />  -->
          <label class="poppins f12 secondary--text mx-1">CONTENT *</label>
          <RichTextEditor v-if="showMce"
            :rules="rules"
            :value="content"
            @setValue="(e) => {content = e}"
          />
          <circular v-else />
          <v-alert
            v-if="errors.content"
            type="error"
            transition="v-scroll-y-transition"
            dense
            outlined
            class="mt-3 f13"
          >
            Announcement content is required
          </v-alert>
          <div class="d-flex align-center">
            <v-switch inset dense v-model="allow_comments"/>
            <div class="roboto">Enable comments</div>
          </div>

        </v-card-text>
        <v-divider class=""/>
        <v-card-actions class="d-flex align-center justify-end">
          <v-btn class="text-capitalize poppins secondary-1--text" text @click="()=>{
            this.$refs.form.reset()
            $emit('close')
          }">Cancel</v-btn>
          <v-btn class="text-capitalize poppins" color="primary" width="100" @click="submit" :loading="loading">Post</v-btn>
        </v-card-actions>
      </v-form>
    </v-card>
  </v-dialog>
</template>

<script>
import Editor from "@tinymce/tinymce-vue";
import { mapActions, mapMutations } from 'vuex'

export default {
  props:['dialog', 'user', 'courses'],
  components: {
    editor: Editor,
  },
  data: () => ({
    valid: true,
    rules: [ v => !!v || 'This is required' ],
    course_id: '',
    content: '',
    allow_comments: false,
    errors: {},
    loading: false,
    component_key: 1,
    alert: false,
    showMce: true,
    mce_key: `${process.env.VUE_APP_TINYMCE_KEY}`,
  }),

  watch: {
    dialog(val) {
      if(val) {
        this.errors = {}
        this.course_id = ''
        this.content = ''
        this.showMce = false;
        this.allow_comments = false;
        this.content = ''
        this.$nextTick(() => {
          setTimeout(() => {
            this.showMce = true;
          }, 2000);
        });
      }

      this.loading = false
    }
  },

  beforeMount(){
  },

  methods: {
    ...mapActions('instructor', [
      'createAccouncementAction'
    ]),

    ...mapMutations(['alertMutation']),

    submit() {
      if(this.$refs.form.validate()){
        this.errors = {}
        this.loading = true
        this.createAccouncementAction({
          user_id: this.user.id,
          course_id: this.course_id,
          content: this.content,
          allow_comments: this.allow_comments
        }).then(res => {
          this.loading = false
          this.$emit('posted')
          this.alertMutation({
            show: true,
            text: "Announcement has been posted",
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
}
</script>