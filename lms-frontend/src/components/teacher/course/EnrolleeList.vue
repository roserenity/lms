<template>
    <v-dialog persistent v-model="dialog" max-width="530">
        <v-card rounded="lg">
            <v-card-text>
				<v-btn icon absolute right class="pt-4"  @click="$emit('close')">
					<v-icon>mdi-close</v-icon>
				</v-btn>
                <div class="pt-4 py-5 f14 poppins fw500">Enrollee Count: {{ this.count }}</div>
                <v-text-field 
                    filled 
                    class="noline general-custom-field roboto f14 secondary-1--text fw500" 
                    dense
                    prepend-inner-icon="mdi-magnify"
                    placeholder="Search Enrollee"
                    hide-details
                    v-model="search"
                />
                
                <v-sheet height="350" class="overflow-y-auto scroller">
                    <v-data-table
                        :headers="headers"
                        :items="enrollees"
                        :search="search"
                    />
                </v-sheet>

                <ButtonPrimary :label="'Create Group Chat'"/>
            </v-card-text>
        </v-card>

    </v-dialog>
</template>

<script>
export default {
    props: ['dialog', 'enrollees', 'count', 'class_categories'],
    data: () => ({
        headers: [
            {
                text: 'Enrollee',
                value: 'name'
            },
            {
                text: 'Date Enrolled',
                value: 'date_enrolled'
            },
        ],
        search: ''
    })
}
</script>