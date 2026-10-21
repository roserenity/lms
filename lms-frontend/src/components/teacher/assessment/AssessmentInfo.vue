<template>
  <section>
    <div class="col-xl-6 col-lg-9">
      <FormLabel
        :label="'DESCRIPTION'"
      />
      <!-- <v-textarea 
        class="mb-5 mt-3 secondary-1--text f14 fw600 poppins" 
        dense 
        v-model="item.description"
        v-on:change="updateAssessment"
        rows="5"
        counter
        :rules="rules"
      /> -->
      <section v-if="$isReadOnly.isReadOnly(status, permission)">
        <v-card-subtitle v-html="item.description ? $dompurifier(item.description) : ''" class="my-3"></v-card-subtitle>
      </section>
      <section v-else>
        <RichTextEditor v-if="showMce"
          :value="item.description"
          @setValue="(e) => {item.description = e, updateAssessment()}"
          :disabled="$isReadOnly.isReadOnly(status, permission)"
          :showSaveButton="true"
        />
        <circular v-else />
      </section>
    </div>
    <v-divider class="mb-2 mt-5" />
    <div class="col-xl-4 col-12">
      <label class="caption mx-1">ASSESSMENT CONFIGURATION </label>
      <div class="d-flex align-center">
        <v-checkbox
          dense
          hide-details
          v-model="item.require_checking"
          @change="updateAssessment"
          :disabled="$isReadOnly.isReadOnly(status, permission)"
        />
        <div class="roboto f14 mt-2 fw500 secondary-2--text">Generate Certificate</div>
      </div>
      <div class="d-flex align-center">
        <v-checkbox
          dense
          hide-details
          v-model="item.shuffle_questions"
          @change="updateAssessment"
          :disabled="$isReadOnly.isReadOnly(status, permission)"
        />
        <div class="roboto f14 mt-2 fw500 secondary-2--text">Shuffle questions</div>
      </div>
      <div class="d-flex align-center">
        <v-checkbox
          dense
          hide-details
          v-model="item.shuffle_answers"
          @change="updateAssessment"
          :disabled="$isReadOnly.isReadOnly(status, permission)"
        />
        <div class="roboto f14 mt-2 fw500 secondary-2--text">Shuffle answers</div>
      </div>
      <div class="d-flex align-center">
        <div class="d-flex align-center">
          <v-checkbox dense hide-details v-model="set_time_limit" :disabled="$isReadOnly.isReadOnly(status, permission)"/>
          <div class="roboto f14 mt-2 fw500 secondary-2--text mr-3">Set time limit</div>
        </div>
        <div class="d-flex align-end" v-if="set_time_limit">
          <input
            class="input-sm"
            type="number"
            min="1"
            v-model="item.time_limit"
            v-on:change="updateAssessment"
            :disabled="$isReadOnly.isReadOnly(status, permission)"
          />
          <label class="roboto fw500 f14 secondary--text ml-2">Minutes</label>
        </div>
      </div>
      

      <div class="d-flex align-center">
        <div class="d-flex align-center">
          <v-checkbox
            dense
            hide-details
            v-model="item.show_each_questions"
            @change="updateAssessment"
            :disabled="$isReadOnly.isReadOnly(status, permission)"
          />
          <div class="roboto f14 mt-2 fw500 secondary-2--text">
            Show one question at a time
          </div>
        </div>
      </div>
      <div class="d-flex align-center ml-10" v-if="item.show_each_questions">
        <div class="d-flex align-center">
          <v-checkbox
            dense
            hide-details
            v-model="item.lock_question_after_answering"
            @change="updateAssessment"
          />
          <div class="roboto f14 mt-3 fw500 secondary-2--text">
            Lock question after answering
          </div>
        </div>
      </div>
    </div>
    <div class="col-xl-6 col-12">
      <label class="caption mx-1">SCORING AND EVALUATION </label>
      <!-- Attempt -->
      <div class="d-flex align-center">
        <v-checkbox dense hide-details v-model="allow_attempts" :disabled="$isReadOnly.isReadOnly(status, permission)"/>
        <div class="roboto f14 mt-2 fw500 secondary-2--text">Allow multiple attempts</div>
      </div>
      <div class="d-flex flex-wrap align-center ml-10" v-if="item.max_attempt > 1">
        <div class="d-flex align-center mr-3 my-3">
          <label class="roboto fw500 f14 secondary-1--text mr-2">Max attempts: </label>
          <input
            class="input-sm"
            type="number"
            v-model="item.max_attempt"
            v-on:change="updateAssessment"
            :disabled="$isReadOnly.isReadOnly(status, permission)"
          />
        </div>
        <div class="d-flex align-center my-3">
          <label class="roboto fw500 f14 secondary-1--text mr-2">Scores to keep: </label>
          <select
            v-model="item.accepted_score"
            class="input-sm"
            v-on:change="updateAssessment"
            :disabled="$isReadOnly.isReadOnly(status, permission)"
          >
            <option
              v-for="(score, i) in scoring"
              :key="i"
              :value="score.value"
              class="roboto fw500 f12 secondary-3--text"
            >
              {{ score.text }}
            </option>
          </select>
        </div>
      </div>
      <div class="d-flex flex-row align-center">
        <v-checkbox dense hide-details v-model="passing_score" :disabled="$isReadOnly.isReadOnly(status, permission)"/>

        <div class="roboto f14 fw500 secondary-2--text mt-2">Passing Score</div>
        
        <div class="d-flex align-end ml-2" v-if="item.passing_score">
          <input
            class="input-sm"
            type="number"
            v-model="item.passing_score"
            v-on:change="handlePassingScore($event)"
            :disabled="$isReadOnly.isReadOnly(status, permission)"
          />
          <label class="roboto fw500 f16 secondary--text ml-2 mb-1">%</label>
        </div>
      </div>
      <!--  -->
      <!-- Feeback -->
      <!-- <div class="d-flex align-center">
        <v-checkbox dense hide-details v-model="view_feed" :disabled="$isReadOnly.isReadOnly(status, permission)"/>
        <div class="roboto f14 mt-2 fw500 secondary-2--text">View feedback results</div>
      </div>
      <div class="d-flex flex-column ml-10" v-if="view_feed">
        <div v-for="(feed, i) in feedbacks" :key="i" class="">
          <div class="d-flex align-center mt-2">
            <input
              type="radio"
              style="accent-color: green"
              :value="feed.value"
              v-model="item.view_results_by"
              @change="updateAssessment"
              :readonly="$isReadOnly.isReadOnly(status, permission)"
            />
            <div class="roboto ml-2 f14 fw500 secondary-2--text">{{ feed.text }}</div>
          </div>
          <div
            class="d-flex align-center my-2"
            v-if="item.view_results_by === 'summary' && i == 0"
          >
            <div class="d-flex align-center">
              <label class="roboto f14 fw500 secondary-2--text ml-5 mr-2">End</label>
              <input
                type="date"
                class="input-sm"
                v-model="item.view_results_until"
                placeholder="dd/mm/yyyy"
                style="width: 130px"
                v-on:change="updateAssessment"
                :readonly="$isReadOnly.isReadOnly(status, permission)"
              />
            </div>
          </div>
        </div>
      </div>
      -->
    </div>
    <v-divider class="my-5" />
    <!-- <WhoCanAccess :item="item" :status="status" :permission="permission"/> -->
    <div class="col-xl-4 col-lg-6">
      <FormLabel
        :label="'DUE DATE'"
      />
      <div class="d-flex flex-column">
        <div class="d-flex align-center justify-space-between">
          <label class="roboto f13 fw500 secondary-2--text">Available from</label>
            <input
              type="date"
              class="input-sm"
              placeholder="mm/dd/yyyy"
              style="width: 130px"
              v-model="due_date.start"
              v-on:change="handleDueDate($event, 0)"
              :readonly="$isReadOnly.isReadOnly(status, permission)"
            />
          <!-- <input
            v-else
            type="date"
            class="input-sm"
            placeholder="dd/mm/yyyy"
            style="width: 130px"
            v-model="JSON.parse(item.due_date).start"
            v-on:input="handleDueDate($event, 0)"
          /> -->
        </div>
        <div class="d-flex align-center justify-space-between mt-1">
          <label class="roboto f13 fw500 secondary-2--text">Until</label>
            <input
              type="date"
              class="input-sm"
              placeholder="mm/dd/yyyy"
              style="width: 130px"
              v-model="due_date.end"
              v-on:change="handleDueDate($event, 1)"
              :readonly="$isReadOnly.isReadOnly(status, permission)"
            />
           <!-- <input
            v-else
            type="date"
            class="input-sm"
            placeholder="dd/mm/yyyy"
            style="width: 130px"
            v-model="JSON.parse(item.due_date).end"
            v-on:input="handleDueDate($event, 1)"
          /> -->
        </div>
      </div>
    </div>
  </section>
</template>

<script>
import Editor from "@tinymce/tinymce-vue";
import { mapActions, mapMutations } from "vuex";
import WhoCanAccess from "./WhoCanAccess.vue";

export default {
  props: ["item", "tab_item", "status", "permission"],
  components: {
    editor: Editor,
    WhoCanAccess,
  },
  mounted() {
    this.$nextTick(() => {
      setTimeout(() => {
        this.showMce = true;
      }, 2000);
    });
  },
  data: () => ({
    feedbacks: [
      { text: "Summary of all responses and feedbacks", value: "summary" },
      { text: "After each attempts", value: "each_attempt" },
      { text: "View all correct answers", value: "all_correct_answers" },
    ],
    scoring: [
      { text: "Highest", value: "highest" },
      { text: "Latest", value: "latest" },
      //{ text: "Average on all attempts", value: "average" },
    ],
    showMce: false,
    rules: [v => v.length <= 250 || 'Max 250 characters'],
    mce_key: `${process.env.VUE_APP_TINYMCE_KEY}`
  }),
  computed: {
    set_time_limit: {
      get() {
        return this.item.time_limit ? true : false;
      },
      set(val) {
        this.item.time_limit = !val ? null : 5; //set default to 5mins
        this.updateAssessment();
        return val;
      },
    },

    allow_attempts: {
      get() {
        return this.item.max_attempt && this.item.max_attempt > 1 ? true : false;
      },
      set(val) {
        this.item.max_attempt = !val ? 1 : 3; //set default to 3
        // this.item.accepted_score = !val ? null : "highest"; //set default to highest
        this.item.accepted_score = !val ? null : "latest";
        this.updateAssessment();
        return val;
      },
    },

    view_feed: {
      get() {
        return this.item.view_results_by ? true : false;
      },
      set(val) {
        this.item.view_results_by = !val ? null : "each_attempt"; //set default to 5mins
        this.item.view_results_until = null; //set default to null
        this.updateAssessment();
        return val;
      },
    },

    due_date: {
      get() {
        return this.item.due_date ? JSON.parse(this.item.due_date) : {start: '', end: '' };
      },
      set() {},
    },

    passing_score: {
      get() {
        return this.item.passing_score ? true : false;
      },
      set(val) {
        this.item.passing_score = !val ? null : 50
        this.updateAssessment();
        return val;
      },
    }

  },
  methods: {
    ...mapActions("instructor", ["updateAssessmentAction"]),
    ...mapMutations(['alertMutation']),

    updateAssessment() {
      this.updateAssessmentAction(this.item).then(() => {
        this.alertMutation({ 
          show: true, 
          text: "Assessment Successfully Updated!",
          type: "success" });
      }).catch(() => {
        this.alertMutation({ 
          show: true, 
          text: "Something went wrong",
          type: "error" });
      })
    },

    handleDueDate(event, index) {
      // this.due_date[index] = event.target.value;
      let val = event.target.value

      if(val) {
        if(index == 1) {
          this.due_date.end = val
        } else {
          this.due_date.start = val
          this.due_date.end = val
        }
        
        this.item.due_date = JSON.stringify(this.due_date);
      } else {
        this.item.due_date = null
      }


      this.updateAssessment();
    },

    handlePassingScore(event) {
      let val = event.target.value

      this.item.passing_score = parseInt(val) === 0 ? 1 : parseInt(val)

      this.updateAssessment();
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
