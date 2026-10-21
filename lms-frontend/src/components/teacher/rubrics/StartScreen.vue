<template>
  <section>
    <v-container fill-height class="my-10">
      <v-row justify="center" align="center">
        <v-col cols="12" xl="6" lg="8">
          <v-item-group v-model="tab">
            <v-row>
              <v-col cols="4" v-for="(item, n) in items" :key="n">
                <v-item v-slot="{ active, toggle }" :value="item">
                  <div class="text-center">
                    <v-sheet
                      class="rounded mx-auto d-flex align-center justify-center primary--text"
                      outlined
                      height="150"
                      max-width="150"
                      transparent                    >
                      <v-icon size="40" color="primary" @click="toggle">mdi-plus</v-icon>
                    </v-sheet>
                    <div class="poppins f14 fw500 my-2">{{ item }}</div>
                  </div>
                </v-item>
              </v-col>
            </v-row>
          </v-item-group>
        </v-col>
      </v-row>
    </v-container>
  </section>
</template>

<script>
export default {
  data: () => ({
    items: ["Blank Rubric", "Essay", "Output Based"],
    tab: null,
  }),
  watch: {
    tab(value) {
      value == "Blank Rubric"
        ? this.$emit("create", { type: "custom" })
        : value == "Essay"
        ? this.$emit("create", { type: "essay" })
        : this.$emit("create", { type: "output_based" });
    },
  },
};
</script>

<style></style>
