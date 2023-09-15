<script setup>
import { reactive } from "vue";
import ApexCharts from "vue3-apexcharts";

import { formatCurrency } from "@/utils/format";

const props = defineProps({
  title: String,
  values: {
    type: Array,
    required: true,
  },
  names: {
    type: Array,
    required: true,
  },
});

const options = reactive({
  labels: props.names,
  dataLabels: {
    enabled: false,
  },
  legend: {
    show: false,
  },
  tooltip: {
    enabled: true,
    x: {
      show: true,
      format: "dd MMM",
    },
    y: {
      formatter: (val) => formatCurrency(val),
      title: {
        formatter: (seriesName) => seriesName,
      },
    },
  },
  plotOptions: {
    offsetX: 0,
    offsetY: 0,
    pie: {
      donut: {
        size: "60%",
      },
    },
  },
  colors: [
    "#F87171",
    "#FB923C",
    "#FBBF24",
    "#A3E635",
    "#4ADE80",
    "#34D399",
    "#2DD4BF",
    "#38BDF8",
    "#60A5FA",
    "#818CF8",
    "#A78BFA",
    "#C084FC",
    "#E879F9",
  ],
});
</script>

<template>
  <div class="card p-7">
    <div class="text-xl text-indigo-900 font-normal mb-6">{{ title }}</div>

    <div class="flex flex-col items-center 2xl:flex-row">
      <ApexCharts
        height="225"
        width="200"
        type="donut"
        :options="options"
        :series="values"
        class="flex-grow-0 flex-shrink-0"
      >
      </ApexCharts>
      <div class="flex justify-center items-center flex-grow">
        <table>
          <tr v-for="(name, index) in names" :key="index">
            <td class="slate-900 p-1">
              <div class="flex items-center">
                <span
                  class="w-[6px] h-[6px] inline-flex mr-2 rounded-full flex-shrink-0"
                  :style="{ backgroundColor: options.colors[index] }"
                ></span>
                {{ name }}
              </div>
            </td>

            <td class="text-indigo-900 p-1 pl-9">
              {{ formatCurrency(values[index]) }}
            </td>
          </tr>
        </table>
      </div>
    </div>
  </div>
</template>
