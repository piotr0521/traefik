<script setup>
import { isArray } from "lodash";
import { useRoute } from "vue-router";

import Return from "@/components/dashboard/Return.vue";
import { useAssetTypeStore } from "@/stores/assetType";
import { usePopupStore } from "@/stores/popup";

const route = useRoute();

const props = defineProps({
  stats: {
    count: {
      type: Number,
    },
  },
});
const { togglePopupState } = usePopupStore();
const handleAddInvestment = () => {
  useAssetTypeStore().setSelected(isArray(route.params.slug) ? route.params.slug[0] : route.params.slug);
  togglePopupState("showInvestmentsPopup");
};
</script>

<template>
  <div class="card w-scre p-0 flex flex-col xl:w-[490px] flex-shrink-0">
    <Return v-if="stats.roi" :amount="stats.roi.amount" :percent="stats.roi.percent" title="Change For Period" />
    <div class="grid divide-y divide-blue-200 divide-dashed px-7 pt-3">
      <div class="flex items-center justify-between py-4">
        <span class="text-slate-900">Accounts:</span>
        <span class="text-indigo-900 font-normal text-xl">
          {{ stats.count }}
        </span>
      </div>
      <div class="flex justify-center py-11 mt-auto">
        <button class="btn-dashboard text-xl py-3 px-7 bg-blue-700 text-white" @click="handleAddInvestment">
          <span>Add Account</span>
        </button>
      </div>
    </div>
  </div>
</template>
