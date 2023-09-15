<script setup lang="ts">
import { computed } from "vue";

import { formatCurrencyStruct, formatDate } from "@/utils/format";

const billing_information = {
  current_plan: "Premium",
  payment_method: {
    type: "Visa",
    number: "**** 7829",
    expiration: "06/2024",
    cv: "***",
  },
  promo: null,
  next_billing: {
    date: "2021-06-01",
    amount: {
      currency: "USD",
      base: 14.99,
    },
  },
};
const nextBilling = computed(
  () =>
    `${formatCurrencyStruct(billing_information.next_billing.amount, 2)} on ${formatDate(
      billing_information.next_billing.date
    )}`
);
const billingButton = computed(
  () => `Get ${formatCurrencyStruct(billing_information.next_billing.amount, 2)} off your next payment`
);
const creditCard = computed(
  () => `${billing_information.payment_method.type} ${billing_information.payment_method.number}`
);
</script>

<template>
  <div class="card p-0 flex flex-col flex-shrink-0 divide-y divide-blue-200 divide-dashed">
    <div class="px-8 py-6">
      <h2 class="h2">Billing</h2>
    </div>
    <div class="px-8 py-6 grid grid-cols-4">
      <div class="md:flex md:items-center col-span-3">
        <div class="mb-1 md:mb-0 md:w-1/3">
          <label class="h3"> Current plan </label>
        </div>
        <div class="md:w-2/3 md:flex-grow">
          <span class="text-indigo-900"> {{ billing_information.current_plan }} </span>
        </div>
      </div>
      <div class="flex justify-end">
        <!-- TODO link button -->
        <button class="btn btn-link">Manage subscribtion</button>
      </div>
    </div>
    <div class="px-8 py-6 grid grid-cols-4">
      <div class="md:flex md:items-center col-span-3">
        <div class="mb-1 md:mb-0 md:w-1/3">
          <label class="h3"> Payment method </label>
        </div>
        <div class="md:w-2/3 md:flex md:flex-grow justify-between md:justify-start md:space-x-16 text-indigo-900">
          <div class="flex space-x-2 items-center">
            <img src="/images/icon/cc-visa.svg" alt="" />
            <span> {{ creditCard }} </span>
          </div>
          <div>Expires {{ billing_information.payment_method.expiration }}</div>
          <div>
            {{ billing_information.payment_method.cv }}
          </div>
        </div>
      </div>
      <div class="flex justify-end">
        <!-- TODO link button -->
        <button class="btn btn-link flex items-center space-x-2">
          <img src="/images/icon/add.svg" alt="" /> <span>Add payment method</span>
        </button>
      </div>
    </div>
    <div class="px-8 py-6 grid grid-cols-4">
      <div class="md:flex md:items-center col-span-3">
        <div class="mb-1 md:mb-0 md:w-1/3">
          <label class="h3"> Promocode </label>
        </div>
        <div class="md:w-2/3 md:flex-grow">
          <span class="text-indigo-900"> {{ billing_information.promo || "None" }} </span>
        </div>
      </div>
    </div>
    <div class="px-8 py-6 grid grid-cols-4">
      <div class="md:flex md:items-center col-span-3">
        <div class="mb-1 md:mb-0 md:w-1/3">
          <label class="h3"> Next payment </label>
        </div>
        <div class="md:w-2/3 md:flex-grow">
          <span class="text-indigo-900">
            {{ nextBilling }}
          </span>
        </div>
      </div>
      <div class="flex justify-end">
        <!-- TODO link button -->
        <button class="btn btn-link">{{ billingButton }}</button>
      </div>
    </div>
  </div>
</template>
