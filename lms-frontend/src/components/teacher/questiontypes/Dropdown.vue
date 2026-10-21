<template>
    <v-container fluid>
        <section class="my-4 f12" v-if="preview">
                <v-select
                    outlined
                    dense
                    hide-details
                    class=" col-sm-6 poppins f13"
                    :items="Object.values(choices)"
                  ></v-select>
        </section>
    </v-container>
</template>

<script>
export default {
    props: ['question_id', 'preview', 'choices'],
    data: () => ({
        hover: null
    })
}
</script>