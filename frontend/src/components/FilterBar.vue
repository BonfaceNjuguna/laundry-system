<template>
  <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
    <input v-model="localFilters.from" type="date" class="border p-2 rounded" placeholder="From date" />
    <input v-model="localFilters.to" type="date" class="border p-2 rounded" placeholder="To date" />

    <select v-model="localFilters.service_id" class="border p-2 rounded">
      <option value="">All Services</option>
      <option v-for="service in services" :key="service.id" :value="service.id">
        {{ service.name }}
      </option>
    </select>

    <select v-model="localFilters.customer_id" class="border p-2 rounded">
      <option value="">All Customers</option>
      <option v-for="customer in customers" :key="customer.id" :value="customer.id">
        {{ customer.name }}
      </option>
    </select>

    <div class="md:col-span-4 flex justify-end">
      <button @click="applyFilters" :disabled="isApplying" class="bg-blue-500 text-white px-4 py-2 rounded mt-2 md:mt-0">
        Apply
      </button>
    </div>
  </div>
</template>

<script setup>
import { reactive, watch, toRefs } from 'vue'

const props = defineProps({
  filters: Object,
  services: Array,
  customers: Array,
  isApplying: Boolean
})
const emit = defineEmits(['update:filters', 'apply'])

const localFilters = reactive({ ...props.filters })

watch(localFilters, newVal => {
  emit('update:filters', { ...newVal })
})

function applyFilters() {
  emit('apply')
}
</script>
