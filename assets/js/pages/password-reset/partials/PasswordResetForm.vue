<script setup lang="ts">
import InputLabelBase from "@/components/input/InputLabelBase.vue";
import Preloader from "@/components/Preloader.vue";
import useForm from "@/composables/useForm";
import type { PasswordReset } from "@/entities/users";
import UserService from "@/services/UserService";
import { useUserStore } from "@/stores/user";
import { toaster } from "@/utils/toaster";
const emit = defineEmits(["cancel", "submit"]);
const { id, username } = useUserStore().user;
const cancel = () => {
  emit("cancel");
};
const { state, errors, isSubmitting, handleSubmit, meta, setTouched } = useForm({
  currentPassword: "",
  newPassword: "",
  newPassword_confirmation: "",
  id,
} as Partial<PasswordReset>);
const [currentPassword, newPassword, newPassword_confirmation] = state;
const onSubmit = handleSubmit(async (values, { resetForm }) => {
  try {
    const res = await new UserService().put(values, "password");
    toaster.success("Password changed successfully.");
    resetForm();
    emit("submit");
    return res;
  } catch (err) {
    toaster.error("Something went wrong.");
    Promise.reject(err);
  }
});
</script>
<template>
  <form class="grid gap-5 bg-white p-8 max-w-screen-md" @submit.prevent="setTouched(), onSubmit($event)">
    <input v-show="false" :value="username" type="text" name="username" autocomplete="username" />
    <InputLabelBase
      v-model:value="currentPassword"
      :error="meta.currentPassword.touched && errors.currentPassword"
      label="Old password"
      type="password"
      autocomplete="current-password"
      name="password"
    />
    <InputLabelBase
      v-model:value="newPassword"
      :error="meta.newPassword.touched && errors.newPassword"
      label="New password"
      type="password"
      autocomplete="new-password"
      name="new-password"
    />
    <InputLabelBase
      v-model:value="newPassword_confirmation"
      :error="meta.newPassword_confirmation.touched && errors.newPassword_confirmation"
      label="Confirm password"
      type="password"
      autocomplete="new-password"
      name="new-password_confirmation"
    />

    <div class="flex gap-8 justify-center mt-4">
      <button type="button" class="btn btn-cancel" @click="cancel">Cancel</button>

      <button type="submit" class="btn btn-primary">Submit</button>
    </div>
  </form>
  <Preloader v-if="isSubmitting" />
</template>

<style scoped></style>
