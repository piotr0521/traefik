<script setup lang="ts">
import { isArray } from "lodash";
import type { PropType } from "vue";
import { useRoute } from "vue-router";

import { useAssetTypeStore } from "@/stores/assetType";
import { usePopupStore } from "@/stores/popup";

defineProps({
  stats: {
    type: Object as PropType<{
      count: number;
      new: number;
      completed: number;
    }>,
    required: true,
  },
});
const { togglePopupState } = usePopupStore();
const route = useRoute();
const handleClick = () => {
  useAssetTypeStore().setSelected(isArray(route.params.slug) ? route.params.slug[0] : route.params.slug);
  togglePopupState("showInvestmentsPopup");
};
// $route.params.slug
</script>

<template>
  <div class="card w-scre p-0 flex flex-col xl:w-[490px] flex-shrink-0">
    <div class="grid divide-y divide-blue-200 divide-dashed px-7 pt-3">
      <div class="flex items-center justify-between py-4">
        <span class="text-slate-900">Accounts:</span>
        <span class="text-indigo-900 font-normal text-xl">
          {{ stats.count }}
        </span>
      </div>
      <div class="flex items-center justify-between py-4">
        <span class="text-slate-900">New Accounts:</span>
        <span class="text-indigo-900 font-normal text-xl">
          {{ stats.new }}
        </span>
      </div>

      <div class="flex items-center justify-between py-4">
        <span class="text-slate-900">Closed Accounts:</span>
        <span class="text-indigo-900 font-normal text-xl">
          {{ stats.completed }}
        </span>
      </div>
    </div>
    <!-- start button -->
    <div class="flex justify-center py-11 mt-auto">
      <button class="btn-dashboard text-xl py-3 px-7 bg-blue-700 text-white" @click="handleClick">Add Account</button>
    </div>
  </div>
</template>
