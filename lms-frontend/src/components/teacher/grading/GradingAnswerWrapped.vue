<template>
    <v-container class="d-flex flex-wrap overflow-y-auto scroller align-start" :style="'height: 250px'">
        <span v-for="answer in answers" v-if="type === 'date'" color="primary" class="mr-3 my-2 pa-1 f14 border px-8 my-3" :style="`background-color: #F2F2F2;`"> {{ $dateFormat.mmDDyy(answer) }} </span>
        <span v-for="answer in answers" v-if="type === 'time'" color="primary" class="mr-3 my-2 pa-1 f14 border" :style="`background-color: #F2F2F2;`"> <input type="time" readonly :value="answer" class="ml-8"/> </span>
        <span v-for="answer in answers" v-if="type === 'short_answer' || type === 'paragraph'" color="primary" class="mr-3  my-2 pa-2 f14 border" :style="`background-color: #F2F2F2;`"> {{ answer }} </span>
        <span v-for="answer in answers" v-if="type === 'link'" color="primary" class="mr-3 my-2 pa-2 f14 border" :style="`background-color: #F2F2F2;`"> <a :href="answer" target="_blank"> {{ answer }} </a> </span>
        <span v-for="answer in answers" v-if="type === 'html'" color="primary" class="mr-3 my-2 pa-2 f14 border inner_p" :style="`background-color: #F2F2F2;`" v-html="$dompurifier(answer.replace(/(\r\n|\n|\r)/g, '<br>'))"></span>
        <span v-for="answer in answers" v-if="type === 'essay_with_attachment'" color="primary" :style="`background-color: #F2F2F2;`" class="mr-3 my-2 pa-2 d-flex flex-row border align-center">
            <span  class="mr-2 f14 inner_p" v-html="$dompurifier(answer.answer.replace(/(\r\n|\n|\r)/g, '<br>'))"></span>
            <a
                v-if="answer.attachment"
                :href="answer.attachment"
                class="primary--text text-decoration-none f10"
                target="_blank"
                >
                (View Attachment)
            </a>
        </span>
        <span v-for="answer in answers" v-if="type === 'short_answer_with_attachment'" color="primary" :style="`background-color: #F2F2F2;`" class="mr-3 my-2 pa-2 d-flex flex-row border align-center">
            <span  class="mr-2 f14 inner_p"> {{ answer.answer }}</span>
            <a
                v-if="answer.attachment"
                :href="answer.attachment"
                class="primary--text text-decoration-none f10"
                target="_blank"
                >
                (View Attachment)
            </a>
        </span>
    </v-container>
</template>

<style scoped>
.inner_p > p {
    margin-bottom: 0 !important;
}
</style>

<script>
export default {
    props: ['type', 'answers']
}
</script>