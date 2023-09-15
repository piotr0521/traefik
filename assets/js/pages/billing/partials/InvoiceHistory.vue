<script setup lang="ts">
import { computed, h, ref } from "vue";

import Badge from "@/components/Badge.vue";
import BaseTable from "@/components/table/BaseTable.vue";
import type { Invoice } from "@/entities/invoices";
import { formatCurrencyStruct, formatDate } from "@/utils/format";

const invoices = ref<Invoice[]>([]);
const mappedInvoices = computed(() => ({
  headings: ["Date", "Amount", "Status", "Description"],
  rows: invoices.value.map((item) => {
    return {
      ...item,
      values: [
        formatDate(item.date),
        item.amount.base ? formatCurrencyStruct(item.amount, 2) : "$0.00",
        h(
          Badge,
          {
            type: item.status == "paid" ? "success" : "danger",
          },
          () => (item.status == "paid" ? "Paid" : "Unpaid")
        ),
        item.description,
      ],
    };
  }),
}));

const fetchInvoices = async function () {
  invoices.value = [
    {
      "@id": "/api/invoices/1",
      "@type": "Invoice",
      id: 1,
      date: "2023-02-25",
      amount: {
        base: 0,
        currency: "USD",
      },
      status: "paid",
      description: "Trial Period",
    },
    {
      "@id": "/api/invoices/2",
      "@type": "Invoice",
      id: 2,
      date: "2023-02-25",
      amount: {
        base: 14.99,
        currency: "USD",
      },
      status: "paid",
      description: "Premium Subscription",
    },
  ];
};

const initialize = function () {
  fetchInvoices();
};

initialize();
</script>

<template>
  <BaseTable title="Invoice history" :data="mappedInvoices" :hide-action="true">
    <template #empty-rows>
      <span>There are no invoices</span>
    </template>
    <template #footer>
      <div class="mt-8">
        <!-- TODO add cancel class, slate 900 for text with opacity 50 -->
        <button class="btn btn-cancel-sub">Cancel Subscription</button>
      </div>
    </template>
  </BaseTable>
</template>
