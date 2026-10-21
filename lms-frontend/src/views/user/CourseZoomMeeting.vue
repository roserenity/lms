<template>
    <section>
          <iframe
            id="zoom-frame"
            style="height: 100vh; width: 100vw"
            :src="`/course/${course_id}/join/${meeting_id}`"
            frameborder="0"
            class="zoom_frame"
          />
          <!-- try for zoom, need to served over https -->
          <!-- allow-modals -> i used alert-->
    </section>
  </template>

<script>
import { mapState } from 'vuex'

export default {
    data: () => ({
        course_id: null,
        meeting_id: null
    }),
    created(){
        this.course_id = this.$route.params.id
        this.meeting_id =  this.$route.params.mn
    },
    computed: {
        ...mapState({
            user: (state) => state.user
        }),
        ...mapState('usr', {
            meeting: (state) => {
              console.log(state.meeting)
            }
        })
    },
}

</script>
