<script setup lang="ts">
import { computed } from "vue";

import useQuery from "@/composables/useQuery";
import type { InstitutionQuery } from "@/entities/institutions";

const { query, setQuery } = useQuery();
defineProps<{
  disabled: boolean;
}>();
const searchQuery = computed<InstitutionQuery>({
  get: () => query.value,
  set: (val) => {
    setQuery(val);
  },
});
const emit = defineEmits(["submit"]);
function submit() {
  emit("submit", searchQuery.value);
}
</script>

<template>
  <div class="card p-8 relative">
    <form class="grid gap-5 xl:grid-cols-2 items-start" @submit.prevent="submit">
      <div class="grid gap-1">
        <input v-model="searchQuery.name" class="form-input" type="text" placeholder="Name" />
      </div>
      <div class="grid justify-end self-end">
        <button type="submit" class="btn btn-primary" :disabled="disabled">Search</button>
      </div>
    </form>
  </div>
</template>
