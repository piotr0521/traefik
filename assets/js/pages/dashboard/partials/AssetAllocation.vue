<script setup>
import { computed } from "vue";

import { useDateIntervalStore } from "@/stores/interval";
import { formatCurrency, formatPercent } from "@/utils/format";
const props = defineProps({
  assetAllocation: {
    type: Object,
    required: true,
  },
});

const interval = computed(() => useDateIntervalStore().interval);
</script>

<template>
  <div v-if="Object.keys(assetAllocation).length > 1" class="card p-7">
    <div class="text-xl text-indigo-900 font-normal mb-6">Asset Allocation</div>

    <div class="table-dashboard">
      <div class="header grid-cols-4">
        <div>Asset</div>
        <div>Current Value</div>
        <div>Change for period</div>
        <div>Allocation</div>
      </div>

      <!-- start items -->
      <div v-for="(item, key) in assetAllocation" :key="item.model.id" class="item grid-cols-4">
        <!-- start name -->
        <div class="flex items-center">
          <router-link
            :to="{
              name: 'assetType',
              params: { slug: key },
              query: { from: interval.start, to: interval.end },
            }"
            class="hover:underline"
          >
            {{ item.model.name }}
          </router-link>
        </div>
        <!-- end name -->

        <!-- start current value -->
        <div class="text-indigo-900 font-normal flex items-center">
          {{ formatCurrency(item.value.current) }}
        </div>
        <!-- end current value -->

        <!-- start return for period  -->
        <div class="col items-center">
          <span :class="item.value.change.percent >= 0 ? 'text-green-500' : 'text-red-500'">
            {{ formatCurrency(item.value.change.amount) }}
          </span>

          <span class="percent font-normal">
            <svg
              v-if="item.value.change.percent > 0"
              width="16"
              height="17"
              viewBox="0 0 16 17"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path d="M0 8.5L1.415 9.915L7 4.33V16.5H9V4.33L14.585 9.915L16 8.5L8 0.5L0 8.5Z" fill="#18D165" />
            </svg>

            <svg
              v-else-if="item.value.change.percent < 0"
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

            <span :class="item.value.change.percent >= 0 ? 'text-green-500' : 'text-red-500'">
              {{ formatPercent(item.value.change.percent) }}%
            </span>
          </span>
        </div>
        <!-- end return for period -->

        <!-- start allocation -->
        <div class="col">
          <span class="xl:text-2xl indigo-900"> {{ formatCurrency(item.allocation.current) }}% </span>

          <span class="percent">
            <svg
              v-if="item.allocation.change.amount > 0"
              width="16"
              height="17"
              viewBox="0 0 16 17"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path d="M0 8.5L1.415 9.915L7 4.33V16.5H9V4.33L14.585 9.915L16 8.5L8 0.5L0 8.5Z" fill="#18D165" />
            </svg>

            <svg
              v-else-if="item.allocation.change.amount < 0"
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

            <span :class="item.allocation.change.amount >= 0 ? 'text-green-500' : 'text-red-500'">
              {{ formatPercent(item.allocation.change.amount) }}%
            </span>
          </span>
        </div>
        <!-- start allocation -->
      </div>
      <!-- end items -->
    </div>
  </div>
</template>
