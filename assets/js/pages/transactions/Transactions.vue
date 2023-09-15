<script setup>
import axios from "axios";
import { onMounted, reactive } from "vue";
import { useRoute, useRouter } from "vue-router";

import ActionsMenu from "@/components/ActionsMenu.vue";
import BaseTable from "@/components/table/BaseTable.vue";
import { BUTTON_ACTIONS } from "@/constants/global";
import axiosInstance from "@/middleware/api";
import { useBreadcrumbsStore } from "@/stores/breadcrumbs";
import { usePopupStore } from "@/stores/popup";
import { formatCurrencyStruct, formatDate } from "@/utils/format";
import { toaster } from "@/utils/toaster";

import SearchTransaction from "./partials/SearchTransaction.vue";
import PopupTransactionAdd from "./partials/TransactionAdd.vue";

useBreadcrumbsStore().reset().addCrumb("Transactions", "transactions");
const { setPopupState } = usePopupStore();

const actions = [
  { key: BUTTON_ACTIONS.edit, label: "Edit" },
  { key: BUTTON_ACTIONS.delete, label: "Delete" },
];

const state = reactive({
  transactions: [],
  pagination: {
    page: 1,
    items: 0,
    pages: 0,
    perPage: 30,
  },
  form: {
    position: null,
  },
  isFetching: false,
});

const route = useRoute();
const router = useRouter();

const mappedTransactions = function () {
  return state.transactions.map((item) => ({
    ...item,
    values: [item.position.name, formatDate(item.transactionDate), formatCurrencyStruct(item.amount)],
  }));
};

const queryObject = function () {
  let query = {};
  if (state.pagination.page !== 1) {
    query.page = state.pagination.page;
  }
  if (state.form.position) {
    query.position = state.form.position;
  }
  return query;
};

function changeRoute() {
  const query = queryObject();
  router.push({
    name: "transactions",
    query: query,
  });
}

function paginationHandler(val) {
  state.pagination.page = val;
  fetchTransactions();
  window.scrollTo(0, 0);
}

async function fetchTransactions() {
  if (state.isFetching) return;

  state.isFetching = true;
  try {
    changeRoute();
    const query = queryObject();
    const { data } = await axiosInstance.get(`/api/transactions`, {
      params: query,
    });
    state.transactions = data["hydra:member"];
    state.pagination.items = data["hydra:totalItems"];
    state.pagination.pages = Math.ceil(data["hydra:totalItems"] / state.pagination.perPage);
    state.isFetching = false;
  } catch (e) {
    state.isFetching = false;
    // this.$toast.error(`Error: fetching transactions`);
    throw e;
  }
}

function actionHandler(action) {
  switch (action) {
    case "add":
      setPopupState({ popupName: "showPopupTransactionAdd", value: true });
  }
}

async function deletePosition(id) {
  state.isFetching = true;
  try {
    const { data } = await axios.delete(`/api/transactions/${id}`);
    toaster.success("Position removed");
    state.isFetching = false;
    fetchTransactions();
  } catch (error) {
    toaster.error("Delete error. Please try again later");
  } finally {
    state.isFetching = false;
  }
}
function initialize() {
  const { page, position } = route.query;
  state.pagination.page = page ?? state.pagination.page;
  state.form.position = position ?? null;
}

onMounted(() => {
  initialize();
  fetchTransactions();
});
</script>

<template>
  <div class="py-6 px-8 grid gap-8">
    <div class="flex justify-between items-center -mb-3">
      <h1 class="h1">Transactions</h1>
      <ActionsMenu :actions="[{ key: BUTTON_ACTIONS.add, label: 'Add Transaction' }]" @action="actionHandler" />
    </div>

    <SearchTransaction v-model:position="state.form.position" @submit="fetchTransactions"></SearchTransaction>

    <BaseTable
      :disabled="state.isFetching"
      :data="{
        headings: ['Investment', 'Date', 'Amount'],
        rows: mappedTransactions(),
      }"
      :page="state.pagination.page"
      :page-count="state.pagination.pages"
      :actions="actions"
      @action:delete="deletePosition"
      @change:paginate="paginationHandler"
    >
      <template #empty-rows>
        <span>There are no transactions to show</span>
      </template>
    </BaseTable>
  </div>

  <PopupTransactionAdd />
</template>
