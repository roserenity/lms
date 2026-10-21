<template>
    <section>
        <v-dialog persistent max-width="450" v-model="dialog">
            <v-img :src="require('@/assets/images/default_success_bg.png')" 
                class="d-flex flex-row align-center justify-center"
            >
                <section class="d-flex flex-column pa-3">
                    <v-icon size="30" color="success" v-if="icon">
                        mdi-check-circle
                    </v-icon>
                    <h2 class="poppins text-center text-bold">
                    {{ title }}
                    </h2>
                    <span class="f15 poppins text-center">
                    {{ message }}
                    </span>
                </section>
            </v-img>
        </v-dialog>
    </section>
</template>

<script>
export default {
props: ['icon', 'dialog', 'title', 'message'],
data: () => ({
    asset_path: `${process.env.VUE_APP_ASSET_PATH}`,
  })
} 
</script>