<template>
    <label class="caption">{{ label }}</label>
</template>

<script>
import { defineComponent } from '@vue/composition-api';

export default defineComponent({
    props: {
        label: {
            type: String,
            default: ""
        },
    },
})
</script>