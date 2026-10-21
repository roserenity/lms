<template>
    <section>
        <v-data-table
            v-if="!loading"
            :headers="assessment_headers"
            :items="assessment_items"
            class="px-3 poppins"
        >
            <template v-slot:item.date_submitted="{ item }">
                {{ $dateFormat.mmDDyy(item.date_submitted) }}
            </template>
        </v-data-table>
        <circular v-else/>
    </section>
</template>

<script>
export default {
    props: ['user_id', 'selected', 'class_category_id', 'module_uuid'],

    data: () => ({
        assessment_headers: [
            {
                text: 'Assessment',
                align: 'start',
                value: 'title',
            },
            {
                text: 'Date Submitted',
                align: 'center',
                value: 'date_submitted',
            },
            {
                text: 'Score',
                align: 'center',
                value: 'score',
            },
            // {
            //     text: 'Action',
            //     align: 'center',
            //     value: 'action',
            // },
        ],
        courses_items: [],
        assessment_items: [],
        loading: false
    }),

    mounted(){
        this.loading = true
        this.$api.get(`/instructor/student-output/assessments`, {params: {
            course_uuid: this.selected,
            class_category_id: this.class_category_id,
            module_uuid: this.module_uuid
        }}).then(res=>{
            let _assessments_items = []
            let assessment = res.data.data.find(_item => _item.uuid === this.$route.params.uuid)
            if(assessment) {
                let __course = assessment.enrollments.find(_item => _item.course && (_item.course.uuid == this.selected))
                __course.course.modules[0].assessments.forEach(_item => {
                    let _item_assessments = _item.assessment_scores.filter(_user => _user.user_id === this.user_id)
                    let _sum = 0
                    let _score = '0%'
                    _item_assessments.forEach(_test => {
                        _sum += parseFloat(_test.score ? _test.score : 0)
                    })

                    if((parseInt(_sum) === 0 && parseInt(_item.assessment_overall_score) === 0) || !_item.assessment_overall_score) {
                        _score = "0%"
                    } else {
                        _score = `${((_sum / parseFloat(_item.assessment_overall_score)) * 100).toFixed(2)}%`
                    }

                    _assessments_items.push(
                        {
                            title: _item.title,
                            date_submitted: _item.created_at,
                            score: _score
                        }
                    )
                })
            }

            this.assessment_items =  _assessments_items
            this.loading = false
        }).catch((err) => {
            this.loading = false
            throw err;
        });
    }
}
</script>