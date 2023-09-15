<script setup>
import { usePopupStore } from "@/stores/popup";

const props = defineProps({
  stats: {
    count: {
      type: Number,
    },
  },
});

const { togglePopupState } = usePopupStore();
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
      <!-- start button -->
      <div class="flex justify-center py-11 mt-auto">
        <button
          class="btn-dashboard text-xl py-3 px-7 bg-blue-700 text-white"
          @click="togglePopupState('showInvestmentsPopup')"
        >
          Add Account
        </button>
      </div>
    </div>
  </div>
</template>
