<template>
    <v-dialog persistent v-model="connection.show" max-width="350">
        <v-card class="pa-5 text-center">
            <h3 class="primary--text poppins">No Internet Connection</h3>
            <FormLabel :label="`It looks like you're offline. Please check your connection.`" />
        </v-card>
    </v-dialog>
</template>

<script>
import { defineComponent } from '@vue/composition-api'
import { mapState, mapMutations } from 'vuex';

export default defineComponent({
    data: () => ({
    }),
    methods: {
        ...mapMutations(['connectionMutation']),
    },
    computed: {
        ...mapState({
            connection: (state) => state.connection,
        }),
    }
})
</script>