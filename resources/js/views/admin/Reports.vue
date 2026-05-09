<template>
  <div class="reports-root" style="background:var(--bg-primary); color:var(--text-primary);">

    <!-- ── Header ── -->
    <div class="reports-header">
      <h1 class="reports-title">📊 Reports</h1>

      <div class="header-controls">
        <!-- Quick presets -->
        <div class="presets">
          <button
            v-for="p in presets" :key="p.label"
            class="preset-btn"
            :class="{ active: activePreset === p.label }"
            @click="applyPreset(p)"
            style="background:var(--bg-tertiary); color:var(--text-primary); border:1px solid var(--border-color);"
          >{{ p.label }}</button>
        </div>

        <!-- Custom date range -->
        <div class="date-range">
          <input v-model="fromDate" type="date" class="date-input" @change="loadReport" 
                 style="background:var(--bg-tertiary); color:var(--text-primary); border:1px solid var(--border-color);" />
          <span class="date-arrow" style="color:var(--text-secondary);">-></span>
          <input v-model="toDate"   type="date" class="date-input" @change="loadReport"
                 style="background:var(--bg-tertiary); color:var(--text-primary); border:1px solid var(--border-color);" />
          <button class="load-btn" @click="loadReport"
                  style="background:var(--accent-color); color:var(--text-inverse); border:1px solid var(--accent-color);">Load</button>
        </div>
      </div>
    </div>

    <!-- ── Loading ── -->
    <div v-if="loading" class="loading-state">
      <div class="spinner" />
      <span>Loading report…</span>
    </div>

    <!-- ── Content ── -->
    <div v-else class="reports-body">

      <!-- SUMMARY CARDS -->
      <div class="summary-grid">
        <div v-for="card in summaryCards" :key="card.label" class="summary-card" style="background:var(--bg-tertiary); border:1px solid var(--border-color);">
          <div class="card-label" style="color:var(--text-secondary);">{{ card.label }}</div>
          <div class="card-value" :style="{ color: card.color }">{{ card.value }}</div>
          <div v-if="card.sub" class="card-sub" style="color:var(--text-muted);">{{ card.sub }}</div>
          <div v-if="card.change !== undefined && card.change !== null"
            class="card-change"
            :class="card.change >= 0 ? 'positive' : 'negative'"
            :style="{ color: card.change >= 0 ? 'var(--success-color)' : 'var(--error-color)' }"
          >
            {{ card.change >= 0 ? 'up' : 'down' }} {{ Math.abs(card.change) }}% vs yesterday
          </div>
        </div>
      </div>

      <!-- ROW 1: Revenue Trend + Payment Methods -->
      <div class="row row-2col-wide">

        <!-- Revenue Trend -->
        <div class="panel" style="background:var(--bg-tertiary); border:1px solid var(--border-color);">
          <div class="panel-title" style="color:var(--text-primary);">💰 Revenue Trend</div>

          <!-- Single day: hourly bars -->
          <div v-if="report?.daily?.length === 1">
            <div class="bar-chart">
              <div v-for="h in visibleHours" :key="h.hour" class="bar-col">
                <div
                  class="bar"
                  :style="{
                    height: maxHourlyRevenue > 0
                      ? Math.max(2, (h.revenue / maxHourlyRevenue) * 100) + 'px'
                      : '2px',
                    background: h.revenue > 0 ? 'var(--accent-color)' : 'var(--border-color)',
                  }"
                  :title="`${h.label}: Rs. ${h.revenue}`"
                />
              </div>
            </div>
            <div class="bar-x-labels">
              <span>00:00</span><span>06:00</span>
              <span>12:00</span><span>18:00</span><span>23:00</span>
            </div>
            <div class="bar-caption">Hourly revenue for {{ fromDate }}</div>
          </div>

          <!-- Multi-day bars -->
          <div v-else>
            <div class="bar-chart bar-chart--scroll">
              <div v-for="d in report?.daily" :key="d.date" class="bar-col bar-col--wide">
                <div class="bar-label-top" v-if="d.revenue > 0">
                  Rs. {{ formatNum(d.revenue) }}
                </div>
                <div
                  class="bar"
                  :style="{
                    height: maxDailyRevenue > 0
                      ? Math.max(2, (d.revenue / maxDailyRevenue) * 110) + 'px'
                      : '2px',
                    background: d.revenue > 0 ? 'var(--accent-color)' : 'var(--border-color)',
                  }"
                  :title="`${d.date}: Rs. ${d.revenue} (${d.orders} orders)`"
                />
                <div class="bar-date">{{ formatShortDate(d.date) }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Payment Methods -->
        <div class="panel panel--narrow">
          <div class="panel-title">💳 Payment Methods</div>
          <div v-if="report?.by_method?.length === 0" class="empty-state">
            No payments in this period
          </div>
          <div v-else class="method-list">
            <div v-for="m in report?.by_method" :key="m.method" class="method-item">
              <div class="method-row">
                <div class="method-label-group">
                  <span class="method-icon">{{ methodIcon(m.method) }}</span>
                  <span class="method-name">{{ m.method_label }}</span>
                </div>
                <div class="method-stats">
                  <div class="method-amount">Rs. {{ formatNum(m.total) }}</div>
                  <div class="method-count">{{ m.count }} txn</div>
                </div>
              </div>
              <div class="progress-track">
                <div
                  class="progress-fill"
                  :style="{
                    width: totalPayments > 0 ? (m.total / totalPayments * 100) + '%' : '0%',
                    background: methodColor(m.method),
                  }"
                />
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ROW 2: Hourly Heatmap + Order Types -->
      <div class="row row-2col-wide2">

        <!-- Hourly heatmap -->
        <div class="panel">
          <div class="panel-title">⏰ Busiest Hours</div>
          <div class="heatmap-grid">
            <div
              v-for="h in report?.hourly?.slice(0,12)" :key="h.hour"
              class="heat-cell"
              :style="{ background: heatColor(h.revenue), opacity: h.revenue > 0 ? 1 : 0.3 }"
              :title="`${h.label} — Rs. ${h.revenue} (${h.orders} orders)`"
            >
              <div class="heat-hour">{{ h.hour }}h</div>
              <div class="heat-orders" :style="{ color: h.revenue > 0 ? 'var(--accent-color)' : 'var(--text-muted)' }">
                {{ h.orders > 0 ? h.orders : '' }}
              </div>
            </div>
          </div>
          <div class="heatmap-grid" style="margin-top:4px;">
            <div
              v-for="h in report?.hourly?.slice(12)" :key="h.hour"
              class="heat-cell"
              :style="{ background: heatColor(h.revenue), opacity: h.revenue > 0 ? 1 : 0.3 }"
              :title="`${h.label} — Rs. ${h.revenue} (${h.orders} orders)`"
            >
              <div class="heat-hour">{{ h.hour }}h</div>
              <div class="heat-orders" :style="{ color: h.revenue > 0 ? 'var(--accent-color)' : 'var(--text-muted)' }">
                {{ h.orders > 0 ? h.orders : '' }}
              </div>
            </div>
          </div>
          <div class="heat-legend">
            <span class="legend-label">Low</span>
            <div class="legend-bar" />
            <span class="legend-label">High</span>
          </div>
        </div>

        <!-- Order Types -->
        <div class="panel panel--narrow2">
          <div class="panel-title">🍽️ Order Types</div>
          <div v-if="!report?.by_type?.length" class="empty-state">No data</div>
          <div v-else class="type-list">
            <div v-for="t in report?.by_type" :key="t.type" class="type-item">
              <div class="type-row">
                <div class="type-label-group">
                  <span class="type-icon">{{ typeIcon(t.type) }}</span>
                  <span class="type-name">{{ t.type?.replace('_', ' ') }}</span>
                </div>
                <div class="type-stats">
                  <div class="type-amount">Rs. {{ formatNum(t.total) }}</div>
                  <div class="type-count">{{ t.count }} orders</div>
                </div>
              </div>
              <div class="progress-track">
                <div
                  class="progress-fill progress-fill--blue"
                  :style="{
                    width: totalOrderRevenue > 0
                      ? (t.total / totalOrderRevenue * 100) + '%' : '0%'
                  }"
                />
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ROW 3: Top Items + Table Performance -->
      <div class="row row-2col-equal">

        <!-- Top Selling Items -->
        <div class="panel">
          <div class="panel-title">🏆 Top Selling Items</div>
          <div v-if="!report?.top_items?.length" class="empty-state">No sales data</div>
          <div v-else class="rank-list">
            <div
              v-for="(item, idx) in report?.top_items" :key="item.item_name"
              class="rank-item"
              :class="{ 'rank-item--last': idx === report.top_items.length - 1 }"
            >
              <div
                class="rank-badge"
                :style="{
                  background: idx === 0 ? 'rgba(245,158,11,0.2)'
                            : idx === 1 ? 'rgba(100,116,139,0.2)'
                            : idx === 2 ? 'rgba(180,83,9,0.2)' : 'var(--border-color)',
                  color: idx === 0 ? 'var(--accent-color)'
                       : idx === 1 ? 'var(--text-muted)'
                       : idx === 2 ? '#B45309' : 'var(--text-secondary)',
                }"
              >{{ idx + 1 }}</div>
              <div class="rank-info">
                <div class="rank-name">{{ item.item_name }}</div>
                <div class="progress-track">
                  <div
                    class="progress-fill"
                    :style="{
                      width: maxItemQty > 0 ? (item.total_qty / maxItemQty * 100) + '%' : '0%',
                      background: 'var(--accent-color)',
                    }"
                  />
                </div>
              </div>
              <div class="rank-stats">
                <div class="rank-revenue">Rs. {{ formatNum(item.total_revenue) }}</div>
                <div class="rank-qty">×{{ item.total_qty }} sold</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Table Performance -->
        <div class="panel">
          <div class="panel-title">🪑 Table Performance</div>
          <div v-if="!report?.table_perf?.length" class="empty-state">No table data</div>
          <div v-else class="rank-list">
            <div
              v-for="(t, idx) in report?.table_perf" :key="t.table_id"
              class="rank-item"
              :class="{ 'rank-item--last': idx === report.table_perf.length - 1 }"
            >
              <div
                class="rank-badge"
                :style="{
                  background: idx === 0 ? 'rgba(59,130,246,0.2)' : 'var(--border-color)',
                  color:      idx === 0 ? 'var(--info-color)' : 'var(--text-secondary)',
                }"
              >{{ idx + 1 }}</div>
              <div class="rank-info">
                <div class="rank-name-row">
                  <span class="rank-name">{{ t.table_name }}</span>
                  <span class="rank-section">{{ t.section }}</span>
                </div>
                <div class="progress-track">
                  <div
                    class="progress-fill progress-fill--blue"
                    :style="{
                      width: maxTableRevenue > 0
                        ? (t.revenue / maxTableRevenue * 100) + '%' : '0%'
                    }"
                  />
                </div>
              </div>
              <div class="rank-stats">
                <div class="rank-revenue rank-revenue--blue">Rs. {{ formatNum(t.revenue) }}</div>
                <div class="rank-qty">{{ t.order_count }} · avg Rs. {{ formatNum(t.avg_order) }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ROW 4: Recent Transactions -->
      <div class="panel panel--full" style="background:var(--bg-tertiary); border:1px solid var(--border-color);">
        <div class="txn-header">
          <div class="panel-title" style="color:var(--text-primary);">💰 Recent Transactions</div>
          <div class="txn-filters">
            <select v-model="transactionFilters.method" @change="loadTransactions" class="filter-select"
                    style="background:var(--bg-secondary); color:var(--text-primary); border:1px solid var(--border-color);">
              <option value="">All Methods</option>
              <option value="cash">Cash</option>
              <option value="card">Card</option>
              <option value="mobile">Mobile</option>
              <option value="voucher">Voucher</option>
              <option value="complimentary">Complimentary</option>
            </select>
            <input v-model="transactionFilters.date" type="date" @change="loadTransactions" class="filter-date"
                   style="background:var(--bg-secondary); color:var(--text-primary); border:1px solid var(--border-color);" />
            <button @click="loadTransactions" class="refresh-btn"
                    style="background:var(--accent-color); color:var(--text-inverse); border:1px solid var(--accent-color);">Refresh</button>
          </div>
        </div>

        <!-- Transaction summary mini-cards -->
        <div v-if="transactionsData?.summary" class="txn-summary">
          <div class="txn-sum-card">
            <div class="txn-sum-label">Total Count</div>
            <div class="txn-sum-value txn-sum-value--blue">
              {{ transactionsData.summary.total_count }}
            </div>
          </div>
          <div class="txn-sum-card">
            <div class="txn-sum-label">Total Amount</div>
            <div class="txn-sum-value txn-sum-value--amber">
              Rs. {{ formatNum(transactionsData.summary.total_amount) }}
            </div>
          </div>
          <div class="txn-sum-card">
            <div class="txn-sum-label">Average</div>
            <div class="txn-sum-value txn-sum-value--green">
              Rs. {{ formatNum(transactionsData.summary.avg_amount) }}
            </div>
          </div>
        </div>

        <!-- Table -->
        <div v-if="transactionsLoading" class="empty-state">Loading transactions…</div>
        <div v-else-if="!transactionsData?.transactions?.length" class="empty-state">
          No transactions found for selected filters
        </div>
        <div v-else class="txn-table-wrap">
          <table class="txn-table">
            <thead>
              <tr>
                <th>Time</th>
                <th>Receipt</th>
                <th>Order</th>
                <th>Table</th>
                <th>Method</th>
                <th class="align-right">Amount</th>
                <th class="align-right">Change</th>
                <th>Cashier</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="txn in transactionsData.transactions" :key="txn.id">
                <td>{{ txn.paid_at_time }}</td>
                <td class="td-muted">{{ txn.receipt_number }}</td>
                <td>
                  <div class="td-primary">#{{ txn.order.order_number }}</div>
                  <div class="td-sub">{{ txn.order.customer_name || 'Guest' }}</div>
                </td>
                <td>{{ txn.order.table?.name || 'N/A' }}</td>
                <td>
                  <div class="method-cell">
                    <span>{{ methodIcon(txn.method) }}</span>
                    <span>{{ txn.method_label }}</span>
                  </div>
                </td>
                <td class="align-right td-amber fw">Rs. {{ formatNum(txn.amount) }}</td>
                <td class="align-right td-green">
                  {{ txn.change_amount > 0 ? 'Rs. ' + formatNum(txn.change_amount) : '-' }}
                </td>
                <td class="td-muted">{{ txn.cashier || '-' }}</td>
                <td>
                  <span
                    class="status-badge"
                    :class="txn.order.payment_status === 'paid' ? 'status-paid' : 'status-pending'"
                  >{{ txn.order.payment_status }}</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Period info -->
      <div class="period-info">
        Report period: {{ report?.period?.from }} → {{ report?.period?.to }}
        · {{ report?.period?.days }} day{{ report?.period?.days > 1 ? 's' : '' }}
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onActivated } from 'vue'
import axios from 'axios'

// ── State ──────────────────────────────────────────────
const loading             = ref(false)
const report              = ref(null)
const fromDate            = ref(getAllTimeStartDate())
const toDate              = ref(today())
const activePreset        = ref('All Time')
const transactionsLoading = ref(false)
const transactionsData    = ref(null)
const transactionFilters  = ref({ method: '', date: today() })

// Helper function to get earliest date
function getAllTimeStartDate() {
  return '2020-01-01' // Start from a very early date to include all orders
}

// ── Presets ────────────────────────────────────────────
const presets = [
  { label: 'All Time',    from: () => getAllTimeStartDate(), to: () => today()        },
  { label: 'Today',       from: () => today(),        to: () => today()        },
  { label: 'Yesterday',   from: () => offsetDay(-1),  to: () => offsetDay(-1)  },
  { label: 'This Week',   from: () => startOfWeek(),  to: () => today()        },
  { label: 'This Month',  from: () => startOfMonth(), to: () => today()        },
  { label: 'This Year',   from: () => startOfYear(),  to: () => today()        },
]

// ── Computed ───────────────────────────────────────────
const summaryCards = computed(() => {
  if (!report.value) return []
  const s = report.value.summary
  return [
    {
      label: 'Monthly Revenue',
      value: 'Rs. ' + formatNum(s.total_revenue),
      color: 'var(--accent-color)',
      sub:   report.value.period.days > 1 ? `${s.total_orders} paid orders` : null,
    },
    {
      label: 'Orders',
      value: s.total_orders,
      color: 'var(--info-color)',
      sub:   `Avg Rs. ${formatNum(s.avg_order_value)} each`,
    },
    {
      label: 'Avg Order Value',
      value: 'Rs. ' + formatNum(s.avg_order_value),
      color: 'var(--success-color)',
      sub:   `${s.total_payments} payments`,
    },
    {
      label: 'Monthly Service Charge',
      value: 'Rs. ' + formatNum(
        report.value.daily?.reduce((a, d) => a + d.revenue, 0) > 0
          ? Math.round((report.value.summary.total_revenue -
              (report.value.top_items?.reduce((a, i) => a + parseFloat(i.total_revenue), 0) ?? 0)
            ) * 100) / 100
          : 0
      ),
      color: 'var(--warning-color)',
      sub:   `From ${report.value.daily?.length || 0} days`,
    },
    {
      label: 'Service Revenue',
      value: 'Rs. ' + formatNum(
        report.value.daily?.reduce((a, d) => a + d.revenue, 0) > 0
          ? Math.round((report.value.summary.total_revenue -
              (report.value.top_items?.reduce((a, i) => a + parseFloat(i.total_revenue), 0) ?? 0)
            ) * 100) / 100
          : 0
      ),
      color: 'var(--accent-color)',
      sub:   report.value.by_method?.length
        ? report.value.by_method.map(m => m.method_label).join(' · ')
        : 'No payments',
    },
  ]
})

const totalPayments      = computed(() => report.value?.by_method?.reduce((s, m) => s + m.total, 0) ?? 0)
const totalOrderRevenue  = computed(() => report.value?.by_type?.reduce((s, t) => s + parseFloat(t.total), 0) ?? 0)
const maxHourlyRevenue   = computed(() => Math.max(...(report.value?.hourly?.map(h => h.revenue) ?? [0])))
const maxDailyRevenue    = computed(() => Math.max(...(report.value?.daily?.map(d => d.revenue) ?? [0])))
const maxItemQty         = computed(() => Math.max(...(report.value?.top_items?.map(i => i.total_qty) ?? [0])))
const maxTableRevenue    = computed(() => Math.max(...(report.value?.table_perf?.map(t => t.revenue) ?? [0])))
const visibleHours       = computed(() => report.value?.hourly ?? [])

// ── Helpers ────────────────────────────────────────────
function today()      { return new Date().toISOString().split('T')[0] }
function offsetDay(n) { const d = new Date(); d.setDate(d.getDate() + n); return d.toISOString().split('T')[0] }
function startOfWeek()  { const d = new Date(); d.setDate(d.getDate() - d.getDay() + 1); return d.toISOString().split('T')[0] }
function startOfMonth() { const d = new Date(); d.setDate(1); return d.toISOString().split('T')[0] }

function applyPreset(preset) {
  activePreset.value = preset.label
  fromDate.value     = preset.from()
  toDate.value       = preset.to()
  loadReport()
}

function formatNum(n) {
  const num = parseFloat(n ?? 0)
  if (isNaN(num)) return '0.00'
  return num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}
function formatShortDate(dateStr) {
  const d = new Date(dateStr)
  return (d.getMonth() + 1) + '/' + d.getDate()
}
function methodIcon(method)  { return { cash:'💵', card:'💳', mobile:'📱', voucher:'🎫', complimentary:'🎁' }[method] ?? '💰' }
function methodColor(method) { return { cash:'var(--success-color)', card:'var(--info-color)', mobile:'var(--accent-color)', voucher:'var(--warning-color)', complimentary:'var(--error-color)' }[method] ?? 'var(--text-secondary)' }
function typeIcon(type)      { return { dine_in:'🍽️', takeaway:'🥡', delivery:'🚚', bar:'🍺', counter:'🏪' }[type] ?? '📦' }

function heatColor(revenue) {
  if (revenue === 0) return 'var(--bg-secondary)'
  const max   = maxHourlyRevenue.value
  if (max === 0) return 'var(--bg-secondary)'
  const ratio = revenue / max
  if (ratio < 0.25) return 'rgba(245,158,11,0.10)'
  if (ratio < 0.50) return 'rgba(245,158,11,0.25)'
  if (ratio < 0.75) return 'rgba(245,158,11,0.45)'
  return 'rgba(245,158,11,0.70)'
}

async function loadReport() {
  loading.value = true
  try {
    const { data } = await axios.get('/reports/summary', {
      params: { from: fromDate.value, to: toDate.value }
    })
    report.value = data
  } catch (e) {
    console.error('Failed to load report:', e)
  } finally {
    loading.value = false
  }
}

async function loadTransactions() {
  transactionsLoading.value = true
  try {
    const params = { limit: 50, ...transactionFilters.value }
    Object.keys(params).forEach(k => { if (params[k] === '') delete params[k] })
    const { data } = await axios.get('/reports/transactions', { params })
    transactionsData.value = data
  } catch (e) {
    console.error('Failed to load transactions:', e)
    transactionsData.value = null
  } finally {
    transactionsLoading.value = false
  }
}

onMounted(() => { loadReport(); loadTransactions() })
onActivated(() => { loadReport(); loadTransactions() })
</script>

<style scoped>
/* ── Variables - Using theme system ── */
:root {
  --bg:       var(--bg-primary);
  --bg-card:  var(--bg-card);
  --bg-deep:  var(--bg-secondary);
  --border:   var(--border-color);
  --border-hi:var(--border-hover);
  --text:     var(--text-primary);
  --muted:    var(--text-secondary);
  --muted2:   var(--text-muted);
  --accent:   var(--accent-color);
  --blue:     var(--info-color);
  --green:    var(--success-color);
}

/* ── Base ── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

.reports-root {
  display: flex;
  flex-direction: column;
  height: 100%;
  background: var(--bg);
  overflow: hidden;
  font-family: 'DM Sans', system-ui, sans-serif;
  font-size: 13px;
  color: var(--text);
}

/* ── Header ── */
.reports-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 20px;
  border-bottom: 1px solid var(--border);
  background: var(--bg-deep);
  flex-shrink: 0;
  flex-wrap: wrap;
  gap: 10px;
}

.reports-title {
  font-size: 20px;
  font-weight: 700;
  color: var(--text);
  white-space: nowrap;
}

.header-controls {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.presets {
  display: flex;
  gap: 4px;
  flex-wrap: wrap;
}

.preset-btn {
  padding: 5px 10px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 600;
  border: 1px solid var(--border-color);
  cursor: pointer;
  transition: all 0.15s;
  background: transparent;
  color: var(--text-secondary);
  white-space: nowrap;
}
.preset-btn.active {
  background: var(--accent-color);
  color: #000;
  border-color: var(--accent-color);
}

.date-range {
  display: flex;
  align-items: center;
  gap: 6px;
  flex-wrap: wrap;
}
.date-input {
  padding: 5px 8px;
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: 6px;
  color: var(--text-primary);
  font-size: 12px;
  outline: none;
}
.date-arrow { color: var(--text-secondary); font-size: 12px; }
.load-btn {
  padding: 5px 12px;
  background: var(--accent-color);
  color: #000;
  border: none;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 700;
  cursor: pointer;
}

/* ── Loading ── */
.loading-state {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  color: var(--text-secondary);
  font-size: 13px;
}
.spinner {
  width: 18px; height: 18px;
  border: 2px solid var(--border-color);
  border-top-color: var(--accent-color);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* ── Body ── */
.reports-body {
  flex: 1;
  overflow-y: auto;
  padding: 16px 20px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

/* ── Summary cards ── */
.summary-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 10px;
}
.summary-card {
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: 12px;
  padding: 16px;
}
.card-label {
  font-size: 11px;
  color: var(--text-secondary);
  text-transform: uppercase;
  letter-spacing: 0.06em;
  margin-bottom: 8px;
  font-weight: 600;
}
.card-value {
  font-size: 22px;
  font-weight: 700;
  line-height: 1.1;
  word-break: break-all;
}
.card-sub   { font-size: 11px; color: var(--text-secondary); margin-top: 4px; }
.card-change { font-size: 11px; margin-top: 4px; font-weight: 600; }
.card-change.positive { color: #10B981; }
.card-change.negative { color: #EF4444; }

/* ── Generic panel ── */
.panel {
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: 12px;
  padding: 16px;
  min-width: 0;
}
.panel--full { /* already full width in flow */ }
.panel-title {
  font-size: 13px;
  font-weight: 700;
  color: var(--text-primary);
  margin-bottom: 14px;
}

/* ── Row layouts ── */
.row {
  display: grid;
  gap: 12px;
}
.row-2col-wide  { grid-template-columns: 1fr 340px; }
.row-2col-wide2 { grid-template-columns: 1fr 280px; }
.row-2col-equal { grid-template-columns: 1fr 1fr; }

/* ── Bar chart ── */
.bar-chart {
  display: flex;
  align-items: flex-end;
  gap: 3px;
  height: 120px;
  padding-bottom: 4px;
}
.bar-chart--scroll { overflow-x: auto; }
.bar-col {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 3px;
  min-width: 0;
}
.bar-col--wide { min-width: 28px; }
.bar {
  width: 100%;
  border-radius: 3px 3px 0 0;
  transition: height 0.3s;
  min-height: 2px;
}
.bar-label-top { font-size: 9px; color: var(--accent-color); white-space: nowrap; }
.bar-date      { font-size: 9px; color: var(--text-secondary); white-space: nowrap; }
.bar-x-labels  {
  display: flex;
  justify-content: space-between;
  margin-top: 4px;
  font-size: 9px;
  color: var(--text-muted);
}
.bar-caption { font-size: 11px; color: var(--text-secondary); margin-top: 4px; text-align: center; }

/* ── Payment methods ── */
.method-list { display: flex; flex-direction: column; gap: 8px; }
.method-item {}
.method-row  {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 4px;
}
.method-label-group {
  display: flex;
  align-items: center;
  gap: 6px;
}
.method-icon   { font-size: 14px; }
.method-name   { font-size: 12px; font-weight: 600; color: var(--text-primary); }
.method-stats  { text-align: right; }
.method-amount { font-size: 13px; font-weight: 700; color: var(--accent-color); }
.method-count  { font-size: 10px; color: var(--text-secondary); }

/* ── Progress bar ── */
.progress-track {
  height: 4px;
  background: var(--border-color);
  border-radius: 2px;
  overflow: hidden;
}
.progress-fill {
  height: 100%;
  border-radius: 2px;
  transition: width 0.5s;
}
.progress-fill--blue { background: var(--info-color); }

/* ── Heatmap ── */
.heatmap-grid {
  display: grid;
  grid-template-columns: repeat(12, 1fr);
  gap: 4px;
}
.heat-cell {
  border-radius: 4px;
  padding: 6px 2px;
  text-align: center;
  cursor: default;
  transition: all 0.3s;
}
.heat-hour   { font-size: 9px; color: var(--text-secondary); margin-bottom: 2px; }
.heat-orders { font-size: 10px; font-weight: 700; }
.heat-legend {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 6px;
  margin-top: 8px;
}
.legend-label { font-size: 10px; color: var(--text-secondary); }
.legend-bar {
  flex: 1;
  height: 4px;
  border-radius: 2px;
  background: linear-gradient(to right, var(--bg-card), #92400E, var(--accent-color));
}

/* ── Order types ── */
.type-list { display: flex; flex-direction: column; gap: 10px; }
.type-row  {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 4px;
}
.type-label-group { display: flex; align-items: center; gap: 6px; }
.type-icon  { font-size: 14px; }
.type-name  { font-size: 12px; font-weight: 600; color: #F1F5F9; text-transform: capitalize; }
.type-stats { text-align: right; }
.type-amount { font-size: 13px; font-weight: 700; color: #3B82F6; }
.type-count  { font-size: 10px; color: #64748B; }

/* ── Rank lists ── */
.rank-list { display: flex; flex-direction: column; }
.rank-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 0;
  border-bottom: 1px solid #252B38;
}
.rank-item--last { border-bottom: none; }
.rank-badge {
  width: 24px; height: 24px;
  border-radius: 6px;
  display: flex; align-items: center; justify-content: center;
  font-size: 11px; font-weight: 700;
  flex-shrink: 0;
}
.rank-info  { flex: 1; min-width: 0; }
.rank-name  {
  font-size: 12px; font-weight: 500; color: var(--text-primary);
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
  margin-bottom: 3px;
}
.rank-name-row {
  display: flex; align-items: center; gap: 6px; margin-bottom: 3px;
}
.rank-section { font-size: 10px; color: var(--text-secondary); }
.rank-stats   { text-align: right; flex-shrink: 0; }
.rank-revenue { font-size: 12px; font-weight: 700; color: var(--accent-color); }
.rank-revenue--blue { color: var(--info-color); }
.rank-qty     { font-size: 10px; color: var(--text-secondary); }

/* ── Transactions ── */
.txn-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 14px;
  flex-wrap: wrap;
  gap: 10px;
}
.txn-filters {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}
.filter-select,
.filter-date {
  padding: 4px 8px;
  background: #252B38;
  border: 1px solid #334155;
  border-radius: 4px;
  color: #F1F5F9;
  font-size: 11px;
  outline: none;
}
.refresh-btn {
  padding: 4px 8px;
  background: #F59E0B;
  color: #000;
  border: none;
  border-radius: 4px;
  font-size: 11px;
  font-weight: 600;
  cursor: pointer;
}

.txn-summary {
  display: flex;
  gap: 12px;
  margin-bottom: 12px;
  flex-wrap: wrap;
}
.txn-sum-card {
  flex: 1;
  min-width: 100px;
  background: var(--bg-secondary);
  border-radius: 8px;
  padding: 8px 12px;
}
.txn-sum-label { font-size: 10px; color: var(--text-secondary); margin-bottom: 2px; }
.txn-sum-value { font-size: 16px; font-weight: 700; }
.txn-sum-value--blue  { color: var(--info-color); }
.txn-sum-value--amber { color: var(--accent-color); }
.txn-sum-value--green { color: var(--success-color); }

.txn-table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
.txn-table {
  width: 100%;
  border-collapse: collapse;
  min-width: 640px; /* ensures horizontal scroll on small screens */
}
.txn-table thead tr { background: var(--bg-secondary); }
.txn-table th {
  padding: 8px;
  text-align: left;
  font-size: 11px;
  color: var(--text-muted);
  font-weight: 600;
  white-space: nowrap;
}
.txn-table td {
  padding: 8px;
  font-size: 12px;
  color: var(--text-primary);
  border-bottom: 1px solid var(--border-color);
}
.align-right { text-align: right !important; }
.td-primary  { font-size: 12px; color: var(--text-primary); }
.td-sub      { font-size: 10px; color: var(--text-secondary); }
.td-muted    { color: var(--text-muted); }
.td-amber    { color: var(--accent-color); }
.td-green    { color: var(--success-color); }
.fw          { font-weight: 600; }
.method-cell { display: flex; align-items: center; gap: 4px; }

.status-badge {
  font-size: 11px;
  padding: 2px 6px;
  border-radius: 4px;
  font-weight: 600;
  white-space: nowrap;
}
.status-paid    { background: rgba(16,185,129,0.2); color: var(--success-color); }
.status-pending { background: rgba(245,158,11,0.2); color: var(--accent-color); }

/* ── Period info ── */
.period-info {
  text-align: center;
  padding: 8px 0;
  font-size: 11px;
  color: var(--text-muted);
}

.empty-state {
  color: var(--text-secondary);
  font-size: 12px;
  text-align: center;
  padding: 20px 0;
}

/* ═══════════════════════════════════════════════════════
   RESPONSIVE
   ═══════════════════════════════════════════════════════ */

/* ── Tablet (≤ 900px) ── */
@media (max-width: 900px) {
  .summary-grid        { grid-template-columns: repeat(2, 1fr); }
  .row-2col-wide       { grid-template-columns: 1fr; }
  .row-2col-wide2      { grid-template-columns: 1fr; }
  .row-2col-equal      { grid-template-columns: 1fr; }
  .panel--narrow,
  .panel--narrow2      { width: 100%; }
}

/* ── Phone (≤ 640px) ── */
@media (max-width: 640px) {
  .reports-header {
    padding: 10px 14px;
    flex-direction: column;
    align-items: flex-start;
  }

  .reports-title { font-size: 17px; }

  .header-controls {
    width: 100%;
    flex-direction: column;
    align-items: stretch;
    gap: 8px;
  }

  .presets {
    gap: 4px;
  }
  .preset-btn {
    font-size: 10px;
    padding: 4px 8px;
    flex: 1;
    text-align: center;
  }

  .date-range {
    justify-content: space-between;
    gap: 4px;
  }
  .date-input { flex: 1; font-size: 11px; padding: 4px 6px; }
  .load-btn   { padding: 5px 10px; font-size: 11px; }

  .reports-body { padding: 12px 12px; gap: 10px; }

  .summary-grid    { grid-template-columns: repeat(2, 1fr); gap: 8px; }
  .summary-card    { padding: 12px; border-radius: 10px; }
  .card-label      { font-size: 9px; margin-bottom: 5px; }
  .card-value      { font-size: 17px; }
  .card-sub        { font-size: 10px; }

  .panel           { padding: 12px; border-radius: 10px; }
  .panel-title     { font-size: 12px; margin-bottom: 10px; }

  /* Heatmap: 2 rows of 12 is fine but cells get tiny — shrink font */
  .heat-cell  { padding: 4px 1px; border-radius: 3px; }
  .heat-hour  { font-size: 8px; }
  .heat-orders{ font-size: 9px; }
  .heatmap-grid { gap: 2px; }

  .txn-header { flex-direction: column; align-items: flex-start; }
  .txn-filters { width: 100%; }
  .filter-select,
  .filter-date { flex: 1; }

  .txn-summary { gap: 8px; }
  .txn-sum-card { padding: 8px 10px; }
  .txn-sum-value{ font-size: 14px; }

  .rank-stats   { display: none; } /* hidden on small — revenue shown in bar */
}

/* ── Very small (≤ 400px) ── */
@media (max-width: 400px) {
  .summary-grid { grid-template-columns: 1fr 1fr; gap: 6px; }
  .card-value   { font-size: 15px; }

  .preset-btn   { font-size: 9px; padding: 3px 5px; }
  .date-input   { font-size: 10px; padding: 3px 4px; }

  .heatmap-grid { grid-template-columns: repeat(8, 1fr); } /* fewer cols */
}
</style>