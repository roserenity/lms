<template>
    <section>
        <circular />
    </section>
</template>

<script>
export default {
    mounted(){
        top.window.location.href = `/course/${this.$route.params.id}`
    }
}
</script>