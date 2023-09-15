import { createRouter, createWebHistory } from "vue-router";

import { useAlertStore } from "@/stores/alert";

import AccountHolders from "./pages/account-holders/AccountHolders.vue";
import Billing from "./pages/billing/Billing.vue";
// components
import Dashboard from "./pages/dashboard/Dashboard.vue";
import PositionDashboard from "./pages/dashboard-position/PositionDashboard.vue";
import AssetTypeDashboard from "./pages/dashbooard-asset-type/AssetTypeDashboard.vue";
import Institutions from "./pages/institutions/Institutions.vue";
import PasswordReset from "./pages/password-reset/PasswordReset.vue";
import AddPosition from "./pages/position-add/AddPosition.vue";
import EditPosition from "./pages/position-edit/EditPosition.vue";
import Security from "./pages/security/Security.vue";
import Sponsors from "./pages/sponsors/Sponsors.vue";
import Tags from "./pages/tags/Tags.vue";
import Transactions from "./pages/transactions/Transactions.vue";
import UserProfile from "./pages/user-profile/UserProfile.vue";

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: "/user/dashboard",
      name: "dashboard",
      component: Dashboard,
    },
    {
      path: "/user/asset/:slug",
      name: "assetType",
      component: AssetTypeDashboard,
    },
    {
      path: "/user/asset/:slug/add",
      name: "addPosition",
      component: AddPosition,
    },
    {
      path: "/user/asset/:slug/edit/:uuid",
      name: "editPosition",
      component: EditPosition,
    },
    {
      path: "/user/position/:uuid",
      name: "position",
      component: PositionDashboard,
    },
    {
      path: "/user/profile",
      name: "profile",
      component: UserProfile,
    },
    {
      path: "/user/account-holders",
      name: "accountHolders",
      component: AccountHolders,
    },
    {
      path: "/user/sponsors",
      name: "sponsors",
      component: Sponsors,
    },
    {
      path: "/user/institutions",
      name: "institutions",
      component: Institutions,
    },
    {
      path: "/user/account-security",
      name: "account-security",
      component: Security,
    },
    {
      path: "/user/billing",
      name: "billing",
      component: Billing,
    },
    {
      path: "/user/transactions",
      name: "transactions",
      component: Transactions,
    },
    {
      path: "/user/tags",
      name: "tags",
      component: Tags,
    },
    {
      path: "/user/password",
      name: "password",
      component: PasswordReset,
    },
  ],
});

router.beforeEach(async (to, from) => {
  window.scrollTo(0, 0);

  // clear alert when route changes
  const alertStore = useAlertStore();
  alertStore.clear();
});

export default router;
