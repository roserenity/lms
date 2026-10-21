import router from "@/router"

export function usersMutation(state, payload) {
  state.users = payload
}

export function registrationTablesMutation(state, payload) {
  state.registration_tables = payload
}

export function columnsMutation(state, payload) {
  state.columns = payload
}

export function classesMutation(state, payload) {
  state.classes = payload
}

export function coursesMutation(state, payload) {
  state.courses = payload
}

export function filteredUsersMutation(state, payload) {
  state.filtered_users = payload
}

export function tenantMutation(state, payload) {
  state.tenant = payload
}

export function enrolleesMutation(state, payload) {
  state.enrollees = payload
}

export function activitylogMutation(state, payload) {
  state.activitylogs = payload
}

export function ticketsMutation(state, payload) {
  state.tickets = payload
}

export function ticketCountMutation(state, payload) {
  state.ticketCount = payload
}

export function logMutation(state, payload) {
  state.logs = payload
}

export function advisoriesMutation(state, payload) {
  state.advisories = payload
}

export function certificateTemplateMutation(state, payload) {
  state.certificate_template_category = payload
}

export function documentsMutation(state, payload) {
  state.documents = payload
}

export function moduleFieldsMutation(state, payload) {
  state.module_fields = payload
}

export function announcementsMutation(state, payload) {
  state.announcements = payload
}

export function forumPostMutation(state, payload) {
  state.forum_posts = payload
}

export function notificationMutation(state, payload) {
  state.notification = payload
}

export function contactsMutation(state, payload) {
  state.contacts = payload
}

export function chatListsMutation(state, payload) {
  state.chat_lists = payload
}

export function groupListsMutation(state, payload) {
  state.group_lists = payload
}

export function chatWrapperMutation(state, payload) {
  state.chat_wrapper = payload
}

export function messagesMutation(state, payload) {
  state.messages = payload
}

export function trainingCreditMutation(state, payload) {
  state.training_credits = payload
}

export function landingPageBackgroundMutation(state, payload) {
  state.landing_page_backgrounds = payload
}

export function exportsMutation(state, payload) {
  state.file_exports = payload
}

export function bannedWordsMutation(state, payload) {
  state.banned_words = payload
}