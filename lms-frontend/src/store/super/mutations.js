import router from "@/router"

export function tenantsMutation(state, payload) {
  state.tenants = payload
}

export function loadingMutation(state, payload) {
  state.loading = payload
}

export function pageMutation(state, payload) {
  state.page = payload
}

export function paginateMutation(state, payload) {
  state.paginate = payload
}