import { defineStore } from "pinia";

import axiosInstance from "@/middleware/api";

// @todo: this store should be persistent between page reloads, account types do not change often
export const useAccountTypeStore = defineStore("accountType", {
  state: () => ({
    hydrating: false,
    error: null,
    types: [],
  }),

  getters: {
    tree(state) {
      const list = state.types;

      const parents = list.filter((item) => !item.parent);
      const children = list.filter((item) => item.parent);

      children.forEach((item) => {
        const index = parents.findIndex((parent) => parent.id === item.parent.id);
        if (!parents[index].children) parents[index].children = [];
        parents[index].children.push(item);
      });

      return parents;
    },

    byParent: (state) => {
      return (parent) =>
        state.types.filter((item) => {
          if (item.parent && item.parent.name === parent) {
            return item;
          }
        });
    },
  },

  actions: {
    getBySlug(slug) {
      for (const el of this.types) {
        if (slug === el.slug) {
          return el;
        }
      }
    },

    async hydrate() {
      if (this.hydrating) return;

      this.hydrating = true;

      try {
        const { data } = await axiosInstance.get(`/api/account_types`);
        this.types = data["hydra:member"];
      } catch (error) {
        this.error = error;
        console.log("Error fetching account types");
        console.log(error);
      } finally {
        this.hydrating = false;
      }
    },
  },
});
