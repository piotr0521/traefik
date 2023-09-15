<script setup>
import { formatCurrencyStruct, formatPercentReal } from "@/utils/format";

const props = defineProps({
  title: {
    type: String,
    default: "Return For Period",
  },
  amount: {
    type: Object,
    required: true,
  },
  percent: {
    type: String,
  },
});
</script>

<template>
  <!-- start period -->
  <div class="flex border-b border-blue-200 justify-between items-end p-7">
    <div class="grid">
      <span class="text-xl text-indigo-900 font-normal whitespace-nowrap">{{ title }}</span>
      <span
        class="text-3xl xl:text-xl 2xl:text-3xl text-blue-700 font-medium mt-2"
        :class="[amount.minor >= 0 ? 'text-blue-700' : 'text-red-500']"
      >
        {{ formatCurrencyStruct(amount) }}
      </span>
    </div>
    <div v-if="percent" class="flex items-center">
      <svg v-if="percent > 0" width="16" height="17" viewBox="0 0 16 17" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M0 8.5L1.415 9.915L7 4.33V16.5H9V4.33L14.585 9.915L16 8.5L8 0.5L0 8.5Z" fill="#18D165" />
      </svg>

      <svg
        v-else-if="percent < 0"
        width="16"
        height="16"
        viewBox="0 0 16 16"
        fill="none"
        xmlns="http://www.w3.org/2000/svg"
      >
        <path
          d="M16 8L14.585 6.585L9 12.17L9 1.56563e-06L7 1.74048e-06L7 12.17L1.415 6.585L-6.99382e-07 8L8 16L16 8Z"
          fill="#FF2626"
        />
      </svg>
      <span class="text-xl ml-3" :class="percent >= 0 ? 'text-green-500' : 'text-red-500'">
        {{ formatPercentReal(percent) }}
      </span>
    </div>
  </div>
  <!-- end period -->
</template>
