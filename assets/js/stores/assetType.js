import { defineStore } from "pinia";

import axiosInstance from "@/middleware/api";

// @todo: this store should be persistent between page reloads, asset types do not change ever
export const useAssetTypeStore = defineStore("assetType", {
  state: () => ({
    hydrating: false,
    error: null,
    types: [],
    selected: "",
  }),

  getters: {
    tree(state) {
      const list = state.types;

      const assets = list.filter((item) => !item.parent && item.isAsset);
      const liabilities = list.filter((item) => !item.parent && !item.isAsset);
      const children = list.filter((item) => item.parent);

      children.forEach((item) => {
        const index = assets.findIndex((parent) => parent.id === item.parent.id);
        if (!assets[index].children) assets[index].children = [];
        assets[index].children.push(item);
      });

      return {
        assets: assets,
        liabilities: liabilities,
      };
    },
  },

  actions: {
    setSelected(value) {
      this.selected = value;
    },
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
        const { data } = await axiosInstance.get(`/api/asset_types`);
        this.types = data["hydra:member"];
      } catch (error) {
        this.error = error;
        console.log("Error fetching asset types");
        console.log(error);
      } finally {
        this.hydrating = false;
      }
    },
  },
});
