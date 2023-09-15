<script setup>
import { h } from "vue";
import { computed } from "vue";

import BaseTable from "@/components/table/BaseTable.vue";
import { BUTTON_ACTIONS } from "@/constants/global";
import { useDateIntervalStore } from "@/stores/interval";
import { formatCurrencyStruct, formatDate } from "@/utils/format";

import Link from "../table/Link.vue";
import Tags from "../table/Tags.vue";

const props = defineProps({
  investments: {
    type: Array,
    required: true,
  },
  loading: {
    type: Boolean,
    required: true,
  },
  page: {
    type: Number,
    required: true,
  },
  pages: {
    type: Number,
    required: true,
  },
});

const mappedInvestments = computed(() => ({
  headings: ["Name", "Open on", "Current value", "Tags"],
  rows: props.investments.map((item) => ({
    ...item,
    values: [
      h(Link, {
        id: item.id,
        interval: interval,
        name: item.name ?? "",
      }),
      formatDate(item.startDate),
      item.lastValue ? formatCurrencyStruct(item.lastValue.amount) : "$0",
      h(Tags, { tags: item.tags }),
    ],
  })),
}));
const actions = [
  { key: BUTTON_ACTIONS.edit, label: "Edit account" },
  { key: BUTTON_ACTIONS.delete, label: "Delete account" },
];

function paginationHandler(page) {
  emit("change:paginate", page);
}
function deleteAccount(id) {
  emit("action:delete", id, `Cash account has been deleted.`);
}
function editAccount(id) {
  emit("action:edit", { id, popup: true });
}
const { interval } = useDateIntervalStore();
const emit = defineEmits(["action:delete", "action:edit", "change:paginate"]);
</script>

<template>
  <BaseTable
    id="account-table"
    title="Accounts"
    :disabled="loading"
    :data="mappedInvestments"
    :actions="actions"
    :page="page"
    :page-count="pages"
    @action:delete="deleteAccount"
    @action:edit="editAccount"
    @change:paginate="paginationHandler"
  >
    <template #empty-rows>
      <span>There are no transactions to show</span>
    </template>
  </BaseTable>
</template>
