import { defineStore } from "pinia";

export const useBreadcrumbsStore = defineStore("breadcrumbs", {
  state: () => ({
    breadcrumbs: [],
  }),
  actions: {
    addDashboard() {
      this.breadcrumbs.push({
        route: {
          name: "dashboard",
        },
        title: "Dashboard",
      });
    },
    addCrumb(title, routeName, routeParams = {}) {
      this.breadcrumbs.push({
        route: {
          name: routeName,
          params: routeParams,
        },
        title: title,
      });
    },
    reset() {
      this.breadcrumbs = [];
      this.addDashboard();
      return this;
    },
  },
});
