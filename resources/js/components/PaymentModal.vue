<template>
  <div
    style="background:#1A1E28; border:1px solid #252B38; border-radius:16px;
           width:420px; max-width:94vw; max-height:92vh; overflow:hidden;
           display:flex; flex-direction:column;
           box-shadow:0 25px 50px -12px rgba(0,0,0,0.5);"
  >

    <!-- ══════════ PAYMENT FORM ══════════ -->
    <template v-if="!paid">

      <!-- Header -->
      <div style="padding:16px 20px; border-bottom:1px solid #252B38;
                  display:flex; align-items:center; justify-content:space-between; flex-shrink:0;">
        <div style="font-size:17px; font-weight:700; color:#F1F5F9;">💳 Payment</div>
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
          <div v-if="order?.table"
            style="background:#12151C; border:1px solid #252B38; border-radius:8px;
                   padding:8px 12px; flex:1; min-width:60px;">
            <div style="font-size:10px; color:#64748B; margin-bottom:2px;">Table</div>
            <div style="font-size:12px; font-weight:700; color:#F1F5F9;">{{ order.table.name }}</div>
          </div>
          <div v-if="order?.customer_name"
            style="background:#12151C; border:1px solid #252B38; border-radius:8px;
                   padding:8px 12px; flex:1; min-width:80px; overflow:hidden;">
            <div style="font-size:10px; color:#64748B; margin-bottom:2px;">Customer</div>
            <div style="font-size:12px; font-weight:700; color:#F1F5F9; white-space:nowrap;
                         overflow:hidden; text-overflow:ellipsis;">
              {{ order.customer_name }}
            </div>
          </div>
        </div>

        <!-- Items list (collapsible) -->
        <div style="background:#12151C; border:1px solid #252B38; border-radius:10px;
                    margin-bottom:14px; overflow:hidden;">
          <div
            @click="showItems = !showItems"
            style="padding:10px 14px; display:flex; align-items:center;
                   justify-content:space-between; cursor:pointer; user-select:none;"
            @mouseenter="e => e.currentTarget.style.background='rgba(255,255,255,0.03)'"
            @mouseleave="e => e.currentTarget.style.background='transparent'"
          >
            <div style="font-size:12px; font-weight:600; color:#F1F5F9;">
              Order Items ({{ activeItems.length }})
            </div>
            <div style="font-size:12px; color:#64748B; transition:transform 0.2s;"
              :style="{ transform: showItems ? 'rotate(180deg)' : 'rotate(0deg)' }">▾</div>
          </div>
          <div v-if="showItems" style="border-top:1px solid #252B38; padding:10px 14px;">
            <div v-for="item in activeItems" :key="item.id" style="margin-bottom:7px;">
              <div style="display:flex; justify-content:space-between; align-items:start;">
                <div style="flex:1; min-width:0;">
                  <div style="font-size:12px; color:#CBD5E1;">
                    ×{{ item.quantity }} {{ item.item_name }}
                  </div>
                  <div v-if="item.modifiers && item.modifiers.length > 0"
                    style="display:flex; flex-wrap:wrap; gap:2px; margin-top:2px;">
                    <span v-for="mod in item.modifiers" :key="mod.id"
                      style="font-size:9px; padding:1px 5px; border-radius:3px;
                             background:#252B38; color:#64748B;">{{ mod.name }}</span>
                  </div>
                  <div v-if="item.notes"
                    style="font-size:10px; color:#64748B; margin-top:1px; font-style:italic;">
                    📝 {{ item.notes }}
                  </div>
                </div>
                <div style="font-size:12px; color:#94A3B8; margin-left:8px; flex-shrink:0;">
                  Rs. {{ parseFloat(item.total_price).toFixed(2) }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- ── SERVICE CHARGE EDITOR ── -->
        <div style="background:#12151C; border:1px solid #252B38; border-radius:10px;
                    padding:14px; margin-bottom:14px;">

          <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;">
            <div style="font-size:12px; font-weight:700; color:#F1F5F9; display:flex; align-items:center; gap:6px;">
              Service Charge
              <span v-if="localTaxRate === 0"
                style="font-size:9px; padding:1px 6px; border-radius:4px;
                       background:rgba(239,68,68,0.1); color:#EF4444; font-weight:700;">
                WAIVED
              </span>
            </div>
            <div v-if="chargeUpdating"
              style="font-size:10px; color:#64748B; display:flex; align-items:center; gap:4px;">
              <div style="width:8px; height:8px; border-radius:50%; background:#F59E0B;
                           animation:pulse 1s infinite;"></div>
              Saving...
            </div>
            <div v-else-if="chargeSaved"
              style="font-size:10px; color:#10B981;">✓ Saved</div>
          </div>

          <!-- Preset buttons -->
          <div style="display:flex; gap:5px; margin-bottom:10px; flex-wrap:wrap;">
            <button
              v-for="preset in chargePresets" :key="preset"
              @click="applyPreset(preset)"
              style="padding:5px 11px; border-radius:6px; font-size:12px;
                     font-weight:600; cursor:pointer; border:1.5px solid; transition:all 0.15s;"
              :style="{
                background:  localTaxRate === preset ? 'rgba(245,158,11,0.12)' : '#1A1E28',
                borderColor: localTaxRate === preset ? '#F59E0B'               : '#252B38',
                color:       localTaxRate === preset ? '#F59E0B'               : '#64748B',
              }"
            >
              {{ preset === 0 ? 'No Charge' : preset + '%' }}
            </button>

            <!-- Custom % input -->
            <div style="display:flex; align-items:center; gap:4px; flex:1; min-width:90px;">
              <input
                v-model.number="localTaxRate"
                type="number"
                min="0"
                max="100"
                step="0.5"
                placeholder="Custom %"
                style="width:100%; padding:5px 8px; background:#1A1E28; border:1.5px solid #252B38;
                       border-radius:6px; color:#F1F5F9; font-size:12px; outline:none;
                       font-family:inherit;"
                @focus="e => e.target.style.borderColor='#F59E0B'"
                @blur="e => { e.target.style.borderColor='#252B38'; applyCharge() }"
                @keyup.enter="applyCharge"
              />
              <span style="font-size:12px; color:#64748B; flex-shrink:0;">%</span>
            </div>
          </div>

          <!-- Live totals preview -->
          <div style="border-top:1px solid #252B38; padding-top:10px;">
            <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
              <span style="font-size:12px; color:#64748B;">Subtotal</span>
              <span style="font-size:12px; color:#94A3B8;">
                Rs. {{ subtotal.toFixed(2) }}
              </span>
            </div>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
              <span style="font-size:12px; color:#64748B;">
                Service ({{ localTaxRate }}%)
              </span>
              <span style="font-size:12px;"
                :style="{ color: localTaxRate > 0 ? '#94A3B8' : '#EF4444' }">
                {{ localTaxRate > 0 ? 'Rs. ' + localTaxAmount.toFixed(2) : 'Waived' }}
              </span>
            </div>
            <div v-if="discount > 0"
              style="display:flex; justify-content:space-between; margin-bottom:4px;">
              <span style="font-size:12px; color:#10B981;">Discount</span>
              <span style="font-size:12px; color:#10B981;">-Rs. {{ discount.toFixed(2) }}</span>
            </div>
            <div style="display:flex; justify-content:space-between;
                        padding-top:8px; border-top:1px solid #252B38; margin-top:4px;">
              <span style="font-size:15px; font-weight:700; color:#F1F5F9;">Total</span>
              <span style="font-size:20px; font-weight:700; color:#F59E0B;">
                Rs. {{ localTotal.toFixed(2) }}
              </span>
            </div>
          </div>
        </div>

        <!-- Payment Method -->
        <div style="margin-bottom:14px;">
          <div style="font-size:11px; color:#64748B; text-transform:uppercase;
                      letter-spacing:0.06em; margin-bottom:8px; font-weight:600;">
            Payment Method
          </div>
          <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:8px;">
            <button
              v-for="m in methods" :key="m.value"
              @click="selectedMethod = m.value"
              style="border-radius:10px; padding:12px 6px; text-align:center;
                     cursor:pointer; transition:all 0.15s; border:2px solid;"
              :style="{
                borderColor: selectedMethod === m.value ? '#F59E0B' : '#252B38',
                background:  selectedMethod === m.value ? 'rgba(245,158,11,0.1)' : '#12151C',
              }"
            >
              <div style="font-size:22px; margin-bottom:4px;">{{ m.icon }}</div>
              <div style="font-size:11px; font-weight:600;"
                :style="{ color: selectedMethod === m.value ? '#F59E0B' : '#64748B' }">
                {{ m.label }}
              </div>
            </button>
          </div>
        </div>

        <!-- Cash tendered -->
        <div v-if="selectedMethod === 'cash'" style="margin-bottom:14px;">
          <div style="font-size:11px; color:#64748B; text-transform:uppercase;
                      letter-spacing:0.06em; margin-bottom:8px; font-weight:600;">
            Cash Tendered
          </div>
          <div style="display:flex; gap:5px; margin-bottom:8px; flex-wrap:wrap;">
            <button
              v-for="amt in quickAmounts" :key="amt"
              @click="tendered = amt.toString()"
              style="padding:5px 10px; border-radius:6px; border:1px solid;
                     background:#12151C; font-size:12px; cursor:pointer; font-weight:500;"
              :style="{
                borderColor: parseFloat(tendered) === amt ? '#F59E0B' : '#252B38',
                color:       parseFloat(tendered) === amt ? '#F59E0B' : '#94A3B8',
              }"
            >
              Rs. {{ amt }}
            </button>
          </div>
          <input
            v-model="tendered"
            type="number"
            step="0.01"
            :placeholder="localTotal.toFixed(2)"
            style="width:100%; background:#12151C; border:1px solid #252B38;
                   border-radius:8px; padding:10px 12px; color:#F1F5F9;
                   font-size:16px; font-weight:700; outline:none; font-family:monospace;"
            @focus="e => e.target.style.borderColor='#F59E0B'"
            @blur="e => e.target.style.borderColor='#252B38'"
          />
          <div v-if="change > 0"
            style="margin-top:10px; padding:10px 14px; background:rgba(16,185,129,0.08);
                   border:1px solid rgba(16,185,129,0.2); border-radius:8px;
                   display:flex; justify-content:space-between; align-items:center;">
            <span style="font-size:13px; color:#10B981; font-weight:600;">Change Due</span>
            <span style="font-size:18px; font-weight:700; color:#10B981; font-family:monospace;">
              Rs. {{ change.toFixed(2) }}
            </span>
          </div>
        </div>

        <!-- Card reference -->
        <div v-if="selectedMethod === 'card'" style="margin-bottom:14px;">
          <div style="font-size:11px; color:#64748B; text-transform:uppercase;
                      letter-spacing:0.06em; margin-bottom:6px; font-weight:600;">
            Card Reference (optional)
          </div>
          <input
            v-model="cardReference"
            placeholder="Last 4 digits or transaction ID"
            style="width:100%; background:#12151C; border:1px solid #252B38;
                   border-radius:8px; padding:10px 12px; color:#F1F5F9;
                   font-size:13px; outline:none; font-family:inherit;"
            @focus="e => e.target.style.borderColor='#F59E0B'"
            @blur="e => e.target.style.borderColor='#252B38'"
          />
        </div>

        <!-- Error -->
        <div v-if="errorMsg"
          style="padding:10px 14px; background:rgba(239,68,68,0.08);
                 border:1px solid rgba(239,68,68,0.2); border-radius:8px;
                 color:#EF4444; font-size:13px; margin-bottom:4px;">
          ⚠️ {{ errorMsg }}
        </div>

      </div>

      <!-- Footer -->
      <div style="padding:14px 20px; border-top:1px solid #252B38;
                  display:flex; gap:8px; flex-shrink:0;">
        <button @click="$emit('cancel')"
          style="flex:1; padding:12px; border-radius:8px; font-size:13px; font-weight:600;
                 background:transparent; color:#64748B; border:1px solid #252B38; cursor:pointer;"
          @mouseenter="e => e.currentTarget.style.background='#252B38'"
          @mouseleave="e => e.currentTarget.style.background='transparent'"
        >Cancel</button>
        <button @click="processPayment"
          :disabled="processing || chargeUpdating"
          style="flex:2; padding:12px; border-radius:8px; font-size:14px; font-weight:700;
                 background:#10B981; color:#fff; border:none; cursor:pointer; transition:all 0.15s;"
          :style="{ opacity: (processing || chargeUpdating) ? '0.6' : '1' }"
          @mouseenter="e => { if(!processing && !chargeUpdating) e.currentTarget.style.filter='brightness(1.1)' }"
          @mouseleave="e => e.currentTarget.style.filter='brightness(1)'"
        >
          {{ processing ? 'Processing...' : chargeUpdating ? 'Updating...' : `✓ Charge Rs. ${localTotal.toFixed(2)}` }}
        </button>
      </div>

    </template>

    <!-- ══════════ RECEIPT ══════════ -->
    <template v-else>

      <div style="padding:16px 20px; border-bottom:1px solid #252B38;
                  display:flex; align-items:center; justify-content:space-between; flex-shrink:0;">
        <div style="font-size:15px; font-weight:700; color:#10B981;">✅ Payment Complete</div>
        <button @click="printReceipt"
          style="padding:5px 12px; background:rgba(59,130,246,0.1); color:#3B82F6;
                 border:1px solid rgba(59,130,246,0.3); border-radius:6px;
                 font-size:11px; font-weight:600; cursor:pointer;">
          🖨️ Print
        </button>
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
            <div v-if="receiptData?.order?.table"
              style="display:flex; justify-content:space-between; margin-bottom:4px;">
              <span style="font-size:11px; color:#64748B;">Table</span>
              <span style="font-size:11px; color:#F1F5F9;">{{ receiptData.order.table }}</span>
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
          Done — New Order
        </button>
      </div>

    </template>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useOrderStore }            from '@/stores/orders'
import axios                        from 'axios'

const props = defineProps({ order: Object })
const emit  = defineEmits(['paid', 'cancel'])

const orderStore = useOrderStore()

// ── State ──────────────────────────────────────────────
const selectedMethod = ref('cash')
const tendered       = ref('')
const cardReference  = ref('')
const processing     = ref(false)
const chargeUpdating = ref(false)
const chargeSaved    = ref(false)
const paid           = ref(false)
const errorMsg       = ref('')
const receiptData    = ref(null)
const showItems      = ref(false)
const localTaxRate   = ref(10)

const methods = [
  { value: 'cash',   label: 'Cash',   icon: '💵' },
  { value: 'card',   label: 'Card',   icon: '💳' },
  { value: 'mobile', label: 'Mobile', icon: '📱' },
]

const chargePresets = [0, 5, 10, 15, 20]

// ── Computed ───────────────────────────────────────────
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
  Math.max(0, Math.round((subtotal.value + localTaxAmount.value - discount.value) * 100) / 100)
)

const quickAmounts = computed(() => {
  const total   = localTotal.value
  const amounts = []
  const roundings = [1, 5, 10, 20, 50, 100]
  for (const r of roundings) {
    const rounded = Math.ceil(total / r) * r
    if (rounded >= total && !amounts.includes(rounded) && amounts.length < 5) {
      amounts.push(rounded)
    }
  }
  return amounts
})

const change = computed(() => {
  const t = parseFloat(tendered.value)
  return !isNaN(t) && t > localTotal.value
    ? Math.round((t - localTotal.value) * 100) / 100
    : 0
})

// ── Service charge ──────────────────────────────────────
function applyPreset(rate) {
  localTaxRate.value = rate
  applyCharge()
}

let chargeTimer = null
async function applyCharge() {
  chargeSaved.value = false
  clearTimeout(chargeTimer)
  chargeTimer = setTimeout(async () => {
    chargeUpdating.value = true
    try {
      await axios.patch(`/orders/${props.order.id}/service-charge`, {
        tax_rate: localTaxRate.value,
      })
      // Refresh store so POSScreen totals update too
      await orderStore.fetchOrder(props.order.id)
      chargeSaved.value = true
      setTimeout(() => { chargeSaved.value = false }, 2000)
    } catch (e) {
      console.error('Failed to update service charge:', e)
    } finally {
      chargeUpdating.value = false
    }
  }, 600)
}

// ── Payment ─────────────────────────────────────────────
async function processPayment() {
  errorMsg.value = ''

  if (selectedMethod.value === 'cash') {
    const t = parseFloat(tendered.value || localTotal.value)
    if (isNaN(t) || t < localTotal.value) {
      errorMsg.value = `Cash tendered must be at least $${localTotal.value.toFixed(2)}`
      return
    }
  }

  // ✅ Save orderId before store clears the order
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
    paid.value = true
  } catch (e) {
    console.error('Payment error:', e)
    errorMsg.value = e.response?.data?.message ?? e.message ?? 'Payment failed. Please try again.'
  } finally {
    processing.value = false
  }
}

// ── Print ───────────────────────────────────────────────
function printReceipt() {
  const printWindow = window.open('', '_blank', 'width=400,height=700')
  const r = receiptData.value
  printWindow.document.write(`
    <html><head>
      <title>Receipt ${r?.order?.order_number ?? ''}</title>
      <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:monospace;font-size:12px;color:#000;background:#fff;padding:10px;width:80mm}
        .c{text-align:center}.b{font-weight:bold}.m{color:#666}.l{font-size:16px}
        .row{display:flex;justify-content:space-between;margin-bottom:3px}
        .div{border-top:1px dashed #999;margin:8px 0}
        .tot{font-size:15px;font-weight:bold}
      </style>
    </head><body>
      <div class="c b l">${r?.receipt?.restaurant_name ?? 'Restaurant POS'}</div>
      <div class="c m" style="font-size:10px;margin-bottom:8px">${r?.receipt?.printed_at}</div>
      <div class="div"></div>
      <div class="row"><span class="m">Order</span><span class="b">${r?.order?.order_number}</span></div>
      ${r?.order?.table ? `<div class="row"><span class="m">Table</span><span>${r.order.table}</span></div>` : ''}
      ${r?.order?.customer_name ? `<div class="row"><span class="m">Customer</span><span>${r.order.customer_name}</span></div>` : ''}
      <div class="row"><span class="m">Cashier</span><span>${r?.receipt?.cashier}</span></div>
      <div class="div"></div>
      ${(r?.items ?? []).map(i => `
        <div class="row">
          <span>${i.quantity}x ${i.name}${i.modifiers?.length ? ' (' + i.modifiers.map(m => m.name).join(', ') + ')' : ''}</span>
          <span>$${i.total_price}</span>
        </div>
        ${i.notes ? `<div class="m" style="font-size:10px;padding-left:8px">* ${i.notes}</div>` : ''}
      `).join('')}
      <div class="div"></div>
      <div class="row"><span class="m">Subtotal</span><span>$${r?.totals?.subtotal}</span></div>
      <div class="row">
        <span class="m">Service (${r?.totals?.tax_rate}%)</span>
        <span>${parseFloat(r?.totals?.tax_rate) === 0 ? 'Waived' : '$' + r?.totals?.tax_amount}</span>
      </div>
      ${parseFloat(r?.totals?.discount_amount) > 0 ? `<div class="row"><span>Discount</span><span>-$${r.totals.discount_amount}</span></div>` : ''}
      <div class="div"></div>
      <div class="row tot"><span>TOTAL</span><span>$${r?.totals?.total}</span></div>
      <div class="div"></div>
      ${(r?.payments ?? []).map(p => `
        <div class="row"><span class="b">${p.method_label}</span><span class="b">$${p.amount}</span></div>
        ${p.method === 'cash' && parseFloat(p.tendered) > parseFloat(p.amount) ? `
          <div class="row"><span class="m">Tendered</span><span>$${p.tendered}</span></div>
          <div class="row"><span class="m">Change</span><span class="b">$${p.change_amount}</span></div>
        ` : ''}
        ${p.reference ? `<div class="row"><span class="m">Ref</span><span>${p.reference}</span></div>` : ''}
      `).join('')}
      <div class="div"></div>
      <div class="c m" style="font-size:10px">${r?.payments?.[0]?.receipt_number}</div>
      <div class="c" style="margin-top:12px">Thank you for your visit!</div>
    </body></html>
  `)
  printWindow.document.close()
  printWindow.focus()
  setTimeout(() => { printWindow.print(); printWindow.close() }, 250)
}

onMounted(() => {
  localTaxRate.value = parseFloat(props.order?.tax_rate ?? 10)
})
</script>