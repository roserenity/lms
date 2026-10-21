<template>
    <v-card tile>
        <v-list>
            <v-list-item
                exact
                class="li"
                v-for="item in roles.filter(r => r !== role.toLowerCase())"
                :key="item"
                @click="changeRole(item === 'USER' ? 'user' : item)"
            >
                <v-list-item-title class="roboto f14 d-flex align-center secondary-1--text">
                    <div class="fw500 px-1">View as {{ item === 'user' ? 'LEARNER' : item.toUpperCase() }}</div>
                </v-list-item-title>
            </v-list-item>


            <v-list-item exact class="li" v-if="role == 'user'"
                :class="$route.name == `${role.charAt(0).toUpperCase()+ role.slice(1)} Profile` 
                ? 'li-active' : $route.meta.parent == `${role.charAt(0).toUpperCase()+ role.slice(1)} Profile` 
                ? 'li-active' : ''"
                :to="{name: `${role.charAt(0).toUpperCase()+ role.slice(1)} Profile`}" >
                    
                <v-list-item-title class="roboto f14 d-flex align-center secondary-1--text">
                    <v-icon size="20" color="#b2bec3" class="px-1">mdi-account-outline</v-icon>
                    <div class="fw500 px-1">Profile</div>
                </v-list-item-title>
            </v-list-item>

            <v-list-item exact class="li " 
                v-if="!$route.name.includes('Super Admin')"
                :class="$route.name == `${role.charAt(0).toUpperCase()+ role.slice(1)} Settings` 
                ? 'li-active' : $route.meta.parent == `${role.charAt(0).toUpperCase()+ role.slice(1)} Settings` 
                ? 'li-active' : ''"
                :to="{name: `${role.charAt(0).toUpperCase()+ role.slice(1)} Settings`}">
                <v-list-item-title class="roboto f14 d-flex align-center secondary-1--text">
                    <v-icon size="20" color="#b2bec3" class="px-1">mdi-cog-outline</v-icon>
                    <div class="fw500 px-1">Settings</div>
                </v-list-item-title>
            </v-list-item>
            <v-list-item exact class="li" v-if="role == 'user'" :to="{name: `${role.charAt(0).toUpperCase()+ role.slice(1)} Help Desk`}">
                <v-list-item-title class="roboto f14 d-flex align-center secondary-1--text">
                    <v-icon size="20" color="#b2bec3" class="px-1">mdi-help-circle-outline</v-icon>
                    <div class="fw500 px-1">Help Desk</div>
                </v-list-item-title>
            </v-list-item>
            <v-list-item exact class="li cursor-pointer" @click="logout">
                <v-list-item-title class="roboto f14 d-flex align-center secondary-1--text">
                    <v-icon size="20" color="#b2bec3" class="px-1">mdi-logout</v-icon>
                    <div class="fw500 px-1">Logout</div>
                </v-list-item-title>
            </v-list-item>
        </v-list>
    </v-card>
</template>

<script>
import { mapActions, mapState } from 'vuex';
import { role } from '@/services/axios'
var CryptoJS = require("crypto-js");

export default {
    props: {
        role: String
    },
    data: () => ({
        admin: [
            'instructor',
            'learner'
        ],
        instructor: [
            'learner'
        ],
    }),
    methods: {
        ...mapActions(['logoutAction']),

        logout() {
            this.logoutAction()
        },

        changeRole(_role) {
           let encryptedRole = CryptoJS.AES.encrypt(_role, "lms-role").toString();          
            localStorage.setItem('current', encryptedRole)

            this.$router.push({ name: `${_role.charAt(0).toUpperCase()+ _role.slice(1)} Dashboard`})
            setTimeout(() => {
                this.$router.go(0)
            }, 500);

        }
    },
    computed: {
        ...mapState({
            user: (state) => state.user,
        }),

        roles(){

            if(this.user.role === 'ADMIN') return ['admin', 'instructor', 'user']
            if(this.user.role === 'INSTRUCTOR') return ['instructor', 'user']
            if(this.user.role === 'USER') return ['user']

            return []
        }
    },
}
</script>