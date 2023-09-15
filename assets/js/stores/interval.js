import { defineStore } from "pinia";

import DateIntervalProvider from "@/utils/range";

const provider = new DateIntervalProvider();
export const useDateIntervalStore = defineStore("dateInterval", {
  state: () => ({
    //Initialize provider with default values instead of initializing it in the calendar component, this way  other components don't have to wait until the calendar component is mounted
    interval: provider.getInterval(),
  }),
  actions: {
    setInterval(interval) {
      this.interval = interval;
      localStorage.setItem("interval", JSON.stringify(interval));
    },
  },
});
