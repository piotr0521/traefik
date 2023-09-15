import _merge from "lodash/merge";
import { defineStore } from "pinia";

import type { AssetTypeConfig } from "@/entities/asset-type-config";

import assetTypeBackend from "../../config/backend/asset_type.json";
import assetTypeFrontend from "../../config/frontend/asset_type.json";

export const useAssetTypeConfigStore = defineStore("assetTypeConfig", {
  state: () => ({
    config: {} as AssetTypeConfig,
  }),

  actions: {
    getBySlug(slug: string) {
      for (const key in this.config) {
        if (slug === key) {
          return this.config[key as keyof AssetTypeConfig];
        }
      }
    },

    async hydrate() {
      this.config = _merge(assetTypeBackend, assetTypeFrontend);
    },
  },
});
