import { defineStore } from "pinia";

import useHydrate from "@/composables/useHydrate";
import { type AccountHolder, AccountHolderState } from "@/entities/account-holder";
import AccountHolderService from "@/services/AccountHolderService";
export const useAccountHolderStore = defineStore("accountHolder", () => {
  const { actions, getters } = useHydrate(new AccountHolderState(), new AccountHolderService());
  // Override the destroy action to remove the item from the list
  const sort = () => {
    actions.setItems(getters.items.value.sort((a, b) => a.name.localeCompare(b.name)));
  };
  const add = async (accountHolder: AccountHolder) => {
    const items = [...getters.items.value, accountHolder];
    actions.setItems(items);
    sort();
  };
  const edit = async (accountHolder: AccountHolder) => {
    getters.items.value.splice(
      getters.items.value.findIndex((item) => item.id === accountHolder.id),
      1,
      accountHolder
    );
    sort();
  };
  const destroy = async (id: AccountHolder["id"]) => {
    await actions.destroy(id);
    actions.setItems([...getters.items.value.filter((item) => item.id !== id)]);
    actions.setHydrating(false);
  };

  return {
    ...getters,
    ...actions,
    add,
    edit,
    destroy,
  };
});
