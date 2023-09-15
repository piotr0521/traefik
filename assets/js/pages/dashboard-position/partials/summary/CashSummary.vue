<script setup>
import Return from "@/components/dashboard/Return.vue";
import { formatDate, formatPercentReal } from "@/utils/format";

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
    <Return v-if="stats.roi" :amount="stats.roi.amount" :percent="stats.roi.percent" title="Change For Period" />
    <div class="grid divide-y divide-blue-200 divide-dashed px-7 pt-3">
      <div v-if="position.startDate" class="flex items-center justify-between py-4">
        <span class="text-slate-900">Open date:</span>
        <span class="text-indigo-900 font-normal text-xl">
          {{ formatDate(position.startDate) }}
        </span>
      </div>
      <div v-if="position.completeDate" class="flex items-center justify-between py-4">
        <span class="text-slate-900">Close date:</span>
        <span class="text-indigo-900 font-normal text-xl">
          {{ formatDate(position.completeDate) }}
        </span>
      </div>
      <div v-if="position.account" class="flex items-center justify-between py-4">
        <span class="text-slate-900">Account type:</span>
        <span class="text-indigo-900 font-normal text-xl">
          {{ position.account.accountType.name }}
        </span>
      </div>
      <div v-if="position.yield" class="flex items-center justify-between py-4">
        <span class="text-slate-900">Yield:</span>
        <span class="text-indigo-900 font-normal text-xl">
          {{ formatPercentReal(position.yield) }}
        </span>
      </div>
    </div>
  </div>
</template>
