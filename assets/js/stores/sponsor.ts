import { defineStore } from "pinia";

import useHydrate from "@/composables/useHydrate";
import { SponsorState } from "@/entities/sponsors";
import SponsorService from "@/services/SponsorService";
export const useSponsorStore = defineStore("sponsor", () => {
  const { actions, getters } = useHydrate(new SponsorState(), new SponsorService());
  return {
    ...getters,
    ...actions,
  };
});
