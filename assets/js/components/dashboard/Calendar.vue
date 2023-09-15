<script setup>
import { format } from "date-fns";
import { storeToRefs } from "pinia";
import { DatePicker } from "v-calendar";
import { computed, onMounted, ref } from "vue";
import { useRoute, useRouter } from "vue-router";

import { useDateIntervalStore } from "@/stores/interval";
import { useStatsStore } from "@/stores/stats";

const pickerConfig = {
  masks: {
    input: "MMM D, YYYY",
  },
  modelConfig: {
    type: "string",
    mask: "YYYY-MM-DD",
  },
  selectAttribute: {
    highlight: {
      class: "bg-blue-700",
      contentClass: "text-white",
      base: {
        class: "bg-blue-50",
      },
      start: {
        class: "bg-blue-700",
        contentClass: "text-white",
      },
      end: {
        class: "bg-blue-700",
        contentClass: "text-white",
      },
    },
  },
};

const route = useRoute();
const router = useRouter();
const dateIntervalStore = useDateIntervalStore();
const { interval } = storeToRefs(dateIntervalStore);

const computedInterval = computed({
  get() {
    return interval.value;
  },
  set(newInterval) {
    dateIntervalStore.setInterval(newInterval);
  },
});

// https://vuejs.org/guide/essentials/template-refs.html
const datepicker = ref(null);
const updatePicker = function (event) {
  const date = {
    start: format(getButtonStartDate(event.currentTarget), "yyyy-MM-dd"),
    end: format(getButtonEndDate(event.currentTarget), "yyyy-MM-dd"),
  };
  datepicker.value.updateValue(date);
};

const getButtonStartDate = function (btn) {
  let start = new Date();
  switch (btn.getAttribute("data-range")) {
    case "ytd":
      start = new Date(start.getFullYear(), 0, 1);
      break;
    case "max":
      start = btn.getAttribute("data-range-start") === "" ? start : Date.parse(btn.getAttribute("data-range-start"));
      break;
    default:
      start.setMonth(start.getMonth() - btn.getAttribute("data-range"));
  }
  return start;
};

const getButtonEndDate = function (btn) {
  if ("max" == btn.getAttribute("data-range")) {
    return Date.parse(btn.getAttribute("data-range-end"));
  }
  return new Date();
};

const btnChangeDate = ref(null);
const btnMax = ref(null);
const setStylesForButtons = function () {
  btnChangeDate.value.querySelectorAll(".btn-dashboard").forEach((el) => {
    if (interval.value.start === format(getButtonStartDate(el), "yyyy-MM-dd")) {
      el.classList.add("active");
    } else {
      el.classList.remove("active");
    }
  });
};

const statsStore = useStatsStore();
const { stats } = storeToRefs(statsStore);
onMounted(() => {
  setStylesForButtons();
});
</script>

<template>
  <div class="card flex flex-wrap items-center gap-5 justify-center xl:justify-between">
    <div ref="btnChangeDate" class="flex gap-2">
      <button ref="btnDefault" class="btn-dashboard uppercase" data-range="1" @click="updatePicker">
        <span class="mr-1">1</span>
        <span class="lg:hidden"> M</span>
        <span class="hidden lg:inline-block"> Month</span>
      </button>

      <button class="btn-dashboard uppercase" data-range="3" @click.stop="updatePicker">
        <span class="mr-1">3</span>
        <span class="lg:hidden"> M</span>
        <span class="hidden lg:inline-block"> Months</span>
      </button>

      <button class="btn-dashboard uppercase" data-range="6" @click="updatePicker">
        <span class="mr-1">6</span>
        <span class="lg:hidden"> M</span>
        <span class="hidden lg:inline-block"> Months</span>
      </button>

      <button class="btn-dashboard uppercase" data-range="12" @click="updatePicker">
        <span class="mr-1">12</span>
        <span class="lg:hidden"> M</span>
        <span class="hidden lg:inline-block"> Months</span>
      </button>

      <button class="btn-dashboard uppercase" data-range="ytd" @click="updatePicker">YTD</button>
      <button
        ref="btnMax"
        class="btn-dashboard uppercase"
        data-range="max"
        :data-range-start="stats.dates.minDate"
        :data-range-end="stats.dates.maxDate"
        @click="updatePicker"
      >
        MAX
      </button>
    </div>

    <div>
      <DatePicker
        ref="datepicker"
        v-model="computedInterval"
        :model-config="pickerConfig.modelConfig"
        :select-attribute="pickerConfig.selectAttribute"
        :drag-attribute="pickerConfig.selectAttribute"
        is-range
        :masks="pickerConfig.masks"
        :columns="2"
        :max-date="new Date()"
        @update:modelValue="setStylesForButtons"
      >
        <template #default="{ inputValue, inputEvents }">
          <div class="flex justify-center items-center bg-blue-50 py-2 px-9 rounded-sm relative">
            <svg class="mr-2" width="25" height="24" viewBox="0 0 25 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path
                fill-rule="evenodd"
                clip-rule="evenodd"
                d="M2.90053 15.0385L2.91318 19.2817L3.03382 19.5918C3.37286 20.4633 3.98539 21.0758 4.85687 21.4148L5.16698 21.5355H12.6413H20.1157L20.4376 21.4334C21.3203 21.1537 22.053 20.4298 22.3592 19.5346L22.4615 19.2357L22.4739 15.0155L22.4863 10.7954H12.6871H2.88788L2.90053 15.0385Z"
                fill="#92B4DD"
              />
              <path
                opacity="0.4"
                fill-rule="evenodd"
                clip-rule="evenodd"
                d="M7.38141 2.04066C7.16739 2.15326 6.98699 2.3377 6.89367 2.5393C6.81846 2.70176 6.80834 2.85051 6.80747 3.81123L6.80646 4.89917L5.99003 4.91812C5.25221 4.93528 5.14375 4.94843 4.8635 5.05473C3.99905 5.38268 3.37139 6.01034 3.04344 6.87479C2.93714 7.15504 2.92399 7.2635 2.90683 8.00132L2.88788 8.81775H12.6939H22.5L22.4811 8.00132C22.4639 7.2635 22.4507 7.15504 22.3444 6.87479C22.0149 6.00616 21.393 5.38429 20.5244 5.05473C20.2441 4.94843 20.1357 4.93528 19.3978 4.91812L18.5814 4.89917V3.82126C18.5814 2.95083 18.5677 2.71041 18.51 2.57233C18.357 2.20597 18.0016 1.96436 17.6159 1.96436C17.21 1.96436 16.9164 2.13877 16.7256 2.49331C16.6334 2.66464 16.6256 2.75383 16.6122 3.79269L16.5977 4.9081H12.6939H8.79022L8.77573 3.79269C8.7623 2.75383 8.75448 2.66464 8.66226 2.49331C8.53797 2.26236 8.39369 2.12663 8.17567 2.03556C7.95787 1.94453 7.55918 1.94711 7.38141 2.04066Z"
                fill="#92B4DD"
              />
            </svg>
            <span class="inline-flex text-indigo-900">
              {{ inputValue.start }}
            </span>

            <span class="inline-flex text-indigo-900 mx-2"> - </span>

            <span class="inline-flex text-indigo-900">
              {{ inputValue.end }}
            </span>

            <input
              :value="inputValue.start"
              class="absolute top-0 left-0 right-0 bottom-0 opacity-0"
              v-on="inputEvents.start"
            />
            <input
              :value="inputValue.end"
              class="absolute top-0 left-0 right-0 bottom-0 opacity-0"
              v-on="inputEvents.end"
            />
          </div>
        </template>
      </DatePicker>
    </div>
  </div>
</template>

<style scoped>
.btn-dashboard.active,
.btn-dashboard.active * {
  pointer-events: none;
  user-select: none;
}
</style>
