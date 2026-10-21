<template>
    <section>
        <section class="d-flex flex-row align-center ma-3">
            <v-btn icon color="primary-1">
                <v-icon color="primary">
                    mdi-chevron-left
                </v-icon>
            </v-btn>
            <v-select hide-details filled dense/>
            <v-btn icon color="primary-1">
                <v-icon color="primary">
                    mdi-chevron-right
                </v-icon>
            </v-btn>
        </section>
        <v-card class="py-5">
            <section class="pa-2">
                <v-sheet color="secondary" class="rounded mx-5 pa-2">
                    Business and Development Department
                </v-sheet>
                <span class="primary--text mx-5 f12" >2 responses</span>
            </section>
            <v-divider class="py-2"/>
            <section class="pa-2">
                <v-sheet color="secondary" class="rounded mx-5 pa-2">
                    IT Department 
                </v-sheet>
                <span class="primary--text mx-5 f12" >2 responses</span>
            </section>
        </v-card>
    </section>
</template>