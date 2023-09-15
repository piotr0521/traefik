import { defineStore } from "pinia";

export const usePopupStore = defineStore("popupStore", {
  state: () => ({
    popups: {},
  }),

  getters: {
    /* A getter that returns the state of the popup. */
    getPopupState: (state) => (popupName) => {
      return state.popups[popupName] || false;
    },
  },

  actions: {
    /* Toggling the state of the popup. */
    togglePopupState(popupName) {
      this.popups[popupName] = !this.getPopupState(popupName);
    },

    /* Setting the state of the popup. */
    setPopupState({ popupName, value }) {
      this.popups[popupName] = value;
    },
  },
});
