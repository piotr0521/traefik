<script setup lang="ts">
import { isArray } from "lodash";
import { useRoute } from "vue-router";

import Return from "@/components/dashboard/Return.vue";
import { useAssetTypeStore } from "@/stores/assetType";
import { usePopupStore } from "@/stores/popup";
import { formatCurrencyStruct, formatPercentReal } from "@/utils/format";
const { togglePopupState } = usePopupStore();
const route = useRoute();

const props = defineProps({
  amount: {
    type: Number,
    required: true,
  },
  percent: {
    type: Number,
    required: true,
  },
  stats: {
    type: Object,
    required: true,
  },
});
const handleAddInvestment = () => {
  useAssetTypeStore().setSelected(isArray(route.params.slug) ? route.params.slug[0] : route.params.slug);
  togglePopupState("showInvestmentsPopup");
};
</script>

<template>
  <div class="card w-scre p-0 flex flex-col xl:w-[490px] flex-shrink-0">
    <Return v-if="stats.roi" :amount="stats.roi.amount" :percent="stats.roi.percent" />
    <div class="grid divide-y divide-blue-200 divide-dashed px-7 pt-3">
      <div class="flex items-center justify-between py-4">
        <span class="text-slate-900">Total number of investments:</span>
        <span class="text-indigo-900 font-normal text-xl">
          {{ stats.count }}
        </span>
      </div>
      <div class="flex items-center justify-between py-4">
        <span class="text-slate-900">New investments:</span>
        <span class="text-indigo-900 font-normal text-xl">
          {{ stats.new }}
        </span>
      </div>
      <div class="flex items-center justify-between py-4">
        <span class="text-slate-900">Completed investments:</span>
        <span class="text-indigo-900 font-normal text-xl">
          {{ stats.completed }}
        </span>
      </div>
      <div class="flex items-center justify-between py-4">
        <span class="text-slate-900">Active investments:</span>
        <span class="text-indigo-900 font-normal text-xl">
          {{ stats.active }}
        </span>
      </div>
      <div class="flex items-center justify-between py-4">
        <span class="text-slate-900">Not started investments:</span>
        <span class="text-indigo-900 font-normal text-xl">
          {{ stats.notStarted }}
        </span>
      </div>
      <div class="flex items-center justify-between py-4">
        <span class="text-slate-900">Contributions:</span>
        <span class="text-indigo-900 font-normal text-xl">
          {{ formatCurrencyStruct(stats.contributions) }}
        </span>
      </div>
      <div class="flex items-center justify-between py-4">
        <span class="text-slate-900">Distributions:</span>
        <span class="text-indigo-900 font-normal text-xl">
          {{ formatCurrencyStruct(stats.distributions) }}
        </span>
      </div>
      <div v-if="stats.twr" class="flex items-center justify-between py-4">
        <span class="text-slate-900">Time Weighted Return:</span>
        <span class="text-indigo-900 font-normal text-xl">
          {{ formatPercentReal(stats.twr) }}
        </span>
      </div>
      <div v-if="stats.atwr" class="flex items-center justify-between py-4">
        <span class="text-slate-900">Annualised Time Weighted Return:</span>
        <span class="text-indigo-900 font-normal text-xl">
          {{ formatPercentReal(stats.atwr) }}
        </span>
      </div>
      <div v-if="stats.xirr" class="flex items-center justify-between py-4">
        <span class="text-slate-900">IRR:</span>
        <span class="text-indigo-900 font-normal text-xl">
          {{ formatPercentReal(stats.xirr) }}
        </span>
      </div>
    </div>

    <div class="flex justify-center py-11 mt-auto">
      <button class="btn-dashboard text-xl py-3 px-7 bg-blue-700 text-white" @click="handleAddInvestment">
        <span>Add Investment</span>
      </button>
    </div>
  </div>
</template>
