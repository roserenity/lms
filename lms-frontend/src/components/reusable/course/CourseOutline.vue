<template>
    <section v-if="!loading && modules.length > 0">
        <div class="fw600 f16 mb-5">
            Course Outline
        </div>

        <div v-for="(item) in modules" :key="item.id" class="mb-5 ml-2">
            <section class="description_div fw500 f13">
                {{item.title ? item.title : 'Untitled' }} 
            </section>
        </div>

    </section>
</template>

<script>

export default {
    props: ['modules', 'loading'],
}
</script>