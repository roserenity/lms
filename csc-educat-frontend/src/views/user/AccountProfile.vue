<template>
    <section>
        <PersonalInfo class="mb-5" />
        <Certificates class="mb-5"/>
        <CompletedCourses class="mb-5"/>
        <ActivityLog/>
    </section>
</template>

<script>
import PersonalInfo from '@/components/user/profile/PersonalInfo.vue'
import Certificates from '@/components/user/profile/Certificates.vue'
import CompletedCourses from '@/components/teacher/course/CompletedCourses.vue'
import ActivityLog from '@/components/user/profile/ActivityLog.vue'

export default {
    components: {
        PersonalInfo,
        Certificates,
        CompletedCourses,
        ActivityLog
    }
}
</script>