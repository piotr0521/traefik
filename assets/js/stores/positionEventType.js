import { defineStore } from "pinia";

import eventTypeFrontend from "../../config/frontend/event_type.json";

export const usePositionEventTypeStore = defineStore("positionEventTypeStore", {
  state: () => ({
    config: [],
  }),

  actions: {
    map(typeEnum) {
      for (const key in this.config) {
        if (typeEnum === key) {
          return this.config[key];
        }
      }
    },

    async hydrate() {
      this.config = eventTypeFrontend;
    },
  },
});
