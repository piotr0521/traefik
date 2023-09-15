<script setup>
import { useRoute } from "vue-router";

import { useAssetTypeConfigStore } from "@/stores/assetTypeConfig";

import CollectablesPosition from "./partials/CollectablesPosition.vue";
import InvestmentPosition from "./partials/InvestmentPosition.vue";

const route = useRoute();
const config = useAssetTypeConfigStore().getBySlug(route.params.slug);
const selectedComponent = config.position;
const selector = {
  CollectablesPosition,
  InvestmentPosition,
};
</script>

<template>
  <component :is="selector[selectedComponent]"></component>
</template>
