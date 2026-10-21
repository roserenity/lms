<template>
    <v-dialog persistent max-width="500" v-model="dialog" >
      <v-sheet class="pa-4" rounded="lg">
        <div class="d-flex align-center">
          <div class="poppins fw600 primary--text">
            New Group
          </div>
          <v-spacer/>
          <v-btn icon small @click="closeDialog()">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </div>
        <div>
          <FormLabel :label="'Customize your group chat by adding a name and an icon.'" />
          <v-divider class="my-3"></v-divider>
          <v-row no-gutters align="center" justify="space-between">
            <v-col cols="12" lg="12">
              <FormTextFieldRequired 
                :label="'GROUP CHAT NAME'"
                :value.sync="name"
                :error="err && err.group_chat_name"
              />
            </v-col>
            <v-col cols="12" lg="12" align="center" justify="center">
              <div width="100px" height="100px" class="bulk-border mt-2 py-8 col-12 d-flex flex-column align-center justify-center align-self-center"
                @dragover="dragover"
                @dragleave="dragleave"
                @drop="drop">
                <v-avatar v-if="image" size="80" color="secondary-5" class="pointer-cursor" id="avatar-profile" style="border: 5px solid">
                  <img
                    alt="Avatar"
                    :src="src"
                  >
                </v-avatar>
                <v-icon v-else x-large color="primary">
                    mdi-cloud-upload-outline
                </v-icon>
                <div class="d-flex flex-column align-center secondary--text f12">
                    <div>
                        <a
                        href="#"
                        @click="$refs.file.click()"
                        class="primary--text text-decoration-none"
                        >
                        Browse
                        </a>
                        or drag file here
                    </div>
                    (.png, .jpg, .jpeg)
                </div>
              </div>
              <alert 
                  v-if="msg.show"
                  :show = "msg.show"
                  :text = "msg.text"
                  :type = "msg.type"
              />
              <!-- <div style="width: 100px; height: 100px" :class="!src && 'group-avatar-uploader'" class="d-flex flex-column align-center justify-center my-2">
                <v-icon large color="primary-1" v-if="!image">mdi-cloud-upload-outline</v-icon>
                <v-avatar v-else width="100%" height="100%"> 
                  <v-img :src="src" />
                </v-avatar>
                <a href="#" v-if="!src" @click="$refs.file.click()" class="text-decoration-none primary--text fw500 f12">Upload</a>
              </div>
              <a href="#" v-if="src" @click="$refs.file.click()" class="text-decoration-none primary--text fw500 f12">Upload</a> -->
            </v-col>
          </v-row>
          <v-divider class="my-5"></v-divider>
          <div class="d-flex align-center justify-end">
            <v-btn @click="create" color="primary" class="text-capitalize poppins f13" :loading="loading">
              <v-icon small left>mdi-plus</v-icon>
              Create
            </v-btn>
          </div>
        </div>
        
        <input type="file" ref="file" class="d-none" @change="accept"  accept=".jpg,.png,.jpeg"/>
      </v-sheet>
    </v-dialog>
  </template>
  
  <script>
  import { mapActions, mapMutations, mapState } from 'vuex'
  
  export default {
    props: ['dialog', 'subgroup'],
    data: () => ({
      items: ['User', 'Instructor'],
      name: '',
      image: '',
      src: '',
      loading: false,
      msg: {
        show: false,
        text: '',
        type: ''
      },
      err: []
    }),
    watch: {
      dialog(){
        this.reset()
      }
    },
    methods: {
      ...mapActions('instructor', [
        'addCourseGroupChatAction',
      ]),
  
      ...mapMutations(['alertMutation']),
  
      dragover(e) {
        e.preventDefault();
        e.currentTarget.classList.add("bulk-border-drag");
      },
  
      dragleave(e) {
        e.preventDefault();
        e.currentTarget.classList.remove("bulk-border-drag");
      },
  
      drop(e) {
        e.preventDefault();
        e.currentTarget.classList.remove("bulk-border-drag");
        this.$refs.file.files = e.dataTransfer.files;
        this.accept()
      },
      
      accept() {
        this.msg = {
          show: false,
          type: '',
          text: ''
        }
        if(!['image/png', 'image/jpeg'].includes(this.$refs.file.files[0].type)){
          this.msg = {
              show: true,
              type: 'error',
              text: `File not supported`
          }
          this.$refs.file.value = ''
          this.image = null
        } else {
          this.image = this.$refs.file.files[0]
          this.src = URL.createObjectURL(this.image)
          this.$refs.file.value = ''
        }
      },
  
      create() {
        this.err = []
        this.loading = true
        let form = new FormData()
        form.append('id', this.$route.params.id)
        form.append('group_chat_name', this.name)
  
        if(this.image !== '' && this.image) {
          form.append('image_file', this.image)
        }

        this.addCourseGroupChatAction({ id: this.$route.params.id, form: form})
          .then(res => {
              this.$emit('close')
              this.alertMutation({
                  show: true,
                  text: 'Group chat successfully created, redirecting ...',
                  type: "success"
              })
              this.image = ''
              this.name = ''
              this.loading = false
              setTimeout(() => {
                this.$router.push({ name: 'Instructor Inbox', query: { group_chat_uuid: res.uuid, timestamp: Date.now() } })
              }, 2000)
          }).catch(e => {
              this.err = e
              this.loading = false
              if(e.image_file) {
                  this.msg = {
                  show: true,
                  type: 'error',
                  text: e.image_file[0]
                  }
              } else {
                this.$emit('close')
                this.alertMutation({
                    show: true,
                    text: 'Something went wrong',
                    type: "error"
                })
              }
          })
        
      },
  
      reset(){
        this.src = ''
        this.image = ''
        this.name = ''
        this.$refs.file.value = ''
        this.image = null
        this.err = []
      },
  
      closeDialog(){
        this.msg = {
          show: false,
          type: '',
          text: ''
        }
        this.reset()
        this.$emit('close')
      }
    }
  }
  </script>