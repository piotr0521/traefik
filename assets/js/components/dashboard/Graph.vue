<script setup>
import { reactive } from "vue";
import ApexCharts from "vue3-apexcharts";

import { formatCurrency } from "@/utils/format";
const props = defineProps({
  current: {
    type: Number,
    required: true,
  },
  series: {
    type: Object,
    required: true,
  },
  label: {
    type: String,
    default: "Net Worth",
  },
});
const options = reactive({
  chart: {
    offsetX: -15,
    toolbar: {
      show: false,
    },
    width: "100%",
  },
  dataLabels: {
    enabled: false,
  },
  stroke: {
    width: 2,
  },
  colors: ["#38BDF8"],
  fill: {
    type: "gradient",
    colors: ["#38BDF8"],
    gradient: {
      opacityFrom: 0.5,
      opacityTo: 0.3,
    },
  },
  yaxis: {
    labels: {
      formatter: (val) => formatCurrency(val),
    },
  },
  tooltip: {
    enabled: true,
    y: {
      title: {
        formatter: (seriesName) => "",
      },
    },
  },
  xaxis: {
    type: "datetime",
    labels: {
      format: "dd MMM",
    },
  },
});
</script>

<template>
  <!-- start graph -->
  <div class="card flex flex-col flex-grow p-7 min-h-400">
    <div class="flex items-end">
      <span class="text-xl text-indigo-900 font-normal">{{ label }}:</span>
      <span class="text-3xl font-medium ml-3" :class="[current >= 0 ? 'text-blue-700' : 'text-red-500']">
        {{ formatCurrency(current) }}
      </span>
    </div>
    <ApexCharts height="100%" width="100%" type="area" :options="options" :series="series"></ApexCharts>
  </div>
  <!-- end graph -->
</template>
