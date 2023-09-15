import { defineStore } from "pinia";
import { computed } from "vue";

import useFetch from "@/composables/useFetch";
import { type User, UserFetchState } from "@/entities/users";
import UserService from "@/services/UserService";
export const useUserStore = defineStore("user", () => {
  const { actions: fetchActions, getters: fetchGetters } = useFetch(new UserFetchState(), new UserService());
  const { item: user, loading, error } = fetchGetters;
  const getters = {
    initials: () =>
      computed(() => user.value.firstName?.charAt(0).toUpperCase() + user.value.lastName?.charAt(0).toUpperCase()),
  };
  const actions = {
    setId: (id: User["id"]) => {
      fetchActions.setItem({ ...user.value, id });
    },
    setName: (firstName: User["firstName"], lastName: User["lastName"]) => {
      fetchActions.setItem({ ...user.value, firstName, lastName });
    },
    setUsername: (username: User["username"]) => {
      fetchActions.setItem({ ...user.value, username });
    },
    fetch: () => fetchActions.fetch(user.value.id),
  };
  return {
    ...getters,
    ...actions,
    user,
    loading,
    error,
  };
});
