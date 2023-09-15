import { reactive, toRefs } from "vue";

import type { SearchQuery } from "@/entities/base";
const state = reactive<{
  query: SearchQuery;
}>({
  query: {},
});
export default function useQuery() {
  const actions = {
    setQuery: (newQuery: SearchQuery) => {
      state.query = newQuery;
    },
  };
  return {
    ...actions,
    ...toRefs(state),
  };
}
