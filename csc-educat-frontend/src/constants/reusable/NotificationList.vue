<template>
    <v-card tile max-height="400"  width="320">
        <v-toolbar
                dense 
                class="label f12 fw600 primary-font secondary-2--text"
                :elevation="offsetTop === 0 ? '0' : '3'"
                
            >
                <v-list-item class="d-flex flex-row align-center justify-space-between px-0">
                    <FormLabel :label="'Notifications'" class="mr-auto" />
                    <v-btn dense text small class="caption secondary-2--text px-1 ml-auto" @click.stop="$emit('markAll')"> Mark all as read </v-btn>
                </v-list-item>
                
        </v-toolbar>
        <v-divider></v-divider>

        <LinearProgress v-if="loading" />
        <v-list id="notification-scroll-list" width="320" dense height="240" style="overflow-y: auto;" v-scroll.self="onScroll">
            <v-container v-if="!loading && notification.length === 0" class="secondary-2--text f12 text-center">
                You have no notifications
            </v-container>
            <section v-else-if="notification.length > 0" >
                <v-list-item dense v-for="(item , i) in notification" :key="i" :class="!item.read_at && 'primary-1'" @click="$emit('readMessage', item)">
                    <v-list-item-avatar v-if="item.type.split('\\')[2] === 'GroupMessageNotification'" color="orange lighten-5">
                        <v-icon color="orange lighten-2">mdi-account-group-outline</v-icon>
                    </v-list-item-avatar>
                    <v-list-item-avatar v-else-if="item.type.split('\\')[2] === 'AssessmentDeadlineNotification'" color="red lighten-5">
                        <v-icon color="red lighten-2">mdi-archive-clock-outline</v-icon>
                    </v-list-item-avatar>
                    <v-list-item-avatar v-else-if="item.type.split('\\')[2] === 'ProfileFilloutNotification'" color="red lighten-5">
                        <v-icon color="red lighten-2">mdi-account-alert-outline</v-icon>
                    </v-list-item-avatar>
                    <v-list-item-avatar v-else>
                        <v-img :src="item.data.photo ? item.data.photo : require('@/assets/default-photo.png')"
                            v-on:error="require('@/assets/default-photo.png')"></v-img>
                    </v-list-item-avatar>
                    <v-list-item-content class="py-1" v-if="item.type.split('\\')[2] === 'AnnouncementNotification'">
                        <span class="f12">
                            <b>{{ item.data.name }}</b> posted an announcement on <b>{{ item.data.course }}</b>
                        </span>
                        <v-list-item-subtitle class="f10">{{ $dateFormat.fromNow(item.created_at) }}</v-list-item-subtitle>
                    </v-list-item-content>
                    <v-list-item-content class="py-1" v-if="item.type.split('\\')[2] === 'PrivateMessageNotification'">
                        <span class="f12">
                            <b>{{ item.data.name }}</b> {{ item.data.text }}
                        </span>
                        <v-list-item-subtitle class="f10">{{ $dateFormat.fromNow(item.created_at) }}</v-list-item-subtitle>
                    </v-list-item-content>
                    <v-list-item-content class="py-1" v-if="item.type.split('\\')[2] === 'AnnouncementCommentNotification'">
                        <span class="f12" v-if="role === 'user'">
                            <b>{{ item.data.name }}</b> replied to your comment in <b> {{ item.data.course }} </b>
                        </span>
                        <span class="f12" v-if="role === 'instructor'">
                            <b>{{ item.data.name }}</b> commented to your announcement on <b> {{ item.data.course }} </b>
                        </span>
                        <v-list-item-subtitle class="f10">{{ $dateFormat.fromNow(item.created_at) }}</v-list-item-subtitle>
                    </v-list-item-content>
                    <v-list-item-content class="py-1" v-if="item.type.split('\\')[2] === 'ForumPostCommentNotification'">
                        <span class="f12" v-if="!item.data.parent_id">
                            <b>{{ item.data.name }}</b> commented to your forum post <b> {{ item.data.title }} </b>
                        </span>
                        <span class="f12" v-else>
                            <b>{{ item.data.name }}</b> replied to your comment in <b> {{ item.data.title }} </b>
                        </span>
                        <v-list-item-subtitle class="f10">{{ $dateFormat.fromNow(item.created_at) }}</v-list-item-subtitle>
                    </v-list-item-content>
                    <v-list-item-content class="py-1" v-if="item.type.split('\\')[2] === 'GroupMessageNotification'">
                        <span class="f12" v-if="item.data.synced_users">
                            <b>{{ item.data.name }}</b> {{ item.data.action }} you to their group <b> {{ item.data.group_name }} </b>
                        </span>
                        <span class="f12" v-else>
                            <b>{{ item.data.name }}</b> sent a message in <b> {{ item.data.group_name }} </b>
                        </span>
                        <v-list-item-subtitle class="f10">{{ $dateFormat.fromNow(item.created_at) }}</v-list-item-subtitle>
                    </v-list-item-content>
                    <v-list-item-content class="py-1" v-if="item.type.split('\\')[2] === 'ShareAssessmentNotification'">
                        <span class="f12">
                            <b>{{ item.data.name }}</b> {{ item.data.text }}
                            <section class="d-flex align-center">
                                <v-icon size="15" color="primary">mdi-file-outline</v-icon> <b> {{ item.data.assessment }}</b>
                            </section>
                        </span>
                        <v-list-item-subtitle class="f10">{{ $dateFormat.fromNow(item.created_at) }}</v-list-item-subtitle>
                    </v-list-item-content>
                    <v-list-item-content class="py-1" v-if="item.type.split('\\')[2] === 'AssessmentDeadlineNotification'">
                        <div >
                            <div class="f12">
                                The deadline for <b>{{ item.data.course }}</b> assessment, <b>{{ item.data.title }}</b>, is approaching on <b>{{ $dateFormat.mmDDyy(item.data.deadline)  }}</b>
                            </div>
                        </div>
                        <v-list-item-subtitle class="f10">{{ $dateFormat.fromNow(item.created_at) }}</v-list-item-subtitle>
                    </v-list-item-content>
                    <v-list-item-content class="py-1" v-if="role === 'user' && item.type.split('\\')[2] === 'ForumPostReviewNotification'">
                        <div>
                            <div class="f12">
                                <b>{{ item.data.moderator_name }}</b> (  {{ item.data.moderator_email }} ) {{ item.data.status.toLowerCase() }} your post <b> {{ item.data.title }} </b>
                            </div>
                        </div>
                        <v-list-item-subtitle class="f10">{{ $dateFormat.fromNow(item.created_at) }}</v-list-item-subtitle>
                    </v-list-item-content>
                    <v-list-item-content class="py-1" v-if="role === 'admin' && item.type.split('\\')[2] === 'TicketReceiveNotification'">
                        <div>
                            <div class="f12">
                                You have received a new support ticket from <b>{{ item.data.name }}</b>. Please review and respond accordingly.
                            </div>
                        </div>
                        <v-list-item-subtitle class="f10">{{ $dateFormat.fromNow(item.created_at) }}</v-list-item-subtitle>
                    </v-list-item-content>
                    <v-list-item-content class="py-1" v-if="role === 'user' && item.type.split('\\')[2] === 'TicketStatusUpdateNotification'">
                        <div>
                            <div class="f12" v-if="item.data.status === 2">
                                Your ticket has been submitted and is now <b>pending</b> review. Our team will get back to you shortly.
                            </div>
                            <div class="f12" v-if="item.data.status === 3">
                                Your submitted ticket is now <b>on hold</b>. Our team is reviewing it, and we will update you as soon as there is progress. Thank you for your patience
                            </div>
                            <div class="f12" v-if="item.data.status === 4">
                                Your submitted ticket has been successfully <b>resolved!</b> If you need further assistance, feel free to submit a new request.
                            </div>
                            <div class="f12" v-if="item.data.status === 5">
                                Your submitted ticket has been successfully <b>closed</b>. If you need further assistance, feel free to submit a new request.
                            </div>
                        </div>
                        <v-list-item-subtitle class="f10">{{ $dateFormat.fromNow(item.created_at) }}</v-list-item-subtitle>
                    </v-list-item-content>
                    <v-list-item-content class="py-1" v-if="item.type.split('\\')[2] === 'TicketMessageNotification'">
                        <div>
                            <div class="f12">
                                <b>{{ item.data.name }}</b> {{ item.data.text }}. Please review and respond accordingly.
                            </div>
                        </div>
                        <v-list-item-subtitle class="f10">{{ $dateFormat.fromNow(item.created_at) }}</v-list-item-subtitle>
                    </v-list-item-content>
                    <v-list-item-content class="py-1" v-if="item.type.split('\\')[2] === 'ProfileFilloutNotification'">
                        <div>
                            <div class="f12">
                                {{ item.data.text }}
                            </div>
                        </div>
                        <v-list-item-subtitle class="f10">{{ $dateFormat.fromNow(item.created_at) }}</v-list-item-subtitle>
                    </v-list-item-content>
                </v-list-item>
            </section>
        </v-list>
    </v-card>
</template>

<script>
export default {
    props: ["notification", "role", "loading", "page", "total"],
    data: () => ({
        offsetTop: 0,
    }),
    mounted() {
        const masonry = document.querySelector('#notification-scroll-list');
        masonry.addEventListener('scroll', () => {
            if (masonry.scrollHeight - masonry.scrollTop <= masonry.clientHeight) {
                this.$emit('fetch')
            }
        })
    },
    methods: {
        onScroll (e) {
            this.offsetTop = e.target.scrollTop
        },
    }
}

</script>