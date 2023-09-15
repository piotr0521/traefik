<script setup>
import { formatCurrency } from "@/utils/format";

const props = defineProps({
  assets: {
    type: Number,
    required: true,
  },
  liabilities: {
    type: Number,
    required: true,
  },
});
</script>

<template>
  <div class="grid divide-y divide-blue-200 divide-dashed px-7 pt-3">
    <div class="flex items-center justify-between py-4">
      <span class="text-slate-900">Assets:</span>
      <span class="text-indigo-900 font-normal text-xl">
        {{ formatCurrency(assets) }}
      </span>
    </div>

    <div class="flex items-center justify-between py-4">
      <span class="text-slate-900">Liabilities</span>
      <span class="text-red-500 font-normal text-xl"> ({{ formatCurrency(liabilities) }}) </span>
    </div>
  </div>
</template>
