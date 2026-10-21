<template>
  <section>
    <v-sheet v-if="!loading">
      <v-row no-gutters>
        <v-col cols="12" md="3" class="order-last order-md-first">
          <v-tabs hide-slider v-model="tab" fixed-tabs>
            <v-tab 
              v-for="item in items" :key="item"
              class="text-capitalize poppins f14 fw500 secondary--text no-spacing"
              active-class="secondary-1--text active"
            >
              {{item}}
            </v-tab>
            <v-tabs-items v-model="tab" style="background-color: transparent">
              <!-- <router-view /> -->
              <v-sheet class="overflow-y-auto scroller" max-height="400" min-height="59vh"  id="parent">
                <MaterialTimeline 
                  :course="course" 
                  :modules = "course.modules"
                  :material="material" 
                  :end="end"
                  v-if="tab==0"
                />
                <AssessmentLists v-if="tab==1" :data="course" />
                <StartScreen :link="false" :data="[]" v-if="tab==1"/>
              </v-sheet>
            </v-tabs-items>
          </v-tabs>
        </v-col>
        <v-col cols="12" md="9">
          <div class="" v-if="tab==0">
            <v-card v-if="material.learning_material" flat>
              <vimeo v-if="material.learning_material.file_type == 'youtube' || material.learning_material.file_type == 'mp4'"
                  :uri="material.learning_material.url"
                  :type="material.learning_material.file_type"
                  :data="material"
                  @start="start=true"
                  @end="end=true"
                />   
              <docx v-else :file="material.learning_material.url"/> 
            </v-card>
            <v-sheet height="50vh" class="d-flex align-center justify-center" v-else>
              No learning material found
            </v-sheet>     
          </div>
          <v-sheet color="transparent" min-height="60vh" class="py-10" v-else>
            <v-row align="center" justify="center" class="fill-height">
              <v-col cols="12" lg="10">
                <StartScreen :link="false" :data="[]" v-if="tab==1"/>
              </v-col>
            </v-row>
          </v-sheet>

        </v-col>
      </v-row>
    </v-sheet>
    <circular v-else/>
  </section>
</template>

<script>
import { mapActions, mapState } from 'vuex';
import MaterialTimeline from '../../components/user/course/MaterialTimeline.vue'
import AssessmentLists from '../../components/user/course/AssessmentLists.vue'
import StartScreen from '../../components/user/assessment/StartScreen.vue'

export default {
  components: {
    MaterialTimeline,
    AssessmentLists,
    StartScreen
  },
  data: () => ({
    tab: null,
    items: ['CONTENTS'],
    loading: true,
    start: false,
    end: false,
  }),
  computed: {
    ...mapState('usr', {
      course: (state) => state.object,
      material: (state) => state.material,
    })
  },
  beforeMount() {
    window.addEventListener("beforeunload", this.beforeReload);
    this.$once("hook:beforeDestroy", () => {
      window.removeEventListener("beforeunload", this.beforeReload);
    });
  },

  mounted() {     
    this.fetch()
  },

  methods: {
    ...mapActions('usr', ['showNextContentAction','showCourseAction']),

    fetch() {
      this.showCourseAction(this.$route.params.course_uuid).then(() => {
        this.showNextContentAction({
          course_uuid: this.$route.params.course_uuid,
          model_uuid: this.$route.params.content_uuid,
          model_type: this.$route.params.type == 'topic' ? 'Topic' : 'SubTopic',
          save_progress: this.$route.params.save_progress ? true : false,
          course_id: this.course.id
        }).then(() => {
          this.loading=false
        })
      }).catch(err => {
        this.loading = false        
        if (err.response && err.response.status === 404) {
          this.$router.push('/404')
        }
      })
    },

    videoEnd() {
      console.log('video ended');
    },

    beforeReload(e) {
      // if(this.tab == 1) return
      // e.preventDefault();
      // e.returnValue = "";
    }
  },

  watch: {
    '$route' (to, from){
      console.log('triggered');
      
      if(to.name == 'User Learning Material') {
        this.loading=true
        this.fetch()
      }else {
        this.beforeReload()
      }
      
      this.end = false
    },

    tab(val) {
      if(val==1 && this.start) {
        if(confirm('Change you made may not be saved')){
          return
        }
        else {
          return this.tab=0
        }
      }
    }
  }
}
</script>

<style>

</style>