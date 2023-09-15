<script setup lang="ts">
import { isArray } from "lodash";
import { storeToRefs } from "pinia";
import { computed, reactive, ref } from "vue";
import { useRoute } from "vue-router";

import ActionsMenu from "@/components/ActionsMenu.vue";
import BaseTable from "@/components/table/BaseTable.vue";
import useQuery from "@/composables/useQuery";
import useVueRouter from "@/composables/useVueRouter";
import { BUTTON_ACTIONS } from "@/constants/global";
import { PaginationQuery } from "@/entities/base";
import type { Sponsor, SponsorTable } from "@/entities/sponsors";
import { SponsorQuery } from "@/entities/sponsors";
import { useBreadcrumbsStore } from "@/stores/breadcrumbs";
import { usePopupStore } from "@/stores/popup";
import { useSponsorStore } from "@/stores/sponsor";
import { toaster } from "@/utils/toaster";

import SearchSponsor from "./partials/SearchSponsorForm.vue";
import AddSponsorPopup from "./popup/AddSponsorPopup.vue";
import EditSponsorPopup from "./popup/EditSponsorPopup.vue";

useBreadcrumbsStore().reset().addCrumb("Sponsors", "sponsors");
const { togglePopupState } = usePopupStore();
const { setQuery, query } = useQuery();
const { items, hydrating, pagination, error } = storeToRefs(useSponsorStore());
const { destroy } = useSponsorStore();
const { pushQuery, replaceQuery } = useVueRouter();
const route = useRoute();
const actions = [
  { key: BUTTON_ACTIONS.edit, label: "Edit" },
  { key: BUTTON_ACTIONS.delete, label: "Delete" },
];

const paginationQuery = reactive<PaginationQuery>(new PaginationQuery());

const mappedSponsors = computed<SponsorTable>(() => ({
  headings: ["Sponsors"],
  rows: items.value.map((item) => ({
    ...item,
    values: [item.name],
  })),
}));
const sponsor = ref<Partial<Sponsor>>({});
const fetchSponsors = async function (forceFetch = false) {
  await useSponsorStore().hydrate(query.value, paginationQuery, forceFetch);
};

const paginationHandler = function (val: number) {
  paginationQuery.page = val;
  pushQuery({ ...query.value, ...paginationQuery });
  fetchSponsors();
  window.scrollTo(0, 0);
};

const searchHandler = function () {
  paginationQuery.page = 1;
  pushQuery({ ...query.value, ...paginationQuery });
  fetchSponsors();
};

const initialize = function () {
  setQuery(new SponsorQuery({ ...route.query }));
  paginationQuery.page = isArray(route.query.page) ? Number(route.query.page[0]) : Number(route.query.page) || 1;
  replaceQuery({ ...query.value, ...paginationQuery });
  fetchSponsors();
};

const sponsorEdit = (id: Sponsor["id"]) => {
  sponsor.value = items.value.find((item) => item.id === id) ?? {};
  togglePopupState("showPopupSponsorEdit");
};
const sponsorDelete = async (id: Sponsor["id"]) => {
  await destroy(id);
  if (!error.value) {
    fetchSponsors(true);
    toaster.success("Sponsor deleted");
  }
};
initialize();
</script>

<template>
  <div class="py-6 px-8 grid gap-8">
    <div class="flex justify-between items-center -mb-3">
      <h1 class="h1">Sponsors</h1>
      <ActionsMenu
        :actions="[{ key: BUTTON_ACTIONS.add, label: 'Add' }]"
        @action="togglePopupState('showPopupSponsorAdd')"
      />
    </div>
    <SearchSponsor :disabled="hydrating" @submit="searchHandler" />
    <BaseTable
      :disabled="hydrating"
      :data="mappedSponsors"
      :page="paginationQuery.page"
      :page-count="pagination.pageCount"
      :actions="actions"
      @action:edit="sponsorEdit"
      @action:delete="sponsorDelete"
      @change:paginate="paginationHandler"
    >
      <template #empty-rows>
        <span>There are no sponsors</span>
      </template>
    </BaseTable>
  </div>

  <AddSponsorPopup @added:sponsor="fetchSponsors"></AddSponsorPopup>
  <EditSponsorPopup :sponsor="sponsor" @updated:sponsor="fetchSponsors"></EditSponsorPopup>
</template>
