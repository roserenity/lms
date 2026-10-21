<template>
    <v-progress-linear
        indeterminate
        :color="landing ? 'l-primary' :'primary'"
        rounded
        height="4"
    />
</template>

<script>
export default {
    props: {
        landing: {
            default: false,
            type: Boolean
        }
    }
}
</script>