import { computed, reactive, type UnwrapRef } from "vue";

import type { AbstractModel, StateFetch } from "@/entities/base";
import type ModelService from "@/services/ModelService";

export default function useFetch<M extends AbstractModel>(type: StateFetch<M>, service: ModelService<M>) {
  const state = reactive<StateFetch<M>>(type);
  const actions = {
    fetch: async (id: M["id"]) => {
      if (state.loading) return;
      state.loading = true;
      try {
        const data = await service.show(id);
        state.item = data as UnwrapRef<M>;
      } catch (error: any) {
        state.error = error;
      } finally {
        state.loading = false;
      }
    },
    setItem: (item: M) => {
      state.item = item as UnwrapRef<M>;
    },
    setHydrating: (loading: boolean) => {
      state.loading = loading;
    },
  };
  const getters = {
    loading: computed(() => state.loading),
    item: computed(() => state.item),
    error: computed(() => state.error),
  };

  return {
    actions,
    getters,
  };
}
