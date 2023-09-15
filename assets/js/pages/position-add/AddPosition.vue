<script setup>
import { useRoute } from "vue-router";

import { useAssetTypeConfigStore } from "@/stores/assetTypeConfig";

import BusinessPosition from "./partials/BusinessPosition.vue";
import CashPosition from "./partials/CashPosition.vue";
import CertificateDepositPosition from "./partials/CertificateDepositPosition.vue";
import CollectablesPosition from "./partials/CollectablesPosition.vue";
import CreditCardPosition from "./partials/CreditCardPosition.vue";
import CryptoCurrencyPosition from "./partials/CryptoCurrencyPosition.vue";
import InvestmentPosition from "./partials/InvestmentPosition.vue";
import LoanPosition from "./partials/LoanPosition.vue";
import MortgagePosition from "./partials/MortgagePosition.vue";
import PropertyDetailsPosition from "./partials/PropertyDetailsPosition.vue";
import PublicSecurityPosition from "./partials/PublicSecurityPosition.vue";

const route = useRoute();
const config = useAssetTypeConfigStore().getBySlug(route.params.slug);
const selectedComponent = config.position;
const selector = {
  CashPosition,
  CollectablesPosition,
  CreditCardPosition,
  CertificateDepositPosition,
  MortgagePosition,
  PublicSecurityPosition,
  PropertyDetailsPosition,
  InvestmentPosition,
  LoanPosition,
  BusinessPosition,
  CryptoCurrencyPosition,
};
</script>

<template>
  <component :is="selector[selectedComponent]"></component>
</template>
