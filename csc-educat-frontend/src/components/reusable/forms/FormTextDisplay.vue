<template>
    <section>
        <label class="caption">{{ label }}</label>
        <div class="rounded secondary-5 pa-2">
            <h5 v-html="value"></h5>
        </div>
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
        value: {
            type: String,
            default: ""
        },
    },
})
</script>