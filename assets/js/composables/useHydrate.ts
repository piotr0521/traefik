import { computed, reactive, type UnwrapNestedRefs } from "vue";

import type { AbstractModel, PaginationQuery, SearchQuery, StateList } from "@/entities/base";
import type ModelService from "@/services/ModelService";

export default function useHydrate<M extends AbstractModel>(type: StateList<M>, service: ModelService<M>) {
  const state = reactive<StateList<M>>(type);
  const actions = {
    hydrate: async (searchQuery: SearchQuery, paginationQuery?: PaginationQuery, forceFetch = false) => {
      if (state.hydrating && !forceFetch) return;
      state.hydrating = true;
      try {
        const data = await service.list({ ...searchQuery, ...paginationQuery });
        state.items = data.items as UnwrapNestedRefs<M[]>;
        state.pagination.itemCount = Number(data.totalItems);
        state.pagination.pageCount = Number(data.pageCount);
      } catch (error: any) {
        state.error = error;
      } finally {
        state.hydrating = false;
      }
    },
    destroy: async (id: M["id"]) => {
      if (state.hydrating) return;
      state.hydrating = true;
      try {
        await service.delete(id);
      } catch (error: any) {
        state.error = error;
        state.hydrating = false;
      }
    },
    setItems: (items: M[]) => {
      state.items = items as UnwrapNestedRefs<M[]>;
    },
    setHydrating: (hydrating: boolean) => {
      state.hydrating = hydrating;
    },
  };
  const getters = {
    hydrating: computed(() => state.hydrating),
    items: computed(() => state.items),
    error: computed(() => state.error),
    pagination: computed(() => state.pagination),
  };

  return {
    actions,
    getters,
  };
}
