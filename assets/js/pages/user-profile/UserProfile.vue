<script setup>
import axios from "axios";
import { onMounted, reactive } from "vue";
import { useRouter } from "vue-router";

import InputLabelBase from "@/components/input/InputLabelBase.vue";
import Preloader from "@/components/Preloader.vue";
import axiosInstance from "@/middleware/api";
import { useBreadcrumbsStore } from "@/stores/breadcrumbs";
import { useUserStore } from "@/stores/user";
import { toaster } from "@/utils/toaster";

const state = reactive({
  form: {
    firstName: null,
    lastName: null,
    username: null,
  },
  errors: {},
  isFetching: true,
});
const router = useRouter();
const id = useUserStore().user.id;
const submit = () => {
  state.isFetching = true;
  axios
    .patch(`/api/users/${id}`, state.form, {
      headers: { "Content-Type": "application/merge-patch+json" },
    })
    .then((response) => {
      useUserStore().setName(state.form.firstName, state.form.lastName);
      toaster.success("Profile updated successfully");
      router.push({ name: "dashboard" });
    })
    .catch((error) => {
      toaster.error("Error updating profile");
      if (error.response?.data?.violations) {
        error.response.data.violations.forEach((item) => {
          state.errors[item.propertyPath] = item.message;
        });
      }
    })
    .finally(() => {
      state.isFetching = false;
    });
};
const fetchUser = async () => {
  try {
    const { data } = await axiosInstance.get(`/api/users/${id}`);
    state.form = { ...data };
  } catch (error) {
    toaster.error("Error fetching user data");
    error.response.data.violations.forEach((item) => {
      state.errors[item.propertyPath] = item.message;
    });
  } finally {
    state.isFetching = false;
  }
};
useBreadcrumbsStore().reset().addCrumb("Update Profile", "profile");
const goBack = () => {
  router.back();
};
onMounted(() => {
  fetchUser();
});
</script>
<template>
  <div class="py-5 px-9 grid">
    <h1 class="text-indigo-900 text-3xl mb-4">Update profile</h1>

    <form class="grid gap-5 bg-white p-8 max-w-screen-md" @submit.prevent="submit">
      <InputLabelBase
        v-model:value="state.form.firstName"
        v-model:error="state.errors.firstName"
        label="First Name *"
        name="groshy_user_profile_firstname"
      />
      <InputLabelBase
        v-model:value="state.form.lastName"
        v-model:error="state.errors.lastName"
        label="Last Name *"
        name="groshy_user_profile_lastname"
      />
      <InputLabelBase
        v-model:value="state.form.username"
        v-model:error="state.errors.username"
        label="Username *"
        name="groshy_user_profile_username"
      />

      <div class="flex gap-8 justify-center mt-4">
        <button type="button" class="btn btn-cancel" @click="goBack()">Cancel</button>

        <button type="submit" class="btn btn-primary">Submit</button>
      </div>
    </form>
  </div>
  <Preloader v-if="state.isFetching" />
</template>
