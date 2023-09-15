<script setup lang="ts">
import { storeToRefs } from "pinia";
import { computed, ref } from "vue";
import { useRoute } from "vue-router";

import ActionsMenu from "@/components/ActionsMenu.vue";
import BaseTable from "@/components/table/BaseTable.vue";
import useQuery from "@/composables/useQuery";
import useVueRouter from "@/composables/useVueRouter";
import { BUTTON_ACTIONS } from "@/constants/global";
import { type AccountHolder, AccountHolderQuery, type AccountHolderTable } from "@/entities/account-holder";
import { useAccountHolderStore } from "@/stores/accountHolder";
import { useBreadcrumbsStore } from "@/stores/breadcrumbs";
import { usePopupStore } from "@/stores/popup";
import { toaster } from "@/utils/toaster";

import AddAccountHolderPopup from "./partials/AddAccountHolderPopup.vue";
import EditAccountHolderPopup from "./partials/EditAccountHolderPopup.vue";
import SearchAccountHolder from "./partials/SearchAccountHolderForm.vue";

useBreadcrumbsStore().reset().addCrumb("Account Holders", "accountHolders");
const { replaceQuery } = useVueRouter();
const { setPopupState } = usePopupStore();
const { query, setQuery } = useQuery();
const { hydrating, items } = storeToRefs(useAccountHolderStore());
const actions = [
  { key: BUTTON_ACTIONS.edit, label: "Edit" },
  { key: BUTTON_ACTIONS.delete, label: "Delete" },
];
const accountHolder = ref<AccountHolder>();
const mappedAccountHolders = computed<AccountHolderTable>(() => ({
  headings: ["Name"],
  rows: localItems.value.map((item) => ({
    ...item,
    values: [item.name],
  })),
}));
const localItems = ref<AccountHolder[]>({ ...items.value });
const searchHandler = (query: AccountHolderQuery) => {
  setQuery({ ...query });
  replaceQuery({ ...query });
  localItems.value = items.value.filter((item) => item.name.includes(query.name as string));
};
const handleAdd = (accountHolder: AccountHolder) => {
  useAccountHolderStore().add(accountHolder);
  localItems.value = items.value;
};
const handleEdit = (accountHolder: AccountHolder) => {
  useAccountHolderStore().edit(accountHolder);
  localItems.value = items.value;
};
const route = useRoute();
const actionHandler = (action: keyof typeof BUTTON_ACTIONS) => {
  switch (action) {
    case "add":
      setPopupState({ popupName: "showPopupAccountHolderAdd", value: true });
      break;
  }
};
const initialize = function () {
  searchHandler(new AccountHolderQuery({ ...route.query }));
};

const editAccountHolder = (id: AccountHolder["id"]) => {
  accountHolder.value = items.value.find((item) => item.id === id);
  setPopupState({ popupName: "showPopupAccountHolderEdit", value: true });
};
const deleteAccountHolder = async (id: AccountHolder["id"]) => {
  useAccountHolderStore()
    .destroy(id)
    .then(() => {
      localItems.value = items.value;
      toaster.success("Account holder deleted");
    });
};

initialize();
</script>

<template>
  <div class="py-6 px-8 grid gap-8">
    <div class="flex justify-between items-center -mb-3">
      <h1 class="h1">Account Holders</h1>
      <ActionsMenu :actions="[{ key: BUTTON_ACTIONS.add, label: 'Add account holder' }]" @action="actionHandler" />
    </div>
    <SearchAccountHolder :disabled="hydrating" @submit="searchHandler(query)" />
    <BaseTable
      :disabled="hydrating"
      :data="mappedAccountHolders"
      :actions="actions"
      @action:edit="editAccountHolder"
      @action:delete="deleteAccountHolder"
    >
      <template #empty-rows>
        <span>There are no account holders</span>
      </template>
    </BaseTable>
  </div>

  <AddAccountHolderPopup @update:accountHolder="handleAdd" />
  <EditAccountHolderPopup :account-holder="accountHolder" @update:accountHolder="handleEdit" />
</template>
