<template>
    <v-text-field 
        outlined 
        dense 
        class="secondary-font f14"
        :error-messages="errorMessage"
        v-model="model"
        :readonly="readonly"
        hide-details="auto"
    />
</template>

<script>
export default {
    props: {
        hideDetails: Boolean,
        errorMessage: String,
        model: String,
        readonly: Boolean,
    }
}
</script>