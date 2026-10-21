<template>
  <section>
    <Avatar :user="user" :name="role === 'super-admin' ? `${user.first_name ? user.first_name : role}` : mini ? `${user.first_name ? user.first_name : role}` : `${user.first_name ? user.first_name : role} ${user.last_name ? user.last_name : 'account'}` " :role="role" :mini="mini"/>
    <SuperNav v-if="role==='super-admin'" :lists="lists" :mini="mini"/>    
    <Nav v-if="role==='admin'" :lists="lists" :mini="mini"/>   
    <TeacherNav v-if="role==='instructor'" :lists="lists" :mini="mini"/>   
    <UserNav v-if="role==='user'" :lists="lists" :mini="mini" @collapse="(e)=>{
        this.$emit('collapse', e)
        $store.commit('drawerMutation', e)
      }"/>
  </section>

</template>

<script>
import { mapActions, mapState } from 'vuex'

import Avatar from '../components/main/Avatar.vue'
import Nav from '../components/main/Nav.vue'
import SuperNav from '../components/main/SuperNav.vue'
import TeacherNav from '../components/main/TeacherNav.vue'
import UserNav from '../components/user/main/UserNav.vue'

export default {
  props: ['user', 'drawer', 'role', 'lists', 'mini'],
  components: {
    Avatar,
    Nav,
    SuperNav,
    TeacherNav,
    UserNav,
  },
  data: () => ({
    overlap: false
  }), 
  computed: {
    ...mapState({
      drawerUser: (state) => state.drawer
    })
  },
  // created() {
  //   this.$route.meta.collapse ? this.mini=true : this.mini=false
  // },
  methods: {
    ...mapActions(['logoutAction']),

    logout() {
      this.logoutAction()
    }
  },
  watch: {

  }
}
</script>
