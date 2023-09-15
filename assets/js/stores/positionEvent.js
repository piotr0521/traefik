import { defineStore } from "pinia";
import qs from "qs";

import axiosInstance from "@/middleware/api";

export const usePositionEventStore = defineStore("positionEvent", {
  state: () => ({
    hydrating: false,
    error: null,
    items: [],
    meta: { page: 1, items: 0, pages: 0, perPage: 10 },
  }),

  actions: {
    async hydrate(query) {
      if (this.hydrating) return;
      this.hydrating = true;
      query.page = this.meta.page;
      query.itemsPerPage = this.meta.perPage;
      try {
        const { data } = await axiosInstance.get(`/api/position_events`, {
          params: query,
          // https://stackoverflow.com/questions/49944387/how-to-correctly-use-axios-params-with-arrays
          paramsSerializer: (params) => {
            return qs.stringify(params);
          },
        });
        this.items = data["hydra:member"];
        this.meta.items = data["hydra:totalItems"];
        this.meta.pages =
          data["hydra:view"] && data["hydra:view"]["hydra:last"]
            ? Number(data["hydra:view"]["hydra:last"].split("page=")[1])
            : 0;
      } catch (error) {
        this.error = error;
        console.log("Error fetching position events");
        console.log(error);
      } finally {
        this.hydrating = false;
      }
    },
    changePerPage(perPage) {
      this.meta.perPage = perPage;
    },
    changePage(page) {
      this.meta.page = page;
    },
  },
});
