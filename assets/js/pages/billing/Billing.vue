<script setup lang="ts">
import { useBreadcrumbsStore } from "@/stores/breadcrumbs";

import BillingInformation from "./partials/BillingInformation.vue";
import InvoiceHistory from "./partials/InvoiceHistory.vue";
const name = "Account Security";
useBreadcrumbsStore().reset().addCrumb(name, "account-security");
</script>

<template>
  <div class="py-6 px-8 grid gap-8">
    <div class="flex justify-between items-center -mb-3">
      <h1 class="h1">{{ name }}</h1>
    </div>
    <BillingInformation />
    <InvoiceHistory />
  </div>
</template>

<style scoped></style>
