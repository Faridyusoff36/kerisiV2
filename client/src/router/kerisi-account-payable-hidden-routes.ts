import type { RouteRecordRaw } from "vue-router";
import ApDirectVoucherView from "@/views/ApDirectVoucherView.vue";

/**
 * HIDDEN_PAGE_LEVEL4 Account Payable stubs were migrated into `router/index.ts` so they use
 * `AccountPayableLegacyPlaceholderView` (same pattern as LEVEL3 AP shells), not GenericLegacyPlaceholderView.
 */
export const kerisiAccountPayableHiddenRoutes: RouteRecordRaw[] = [
  {
    path: "/admin/kerisi/m/3461",
    name: "kerisi-ap-direct-voucher",
    component: ApDirectVoucherView,
    meta: { requiresAuth: true, title: "Direct Voucher" },
  },
];
