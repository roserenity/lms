<template>
    <v-container>
        <section class="my-4 f12">
            <v-select
                outlined
                dense
                hide-details
                class=" col-sm-6 poppins f13"
                :items="Object.values(choices)"
                v-model="choices[answer]"
                readonly
                ></v-select>
        </section>
    </v-container>
</template>

<script>
export default {
    props: ['choices', 'answer'],
    data: () => ({
        hover: null,
    }),
}
</script>