<script setup lang="ts">
import { computed } from "vue";

import useQuery from "@/composables/useQuery";
import type { SponsorQuery } from "@/entities/sponsors";

const { setQuery, query } = useQuery();
defineProps<{
  disabled: boolean;
}>();
const searchQuery = computed<SponsorQuery>({
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
  <div class="card p-8">
    <form class="xl:flex justify-between items-center" @submit.prevent="submit">
      <div class="xl:mr-20 flex-grow mb-5 xl:mb-0">
        <input v-model="searchQuery.name" class="form-input" type="text" placeholder="Name" />
      </div>
      <div class="flex items-center">
        <span class="mr-7 text-indigo-900">Privacy:</span>
        <div class="grid grid-flow-col items-center mr-6">
          <input
            id="_public"
            v-model="searchQuery.privacy"
            type="checkbox"
            value="public"
            class="border-slate-300 rounded-md w-6 h-6"
          />
          <label for="_public" class="ml-4">Public</label>
        </div>
        <div class="grid grid-flow-col items-center">
          <input
            id="_private"
            v-model="searchQuery.privacy"
            type="checkbox"
            value="private"
            class="border-slate-300 rounded-md w-6 h-6"
          />
          <label for="_private" class="ml-4">Private</label>
        </div>
      </div>
      <button type="submit" class="btn btn-primary xl:ml-20 2xl:ml-44 mt-5 xl:mt-0" :disabled="disabled">Search</button>
    </form>
  </div>
</template>
