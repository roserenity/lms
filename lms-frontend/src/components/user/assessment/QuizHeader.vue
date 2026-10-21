<template>
  <section class="f14 secondary-1--text mx-4">
    
    <v-alert v-if="['essay', 'justified_multiple_choice', 'modified_true_or_false', 'sliders'].includes(item.type_of_question) && !checked && !start" dense small color="warning" text >
      <h6>Not yet checked</h6>
    </v-alert>
    <div class="d-flex flex-wrap align-center justify-space-between">
      <FormLabel :label="`Question ${i+1} (${item.score} ${item.score <= 1 ? 'pt' : 'pts'}):`" />
      <v-alert dense small :color="'#BDBDBD33'" class='ml-auto my-2' v-if="score">
        <FormLabel :label="`Score: ${score.score} ${score.score <= 1 ? 'pt' : 'pts'}`" />
      </v-alert>
    </div>
    <div width="100%" class="question-div text-wrap my-5 f16" v-html="item.question ? $dompurifier(item.question) : ''"/>
    <div v-if="item.rubric_id">
      <FormLabel :label="`Rubrics:`" />
      <Rubric :rubric="item.rubric"/>
    </div>
  </section>
</template>

<script>
import Rubric from '../rubric/Rubric.vue'
export default {
  props: ['i', 'item', 'checked', 'score', 'start'],
  components: {
    Rubric
  }
}
</script>
<style>
  .question-div {
    white-space: normal !important;
    word-wrap: break-word;
  }
</style>