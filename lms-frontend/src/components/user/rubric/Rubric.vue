<template>
    <section class="overflow-y-auto scroller">
    <v-sheet class="rounded-lg fade pa-0">
      <table class="rubric border fixed">
        <tr>
          <th rowspan="2" width="200" class="rubric-inner">
            <div class="d-flex align-center justify-center">
              <div class="mr-2">Criteria</div>
            </div>
          </th>
          <th class="rubric-inner">
            <div class="d-flex align-center justify-center">
              <div class="mr-2">Rating</div>
            </div>
          </th>
          <th rowspan="2" width="200" class="rubric-inner">Score</th>
        </tr>
        <tr>
          <div class="wrapper">
            <td v-for="(item, n) in rubric.ratings" :key="n" class="rubric-inner">
              <v-sheet width="150" class="d-flex align-center justify-center">
                <input
                  v-model="item.rate"
                  type="number"
                  style="width: 50px"
                  class="input-sm"
                  readonly
                />
              </v-sheet>
            </td>
          </div>
        </tr>
        <tr
          v-for="(item, c) in rubric.criterias"
          :key="c"
          class="roboto f14 secondary-3--text"
        >
          <td class="rubric-inner">
            <div class="text-center">
              <Editable
                :data="item"
                :index="c"
                :parent="0"
                :action="action"
                :edit="false"
                type="title"
              />
            </div>
          </td>
          <div class="wrapper">
            <td v-for="rating in rubric.ratings" :key="rating.id" class="rubric-inner">
              <div v-for="indicator in rating.indicators" :key="indicator.id">
                <Editable
                  v-if="indicator.rubric_criteria_id == item.id"
                  :data="indicator"
                  :action="action"
                  :edit="false"
                  type="desc"
                />
              </div>
            </td>
          </div>

          <td class="rubric-inner">
            <div class="d-flex">
              <span class="unit-input mx-auto">
                <input
                  class="unit-input__input f13 roboto fw500"
                  type="number"
                  v-model="item.score"
                  readonly
                />
                <span class="unit-input__append">pts</span>
              </span>
            </div>
          </td>
        </tr>
        <tr>
          <th colspan="100%" class="rubric-inner">
            <div class="d-flex align-center justify-end roboto mx-7">
              <div class="mr-16">Total Score</div>
              <div class="f14 secondary-2--text ml-16">
                {{ `${total} pts`}}
              </div>
            </div>
          </th>
        </tr>
      </table>
    </v-sheet>
  </section>
</template>

<script>
import Editable from './Editable.vue'
export default {
  props: ["rubric"],
  components: {
    Editable,
  },
  computed: {
    total: {
      get() {
        let score = this.rubric.criterias.map((el) => {
          return el.score;
        });
        return score.reduce((a, b) => a + b, 0);
      },
    },
  },
};
</script>