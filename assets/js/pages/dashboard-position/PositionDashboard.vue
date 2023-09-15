<script setup>
import axios from "axios";
import { storeToRefs } from "pinia";
import { computed, reactive, ref, watch } from "vue";
import { useRoute } from "vue-router";

import Calendar from "@/components/dashboard/Calendar.vue";
import DashboardEmpty from "@/components/dashboard/DashboardEmpty.vue";
import Graph from "@/components/dashboard/Graph.vue";
import Preloader from "@/components/Preloader.vue";
import BaseTable from "@/components/table/BaseTable.vue";
import { BUTTON_ACTIONS } from "@/constants/global";
import axiosInstance from "@/middleware/api";
import { useAssetTypeConfigStore } from "@/stores/assetTypeConfig";
import { useDateIntervalStore } from "@/stores/interval";
import { usePopupStore } from "@/stores/popup";
import { usePositionEventStore } from "@/stores/positionEvent";
import { usePositionEventTypeStore } from "@/stores/positionEventType";
import { useStatsStore } from "@/stores/stats";
import { contains } from "@/utils/array";
import useBreadcrumbs from "@/utils/breadcrumbs";
import { formatCurrencyStruct, formatDate } from "@/utils/format";
import { toaster } from "@/utils/toaster";

import PopupCompleteInvestmentEdit from "./partials/event/CompleteEventEdit.vue";
import PopupCompleteInvestmentAdd from "./partials/event/CompleteInvestmentAdd.vue";
import PopupEventsAdd from "./partials/event/EventsAdd.vue";
import PopupEventsEdit from "./partials/event/EventsEdit.vue";
import CashSummary from "./partials/summary/CashSummary.vue";
import CreditCardSummary from "./partials/summary/CreditCardSummary.vue";
import DefaultSummary from "./partials/summary/DefaultSummary.vue";
import InvestmentSummary from "./partials/summary/InvestmentSummary.vue";

const state = reactive({
  isFetching: false,
});
const asset = ref(null);
const position = ref(null);
const positionEventTypesConfig = ref([]);

const route = useRoute();

const dateIntervalStore = useDateIntervalStore();
const statsStore = useStatsStore();
const assetTypeConfigStore = useAssetTypeConfigStore();
const positionEventStore = usePositionEventStore();
const positionEventTypeStore = usePositionEventTypeStore();
const eventEdit = ref(null);

const { setPopupState, getPopupState } = usePopupStore();
const { interval } = storeToRefs(dateIntervalStore);
const fetchEvents = function () {
  positionEventStore.hydrate({
    position: route.params.uuid,
    date: {
      after: interval.value.start,
      before: interval.value.end,
    },
  });
};
const fetchData = function () {
  statsStore.fetchByIntervalAndPosition(interval.value, route.params.uuid);
  fetchEvents();
};
// initial fetch
fetchData();

// watch for interval changes
watch(interval, () => fetchData());

const fetchPosition = async function () {
  if (state.isFetching) return;
  state.isFetching = true;
  try {
    const { data } = await axiosInstance.get(`/api/position/positions/${route.params.uuid}`);
    position.value = data;
    const assetData = await axiosInstance.get(data.asset["@id"]);
    asset.value = assetData.data;
  } catch (e) {
    toaster.error("Loading error. Please try again later");
    throw e;
  } finally {
    state.isFetching = false;
  }
};
fetchPosition();

const createBreadcrumbs = function () {
  useBreadcrumbs.forPosition(asset.value.assetType.slug, position.value);
};
watch(asset, () => createBreadcrumbs());

const summaryComponent = ref(null);
watch(asset, () => {
  const config = assetTypeConfigStore.getBySlug(asset.value.assetType.slug);
  positionEventTypesConfig.value = config.positionEventTypes;
  summaryComponent.value = config.positionSummaryComponent;
});

const summarySelector = {
  DefaultSummary,
  CreditCardSummary,
  InvestmentSummary,
  CashSummary,
};
const mappedPositionEvents = computed(() => {
  return {
    headings: [
      "Date",
      contains(positionEventTypesConfig.value, ["DISTRIBUTION", "REINVEST"]) && "Distribution",
      contains(positionEventTypesConfig.value, ["CONTRIBUTION", "REINVEST"]) && "Contribution",
      "Value",
      "Type",
      "Notes",
    ].filter((i) => i !== false),
    rows: positionEventStore.items.map((item) => ({
      ...item,
      values: [
        formatDate(item.date),
        item.cashOut
          ? formatCurrencyStruct(item.cashOut, 2)
          : contains(positionEventTypesConfig.value, ["DISTRIBUTION", "REINVEST"])
          ? ""
          : false,
        item.cashIn
          ? formatCurrencyStruct(item.cashIn, 2)
          : contains(positionEventTypesConfig.value, ["CONTRIBUTION", "REINVEST"])
          ? ""
          : false,
        item.value && item.value.amount ? formatCurrencyStruct(item.value.amount) : "",
        positionEventTypeStore.map(item.type),
        item.notes,
      ].filter((i) => i !== false),
    })),
  };
});
const paginationHandler = function (val) {
  positionEventStore.changePage(val);
  fetchEvents();
};

const tableHeaderActions = [
  { key: BUTTON_ACTIONS.add, label: "Add Event" },
  // TODO: map out add complete if config doesnt contain it
  { key: BUTTON_ACTIONS.add_complete, label: "Add Complete Event" },
];
const actions = [
  { key: BUTTON_ACTIONS.edit, label: "Edit" },
  { key: BUTTON_ACTIONS.delete, label: "Delete" },
];

async function deletePositionEvent(id) {
  state.isFetching = true;
  try {
    const { data } = await axios.delete(`/api/position_events/${id}`);
    toaster.success("Event removed");
    fetchData();
  } catch (error) {
    toaster.error("Delete error. Please try again later");
  } finally {
    state.isFetching = false;
  }
}
function editEvent(id) {
  eventEdit.value = positionEventStore.items.find((item) => item.id == id);
  if (eventEdit.value.type == "COMPLETE") {
    setPopupState({
      popupName: "showPopupCompleteInvestmentEdit",
      value: true,
    });
  } else {
    setPopupState({ popupName: "showPopupEventEdit", value: true });
  }
}
</script>

<template>
  <div class="grid p-10 gap-7 lg:grid-cols-2">
    <template v-if="!statsStore.loading && !state.isFetching">
      <Calendar v-if="!statsStore.isEmpty" class="lg:col-span-2" />
      <template v-if="!statsStore.isEmpty">
        <div class="flex flex-col xl:flex-row gap-7 lg:col-span-2">
          <Graph :current="statsStore.currentNetValue" :series="statsStore.graphSeries" :label="'Net Value'" />
          <component
            :is="summarySelector[summaryComponent]"
            :amount="statsStore.change.amount"
            :percent="statsStore.change.percent"
            :stats="statsStore.stats"
            :position="position"
          ></component>
        </div>

        <BaseTable
          :title="'Events'"
          :disabled="positionEventStore.hydrating"
          :data="mappedPositionEvents"
          :page="positionEventStore.meta.page"
          :page-count="positionEventStore.meta.pages"
          :actions="actions"
          :table-header-actions="tableHeaderActions"
          class="lg:col-span-2"
          @action:add="setPopupState({ popupName: 'showPopupEventAdd', value: true })"
          @action:add_complete="setPopupState({ popupName: 'showPopupCompleteInvestmentAdd', value: true })"
          @action:delete="deletePositionEvent"
          @action:edit="editEvent"
          @change:paginate="paginationHandler"
        >
          <template #empty-rows>
            <span>There are no events to show</span>
          </template>
        </BaseTable>
      </template>

      <DashboardEmpty v-else class="lg:col-span-2 my-6"></DashboardEmpty>
    </template>

    <Preloader v-else />

    <PopupEventsAdd :position-event-types-config="positionEventTypesConfig" @event-add="fetchData" />
    <PopupEventsEdit
      :position-event-types-config="positionEventTypesConfig"
      :event="eventEdit"
      @event:edit="fetchData"
    />

    <PopupCompleteInvestmentAdd :position="position" @complete-investment-add="fetchData" />
    <PopupCompleteInvestmentEdit :event="eventEdit" @event:edit="fetchData" />
  </div>
</template>
