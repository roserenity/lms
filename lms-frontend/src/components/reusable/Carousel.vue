<template>
    <div>
        <v-carousel hide-delimiters
            cycle
            height="auto"
            :show-arrows="items.length === 1 ? false : true">
            <v-carousel-item
                v-for="(item,i) in items"
                    :key="i"
                >
                <v-sheet
                    color="transparent"
                >
                <v-img
                    height="auto"
                    :src="item"
                />
                </v-sheet>
            </v-carousel-item>
        </v-carousel>
    </div>
</template>

<script>

export default {
    props: {
        items: {
            default: [],
            type: Array
        }
    }
}

</script>