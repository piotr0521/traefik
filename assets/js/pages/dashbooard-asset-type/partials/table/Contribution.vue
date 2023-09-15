<script setup>
import { formatCurrencyStruct } from "@/utils/format";

const props = defineProps({
  fullyCommitted: {
    type: Boolean,
    default: false,
  },
  hasDefaultValue: {
    type: Boolean,
    default: false,
  },
  contributions: {
    type: Object,
    required: true,
  },
  capitalCommitment: {
    type: Object,
    required: true,
  },
  hasLastValue: {
    type: Boolean,
    default: false,
  },
});
</script>
<template>
  <div>
    <template v-if="fullyCommitted">
      <span v-if="contributions" class="text-indigo-900 font-normal">
        {{ formatCurrencyStruct(contributions) }}
      </span>
    </template>
    <template v-else>
      <div>
        <span class="text-slate-900">Committed:</span>
        <span class="text-indigo-900 font-normal">
          {{ formatCurrencyStruct(capitalCommitment) }}
        </span>
      </div>
      <div v-if="contributions">
        <span class="text-slate-900">Called:</span>
        <span v-if="hasDefaultValue" class="text-indigo-900 font-normal">
          {{ formatCurrencyStruct(contributions) }}
        </span>
      </div>
    </template>
  </div>
</template>
