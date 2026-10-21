<template>
  <section>
    <v-dialog persistent v-model="dialog" max-width="550">
      <v-card class="rounded-lg">
        <v-card-title class="d-flex align-center justify-space-between mb-5">
          <h4 class="primary--text poppins fw600">Preview</h4>
          <v-btn icon @click="$emit('close')">
            <v-icon size="22">mdi-close</v-icon>
          </v-btn>
        </v-card-title>
        <div class="pa-2 d-flex justify-center">
          <v-img
            max-height="150"
            max-width="250"
            contain
            :src="require('@/assets/images/default-course-bg.png')"
          />
        </div>
        <v-card-text>
          <h3 class="poppins">{{ course.title }}</h3>
          <div class="mt-3 d-flex">
            <v-icon small class=" ml-3">mdi-account-multiple</v-icon>
            <div class="d-flex fw500" v-for="item in course.class_categories" :key="item.id">
              <div class="d-flex align-center roboto f14">
                <div class="secondary--text">{{ `${item.class_code}` }}</div>
                <div class="secondary-1--text"> {{ `- ${ item.class_name},` }}</div>
              </div>
            </div>
          </div>
          <v-expansion-panels accordion class="border mt-5">
            <v-expansion-panel v-for="item in course.modules" :key="item.id">
              <v-expansion-panel-header
                class="roboto f16 fw600 secondary-1--text secondary-5"
              >
                {{ item.title ? item.title : "Untitled" }}
              </v-expansion-panel-header>
              <v-expansion-panel-content class="">
                <v-timeline dense>
                  <v-timeline-item
                    color="secondary-3"
                    v-for="topic in item.topics"
                    :key="topic.id"
                    small
                  >
                    <div class="roboto f15 fw600">
                      <v-icon size="28" color="primary-2" class="mr-3">{{
                        topic.learning_material
                          ? topic.learning_material.file_type == "youtube"
                            ? "mdi-youtube"
                            : topic.learning_material.file_type == "pdf"
                            ? "mdi-file-pdf-box"
                            : topic.learning_material.file_type == 'mp4'
                            ? 'mdi-file-video-outline'
                            : 'mdi-folder-question-outline'
                          : ""
                      }}</v-icon>
                      {{ topic.title }}
                    </div>
                  </v-timeline-item>
                </v-timeline>
              </v-expansion-panel-content>
            </v-expansion-panel>
          </v-expansion-panels>
        </v-card-text>
      </v-card>
    </v-dialog>
  </section>
</template>

<script>
import { mapState } from "vuex";
export default {
  props: ["dialog", "data"],
  components: {},
  data: () => ({
    asset_path: `${process.env.VUE_APP_ASSET_PATH}`
  }),
  computed: {
    ...mapState("instructor", {
      course: (state) => state.course,
    }),
    c: {
      get() {
        let course = this.$store.state["instructor"].course;
        let topics = [];

        course.modules.forEach((element) => {
          element.topics.forEach((el) => {
            console.log(el);
            topics.push(el);
            if (el.sub_topics.length > 0) {
              el.sub_topics.forEach((sub) => {

                topics.push(sub);
              });
            }
          });
        });
        return console.log(topics);
      },
    },
  },
  methods: {},
};
</script>
