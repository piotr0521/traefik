<script setup lang="ts">
import { computed } from "vue";

import useQuery from "@/composables/useQuery";
import type { AccountHolderQuery } from "@/entities/account-holder";

const props = defineProps({
  disabled: Boolean,
});

const { query, setQuery } = useQuery();
const searchQuery = computed<AccountHolderQuery>({
  get: () => query.value,
  set: (value) => setQuery(value),
});
const emit = defineEmits(["submit", "change:query"]);

function submit() {
  emit("submit");
}
</script>

<template>
  <div class="card p-8">
    <form class="xl:flex justify-between items-center" @submit.prevent="submit">
      <div class="xl:mr-20 flex-grow mb-5 xl:mb-0">
        <input v-model="searchQuery.name" class="form-input" type="text" placeholder="Name" />
      </div>
      <button type="submit" class="btn btn-primary xl:ml-20 2xl:ml-44 mt-5 xl:mt-0" :disabled="disabled">Search</button>
    </form>
  </div>
</template>
