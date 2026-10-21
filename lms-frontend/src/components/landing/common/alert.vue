<template>
    <v-alert
        type="success"
        v-if="info.view"
    >
        {{  info.text  }}
    </v-alert>
</template>

<script>
export default {
    name: 'alert-common',
    props: ['info']
}
</script>