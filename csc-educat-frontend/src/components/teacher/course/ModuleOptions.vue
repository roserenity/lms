<template>
  <div class="d-flex justify-end">
    <v-menu transition="slide-y-transition" bottom offset-y >
      <template v-slot:activator="{ on, attrs }">
        <v-btn v-bind="attrs" v-on="on" icon >
          <v-icon size="18">mdi-dots-vertical</v-icon>
        </v-btn>
      </template>
      <v-list dense class="custom-border">
        <!-- <v-list-item link @click="$emit('addPrerequisite', module)">
          <v-list-item-title class="roboto f14 d-flex align-center">
            <v-icon size="20" left>mdi-file-plus-outline</v-icon>
            <div class="">Add pre-requisite</div>
          </v-list-item-title>
        </v-list-item> -->
        <v-list-item link @click="$emit('addModuleEvaluation', module)">
          <v-list-item-title class="roboto f14 d-flex align-center">
            <v-icon size="20" left>mdi-file-document-edit-outline</v-icon>
            <div class="">Add Module Evaluation</div>
          </v-list-item-title>
        </v-list-item>
        <v-list-item link @click="$emit('showLock', module)">
          <v-list-item-title class="roboto f14 d-flex align-center">
            <v-icon size="20" left>mdi-lock-outline</v-icon>
            <div class="">Lock Module</div>
          </v-list-item-title>
        </v-list-item>
        <v-list-item link @click="$emit('deleteModule', {item:module, type: 'Module'})">
          <v-list-item-title class="roboto f14 d-flex align-center">
            <v-icon size="20" left>mdi-delete-outline</v-icon>
            <div class="">Delete Module</div>
          </v-list-item-title>
        </v-list-item>
      </v-list>
    </v-menu>
    <v-btn v-if="status != 'PUBLISH' && !$vuetify.breakpoint.mobile" icon class="mr-1 sortable"><v-icon size="18">mdi-cursor-move</v-icon></v-btn>
  </div>
</template>

<script>
export default {
  props: ['module', 'status']
}
</script>
