// Standalone mode: domain validation is bypassed.
// The multi-tenant version called GET /tenants/domain to confirm the current
// URL is a registered tenant domain. In standalone there is only one domain,
// so no check is needed.
export async function domain({next}) {
  // no-op
}
