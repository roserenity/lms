<template>
    <section>
        <section>
            <v-icon v-if="/^.{8,}$/.test(password)" color="success" small>
                mdi-check-circle
            </v-icon>
            <v-icon v-else color="error" small>
                mdi-close-circle
            </v-icon>
            <span class="f11 ml-2">Password must be at least 8 characters</span>
        </section>
        <section>
            <v-icon v-if="/[A-Z]/.test(password)" color="success" small>
                mdi-check-circle
            </v-icon>
            <v-icon v-else color="error" small>
                mdi-close-circle
            </v-icon>
            <span class="f11 ml-2">Password must have at least one uppercase letter</span>
        </section>
        <section>
            <v-icon v-if="/[a-z]/.test(password)" color="success" small>
                mdi-check-circle
            </v-icon>
            <v-icon v-else color="error" small>
                mdi-close-circle
            </v-icon>
            <span class="f11 ml-2">Password must have at least one lowercase letter</span>
        </section>
        <section>
            <v-icon v-if="/[0-9]/.test(password)" color="success" small>
                mdi-check-circle
            </v-icon>
            <v-icon v-else color="error" small>
                mdi-close-circle
            </v-icon>
            <span class="f11 ml-2">Password must have at least one number</span>
        </section>
        <section>
            <v-icon v-if="/[#?!@$%^&*_-]/.test(password)" color="success" small>
                mdi-check-circle
            </v-icon>
            <v-icon v-else color="error" small>
                mdi-close-circle
            </v-icon>
            <span class="f11 ml-2">Password must have at least one symbol ( #, ?, !, @, $, %, ^, &, *, _, - )</span>
        </section>
    </section>
</template>

<script>
export default {
    props: {
        password: String
    }
}
</script>
