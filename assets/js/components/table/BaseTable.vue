<script setup>
import { computed, isVNode, ref, useSlots } from "vue";

import Preloader from "@/components/Preloader.vue";

import Pagination from "./Pagination.vue";
import TableActions from "./TableActions.vue";
import TableHeaderActions from "./TableHeaderActions.vue";
const props = defineProps({
  id: {
    type: String,
    default: null,
  },
  title: String,
  disabled: Boolean,
  data: {
    type: Object,
    required: true,
  },
  pageCount: {
    type: Number,
  },
  page: {
    type: Number,
  },
  actions: {
    type: Array,
  },
  tableHeaderActions: {
    type: Array,
    default: null,
  },
  hideAction: {
    type: Boolean,
    default: false,
  },
});
const emit = defineEmits(["change:paginate", "action:add", "action:edit", "action:delete"]);
const activeRow = ref(null);
const page = computed(() => props.page);
const pageCount = computed(() => props.pageCount);
const slots = useSlots();
const hasEmptyRowsInjected = () => {
  return !!slots["empty-rows"];
};
function paginationHandler(val) {
  emit("change:paginate", val);
}

function showActions(item) {
  activeRow.value = item && item.id !== activeRow.value?.id ? { ...item } : null;
}

function actionHandler(action, id = null) {
  switch (action) {
    case "add":
      emit("action:add");
      break;
    case "add_complete":
      emit("action:add_complete");
      break;
    case "edit":
      emit("action:edit", id);
      break;
    case "delete":
      emit("action:delete", id);
      break;
  }
}
</script>

<template>
  <div class="card p-8 flex flex-col relative">
    <div v-if="title" class="text-xl text-indigo-900 font-normal mb-6 flex justify-between items-center">
      <span>
        {{ title }}
      </span>
      <TableHeaderActions
        v-if="tableHeaderActions"
        :table-header-actions="tableHeaderActions"
        @action="actionHandler"
      />
    </div>

    <table :id="id" class="table-dashboard table-base">
      <!-- start header -->
      <thead>
        <tr>
          <th
            v-for="(item, index) in data.headings"
            :key="index"
            class="table-dashboard__th"
            :colspan="!hideAction && index === data.headings.length - 1 ? 2 : 1"
          >
            {{ item }}
          </th>
        </tr>
      </thead>
      <!-- end header -->

      <!-- start items -->
      <tbody>
        <tr v-if="!data.rows.length && !disabled">
          <td class="table-dashboard__td" :colspan="data.headings.length + 1">
            <div class="flex justify-center items-center h-full text-gray-500">
              <template v-if="hasEmptyRowsInjected">
                <slot name="empty-rows" />
              </template>
              <template v-else> No results to show </template>
            </div>
          </td>
        </tr>
        <tr v-for="(row, index) in data.rows" v-else :key="index">
          <td v-for="(col, index) in row.values" :key="index" class="table-dashboard__td">
            <component :is="col" v-if="isVNode(col)" />
            <span v-else class="text-slate-900" v-text="col" />
          </td>
          <td v-if="!hideAction" class="table-dashboard__td text-right">
            <TableActions :actions="actions" :element="row" @action="actionHandler" @change:show="showActions" />
          </td>
        </tr>
      </tbody>
      <!-- end items -->
    </table>

    <Pagination v-if="pageCount > 1 && page" :page-count="pageCount" :page="page" @change="paginationHandler" />
    <slot name="footer" />
    <Preloader v-if="disabled" />
  </div>
</template>
