<template>
  <div style="background:#1A1E28; border:1px solid #252B38; border-radius:16px; padding:24px; width:400px; max-width:90vw; box-shadow:0 20px 25px -5px rgba(0,0,0,0.3);">
    <div v-if="!paid">
      <!-- Header -->
      <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
        <h2 style="font-size:18px; font-weight:700; color:#F1F5F9; margin:0; display:flex; align-items:center; gap:8px;">
          💳 Payment
        </h2>
        <button 
          @click="$emit('cancel')"
          style="width:32px; height:32px; background:transparent; border:none; color:#64748B; cursor:pointer; border-radius:6px; display:flex; align-items:center; justify-content:center; font-size:16px;"
          @mouseenter="e => e.currentTarget.style.background='#252B38'"
          @mouseleave="e => e.currentTarget.style.background='transparent'"
        >
          ✕
        </button>
      </div>

      <!-- Order Summary -->
      <div style="background:#12151C; border:1px solid #252B38; border-radius:8px; padding:12px; margin-bottom:20px;">
        <div style="font-size:12px; color:#64748B; margin-bottom:4px;">Order Summary</div>
        <div style="display:flex; justify-content:space-between; font-size:11px; color:#64748B; margin-bottom:2px;">
          <span>Subtotal</span><span>${{ order?.subtotal }}</span>
        </div>
        <div style="display:flex; justify-content:space-between; font-size:11px; color:#64748B; margin-bottom:2px;">
          <span>Service Charge (10%)</span><span>${{ order?.tax_amount }}</span>
        </div>
        <div style="display:flex; justify-content:space-between; font-weight:600; font-size:14px; color:#F1F5F9; border-top:1px solid #252B38; padding-top:4px; margin-top:4px;">
          <span>Total</span><span style="color:#F59E0B;">${{ order?.total }}</span>
        </div>
      </div>

      <!-- Payment Methods -->
      <div style="margin-bottom:20px;">
        <div style="font-size:12px; color:#64748B; margin-bottom:8px;">Select Payment Method</div>
        <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:8px;">
          <button
            v-for="m in methods"
            :key="m.value"
            @click="selectedMethod = m.value"
            style="border:2px solid #252B38; border-radius:8px; padding:12px 8px; text-align:center; transition:all 0.15s; cursor:pointer; background:#12151C;"
            :style="{ 
              borderColor: selectedMethod === m.value ? '#F59E0B' : '#252B38',
              background: selectedMethod === m.value ? 'rgba(245,158,11,0.1)' : '#12151C'
            }"
            @mouseenter="e => { if(selectedMethod !== m.value) e.currentTarget.style.borderColor='#3B82F6' }"
            @mouseleave="e => { if(selectedMethod !== m.value) e.currentTarget.style.borderColor='#252B38' }"
          >
            <div style="font-size:20px; margin-bottom:4px;">{{ m.icon }}</div>
            <div style="font-size:10px; color:#64748B; font-weight:500;">{{ m.label }}</div>
          </button>
        </div>
      </div>

      <!-- Cash Input -->
      <div v-if="selectedMethod === 'cash'" style="margin-bottom:20px;">
        <label style="font-size:12px; color:#64748B; display:block; margin-bottom:6px;">Cash Tendered</label>
        <input
          v-model="tendered"
          type="number"
          step="0.01"
          style="width:100%; background:#12151C; border:1px solid #252B38; border-radius:6px; padding:10px 12px; color:#F1F5F9; font-size:14px; outline:none;"
          :placeholder="order?.total"
          @focus="e => e.currentTarget.style.borderColor='#F59E0B'"
          @blur="e => e.currentTarget.style.borderColor='#252B38'"
        />
        <div v-if="change > 0" style="font-size:12px; color:#10B981; margin-top:6px; font-weight:600;">
          Change: ${{ change.toFixed(2) }}
        </div>
      </div>

      <!-- Action Buttons -->
      <div style="display:flex; gap:8px;">
        <button
          @click="$emit('cancel')"
          style="flex:1; padding:12px; border-radius:8px; font-size:14px; font-weight:600; background:transparent; color:#64748B; border:1px solid #252B38; cursor:pointer; transition:all 0.15s;"
          @mouseenter="e => e.currentTarget.style.background='#252B38'"
          @mouseleave="e => e.currentTarget.style.background='transparent'"
        >
          Cancel
        </button>
        <button
          @click="processPayment"
          :disabled="processing"
          style="flex:2; padding:12px; border-radius:8px; font-size:14px; font-weight:700; background:#10B981; color:#fff; border:none; cursor:pointer; transition:all 0.15s;"
          :style="{ opacity: processing ? '0.6' : '1', cursor: processing ? 'not-allowed' : 'pointer' }"
          @mouseenter="e => { if(!processing) e.currentTarget.style.background='#059669' }"
          @mouseleave="e => { if(!processing) e.currentTarget.style.background='#10B981' }"
        >
          {{ processing ? 'Processing...' : '✓ Confirm Payment' }}
        </button>
      </div>
    </div>

    <!-- Success State -->
    <div v-else style="text-align:center; padding:20px 0;">
      <div style="font-size:48px; margin-bottom:12px;">✅</div>
      <div style="font-size:20px; font-weight:700; color:#10B981; margin-bottom:8px;">Payment Successful!</div>
      <div style="font-size:14px; color:#64748B; margin-bottom:4px;">${{ order?.total }} · {{ methodLabel }}</div>
      <div style="font-size:12px; color:#64748B; font-family:monospace; opacity:0.7;">Receipt #{{ receiptNo }}</div>
      
      <button
        @click="$emit('paid')"
        style="margin-top:20px; width:100%; padding:12px; background:#F59E0B; color:#000; border-radius:8px; font-weight:700; cursor:pointer; border:none; transition:all 0.15s;"
        @mouseenter="e => e.currentTarget.style.background='#D97706'"
        @mouseleave="e => e.currentTarget.style.background='#F59E0B'"
      >
        Done
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useOrderStore } from '@/stores/orders'

const props = defineProps({ order: Object })
const emit  = defineEmits(['paid', 'cancel'])
const orderStore = useOrderStore()

const methods = [
  { value: 'cash',   label: 'Cash',   icon: '💵' },
  { value: 'card',   label: 'Card',   icon: '💳' },
  { value: 'mobile', label: 'Mobile', icon: '📱' },
]

const selectedMethod = ref('cash')
const tendered   = ref('')
const processing = ref(false)
const paid       = ref(false)
const receiptNo  = ref('')

const change = computed(() => {
  const t = parseFloat(tendered.value)
  const total = parseFloat(props.order?.total ?? 0)
  return t > total ? t - total : 0
})

const methodLabel = computed(() => methods.find(m => m.value === selectedMethod.value)?.label ?? '')

async function processPayment() {
  processing.value = true
  try {
    const result = await orderStore.processPayment(props.order.id, [{
      method:   selectedMethod.value,
      amount:   parseFloat(props.order.total),
      tendered: selectedMethod.value === 'cash' ? parseFloat(tendered.value || props.order.total) : parseFloat(props.order.total),
    }])
    receiptNo.value = result.payments[0]?.receipt_number ?? ''
    paid.value = true
  } finally {
    processing.value = false
  }
}
</script>