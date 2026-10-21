<template>
    <v-card min-height="300" class="pa-5 border">
      <section v-if="!loading">
        <div class="">
            
            <v-btn text dense @click="(prevRoute && prevRoute.name === 'Instructor Rubric') ? $router.push({ name: 'Instructor Rubric', query: { ...prevRoute.query}}) : $router.push({ name: 'Instructor Rubric', query: { search: '', page: 1, paginate: 10, search: '', tab: 0 }})" class="mr-1 pa-0 f14">
                <v-icon small>
                    mdi-chevron-left
                </v-icon>
                My Rubrics
            </v-btn>
        </div>
        <div v-if="rubric">
          <RubricForm :rubric="rubric" :action="$route.name === 'Instructor Rubric Edit' ? 'update' : 'view'" :type="rubric.type_of_rubric" />
        </div>
      </section>
      <circular v-else  class="ma-auto"/>
    </v-card>
  </template>
  
  <script>
    import { mapActions, mapState, mapMutations } from "vuex";
    import RubricForm from "@/components/teacher/rubrics/RubricForm.vue";
    
    export default {
        components: {
            RubricForm,
        },
        data: () => ({
            loading: false,
            prevRoute: null,
            rubric: null
        }),
        created() {
            this.loading = true
            this.showRubricAction(this.$route.params.id).then(res => {
                this.rubric = res
                this.loading = false;
            }).catch(() => {
                this.loading = false;
                this.alertMutation({
                    show: true,
                    text: 'Something went wrong in fetching your rubric',
                    type: "error"
                })
            });
        },
        methods: {
            ...mapActions("instructor", ["showRubricAction"]),
            ...mapMutations(["alertMutation"]),
        },
        
        beforeRouteEnter(to, from, next) {
            next(vm => {
                vm.prevRoute = from 
            })
        },
    };
  </script>
  
  <style></style>
  