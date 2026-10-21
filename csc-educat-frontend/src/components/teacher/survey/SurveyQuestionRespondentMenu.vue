<template>
    <v-menu
        origin="center center"
        transition="slide-y-transition"
        :close-on-content-click="false"
        content-class="my-menu elevation-0"
        max-width="300"
        offset-x
        :disabled="responses_count === 0"
    >
        <template v-slot:activator="{ on, attrs }">
        <span class="px-5">
            <a
                v-bind="attrs"
                v-on="on"
                href="#"
                class="primary--text text-decoration-none"
                >{{ responses_count }} Responses</a
            >
        </span>
        </template>
        <!-- <v-card outlined class="custom-border">
        <v-card-text>
            <div
            class="roboto f14 secondary-1--text my-1"
            v-for="item in respondents"
            :key="item"
            >
            {{ item }}
            </div>
        </v-card-text>
        </v-card> -->
    </v-menu>
</template>

<script>
export default {
    props: ['responses_count', 'respondents']
}
</script>