<template>
  <section>
    <v-checkbox
      dense
      class="f9 mb-1"
      :value="val"
      :label="label"
      v-model="checked"
      hide-details="auto"
    />
  </section>
</template>

<script>
import { defineComponent } from '@vue/composition-api';

export default defineComponent({
  props: {
    label: {
      type: String,
      default: ""
    },
    val: {
      type: String,
      default: ""
    },
    modelValue: {
      type: Array,
      default: () => []
    }
  },
  data: () => ({
    checked: []
  }),
  watch:{
    checked(val) {
        if(!this.modelValue.includes(this.val)) {
          if(val.length > 0) {
            const updatedValue = [...this.modelValue, this.val];
            this.$emit('update:modelValue', updatedValue);
          }
        } else {
          const updatedValue = this.modelValue.filter(val => val !== this.val);
          this.$emit('update:modelValue', updatedValue);
        }
    }, 
    modelValue(val) {
      if(val.length === 0) {
        this.checked = []
      }
    }
  }
});
</script>
