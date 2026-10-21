<template>
    <v-skeleton-loader
        :height="height"
        width="100%"
        type="image"
        class="border"
    ></v-skeleton-loader>
</template>

<script>

export default {
    props: {
        height: {
            type: String,
            default: '100'
        }
    }
}

</script>