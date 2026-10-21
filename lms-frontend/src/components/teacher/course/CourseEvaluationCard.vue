<template>
    <v-card flat class="mx-3 b5 sortabl">
        <div class="pt-0 col-xl-7 col-lg-9">
            <section class="d-flex align-start" >
                <section class="general-custom-div">
                <v-text-field
                    dense
                    outlined
                    hide-details
                    readonly
                    prepend-inner-icon="mdi-file-document-edit-outline"
                    class="no-outline-custom-field roboto f14 fw500 secondary--text"
                    v-model="evaluation.title"
                    >
                    <template v-slot:append>
                    <v-menu offset-y transition="slide-y-transition" bottom>
                        <template v-slot:activator="{ on, attrs }">
                        <v-btn icon v-bind="attrs" v-on="on" style="margin: -6px" dense>
                            <v-icon size="20"> mdi-dots-vertical </v-icon>
                        </v-btn>
                        </template>
                        <v-list dense class="custom-border">
                            <v-list-item @click="$emit('viewCourseEvaluation', evaluation)">
                                <v-list-item-title class="roboto f14 d-flex align-center">
                                <v-icon size="20" left>mdi-eye-outline</v-icon>
                                <div class="">View Evaluation</div>
                                </v-list-item-title>
                            </v-list-item>
                            <v-list-item
                                link
                                @click="$emit('lockEvaluation', evaluation )"
                                v-if="!$isReadOnly.isReadOnly(status, permission)"
                            >
                                <v-list-item-title class="roboto f14 d-flex align-center">
                                <v-icon size="20" left>mdi-lock-outline</v-icon>
                                <div class="">Lock Evaluation</div>
                                </v-list-item-title>
                            </v-list-item>
                            <v-list-item
                                link
                                @click="$emit('deleteCourseEvaluation', evaluation )"
                                v-if="!$isReadOnly.isReadOnly(status, permission)"
                            >
                                <v-list-item-title class="roboto f14 d-flex align-center">
                                <v-icon size="20" left>mdi-delete-outline</v-icon>
                                <div class="">Delete Evaluation</div>
                                </v-list-item-title>
                            </v-list-item>
                        </v-list>
                    </v-menu>
                    </template>
                </v-text-field>
                </section>
            </section>
        </div>
        <!-- <ViewMaterial :dialog="dialog" :data="topic" @close="dialog = false" type="topic" /> -->
  </v-card>
</template>

<script>
export default {
  props: ["status", "evaluation", "permission"],
  data: () => ({
  }),
  methods: {
  }
}
</script>