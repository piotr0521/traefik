import { defineStore } from "pinia";

import { useAccountHolderStore } from "./accountHolder";
import { useAccountTypeStore } from "./accountType";
import { useAssetTypeStore } from "./assetType";
import { useAssetTypeConfigStore } from "./assetTypeConfig";
import { usePositionEventTypeStore } from "./positionEventType";
import { useTagStore } from "./tags";
import { useUserStore } from "./user";

// Top level store to manage state of the data loading for other stores
// see https://github.com/directus/directus/blob/main/app/src/stores/app.ts
export const useAppStore = defineStore("app", {
  state: () => ({
    hydrated: false,
    hydrating: false,
    error: null,
  }),

  actions: {
    async hydrate() {
      const positionEventTypeStore = usePositionEventTypeStore();
      const assetTypeStore = useAssetTypeStore();
      const assetTypeConfigStore = useAssetTypeConfigStore();
      const userStore = useUserStore();
      const accountType = useAccountTypeStore();
      const accountHolder = useAccountHolderStore();
      const tagStore = useTagStore();
      if (this.hydrated === false) {
        this.hydrating = true;
        try {
          await assetTypeStore.hydrate();
          await positionEventTypeStore.hydrate();
          await assetTypeConfigStore.hydrate();
          await userStore.fetch();
          await accountType.hydrate();
          await accountHolder.hydrate();
          await tagStore.getTagGroups();
        } catch (error) {
          console.log(error);
          this.error = error;
        } finally {
          this.hydrating = false;
        }
        this.hydrated = true;
      }
    },
  },
});
