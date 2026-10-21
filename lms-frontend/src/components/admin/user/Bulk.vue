<template>
  <section>
    <FormUploadFile 
        :accepted_types="'(.csv)'"
        :accept="'.csv'"
        :accepted_types_list="['text/csv']"
        :multiple="false"
        @acceptFile="acceptFile"
        ref="formUploadFile"
        :show="dialog"/>
      <v-chip 
        v-if="file"
        class="ma-1 my-3"
        color="primary"
        small
        outlined
        close
        @click:close="remove"
        >
        <v-icon small left>
          mdi-csv
        </v-icon>
        {{ file.name }}
      </v-chip>
  </section>
  
</template>

<script>
export default {
  props: ['manual', 'dialog'],
  data: () => ({
    error: '',
    file: null,
    loading: false,
    msg: {
      show: false,
      type: '',
      text: ''
    }
  }),
  methods: {
    acceptFile(e) {
      this.file = e
      this.$emit('onbulk', this.file)
    },
    
    remove() {
      // this.$refs.file.value = null
      this.removeFiles = true
      this.file = null
      this.$refs.formUploadFile.clear()
      this.$emit('onbulk', null)
      setTimeout(() => {
        this.removeFiles = false
      }, 2000)
    },
  },
  watch: {
    manual(val) {
      if(val){
        this.file = null
        // this.$refs.file.value = null
        this.filelist= []
      }
    },
    dialog(val) {
      this.msg =  {
        show: false,
        type: '',
        text: ''
      }
      if(val){
        // alert('here')
        // this.$refs.file.value = null
        this.file = null
      }
    }
  }
}
</script>