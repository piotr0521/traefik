<script setup lang="ts">
import { isArray } from "lodash";
import { storeToRefs } from "pinia";
import { computed, reactive } from "vue";
import { useRoute } from "vue-router";

import ActionsMenu from "@/components/ActionsMenu.vue";
import BaseTable from "@/components/table/BaseTable.vue";
import useQuery from "@/composables/useQuery";
import useVueRouter from "@/composables/useVueRouter";
import { BUTTON_ACTIONS } from "@/constants/global";
import { PaginationQuery } from "@/entities/base";
import { type Institution, InstitutionQuery, type InstitutionTable } from "@/entities/institutions";
import { useBreadcrumbsStore } from "@/stores/breadcrumbs";
import { useInstitutionStore } from "@/stores/institutions";
import { usePopupStore } from "@/stores/popup";

import SearchInstitution from "./partials/SearchInstitutionForm.vue";
import AddInstitutionPopup from "./popup/AddInstitutionForm.vue";

useBreadcrumbsStore().reset().addCrumb("Institutions", "institutions");
const { items, hydrating, pagination } = storeToRefs(useInstitutionStore());
const { query, setQuery } = useQuery();
const { togglePopupState, getPopupState } = usePopupStore();
const { pushQuery, replaceQuery } = useVueRouter();

const paginationQuery = reactive<PaginationQuery>(new PaginationQuery());

const route = useRoute();

const actionHandler = (action: keyof typeof BUTTON_ACTIONS) => {
  switch (action) {
    case "add":
      togglePopupState("showPopupInstitutionAdd");
      break;
  }
};

const mappedInstitutions = computed<InstitutionTable>(() => ({
  headings: ["Name", "Website"],
  rows: items.value.map((item: Institution) => ({
    ...item,
    values: [item.name, item.website ?? ""],
  })),
}));
const fetchInstitutions = async function () {
  await useInstitutionStore().hydrate(query.value, paginationQuery);
};

const paginationHandler = function (val: number) {
  paginationQuery.page = val;
  pushQuery({ ...query.value, ...paginationQuery });
  fetchInstitutions();
  window.scrollTo(0, 0);
};

const searchHandler = function () {
  paginationQuery.page = 1;
  pushQuery({ ...query.value, ...paginationQuery });
  fetchInstitutions();
};

const initialize = function () {
  setQuery(new InstitutionQuery({ ...route.query }));
  paginationQuery.page = isArray(route.query.page) ? Number(route.query.page[0]) : Number(route.query.page) || 1;
  replaceQuery({ ...query.value, ...paginationQuery });
  fetchInstitutions();
};
initialize();
</script>

<template>
  <div class="py-6 px-8 grid gap-8">
    <div class="flex justify-between items-center -mb-3">
      <h1 class="h1">Institutions</h1>
      <ActionsMenu :actions="[{ key: BUTTON_ACTIONS.add, label: 'Add institution' }]" @action="actionHandler" />
    </div>
    <SearchInstitution :disabled="hydrating" @submit="searchHandler" />
    <BaseTable
      :disabled="hydrating"
      :data="mappedInstitutions"
      :page="paginationQuery.page"
      :page-count="pagination.pageCount"
      :hide-action="true"
      @change:paginate="paginationHandler"
    >
      <template #empty-rows>
        <span>There are no transactions to show</span>
      </template>
    </BaseTable>
    <AddInstitutionPopup v-if="getPopupState('showPopupInstitutionAdd')" @added:institution="fetchInstitutions" />
  </div>
</template>
