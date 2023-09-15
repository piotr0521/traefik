<script setup>
import axios from "axios";
import { storeToRefs } from "pinia";
import { reactive, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";

import Calendar from "@/components/dashboard/Calendar.vue";
import DashboardEmpty from "@/components/dashboard/DashboardEmpty.vue";
import Graph from "@/components/dashboard/Graph.vue";
import PopupCashAccoutEdit from "@/components/position-edit/CashAccoutEdit.vue";
import Preloader from "@/components/Preloader.vue";
import AssetAllocation from "@/pages/dashboard/partials/AssetAllocation.vue";
import { useAssetTypeStore } from "@/stores/assetType";
import { useAssetTypeConfigStore } from "@/stores/assetTypeConfig";
import { useDateIntervalStore } from "@/stores/interval";
import { usePopupStore } from "@/stores/popup";
import { useStatsStore } from "@/stores/stats";
import useBreadcrumbs from "@/utils/breadcrumbs";
import { toaster } from "@/utils/toaster";

import CashAccount from "./partials/account/CashAccount.vue";
import DefaultAccount from "./partials/account/DefaultAccount.vue";
import InvestmentAccount from "./partials/account/InvestmentAccount.vue";
import CashSummary from "./partials/summary/CashSummary.vue";
import CreditCardSummary from "./partials/summary/CreditCardSummary.vue";
import DefaultSummary from "./partials/summary/DefaultSummary.vue";
import InvestmentSummary from "./partials/summary/InvestmentSummary.vue";

// Dependencies
const route = useRoute();
const router = useRouter();
const assetTypeStore = useAssetTypeStore();
const dateIntervalStore = useDateIntervalStore();
const statsStore = useStatsStore();
const assetTypeConfigStore = useAssetTypeConfigStore();
useBreadcrumbs.forAssetType(route.params.slug);
function getInvestments() {
  loading.value = true;
  axios
    .get(assetTypeConfigStore.getBySlug(route.params.slug).positionUrl, {
      params: {
        assetType: assetTypeId,
        startDate: {
          before: dateIntervalStore.interval.end,
        },
        completeDate: {
          after: dateIntervalStore.interval.start,
        },
        order: {
          startDate: "DESC",
        },
        page: state.pagination.page,
      },
    })
    .then((response) => {
      investments.value = response.data["hydra:member"];
      state.pagination.items = response.data["hydra:totalItems"];
      state.pagination.pages = Math.ceil(response.data["hydra:totalItems"] / state.pagination.perPage);
    })
    .catch((errors) => {
      console.log("getInvestments error " + errors.message);
    })
    .finally(() => {
      loading.value = false;
    });
}
const investments = ref([]);
const loading = ref(false);
const assetTypeId = assetTypeStore.getBySlug(route.params.slug).id;
const state = reactive({
  pagination: {
    page: 1,
    items: 0,
    pages: 0,
    perPage: 30,
  },
});
const investment = ref(null);
const table = ref(null);
const paginationHandler = (page) => {
  state.pagination.page = page;
  getInvestments();
  if (table.value) {
    window.scrollTo(table.value.$el.offsetLeft, table.value.$el.offsetTop);
  } else {
    window.scrollTo(0, 0);
  }
};
getInvestments();

statsStore.fetchByIntervalAndType(dateIntervalStore.interval, assetTypeId);
const { interval } = storeToRefs(dateIntervalStore);
const { setPopupState } = usePopupStore();

watch(interval, (state) => {
  statsStore.fetchByIntervalAndType(state, assetTypeId);
  getInvestments(assetTypeId, route.params.slug);
});

const deleteAccount = (id, toasterText = "Position account deleted.") => {
  axios
    .delete(`${assetTypeConfigStore.getBySlug(route.params.slug).positionUrl}/${id}`)
    .then((response) => {
      toaster.success(toasterText);
      getInvestments(assetTypeId, route.params.slug);
    })
    .catch((errors) => {
      console.log("deleteAccount error " + errors.message);
      toaster.error("Delete error. Please try again later.");
    });
};
const editAccount = ({ id, popup = false }) => {
  if (!popup) {
    router.push({
      name: "editPosition",
      params: { slug: route.params.slug, uuid: id },
    });
  } else {
    setPopupState({ popupName: "showPopupCashAccountEdit", value: true });
    investment.value = investments.value.find((investment) => investment.id === id);
  }
};
const config = assetTypeConfigStore.getBySlug(route.params.slug);
const accountComponent = config.accountComponent;
const summaryComponent = config.summaryComponent;
const accountSelector = {
  InvestmentAccount,
  CashAccount,
  DefaultAccount,
};
const summarySelector = {
  InvestmentSummary,
  CashSummary,
  DefaultSummary,
  CreditCardSummary,
};
</script>

<template>
  <div class="grid p-10 gap-7 lg:grid-cols-2">
    <template v-if="!statsStore.loading">
      <Calendar v-if="!statsStore.isEmpty" class="lg:col-span-2" />
      <template v-if="!statsStore.isEmpty">
        <div class="flex flex-col xl:flex-row gap-7 lg:col-span-2">
          <Graph :current="statsStore.currentNetValue" :series="statsStore.graphSeries" :label="'Net Value'" />
          <component
            :is="summarySelector[summaryComponent]"
            :amount="statsStore.change.amount"
            :percent="statsStore.change.percent"
            :stats="statsStore.stats"
          ></component>
        </div>

        <AssetAllocation :asset-allocation="statsStore.typeAssetAllocation" class="lg:col-span-2"></AssetAllocation>

        <component
          :is="accountSelector[accountComponent]"
          :ref="
            (el) => {
              table = el;
            }
          "
          :investments="investments"
          :loading="loading"
          :page="state.pagination.page"
          :pages="state.pagination.pages"
          class="lg:col-span-2"
          @action:delete="deleteAccount"
          @action:edit="editAccount"
        ></component>
      </template>

      <DashboardEmpty v-else class="lg:col-span-2 my-6"></DashboardEmpty>
    </template>

    <Preloader v-else />
    <PopupCashAccoutEdit
      :cash-account-edit="investment"
      @cash-account-edit="getInvestments(assetTypeId, route.params.slug)"
    />
  </div>
</template>
