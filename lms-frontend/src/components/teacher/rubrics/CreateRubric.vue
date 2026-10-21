<template>
  <section class="pa-5">
    <StartScreen @create="create" v-if="!hideStart" />
    <RubricForm
      v-else
      :type="type"
      :rubric="rubric"
      action="create"
      @onSave="onSaveState"
    />
  </section>
</template>

<script>
import { mapActions } from "vuex";
import StartScreen from "./StartScreen.vue";
import RubricForm from "./RubricForm.vue";

const rubricInit = () => {
  return {
    title: "",
    description: "",
    ratings: [{ rate: 3 }, { rate: 2 }, { rate: 1 }],
    criterias: [
      {
        title: "New Criterion",
        score: 0,
        indicators: [
          { body: "Indicator for criterion" },
          { body: "Indicator for criterion" },
          { body: "Indicator for criterion" },
        ],
      },
      {
        title: "New Criterion",
        score: 0,
        indicators: [
          { body: "Indicator for criterion" },
          { body: "Indicator for criterion" },
          { body: "Indicator for criterion" },
        ],
      },
    ],
  };
};

export default {
  props: ["tab"],
  components: {
    StartScreen,
    RubricForm,
  },
  data: () => ({
    hideStart: false,
    onInput: false,
    type: "",
    rubric: rubricInit(),
  }),

  beforeMount() {
    window.addEventListener("beforeunload", this.beforeReload);
    this.$once("hook:beforeDestroy", () => {
      window.removeEventListener("beforeunload", this.beforeReload);
    });
  },
  methods: {
    ...mapActions("instructor", ["addRubricAction"]),

    beforeReload(event) {
      if (this.hideStart == false) return;
      event.preventDefault();
      event.returnValue = "";
    },

    create(e) {
      this.hideStart = true;
      this.type = e.type;
    },

    onSaveState(tab) {
      this.hideStart = false;
      this.type = "";
      this.$data.rubric = rubricInit();
      this.$emit('goToTab', tab)
    }
  },

  watch: {
    tab(val) {
      return (this.hideStart = val ? true : false);
    },
  },
};
</script>
