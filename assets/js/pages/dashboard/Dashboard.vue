<script setup>
import axios from "axios";
import { storeToRefs } from "pinia";
import { ref, watch } from "vue";

import Calendar from "@/components/dashboard/Calendar.vue";
import DashboardEmpty from "@/components/dashboard/DashboardEmpty.vue";
import Graph from "@/components/dashboard/Graph.vue";
import Preloader from "@/components/Preloader.vue";
import DashboardSummary from "@/pages/dashbooard-asset-type/partials/summary/DashboardSummary.vue";
import { useAssetTypeStore } from "@/stores/assetType";
import { useBreadcrumbsStore } from "@/stores/breadcrumbs";
import { useDateIntervalStore } from "@/stores/interval";
import { useStatsStore } from "@/stores/stats";

import AssetAllocation from "./partials/AssetAllocation.vue";
import PieChars from "./partials/PieChars.vue";
import ShowLatest from "./partials/ShowLatest.vue";

useBreadcrumbsStore().reset();

const lastInvestments = ref({
  name: "Last Investments",
  data: null,
  header: ["Investment", "Date", "Capital commitment"],
});
const lastTransaction = ref({
  name: "Last Transactions",
  data: null,
  header: ["Investment", "Date", "Amount"],
});
const sponsors = ref({
  title: "Top sponsors",
  graph: {
    values: [],
    names: [],
  },
});
const vintage = ref({
  title: "Vintage years",
  graph: {
    values: [],
    names: [],
  },
});
const isHaveInvestments = ref();

const dateIntervalStore = useDateIntervalStore();
const statsStore = useStatsStore();
statsStore.fetchByInterval(dateIntervalStore.interval);
const { interval } = storeToRefs(dateIntervalStore);
watch(interval, (state) => {
  statsStore.fetchByInterval(state);
});

function getLastInvestments(assetsId) {
  axios
    .get(
      `/api/position/investments?page=1&itemsPerPage=5&asset.assetType.parent=%2Fapi%2Fasset_types%2F${assetsId}&order%5BstartDate%5D=desc`
    )
    .then((response) => {
      if (Array.isArray(response.data["hydra:member"]) && response.data["hydra:member"].length) {
        lastInvestments.value.data = response.data["hydra:member"].map((item) => {
          return {
            name: item.name,
            value: item.capitalCommitment.base,
            date: item.startDate,
          };
        });
        // check investments
        isHaveInvestments.value = true;
      }
    })
    .catch((error) => {
      console.log("dashboard investments:" + error.message);
    });
}
function getLastTransactions(assetsId) {
  axios
    .get(
      `/api/transactions?page=1&itemsPerPage=5&position.asset.assetType.parent=%2Fapi%2Fasset_types%2F${assetsId}&order%5BvalueDate%5D=desc`
    )
    .then((response) => {
      if (Array.isArray(response.data["hydra:member"]) && response.data["hydra:member"].length) {
        lastTransaction.value.data = response.data["hydra:member"].map((item) => {
          return {
            name: item.position.name,
            value: item.amount.base,
            date: item.transactionDate,
          };
        });
      }
    })
    .catch((error) => {
      console.log("dashboard transactions:" + error.message);
    });
}
const assetsId = useAssetTypeStore().getBySlug("real-estate")?.id;
getLastInvestments(assetsId);
getLastTransactions(assetsId);

function getSponsors() {
  axios
    .get(`/api/sponsors/stats`)
    .then((response) => {
      sponsors.value.graph.values = response.data["hydra:member"].map((item) => item.total);
      sponsors.value.graph.names = response.data["hydra:member"].map((item) => item.sponsor.name);
    })
    .catch((error) => {
      console.log("dashboard sponsors:" + error.message);
    });
}
function getVintage() {
  axios
    .get(`/api/years/stats`)
    .then((response) => {
      vintage.value.graph.names = response.data["hydra:member"].map((item) => item.year);
      vintage.value.graph.values = response.data["hydra:member"].map((item) => item.total);
    })
    .catch((error) => {
      console.log("vintage years " + error.message);
    });
}
getSponsors();
getVintage();
</script>

<template>
  <div class="grid p-10 gap-7 lg:grid-cols-2">
    <template v-if="!statsStore.loading">
      <Calendar v-if="!statsStore.isEmpty" class="lg:col-span-2" />
      <template v-if="!statsStore.isEmpty">
        <div class="flex flex-col xl:flex-row gap-7 lg:col-span-2">
          <Graph :current="statsStore.currentNetValue" :series="statsStore.graphSeries" :label="'Net Value'" />
          <DashboardSummary
            :amount="statsStore.change.amount"
            :percent="statsStore.change.percent"
            :stats="statsStore.stats"
          />
        </div>

        <AssetAllocation :asset-allocation="statsStore.assetAllocation" class="lg:col-span-2"></AssetAllocation>

        <template v-if="isHaveInvestments">
          <h2 class="text-normal text-indigo-900 text-3xl lg:col-span-2">Real Estate</h2>

          <ShowLatest class="lg:col-span-2 xl:col-span-1" :last-data="lastInvestments"> </ShowLatest>

          <ShowLatest class="lg:col-span-2 xl:col-span-1" :last-data="lastTransaction"> </ShowLatest>

          <PieChars :title="sponsors.title" :values="sponsors.graph.values" :names="sponsors.graph.names"> </PieChars>

          <PieChars :title="vintage.title" :values="vintage.graph.values" :names="vintage.graph.names"> </PieChars>
        </template>
      </template>

      <DashboardEmpty v-else class="lg:col-span-2 my-6"></DashboardEmpty>
    </template>

    <Preloader v-else />
  </div>
</template>
