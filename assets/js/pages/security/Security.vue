<script setup lang="ts">
import { useBreadcrumbsStore } from "@/stores/breadcrumbs";

import MfaForm from "./partials/MFAForm.vue";
import SecurityForm from "./partials/SecurityForm.vue";
const name = "Account Security";
useBreadcrumbsStore().reset().addCrumb(name, "account-security");
</script>

<template>
  <div class="py-6 px-8 grid gap-8">
    <div class="flex justify-between items-center -mb-3">
      <h1 class="h1">{{ name }}</h1>
    </div>
    <SecurityForm />
    <MfaForm />
  </div>
</template>

<style scoped></style>
