<script setup>
import Return from "@/components/dashboard/Return.vue";
import { usePopupStore } from "@/stores/popup";
import { formatCurrency } from "@/utils/format";

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
    liabilities: {
      type: Number,
    },
    assets: {
      type: Number,
    },
  },
});

const { togglePopupState } = usePopupStore();
</script>

<template>
  <div class="card w-scre p-0 flex flex-col xl:w-[490px] flex-shrink-0">
    <Return v-if="stats.roi" :amount="stats.roi.amount" :percent="stats.roi.percent" />
    <div class="grid divide-y divide-blue-200 divide-dashed px-7 pt-3">
      <div class="flex items-center justify-between py-4">
        <span class="text-slate-900">Assets:</span>
        <span class="text-indigo-900 font-normal text-xl">
          {{ formatCurrency(stats.assets) }}
        </span>
      </div>

      <div class="flex items-center justify-between py-4">
        <span class="text-slate-900">Liabilities</span>
        <span class="text-red-500 font-normal text-xl"> ({{ formatCurrency(stats.liabilities) }}) </span>
      </div>
    </div>
    <!-- start button -->
    <div class="flex justify-center py-11 mt-auto">
      <button
        class="btn-dashboard text-xl py-3 px-7 bg-blue-700 text-white"
        @click="togglePopupState('showInvestmentsPopup')"
      >
        Add Asset/Liability
      </button>
    </div>
  </div>
</template>
