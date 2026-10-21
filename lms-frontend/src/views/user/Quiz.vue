<template>
  <section>
    <v-sheet class="fade" color="transparent" :height="lists?'': '80vh'">
        <v-btn 
            text 
            color="primary" 
            dense 
            class="px-1" 
            @click="$emit('back')">
            <v-icon small>
            mdi-chevron-left
            </v-icon>
            BACK
        </v-btn>
      <OnePager 
        v-if="!data.show_each_questions" 
        :answers="answers" 
        :data="assessment"
        :errorFile="errorFile"
        @findFile="findFile"
        @getDropFiles="getDropFiles"
        @submit="$emit('submit')"
      />
      <MultiPage 
        v-else
        :answers="answers"
        :data="assessment"
        :errorFile="errorFile"
        @getDropFiles="getDropFiles"
        @findFile="findFile"
        @submit="$emit('submit')"
      />
      
      <input
        type="file"
        ref="file"
        class="d-none"
        accept=".pdf,.docx,.jpg,.png,.jpeg"
        @change="accept"
      />
      
    </v-sheet>

  </section>
</template>

<script>
import { mapState } from 'vuex';
import MultiPage from '../../components/user/assessment/MultiPage.vue'
import OnePager from '../../components/user/assessment/OnePager.vue'
import DialogLeave from '@/components/user/course/DialogLeave'

export default {
  props: ['data', 'answers'],

  components: {
    MultiPage,
    OnePager,
    DialogLeave
  },

  data: () => ({
    reviewAnswers: false,
    lists: true,
    indexFile: null,
    formHasErrors: false,
    errorFile: false,
  }),
  
  computed: {
    ...mapState('usr', {
      assessment: (state) => state.assessments,
    })
  },

  methods: {
    findFile(index) {
      this.indexFile = index
      this.$refs.file.click()
    },

    accept() {

      if(!['image/png', 'image/jpeg', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation'].includes(this.$refs.file.files[0].type)) {
        this.errorFile = true
        this.$refs.file.value = ''
        this.answers[this.indexFile].errorMessages = "File type not supported"
        this.answers[this.indexFile].file = null
        this.answers[this.indexFile].loading = false
      } else {
        this.answers[this.indexFile].loading = true
        this.errorFile = false
        this.answers[this.indexFile].file = this.$refs.file.files[0]
        this.answers[this.indexFile].errorMessages = ''
        this.$refs.file.value = ''
        setTimeout(()=>{
          this.answers[this.indexFile].loading = false
        }, 3000)
      }
      
      // return false
    },

    getDropFiles(item){
      var { index, file } = item
      this.indexFile = index
      this.$refs.file.files = file
      this.accept()
    },
  },

}
</script>