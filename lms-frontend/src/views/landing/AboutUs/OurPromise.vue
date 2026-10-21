<template>
    <v-container class="fade">
        <v-row class="primary-font mt-3">
            <v-col cols="12" md="6" class="py-3 f14 d-flex flex-column justify-center">
                <span class="f20 fw600">Our Promise</span>
                <v-divider class="l-primary my-5" width="70%" />
                <p>
                    Our engagement model puts learners and their organizations at the heart of everything we do. We promise to understand our customers’ needs, deliver relevant and responsive solutions, and define realistic measures of success.
                </p>
                <p>
                    Our Business Partners act as “the voice” of our clients’ training and development needs, while our Service Fulfillment Group manages the core learning processes – design, development, delivery and evaluation – with a focus on areas such as leadership, people management and foundation skills. Both groups are supported by our Institute Services Group, which handles data management, market research, partnerships and scholarship administration.  
                </p>            
            </v-col>
            <v-col cols="12" md="6" class="d-flex align-center justify-center">
                <v-img :src="require('@/assets/images/landing/promise-photo.png')" contain/>
            </v-col>
        </v-row>
        <v-carousel
            continuous
            hide-delimiters
            class="primary-font mb-10"
            >
            <v-carousel-item
                v-for="(model, i) in models"
                :key="i"
                outline
            >
                <v-card
                height="100%"
                tile
                class="d-flex flex-column align-center justify-center"
                >
                    <v-card class="mx-15 px-3 py-10 d-flex flex-column align-center justify-center">
                        <div class="f18 fw600 text-center l-primary--text my-1">
                            {{  model.title }}
                        </div>
                        <div class="f16 text-center my-1">
                            {{  model.subtitle }}
                        </div>
                        <v-divider class="l-primary text-center  my-5" width="50%" />
                        <div class="f14 my-1">
                            <ol >
                                <li v-for="step in model.steps" :key="step"> {{ step }}</li>
                            </ol>
                        </div>
                    </v-card>
                </v-card>
            </v-carousel-item>
        </v-carousel>
    </v-container>
</template>

<script>
export default {
    data: () => ({
        models: [
            {
                title: 'ENGAGEMENT MODEL 1',
                subtitle: 'Organization Wide',
                steps: [
                    'Business Partner coordinates with client to conduct training needs analysis. Business Partner analyzes results and coordinates with Service. Fulfillment to prepare the organization-wide training plan and calendar.',
                    'Service Fulfillment designs and develops learning solutions based on results of needs analysis and assigns trainers for each program, while coordinating with Institute Services for assistance.',
                    'Business Partner monitors completion of intervention/s and partners with client in monitoring and evaluating learning outcomes.'
                ]
            },
            {
                title: 'ENGAGEMENT MODEL 2',
                subtitle: 'Based on Customer Needs',
                steps: [
                    'Business Partner presents training plan and calendar for their needs that are similar to the needs of other organizations and assists clients in identifying and enrolling the appropriate people in the organization. Business Partner then discusses and explores solutions for needs that are specific to the client and coordinates with Service, Fulfillment and Institute Services.',
                    'Service Fulfillment customizes design and development of learning solutions based on client’s needs and coordinates directly with client for intervention delivery/ conduct, or, Institute Services sources for learning service providers who can help address the client need.',
                    'Business Partner monitors completion of intervention/s and partners with client in monitoring and evaluating.'
                ]
            },
            {
                title: 'ENGAGEMENT MODEL 3',
                subtitle: 'Based on Program Offers',
                steps: [
                    'Service Fulfillment designs and develops learning solutions related to the stated needs of clients or emerging priorities of the Academy and coordinates with the Business Partners for possible introduction of the programs to their respective clients.',
                    'Business Partner determines the relevance of the new programs to client situation and needs and presents the solutions as appropriate.',
                    'Service Fulfillment deploys trainers to client or coordinates with Institute Services for learning service providers who can help address the client need.',
                    'Business Partner monitors completion of intervention/s and partners with client in monitoring and evaluating learning outcomes.'
                ]
            },
        ]
    })
}
</script>