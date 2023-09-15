<script setup>
import BaseTableHeader from "@/components/table/BaseTableHeader.vue";
import { formatCurrency, formatDate } from "@/utils/format";
const props = defineProps({
  lastData: {
    type: Object,
    required: true,
  },
});
</script>

<template>
  <div class="card p-7 flex flex-col">
    <div class="text-xl text-indigo-900 font-normal mb-6">
      {{ lastData.name }}
    </div>

    <table class="table-dashboard">
      <BaseTableHeader :header="lastData.header" />

      <!-- start items -->
      <tbody>
        <tr v-if="!lastData.data || !lastData.data.length">
          <td class="table-dashboard__td" :colspan="lastData.header.length">
            <div class="flex justify-center items-center h-full">
              <span class="text-gray-500">There are no results to show</span>
            </div>
          </td>
        </tr>
        <tr v-for="(item, key) in lastData.data" v-else :key="key">
          <!-- start name -->
          <td class="table-dashboard__td">{{ item.name }}</td>
          <!-- end name -->

          <!-- start date -->
          <td class="table-dashboard__td text-indigo-900 font-normal whitespace-nowrap">
            {{ formatDate(item.date) }}
          </td>
          <!-- end date -->

          <!-- start capital -->
          <td class="table-dashboard__td text-indigo-900 font-normal">
            {{ formatCurrency(item.value) }}
          </td>
          <!-- end capital -->
        </tr>
      </tbody>
      <!-- end items -->
    </table>
  </div>
</template>
