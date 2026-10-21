<template>
    <v-dialog v-model="modal" persistent max-width="550">
      <v-card >
        <div class="poppins f16 fw600 d-flex px-2 py-3">
          <div class="mx-auto d-flex align-center">
            <div class="primary--text d-flex align-center"> {{selectedClass.class_code}}-
                <v-text-field
                    outlined
                    dense 
                    v-if="onEdit" 
                    v-model="className"
                    hide-details
                    width="fit-content"/>
                <span v-else class="primary--text">{{selectedClass.class_name}}</span>
            </div>
          </div>
          <v-btn icon small @click="onEdit = false, $emit('close')">
            <v-icon size="18">mdi-close</v-icon>
          </v-btn>
        </div>
        <v-card-text>
            <!-- <v-autocomplete
                dense
                class="poppins f14 fw600 secondary-1--text"
                prepend-inner-icon="mdi-magnify"
                outlined
                hide-details
                solo
                label="Search"
            /> -->
            
            <div v-if="selectedClass.courses.length > 0">
                <span class="poppins f13 fw600"> COURSES </span>
                <v-sheet max-height="300" class="overflow-y-auto scroller">
                    <div v-for="(courses, i) in selectedClass.courses" :key="i" class="d-flex flex-column my-3">
                        <span class="f16 fw500 secondary-1--text">
                            {{ courses.title }}
                        </span>
                        <span class="f12 fw400 secondary-3--text">
                            Assigned teacher: 
                              {{ courses.uploader.first_name ? courses.uploader.first_name : 'Instructor' }}
                              {{ $userFormat.middle_initial(courses.uploader.middle_name) }}
                              {{ courses.uploader.last_name ? courses.uploader.last_name : 'Account' }}
                              {{ courses.uploader.suffix ? courses.uploader.suffix : '' }}
                        </span>
                    </div>
                </v-sheet>
            </div>
            <v-container v-else class="text-center">
              <FormLabel :label="'Class does not have any assigned courses yet.'" />
                <span class="f15 fw600 secondary-2--text">
                    
                </span>
            </v-container>
        </v-card-text>
        <v-divider></v-divider>
        <v-card-actions class="px-5" v-if="!onEdit">
            <!-- <v-btn
                class="error--text text-capitalize f14 poppins"
                depressed
                outlined
                @click="$emit('deleteClass')"
            >
                <v-icon left>mdi-delete-outline </v-icon>
                Delete
                </v-btn> -->
            <v-spacer></v-spacer>
            <v-btn 
                class="text-capitalize poppins" 
                color="primary"
                @click="className = selectedClass.class_name, onEdit = true"
                >Edit</v-btn>
        </v-card-actions>
        <v-card-actions class="px-5" v-else>
            <v-btn
                class="secondary-3--text text-capitalize f14 poppins"
                depressed
                outlined
                @click="onEdit = false"
            >
                Cancel
                </v-btn>
            <v-spacer></v-spacer>
            <v-btn 
                class="text-capitalize poppins" 
                color="primary"
                @click="save"
            >Save Changes</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </template>

<script>
import { mapActions, mapState, mapMutations } from 'vuex'

export default {
  props: ['modal', 'selectedClass'],
  computed: {
    ...mapState({
      errors: (state) => state.errors,
    })
  },
  data: () => ({
    onEdit: false,
    className: null
  }),
  methods: {
    ...mapActions('admin', [
      'updateClassesAction',
    ]),

    ...mapMutations(['alertMutation']),

    save(){
        this.selectedClass.class_name = this.className
        this.updateClassesAction(this.selectedClass).then(()=>{
          this.alertMutation({
            show: true,
            text: 'Class name updated.',
            type: "success"
          })
          this.onEdit = false
        }).catch(() => {
          this.onEdit = false
        })
    },

    cancel() {
      this.reset()
      this.$emit('close')
    },
  }
}
</script>