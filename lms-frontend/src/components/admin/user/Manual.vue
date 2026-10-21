<template>
  <section>
    <v-divider v-if="i != 0" />
    <div class="mr-3 my-5">
      <div class="text-right">
        <v-btn @click="$emit('removeInput', i)" text small color="danger-1" class="text-capitalize poppins f12 fw500">
          <v-icon left >mdi-close</v-icon>
          Delete
        </v-btn>
      </div>
      <v-row dense>
        
        <v-col cols="12">
          <FormTextFieldRequired
            :label="'FIRST NAME *'"
            :value.sync="input.first_name"
            :error="error && error.first_name"
          />
        </v-col>
        
        <v-col cols="12">
          <FormTextFieldOptional
            :label="'MIDDLE NAME'"
            :value.sync="input.middle_name"
            :error="error && error.middle_name"
          />
        </v-col>

        <v-col cols="12">
          <FormTextFieldRequired
            :label="'LAST NAME *'"
            :value.sync="input.last_name"
            :error="error && error.last_name"
          />
        </v-col>

        <v-col cols="12">
          <FormTextFieldOptional
            :label="'SUFFIX'"
            :value.sync="input.suffix"
            :error="error && error.suffix"
          />
        </v-col>

        <v-col cols="12">
          <FormSelectArray 
            :label="'ROLE *'"
            :items="items"
            :error="error && error.role" 
            :value.sync="input.role"
          />
        </v-col>
          <v-col cols="12">
          <FormTextFieldRequired
            :label="'EMAIL ADDRESS *'"
            :value.sync="input.email"
            :error="error && error.email"
          />
        </v-col>
      </v-row>
    </div>
  </section>
</template>

<script>
import { mapState } from 'vuex'
export default {
  name: 'ManualInput',
  props: ['i', 'input', 'error'],
  data: () => ({
    items: ['user', 'instructor', 'admin']
  }),
  computed: {
    // ...mapState('admin', {
    //   classes: (state) => state.classes
    // })

    ...mapState('admin', {
      //courses: (state) => state.courses,
      classes: (state) => state.classes
    }),

    ...mapState({
      tenant: (state) => state.tenant
    }),

    class_courses(){
      let _courses = []
      this.input.class_category_id.forEach((_class)=>{
        _courses.push(...this.classes.filter(item => item.id === _class)[0].courses)
      })

      return _courses
    }
  }
}
</script>
