<script setup lang="ts">
import _debounce from "lodash/debounce";
import { storeToRefs } from "pinia";
import { computed, ref } from "vue";

import Badge from "@/components/Badge.vue";
import Preloader from "@/components/Preloader.vue";
import useForm from "@/composables/useForm";
import type { BadgeState } from "@/entities/base";
import type { User } from "@/entities/users.js";
import UserService from "@/services/UserService";
import { usePopupStore } from "@/stores/popup";
import { useUserStore } from "@/stores/user";
import { toaster } from "@/utils/toaster";

import EditPasswordPopup from "./EditPasswordPopup.vue";
const editEmail = ref(false);
const { setPopupState } = usePopupStore();
const { user } = storeToRefs(useUserStore());
const userForm = computed<{
  id: string | number | undefined;
  username: string | undefined;
  firstName: string | undefined;
  lastName: string | undefined;
}>(() => {
  return {
    id: user.value?.id,
    username: user.value?.username,
    firstName: user.value?.firstName,
    lastName: user.value?.lastName,
  };
});
const { state, errors, isSubmitting, meta, update } = useForm(userForm.value as Partial<User>);
const showPopupPasswordEdit = () => {
  setPopupState({ popupName: "showPopupPasswordEdit", value: true });
};
const [id, username, firstName, lastName] = state;
const states: { [key: string]: { value: BadgeState; label: string } } = {
  verified: {
    value: "success",
    label: "Verified",
  },
  unverified: {
    value: "danger",
    label: "Unverified",
  },
};
const verified = ref(states.verified); //TODO get user verified state on show method
const handleEdit = _debounce(({ target }) => {
  handleEdit.cancel();
  if (target.type === "submit") return;
  if (editEmail.value) {
    editEmail.value = false;
  } else {
    editEmail.value = true;
  }
}, 1);
async function handleSubmit() {
  const res = await update(new UserService(), false);
  if (res) {
    toaster.success("Username changed successfully");
    useUserStore().setUsername(username.value as string);
    editEmail.value = false;
  }
}
</script>
<template>
  <div class="card p-0 flex flex-col flex-shrink-0 divide-y divide-blue-200 divide-dashed">
    <div class="px-8 py-6">
      <h2 class="h2">Email & Password</h2>
    </div>
    <div class="px-8 py-6">
      <form class="grid grid-cols-6" @submit.prevent="handleSubmit">
        <div class="md:flex md:items-center col-span-5">
          <div class="mb-1 md:mb-0 md:w-1/3">
            <label class="h3"> Email </label>
          </div>
          <div class="md:w-2/3 md:flex-grow">
            <div v-show="!editEmail" class="flex">
              <span class="text-indigo-900" v-text="username" />
              <Badge class="ml-3" :type="verified.value"> {{ verified.label }} </Badge>
            </div>
            <div>
              <input
                v-show="editEmail"
                v-model="username"
                class="form-input py-2"
                placeholder="Username *"
                @keyup.esc="handleEdit"
              />
              <span v-if="meta.username.touched && errors.username" class="text-red-500">{{ errors.username }}</span>
            </div>
          </div>
        </div>
        <div class="flex justify-end">
          <button
            class="btn max-h-[38px]"
            :class="{
              'btn-security': !editEmail,
              'btn-primary font-normal text-base py-1.5 px-10': editEmail,
            }"
            :type="editEmail ? 'submit' : 'button'"
            @click="handleEdit"
          >
            {{ editEmail ? "Save" : "Edit" }}
          </button>
        </div>
      </form>
      <Preloader v-if="isSubmitting" />
    </div>
    <div class="px-8 py-6 grid grid-cols-6">
      <div class="md:flex md:items-center col-span-5">
        <div class="mb-1 md:mb-0 md:w-1/3">
          <label class="h3"> Password </label>
        </div>
        <div class="md:w-2/3 md:flex-grow">
          <span class="text-indigo-900">
            Click the button to change your password
            <!-- TODO Account status pill -->
          </span>
        </div>
      </div>

      <div class="flex justify-end">
        <button class="btn btn-security" @click="showPopupPasswordEdit">Edit</button>
      </div>
    </div>
  </div>
  <EditPasswordPopup />
</template>

<style scoped></style>
