<script setup>
import { useRouter } from "vue-router";

import { useBreadcrumbsStore } from "@/stores/breadcrumbs";

import PasswordResetForm from "./partials/PasswordResetForm.vue";

const router = useRouter();
useBreadcrumbsStore().reset().addCrumb("Change password", "password");
</script>
<template>
  <div class="py-5 px-9 grid">
    <h1 class="text-indigo-900 text-3xl mb-4">Change Password</h1>
    <PasswordResetForm @cancel="router.back()" @submit="router.push('dashboard')" />
  </div>
</template>
