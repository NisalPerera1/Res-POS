<template>
  <div
    style="background:#1A1E28; border:1px solid #252B38; border-radius:16px;
           width:420px; max-width:94vw; max-height:92vh; overflow:hidden;
           display:flex; flex-direction:column;
           box-shadow:0 25px 50px -12px rgba(0,0,0,0.5);"
  >

    <!-- PAYMENT FORM -->
    <div v-if="!showReceipt" style="display:flex; flex-direction:column; height:100%;">

      <!-- Header -->
      <div style="padding:16px 20px; border-bottom:1px solid #252B38;
                  display:flex; align-items:center; justify-content:space-between; flex-shrink:0;">
        <div style="font-size:17px; font-weight:700; color:#F1F5F9;"> Payment</div>
        <button @click="$emit('cancel')"
          style="width:30px; height:30px; background:transparent; border:none; color:#64748B;
                 cursor:pointer; border-radius:6px; font-size:18px; display:flex;
                 align-items:center; justify-content:center;"
          @mouseenter="e => e.currentTarget.style.background='#252B38'"
          @mouseleave="e => e.currentTarget.style.background='transparent'"
        >×</button>
      </div>

      <div style="flex:1; overflow-y:auto; padding:16px 20px;">

        <!-- Order info row -->
        <div style="display:flex; gap:8px; margin-bottom:14px; flex-wrap:wrap;">
          <div style="background:#12151C; border:1px solid #252B38; border-radius:8px;
                      padding:8px 12px; flex:1; min-width:80px;">
            <div style="font-size:10px; color:#64748B; margin-bottom:2px;">Order</div>
            <div style="font-size:12px; font-weight:700; color:#F1F5F9; font-family:monospace;">
              {{ order?.order_number }}
            </div>
          </div>
          <div style="background:#12151C; border:1px solid #252B38; border-radius:8px;
                   padding:8px 12px; flex:1; min-width:80px;">
            <div style="font-size:10px; color:#64748B; margin-bottom:2px;">Type</div>
            <div style="font-size:12px; font-weight:700; color:#F1F5F9;">Direct Order</div>
          </div>
        </div>

        <!-- Order items summary -->
        <div style="background:#12151C; border:1px solid #252B38; border-radius:8px;
                    padding:12px; margin-bottom:16px;">
          <div style="font-size:11px; color:#64748B; margin-bottom:8px; font-weight:600;">
            ORDER ITEMS ({{ activeItems.length }})
          </div>
          <div style="max-height:120px; overflow-y:auto;">
            <div v-for="item in activeItems" :key="item.id" 
              style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px; padding-bottom:8px; border-bottom:1px solid #252B38;">
              <div style="flex:1;">
                <div style="font-size:12px; color:#F1F5F9;">
                  {{ item.quantity }}x {{ item.name }}
                </div>
                <div v-if="item.modifiers && item.modifiers.length > 0"
                  style="font-size:10px; color:#64748B; margin-top:2px;">
                  {{ item.modifiers.map(m => m.name).join(', ') }}
                </div>
              </div>
              <span style="font-size:12px; font-weight:700; color:#F59E0B; margin-left:8px;">
                Rs. {{ item.total_price }}
              </span>
            </div>
          </div>
        </div>

        <!-- Payment methods -->
        <div style="margin-bottom:16px;">
          <div style="font-size:12px; color:#64748B; margin-bottom:8px; font-weight:600;">PAYMENT METHOD</div>
          <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:8px;">
            <button v-for="method in methods" :key="method.value"
              @click="selectedMethod = method.value"
              :style="{
                padding: '10px',
                border: selectedMethod === method.value ? '1px solid #F59E0B' : '1px solid #252B38',
                borderRadius: '8px',
                background: selectedMethod === method.value ? '#F59E0B' : '#12151C',
                color: selectedMethod === method.value ? '#000' : '#F1F5F9',
                fontSize: '12px',
                fontWeight: '600',
                cursor: 'pointer',
                display: 'flex',
                flexDirection: 'column',
                alignItems: 'center',
                gap: '4px'
              }"
              @mouseenter="e => { if(selectedMethod !== method.value) e.currentTarget.style.background='#252B38' }"
              @mouseleave="e => { if(selectedMethod !== method.value) e.currentTarget.style.background='#12151C' }"
            >
              <div style="font-size:16px;">{{ method.icon }}</div>
              <div>{{ method.label }}</div>
            </button>
          </div>
        </div>

        <!-- Cash payment details -->
        <div v-if="selectedMethod === 'cash'" style="margin-bottom:16px;">
          <div style="font-size:12px; color:#64748B; margin-bottom:8px; font-weight:600;">CASH TENDERED</div>
          <div style="display:flex; gap:8px; align-items:center;">
            <input
              v-model="tendered"
              type="number"
              step="0.01"
              placeholder="0.00"
              style="flex:1; padding:10px; background:#12151C; border:1px solid #252B38;
                     border-radius:8px; color:#F1F5F9; font-size:14px;"
              @focus="e => e.currentTarget.style.borderColor='#F59E0B'"
              @blur="e => e.currentTarget.style.borderColor='#252B38'"
            />
            <div style="font-size:12px; color:#64748B;">Rs.</div>
          </div>
          <div v-if="tendered && parseFloat(tendered) >= localTotal" style="margin-top:8px;">
            <div style="display:flex; justify-content:space-between; font-size:11px; color:#64748B;">
              <span>Change:</span>
              <span style="color:#10B981; font-weight:700;">Rs. {{ (parseFloat(tendered) - localTotal).toFixed(2) }}</span>
            </div>
          </div>
        </div>

        <!-- Card payment details -->
        <div v-if="selectedMethod === 'card'" style="margin-bottom:16px;">
          <div style="font-size:12px; color:#64748B; margin-bottom:8px; font-weight:600;">CARD REFERENCE</div>
          <input
            v-model="cardReference"
            type="text"
            placeholder="Last 4 digits or transaction ID"
            style="width:100%; padding:10px; background:#12151C; border:1px solid #252B38;
                   border-radius:8px; color:#F1F5F9; font-size:14px;"
            @focus="e => e.currentTarget.style.borderColor='#F59E0B'"
            @blur="e => e.currentTarget.style.borderColor='#252B38'"
          />
        </div>

        <!-- Error message -->
        <div v-if="errorMsg" style="margin-bottom:16px; padding:8px; background:rgba(239,68,68,0.1);
                    border:1px solid rgba(239,68,68,0.3); border-radius:6px;">
          <div style="font-size:11px; color:#EF4444;">{{ errorMsg }}</div>
        </div>

      </div>

      <!-- Footer with charge button -->
      <div style="padding:16px 20px; border-top:1px solid #252B38; flex-shrink:0;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
          <span style="font-size:12px; color:#64748B;">TOTAL</span>
          <span style="font-size:18px; font-weight:700; color:#F59E0B;">Rs. {{ localTotal.toFixed(2) }}</span>
        </div>
        <button @click="processPayment"
          :disabled="processing || chargeUpdating"
          style="flex:2; padding:12px; border-radius:8px; font-size:14px; font-weight:700;
                 background:#10B981; color:#fff; border:none; cursor:pointer; transition:all 0.15s;"
          :style="{ opacity: (processing || chargeUpdating) ? '0.6' : '1' }"
          @mouseenter="e => { if(!processing && !chargeUpdating) e.currentTarget.style.filter='brightness(1.1)' }"
          @mouseleave="e => e.currentTarget.style.filter='brightness(1)'"
        >
          {{ processing ? 'Processing...' : chargeUpdating ? 'Updating...' : ` Charge Rs. ${localTotal.toFixed(2)}` }}
        </button>
      </div>

    </div>

    <!-- RECEIPT -->
    <div v-else style="display:flex; flex-direction:column; height:100%;">

      <div style="padding:16px 20px; border-bottom:1px solid #252B38;
                  display:flex; align-items:center; justify-content:space-between; flex-shrink:0;">
        <div style="font-size:15px; font-weight:700; color:#10B981;"> Payment Complete</div>
        <div style="display:flex; gap:8px;">
          <button @click="printReceipt"
            style="padding:5px 12px; background:rgba(59,130,246,0.1); color:#3B82F6;
                   border:1px solid rgba(59,130,246,0.3); border-radius:6px;
                   font-size:11px; font-weight:600; cursor:pointer;">
            Print
          </button>
          <button @click="$emit('receiptClosed')"
            style="padding:5px 12px; background:rgba(239,68,68,0.1); color:#EF4444;
                   border:1px solid rgba(239,68,68,0.3); border-radius:6px;
                   font-size:11px; font-weight:600; cursor:pointer;">
            Close
          </button>
        </div>
      </div>

      <div style="flex:1; overflow-y:auto; padding:0;" id="receipt-content">
        <div style="padding:20px; font-family:monospace;">

          <div style="text-align:center; margin-bottom:16px;">
            <div style="font-size:16px; font-weight:700; color:#F1F5F9;">
              {{ receiptData?.receipt?.restaurant_name ?? 'Restaurant POS' }}
            </div>
            <div style="font-size:11px; color:#64748B; margin-top:2px;">
              {{ receiptData?.receipt?.printed_at }}
            </div>
          </div>

          <div style="border-top:1px dashed #252B38; border-bottom:1px dashed #252B38;
                      padding:10px 0; margin-bottom:12px;">
            <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
              <span style="font-size:11px; color:#64748B;">Order</span>
              <span style="font-size:11px; color:#F1F5F9;">{{ receiptData?.order?.order_number }}</span>
            </div>
            <div v-if="receiptData?.order?.customer_name"
              style="display:flex; justify-content:space-between; margin-bottom:4px;">
              <span style="font-size:11px; color:#64748B;">Customer</span>
              <span style="font-size:11px; color:#F1F5F9;">{{ receiptData.order.customer_name }}</span>
            </div>
            <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
              <span style="font-size:11px; color:#64748B;">Cashier</span>
              <span style="font-size:11px; color:#F1F5F9;">{{ receiptData?.receipt?.cashier }}</span>
            </div>
          </div>

          <div style="margin-bottom:12px;">
            <div v-for="item in receiptData?.items" :key="item.id" style="margin-bottom:8px;">
              <div style="display:flex; justify-content:space-between; align-items:start;">
                <div style="flex:1;">
                  <span style="font-size:12px; color:#F1F5F9;">
                    {{ item.quantity }}x {{ item.name }}
                  </span>
                  <div v-if="item.modifiers && item.modifiers.length > 0"
                    style="font-size:10px; color:#64748B; margin-top:1px;">
                    {{ item.modifiers.map(m => m.name).join(', ') }}
                  </div>
                  <div v-if="item.notes"
                    style="font-size:10px; color:#64748B; margin-top:1px; font-style:italic;">
                    * {{ item.notes }}
                  </div>
                </div>
                <span style="font-size:12px; color:#94A3B8; margin-left:8px;">
                  Rs. {{ item.total_price }}
                </span>
              </div>
            </div>
          </div>

          <div style="border-top:1px dashed #252B38; padding-top:10px; margin-bottom:12px;">
            <div style="display:flex; justify-content:space-between; margin-bottom:3px;">
              <span style="font-size:12px; color:#64748B;">Subtotal</span>
              <span style="font-size:12px; color:#94A3B8;">Rs. {{ receiptData?.totals?.subtotal }}</span>
            </div>
            <div style="display:flex; justify-content:space-between; margin-bottom:3px;">
              <span style="font-size:12px; color:#64748B;">
                Service ({{ receiptData?.totals?.tax_rate }}%)
              </span>
              <span style="font-size:12px; color:#94A3B8;">
                {{ parseFloat(receiptData?.totals?.tax_rate) === 0
                   ? 'Waived'
                   : 'Rs. ' + receiptData?.totals?.tax_amount }}
              </span>
            </div>
            <div v-if="parseFloat(receiptData?.totals?.discount_amount) > 0"
              style="display:flex; justify-content:space-between; margin-bottom:3px;">
              <span style="font-size:12px; color:#10B981;">Discount</span>
              <span style="font-size:12px; color:#10B981;">-Rs. {{ receiptData.totals.discount_amount }}</span>
            </div>
            <div style="display:flex; justify-content:space-between;
                        padding-top:8px; border-top:1px dashed #252B38; margin-top:4px;">
              <span style="font-size:15px; font-weight:700; color:#F1F5F9;">TOTAL</span>
              <span style="font-size:15px; font-weight:700; color:#F59E0B;">
                Rs. {{ receiptData?.totals?.total }}
              </span>
            </div>
          </div>

          <div style="background:#12151C; border:1px solid #252B38; border-radius:8px;
                      padding:12px; margin-bottom:12px;">
            <div style="font-size:11px; color:#64748B; text-transform:uppercase;
                        letter-spacing:0.05em; margin-bottom:8px; font-weight:600;">
              Payment Details
            </div>
            <div v-for="p in receiptData?.payments" :key="p.id" style="margin-bottom:6px;">
              <div style="display:flex; justify-content:space-between; margin-bottom:2px;">
                <span style="font-size:12px; color:#F1F5F9; font-weight:600;">{{ p.method_label }}</span>
                <span style="font-size:12px; font-weight:700; color:#10B981;">Rs. {{ p.amount }}</span>
              </div>
              <div v-if="p.method === 'cash' && parseFloat(p.tendered) > parseFloat(p.amount)"
                style="display:flex; justify-content:space-between;">
                <span style="font-size:11px; color:#64748B;">Tendered</span>
                <span style="font-size:11px; color:#64748B;">Rs. {{ p.tendered }}</span>
              </div>
              <div v-if="parseFloat(p.change_amount) > 0"
                style="display:flex; justify-content:space-between;">
                <span style="font-size:11px; color:#10B981;">Change</span>
                <span style="font-size:11px; font-weight:700; color:#10B981;">Rs. {{ p.change_amount }}</span>
              </div>
              <div v-if="p.reference"
                style="display:flex; justify-content:space-between;">
                <span style="font-size:11px; color:#64748B;">Ref</span>
                <span style="font-size:11px; color:#64748B;">{{ p.reference }}</span>
              </div>
              <div style="font-size:10px; color:#334155; margin-top:1px;">
                {{ p.paid_at }} · {{ p.cashier }}
              </div>
            </div>
          </div>

          <div style="text-align:center; padding:10px 0;">
            <div style="font-size:10px; color:#334155;">Receipt No.</div>
            <div style="font-size:13px; font-weight:700; color:#64748B; letter-spacing:0.1em;">
              {{ receiptData?.payments?.[0]?.receipt_number }}
            </div>
          </div>

          <div style="text-align:center; padding-top:10px; border-top:1px dashed #252B38;">
            <div style="font-size:13px; color:#64748B;">Thank you for your visit!</div>
          </div>

        </div>
      </div>

      <div style="padding:14px 20px; border-top:1px solid #252B38; flex-shrink:0;">
        <button @click="$emit('paid')"
          style="width:100%; padding:13px; border-radius:9px; font-size:14px; font-weight:700;
                 background:#F59E0B; color:#000; border:none; cursor:pointer;"
          @mouseenter="e => e.currentTarget.style.filter='brightness(1.1)'"
          @mouseleave="e => e.currentTarget.style.filter='brightness(1)'"
        >
          Done
        </button>
      </div>

    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useOrderStore }            from '@/stores/orders'
import axios                        from 'axios'

const props = defineProps({ order: Object })
const emit  = defineEmits(['paid', 'cancel', 'receiptClosed'])

const orderStore = useOrderStore()

// State
const selectedMethod = ref('cash')
const tendered       = ref('')
const cardReference  = ref('')
const processing     = ref(false)
const chargeUpdating = ref(false)
const chargeSaved    = ref(false)
const showReceipt    = ref(false)
const errorMsg       = ref('')
const receiptData    = ref(null)
const showItems      = ref(false)
const localTaxRate   = ref(10)

const methods = [
  { value: 'cash',   label: 'Cash',   icon: ' ' },
  { value: 'card',   label: 'Card',   icon: ' ' },
  { value: 'mobile', label: 'Mobile', icon: ' ' },
]

const chargePresets = [0, 5, 10, 15, 20]

// Computed
const activeItems = computed(() => {
  const items = props.order?.items ?? []
  return items.filter(i => !i.is_void && i.is_void !== 1 && i.is_void !== '1')
})

const subtotal = computed(() =>
  parseFloat(props.order?.subtotal ?? 0)
)

const discount = computed(() =>
  parseFloat(props.order?.discount_amount ?? 0)
)

const localTaxAmount = computed(() =>
  Math.round(subtotal.value * (localTaxRate.value / 100) * 100) / 100
)

const localTotal = computed(() =>
  subtotal.value - discount.value + localTaxAmount.value
)

// Functions
function updateCharge(charge) {
  const newTotal = localTotal.value + charge
  if (selectedMethod.value === 'cash') {
    tendered.value = newTotal.toString()
  }
}

async function processPayment() {
  // Validation
  if (selectedMethod.value === 'cash') {
    const t = parseFloat(tendered.value || localTotal.value)
    if (isNaN(t) || t < localTotal.value) {
      errorMsg.value = `Cash tendered must be at least $${localTotal.value.toFixed(2)}`
      return
    }
  }

  // Save orderId before store clears it
  const orderId = props.order.id

  processing.value = true
  try {
    await orderStore.processPayment(orderId, [{
      method:    selectedMethod.value,
      amount:    localTotal.value,
      tendered:  selectedMethod.value === 'cash'
        ? parseFloat(tendered.value || localTotal.value)
        : localTotal.value,
      reference: cardReference.value || null,
    }])

    // Fetch receipt data using saved orderId
    const { data } = await axios.get(`/orders/${orderId}/receipt`)
    receiptData.value = data
    
    // Show receipt instead of setting paid
    showReceipt.value = true
    
  } catch (e) {
    console.error('Payment error:', e)
    errorMsg.value = e.response?.data?.message ?? e.message ?? 'Payment failed. Please try again.'
  } finally {
    processing.value = false
  }
}

// Print
function printReceipt() {
  const printWindow = window.open('', '_blank', 'width=400,height=700')
  const r = receiptData.value
  printWindow.document.write(`
    <html>
      <head>
        <title>Receipt - ${r?.receipt?.restaurant_name ?? 'Restaurant'}</title>
        <style>
          body { font-family: monospace; padding: 20px; margin: 0; }
          .header { text-align: center; margin-bottom: 20px; }
          .info { border-top: 1px dashed #000; border-bottom: 1px dashed #000; padding: 10px 0; margin: 10px 0; }
          .item { margin-bottom: 8px; }
          .total { border-top: 1px dashed #000; padding-top: 10px; margin-top: 10px; }
          .footer { text-align: center; margin-top: 20px; font-size: 12px; }
        </style>
      </head>
      <body>
        <div class="header">
          <h2>${r?.receipt?.restaurant_name ?? 'Restaurant POS'}</h2>
          <p>${r?.receipt?.printed_at}</p>
        </div>
        
        <div class="info">
          <div><strong>Order:</strong> ${r?.order?.order_number}</div>
          ${r?.order?.customer_name ? `<div><strong>Customer:</strong> ${r.order.customer_name}</div>` : ''}
          <div><strong>Cashier:</strong> ${r?.receipt?.cashier}</div>
        </div>
        
        <div>
          ${r?.items?.map(item => `
            <div class="item">
              ${item.quantity}x ${item.name} - Rs. ${item.total_price}
            </div>
          `).join('')}
        </div>
        
        <div class="total">
          <div><strong>Subtotal:</strong> Rs. ${r?.totals?.subtotal}</div>
          <div><strong>Service (${r?.totals?.tax_rate}%):</strong> Rs. ${r?.totals?.tax_amount}</div>
          <div><strong>TOTAL:</strong> Rs. ${r?.totals?.total}</div>
        </div>
        
        <div class="footer">
          <p>Receipt No: ${r?.payments?.[0]?.receipt_number}</p>
          <p>Thank you for your visit!</p>
        </div>
      </body>
    </html>
  `)
  printWindow.document.close()
  printWindow.focus()
  setTimeout(() => { printWindow.print(); printWindow.close() }, 250)
}

onMounted(() => {
  localTaxRate.value = parseFloat(props.order?.tax_rate ?? 10)
  
  // Add ENTER key listener for new order after payment
  const handleEnterKey = (e) => {
    if (e.key === 'Enter' && showReceipt.value) {
      emit('paid')
    }
  }
  document.addEventListener('keydown', handleEnterKey)
  
  onUnmounted(() => {
    document.removeEventListener('keydown', handleEnterKey)
  })
})
</script>
