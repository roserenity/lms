<template>
  <section>
      <div class="col-xl-6 col-lg-9">
        <label class="caption mb-2">TITLE *</label>
        <v-text-field
          dense
          hide-details
          class="secondary-1--text f14 fw600 poppins"
          v-model="evaluation.title"
          v-on:change="updateEvaluation"
          :disabled="$isReadOnly.isReadOnly(status, permission)"
        />
      </div>
      <div class="col-xl-6 col-lg-9">
          <label class="caption mb-2">DESCRIPTION</label>
          <!--v-model="item.description"-->
          <section v-if="$isReadOnly.isReadOnly(status, permission)">
            <v-card-subtitle v-html="evaluation.description ? $dompurifier(evaluation.description) : ''" class="my-3"></v-card-subtitle>
          </section>
          <section v-else>
            <RichTextEditor
              v-if="showMce"
              :value="evaluation.description"
              @setValue="(e) => {evaluation.description = e, updateEvaluation(e)}"
              :disabled="$isReadOnly.isReadOnly(status, permission)"
              :showSaveButton="true"
            />
            <circular v-else />
          </section>
      </div>
      <v-divider class="mb-2 mt-5" />
      <div class="col-xl-4 col-12 mx-2">
          <div class="d-flex align-center">
              <!--v-model="item.shuffle_questions"-->
              <v-checkbox
                  dense
                  hide-details
                  @change="updateEvaluation"
                  v-model="evaluation.is_shuffle_questions"
                  :disabled="$isReadOnly.isReadOnly(status, permission)"
              />
              <div class="roboto f14 mt-2 fw500 secondary-2--text">Shuffle questions</div>
          </div>
          <div class="d-flex align-center">
              <!--v-model="item.shuffle_questions"-->
              <v-checkbox
                  dense
                  hide-details
                  @change="updateEvaluation"
                  v-model="evaluation.is_send_feedback_to_user"
                  :disabled="$isReadOnly.isReadOnly(status, permission)"
              />
              <div class="roboto f14 mt-2 fw500 secondary-2--text">Send feedback to user's emails</div>
          </div>
          <div class="d-flex align-center">
              <v-checkbox 
                  dense 
                  hide-details 
                  v-model="updateMultipleSubmission"
                  :disabled="$isReadOnly.isReadOnly(status, permission)"
                  />
              <div class="roboto f14 mt-2 fw500 secondary-2--text mr-2">Allow multiple submission</div>
              <select
                  class="input-sm mr-2"
                  @change="updateEvaluation"
                  v-model="evaluation.submission_limit"
                  :disabled="updateMultipleSubmission"
                  :readonly="$isReadOnly.isReadOnly(status, permission)"
              >
                  <option
                      :value="2"
                      class="roboto fw500 f12 secondary-3--text"
                  >
                      2
                  </option>
                  <option
                      :value="3"
                      class="roboto fw500 f12 secondary-3--text"
                  >
                      3
                  </option>
              </select>
          </div>
          <!-- <div class="d-flex align-center">
              <v-checkbox 
                  dense 
                  hide-details
                  @change="updateEvaluation"
                  v-model="evaluation.is_allow_to_review"
                  :disabled="$isReadOnly.isReadOnly(status, permission)"/>
              <div class="roboto f14 mt-2 fw500 secondary-2--text">Allow review after submission</div>
          </div> -->
    </div>
    <!-- <WhoCanAccess :item="item"/> -->
  </section>
</template>

<script>
import Editor from "@tinymce/tinymce-vue";
import { mapActions } from "vuex";

export default {
  props: ["evaluation", "loading", "status", "permission"],
  components: {
    editor: Editor,
  },
  mounted() {
    this.$nextTick(() => {
      setTimeout(() => {
        this.showMce = true;
      }, 2000);
    });
  },
  data: () => ({
    showMce: false,
    rules: [v => v.length <= 250 || 'Max 250 characters'],
    mce_key: `${process.env.VUE_APP_TINYMCE_KEY}`
  }),

  methods: {
    ...mapActions("instructor", [ "updateCourseEvaluationAction"]),

    updateEvaluation(e) {
      this.$emit('updateEvaluationSettings', this.evaluation)
      // this.updateCourseEvaluationAction(this.evaluation);
    },

  },

  computed: {
    updateMultipleSubmission: {
      get() {
          if(this.evaluation.submission_limit > 1) {
              return true
          } else {
              return false
          }
      },
      set(val) {
          if(val) {
              return true
          } else {
              this.evaluation.submission_limit = 1
              this.updateEvaluation(this.evaluation)
              return false
          }
      }
    }
  },

  watch: {
    item(prev, current) {
      if(prev.id !== current.id) {
        this.showMce = false
        this.$nextTick(() => {
          setTimeout(() => {
            this.showMce = true;
          }, 2000);
        });
      }
    }
  }
};
</script>

<style>
.tox.tox-tinymce {
  border-radius: 5px !important;
}
input[type="date"]::-webkit-calendar-picker-indicator {
  width: 15px;
  padding: 0px;
  margin: 0px;
  position: absolute;
}
input::-webkit-datetime-edit {
  position: relative;
  left: 20px;
}
</style>
