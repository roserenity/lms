<template>
  <section>
    <v-list two-line>
      <div v-for="item in data.assessments" :key="item.id">
        <v-list-item link @click="locked(JSON.parse(item.due_date)) ? view(item): '' "
          v-if="item.due_date"
          class="d-flex justify-space-between align-center"
        >
          <v-list-item-content>
            <div class="d-flex text-truncate">
              <div class="align-self-start mr-5">
                <v-icon size="20" color="primary-1">mdi-help-box</v-icon>
              </div>
              <div class="align-self-center text-truncate">
                <v-list-item-title class="roboto f13">
                  {{item.title}}
                </v-list-item-title>
                <v-list-item-subtitle class="f12" v-if="JSON.parse(item.due_date).end != ']'">
                  Due Date: {{ JSON.parse(item.due_date).end }}
                </v-list-item-subtitle>
              </div>
            </div>
          </v-list-item-content>
            <v-icon size="20">{{locked(JSON.parse(item.due_date)) ? 'mdi-lock-open-outline' : 'mdi-lock-outline'}}</v-icon>
        </v-list-item>
      </div>
    </v-list>
  </section>
</template>

<script>
export default {
  props: ['data'],
  mounted() {

  },
  methods: {
    locked(due_date) {
      let start = new Date(due_date.start)
      let due = new Date(due_date.end)
      let today = new Date();
      if(due >= today && today >= start) return true
      return false
    },

    view(item) {
      this.$router.push({
        name: 'User Assessment',
        params: { uuid: item.uuid }
      })
    }
  }
}
</script>