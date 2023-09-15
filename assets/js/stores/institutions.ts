import { defineStore } from "pinia";

import useHydrate from "@/composables/useHydrate";
import { InstitutionState } from "@/entities/institutions";
import InstitutionService from "@/services/InstitutionService";
export const useInstitutionStore = defineStore("institution", () => {
  const { actions, getters } = useHydrate(new InstitutionState(), new InstitutionService());
  return {
    ...getters,
    ...actions,
  };
});
