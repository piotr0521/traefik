<script setup>
import { computed } from "vue";
import Paginate from "vuejs-paginate-next";
const props = defineProps({
  pageCount: {
    type: Number,
    required: true,
  },
  page: {
    type: Number,
    required: true,
  },
});
const page = computed(() => props.page);
const pageCount = computed(() => props.pageCount);
const emit = defineEmits(["change"]);

function clickCallback(val) {
  emit("change", val);
}
</script>

<template>
  <paginate
    :page-count="pageCount"
    :initial-page="page"
    :force-page="page"
    :click-handler="clickCallback"
    :prev-text="`
      <svg xmlns=&quot;http://www.w3.org/2000/svg&quot; class=&quot;h-4 w-4&quot; fill=&quot;none&quot; viewBox=&quot;0 0 24 24&quot; stroke=&quot;currentColor&quot; stroke-width=&quot;2&quot;>
        <path stroke-linecap=&quot;round&quot; stroke-linejoin=&quot;round&quot; d=&quot;M15 19l-7-7 7-7&quot; />
      </svg>
    `"
    :next-text="`
      <svg xmlns=&quot;http://www.w3.org/2000/svg&quot; class=&quot;h-4 w-4&quot; fill=&quot;none&quot; viewBox=&quot;0 0 24 24&quot; stroke=&quot;currentColor&quot; stroke-width=&quot;2&quot;>
        <path stroke-linecap=&quot;round&quot; stroke-linejoin=&quot;round&quot; d=&quot;M9 5l7 7-7 7 &quot; />
      </svg>
    `"
    :container-class="'pagination'"
  >
  </paginate>
</template>
