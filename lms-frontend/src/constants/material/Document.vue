<template >
  <v-sheet
    elevation="2"
    class="my-5"
    v-if="extension != 'txt'">
    <div id="container-frame" class="container-frame">
      <iframe 
        v-if="extension == 'pdf'|| extension == 'docx' || extension == 'ppt'"
        id="material-iframe"
        :src="fetching()"
        class="responsive-iframe"
        allowfullscreen="true"
        webkitallowfullscreen="true"
        mozallowfullscreen="true">
      </iframe>
      <embed v-else :src="fetching()" height="100%" width="100%" class="material-iframe"/>
    </div>
  </v-sheet>
</template>
// :src="https://docs.google.com/gview?url=${file}&embedded=true"


<style>
.container-frame {
  position: relative;
  overflow: hidden;
  width: 100%; /* 16:9 Aspect Ratio (divide 9 by 16 = 0.5625) */
}

.material-iframe {
  height: 500px;
}

@media only screen and (max-width: 768px){
  .material-iframe {
    height: 300px;
    width: 100%;
  }
}

</style>

<script>
export default {
  props: ['file'],
  data: () => ({
    extension: null
  }),
  methods: {
    fetching() {
      this.extension = this.file.split('.').pop()
      //if(process.env.NODE_ENV === 'production') {
        // return https://docs.google.com/gview?url=${this.file}&embedded=true
      //}

      //return this.file;
      if( this.extension == 'pdf' || this.extension == 'docx' || this.extension == 'ppt' ) {
        return `https://docs.google.com/gview?url=${this.file}&embedded=true`
      } else {
        return this.file
      }
    }
  }
};
</script>