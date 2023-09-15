import { parse } from "date-fns";
import _merge from "lodash/merge";
import { defineStore } from "pinia";

import axiosInstance from "@/middleware/api";

import { useUserStore } from "./user";

export const useStatsStore = defineStore("stats", {
  state: () => ({
    loading: false,
    data: [],
    error: null,
  }),
  getters: {
    isEmpty: (state) => 0 == state.data.stats.count,
    currentNetValue: (state) => state.data.total.total.value.current,
    change: (state) => state.data.total.total.value.change,
    assetAllocation: (state) => ("root_type" in state.data ? state.data.root_type : []),
    typeAssetAllocation: (state) => ("type" in state.data ? state.data.type : []),

    stats: (state) =>
      _merge(state.data.stats, {
        assets: state.data.balance[1].value.current,
        liabilities: state.data.balance[0].value.current,
      }),
    graphSeries(state) {
      return [
        {
          data: Object.entries(state.data.total.total.value.graph).map((value) => {
            return {
              x: parse(value[0], "yyyy-MM-dd", new Date()),
              y: value,
            };
          }),
        },
      ];
    },
  },
  actions: {
    async fetchByInterval(interval) {
      const userStore = useUserStore();
      const url = `/api/users/${userStore.user.id}/stats?from=${interval.start}&to=${interval.end}`;
      await this.fetch(url);
    },

    async fetchByIntervalAndType(interval, assetTypeId) {
      const userStore = useUserStore();
      const url = `/api/users/${userStore.user.id}/stats?type=${assetTypeId}&from=${interval.start}&to=${interval.end}`;
      await this.fetch(url);
    },

    async fetchByIntervalAndPosition(interval, positionId) {
      const userStore = useUserStore();
      const url = `/api/users/${userStore.user.id}/stats?position=${positionId}&from=${interval.start}&to=${interval.end}`;
      await this.fetch(url);
    },

    async fetch(url) {
      if (this.loading) return;

      this.loading = true;
      try {
        const { data } = await axiosInstance.get(url);
        this.data = data;
      } catch (error) {
        this.error = error;
        console.log("Error fetching data for user dashboard " + url);
        console.log(error);
      } finally {
        this.loading = false;
      }
    },
  },
});
