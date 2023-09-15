<script setup>
import Return from "@/components/dashboard/Return.vue";
import { formatCurrencyStruct, formatDate, formatDecimalNumber, formatPercentReal } from "@/utils/format";

const props = defineProps({
  stats: {
    count: {
      type: Number,
    },
  },
  position: {
    type: Object,
  },
});
</script>

<template>
  <div class="card w-scre p-0 flex flex-col xl:w-[490px] flex-shrink-0">
    <Return v-if="stats.roi" :amount="stats.roi.amount" :percent="stats.roi.percent" />
    <div class="grid divide-y divide-blue-200 divide-dashed px-7 pt-3">
      <div class="flex items-center justify-between py-4">
        <span class="text-slate-900">Distributions:</span>
        <span class="text-indigo-900 font-normal text-xl">
          {{ formatCurrencyStruct(stats.distributions) }}
        </span>
      </div>
      <div class="flex items-center justify-between py-4">
        <span class="text-slate-900">Contributions:</span>
        <span class="text-indigo-900 font-normal text-xl">
          {{ formatCurrencyStruct(stats.contributions) }}
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
      <div v-if="position.startDate" class="flex items-center justify-between py-4">
        <span class="text-slate-900">Start date:</span>
        <span class="text-indigo-900 font-normal text-xl">
          {{ formatDate(position.startDate) }}
        </span>
      </div>
      <div v-if="position.completeDate" class="flex items-center justify-between py-4">
        <span class="text-slate-900">Complete date:</span>
        <span class="text-indigo-900 font-normal text-xl">
          {{ formatDate(position.completeDate) }}
        </span>
      </div>
      <div class="flex items-center justify-between py-4">
        <span class="text-slate-900">Total contributions:</span>
        <span class="text-indigo-900 font-normal text-xl">
          {{ formatCurrencyStruct(position.contributions) }}
        </span>
      </div>
      <div class="flex items-center justify-between py-4">
        <span class="text-slate-900">Total distributions:</span>
        <span class="text-indigo-900 font-normal text-xl">
          {{ formatCurrencyStruct(position.distributions) }}
        </span>
      </div>
      <div v-if="position.irr" class="flex items-center justify-between py-4">
        <span class="text-slate-900">Total IRR:</span>
        <span class="text-indigo-900 font-normal text-xl">
          {{ formatPercentReal(position.irr) }}
        </span>
      </div>
      <div v-if="position.multiplier" class="flex items-center justify-between py-4">
        <span class="text-slate-900">Multiplier:</span>
        <span class="text-indigo-900 font-normal text-xl"> {{ formatDecimalNumber(position.multiplier) }}x </span>
      </div>
    </div>
  </div>
</template>
