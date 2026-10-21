<template>
  <v-dialog persistent v-model="dialog" max-width="520">
    <v-sheet class="dialog pb-3">
      <div class="header pa-4 d-flex justify-space-between align-center">
        <h2 class="nunito ml-2">{{role}} Account</h2>
        <v-btn icon @click="$emit('close')">
          <v-icon>mdi-close</v-icon>
        </v-btn>
      </div>
      <v-divider/>
      <UserForm v-if="role === 'User'"/>      
    </v-sheet>
  </v-dialog>
</template>

<script>
import UserForm from './UserForm.vue'

export default {
  components: {
    UserForm
  },
  props: ['dialog', 'role']
}
</script>