<template>
  <div class="dashboard-container" :class="isDarkTheme ? 'dark-theme' : 'light-theme'">

    <!-- ═══ TOPBAR ═══════════════════════════════════════════════════ -->
    <div class="topbar">
      <div class="topbar-left">
        <div class="page-title">{{ greeting }}<span class="title-sep">—</span>{{ todayLabel }}</div>
        <div class="page-sub">Pambala, Madampe · Lanka Standard Time</div>
      </div>
      <div class="topbar-right">
        <div class="pill">
          <div class="pill-dot"></div>
          {{ stats.activeStaff }} staff on shift
        </div>
        <button class="btn-refresh" :disabled="loading" @click="refreshData">
          <svg width="13" height="13" viewBox="0 0 13 13" fill="none" :class="{ spinning: loading }">
            <path d="M11 6.5A4.5 4.5 0 1 1 9.18 2.8" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
            <path d="M9 1v2.5h2.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          {{ loading ? 'Refreshing…' : 'Refresh' }}
        </button>
      </div>
    </div>

    <!-- ═══ QUICK ACTIONS STRIP ══════════════════════════════════════ -->
    <div class="quick-strip">
      <div class="qs-label">Quick Actions</div>
      <div class="qs-actions">
        <router-link :to="{ name: 'tables' }" class="qs-btn qs-orange">
          <svg width="15" height="15" viewBox="0 0 16 16" fill="none"><rect x="2" y="2" width="5" height="5" rx="1.5" fill="currentColor"/><rect x="9" y="2" width="5" height="5" rx="1.5" fill="currentColor"/><rect x="2" y="9" width="5" height="5" rx="1.5" fill="currentColor"/><rect x="9" y="9" width="5" height="5" rx="1.5" fill="currentColor"/></svg>
          Tables
        </router-link>
        <router-link :to="{ name: 'direct-order' }" class="qs-btn qs-blue">
          <svg width="15" height="15" viewBox="0 0 16 16" fill="none"><path d="M3 5h10M3 8h7M3 11h5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
          Direct Order
        </router-link>
        <router-link :to="{ name: 'kitchen' }" class="qs-btn qs-amber">
          <svg width="15" height="15" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="5" stroke="currentColor" stroke-width="1.6"/><path d="M8 5v3l2 2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
          Kitchen
        </router-link>
        <router-link v-if="auth.isAdmin" :to="{ name: 'menu' }" class="qs-btn qs-green">
          <svg width="15" height="15" viewBox="0 0 16 16" fill="none"><path d="M4 8l3 3 5-5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
          Menu
        </router-link>
        <router-link v-if="auth.isAdmin" :to="{ name: 'reports' }" class="qs-btn qs-purple">
          <svg width="15" height="15" viewBox="0 0 16 16" fill="none"><rect x="2" y="10" width="2.5" height="4" rx="1" fill="currentColor"/><rect x="6.75" y="6" width="2.5" height="8" rx="1" fill="currentColor"/><rect x="11.5" y="2" width="2.5" height="12" rx="1" fill="currentColor"/></svg>
          Reports
        </router-link>
        <router-link v-if="auth.isAdmin" :to="{ name: 'staff' }" class="qs-btn qs-muted">
          <svg width="15" height="15" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="5" r="2.5" stroke="currentColor" stroke-width="1.6"/><path d="M3 13c0-2.761 2.239-4 5-4s5 1.239 5 4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
          Staff
        </router-link>
        <button @click="openWebsite" class="qs-btn qs-website">
          <svg width="15" height="15" viewBox="0 0 16 16" fill="none"><path d="M3 9h10M3 12h7M6 3v10M8 1l7 7-7 7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
          Website
        </button>
      </div>
    </div>

    <div class="content">

      <!-- ═══ PERIOD TABS + SUMMARY BANNER ════════════════════════════ -->
      <div class="period-bar">
        <div class="period-tabs">
          <button
            v-for="tab in ['daily', 'weekly', 'monthly']"
            :key="tab"
            @click="switchTab(tab)"
            :class="['ptab', { active: activeTab === tab }]"
          >
            {{ tab.charAt(0).toUpperCase() + tab.slice(1) }}
          </button>
        </div>
        <div class="period-summary" v-if="currentTabData && currentTabData.revenue !== undefined">
          <div class="ps-item">
            <span class="ps-val">Rs {{ formatCurrency(currentTabData.revenue || 0) }}</span>
            <span class="ps-key">revenue</span>
          </div>
          <div class="ps-sep"></div>
          <div class="ps-item">
            <span class="ps-val">{{ currentTabData.orders || 0 }}</span>
            <span class="ps-key">orders</span>
          </div>
          <div class="ps-sep"></div>
          <div class="ps-item">
            <span class="ps-val">Rs {{ formatCurrency(currentTabData.avg_order_value || 0) }}</span>
            <span class="ps-key">avg value</span>
          </div>
          <div class="ps-sep"></div>
          <div class="ps-item">
            <span class="ps-val ps-range">{{ currentTabData.date_range || '—' }}</span>
            <span class="ps-key">period</span>
          </div>
        </div>
      </div>

      <!-- ═══ STAT CARDS ════════════════════════════════════════════ -->
      <div class="stats-row">
        <div class="stat stat-1">
          <div class="stat-header">
            <div class="stat-label">Today's Revenue</div>
            <div class="stat-badge up">{{ stats.todayOrders }} orders</div>
          </div>
          <div class="stat-value">Rs {{ formatCurrency(stats.todaySales) }}</div>
          <div class="stat-meta">from {{ stats.todayOrders }} paid orders</div>
          <div class="stat-bar"><div class="stat-bar-fill" style="background: var(--accent); width: 100%"></div></div>
        </div>

        <div class="stat stat-2">
          <div class="stat-header">
            <div class="stat-label">This Week</div>
            <div class="stat-badge up">{{ stats.weekOrders }} orders</div>
          </div>
          <div class="stat-value">Rs {{ formatCurrency(stats.weekSales) }}</div>
          <div class="stat-meta">from {{ stats.weekOrders }} paid orders</div>
          <div class="stat-bar"><div class="stat-bar-fill" style="background: var(--blue); width: 85%"></div></div>
        </div>

        <div class="stat stat-3">
          <div class="stat-header">
            <div class="stat-label">This Month</div>
            <div class="stat-badge up">{{ stats.monthOrders }} orders</div>
          </div>
          <div class="stat-value">Rs {{ formatCurrency(stats.monthSales) }}</div>
          <div class="stat-meta">from {{ stats.monthOrders }} paid orders</div>
          <div class="stat-bar"><div class="stat-bar-fill" style="background: var(--amber); width: 90%"></div></div>
        </div>

        <div class="stat stat-4">
          <div class="stat-header">
            <div class="stat-label">Avg Order Value</div>
            <div class="stat-badge neutral">today</div>
          </div>
          <div class="stat-value">Rs {{ formatCurrency(avgOrderValue) }}</div>
          <div class="stat-meta">Based on today's orders</div>
          <div class="stat-bar"><div class="stat-bar-fill" style="background: var(--purple); width: 55%"></div></div>
        </div>
      </div>

      <!-- ═══ MAIN CHARTS ROW ═══════════════════════════════════════ -->
      <div class="two-col">
        <!-- Revenue chart -->
        <div class="card">
          <div class="card-header">
            <div class="card-title">Revenue — Today vs Yesterday</div>
            <router-link :to="{ name: 'reports' }" class="card-action">Full report →</router-link>
          </div>
          <div class="chart-wrap">
            <div class="chart-legend">
              <div class="legend-item"><div class="legend-dot" style="background:#D85A30"></div>Today</div>
              <div class="legend-item"><div class="legend-dot" style="background:#3b82f6; opacity:0.7"></div>Yesterday</div>
            </div>
            <div class="chart-container">
              <canvas ref="revChartRef" role="img" aria-label="Hourly revenue comparison today vs yesterday"></canvas>
            </div>
          </div>
        </div>

        <!-- Recent orders -->
        <div class="card">
          <div class="card-header">
            <div class="card-title">Recent Orders</div>
            <router-link :to="{ name: 'orders' }" class="card-action">View all →</router-link>
          </div>
          <div class="order-list">
            <div v-if="recentOrders.length === 0" class="no-activity">No recent orders</div>
            <div
              v-else
              v-for="order in recentOrders"
              :key="order.id"
              class="order-item"
            >
              <div class="order-num">#{{ String(order.id).padStart(4, '0') }}</div>
              <div class="order-info">
                <div class="order-name">{{ order.table_id ? `Table ${order.table_id}` : 'Direct' }}{{ order.customer_name ? ` — ${order.customer_name}` : '' }}</div>
                <div class="order-time">{{ formatTime(order.created_at) }}</div>
              </div>
              <div class="order-amount">Rs {{ formatCurrency(order.total_amount) }}</div>
              <div class="status-chip" :class="getStatusClass(order.status)">{{ order.status }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- ═══ BOTTOM ROW — Floor / Status breakdown / Category ═════ -->
      <div class="three-col">

        <!-- Floor plan -->
        <div class="card">
          <div class="card-header">
            <div class="card-title">Floor Plan</div>
            <router-link :to="{ name: 'tables' }" class="card-action">Manage →</router-link>
          </div>
          <div class="tables-grid">
            <div
              v-for="table in tables"
              :key="table.id"
              class="table-cell"
              :class="table.current_order_id ? 'occupied' : 'free'"
              @click="$router.push({ name: 'tables' })"
            >
              <div class="table-num">{{ String(table.id).padStart(2, '0') }}</div>
              <span>{{ table.current_order_id ? 'active' : 'free' }}</span>
            </div>
          </div>
        </div>

        <!-- ★ NEW — Order Status Breakdown donut -->
        <div class="card">
          <div class="card-header">
            <div class="card-title">Order Status</div>
            <span class="card-sub">Today's activity</span>
          </div>
          <div class="status-body">
            <div class="chart-container" style="height:150px; position:relative">
              <canvas ref="statusChartRef" role="img" aria-label="Order status breakdown donut chart"></canvas>
              <div class="donut-center">
                <div class="donut-val">{{ totalTodayOrders }}</div>
                <div class="donut-lbl">orders</div>
              </div>
            </div>
            <div class="status-legend">
              <div v-for="s in orderStatusBreakdown" :key="s.label" class="sl-row">
                <span class="sl-dot" :style="{ background: s.color }"></span>
                <span class="sl-label">{{ s.label }}</span>
                <span class="sl-bar-wrap"><span class="sl-bar-fill" :style="{ width: s.pct + '%', background: s.color }"></span></span>
                <span class="sl-count">{{ s.count }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Category breakdown -->
        <div class="card">
          <div class="card-header">
            <div class="card-title">Sales by Category</div>
            <span class="card-sub">Last 30 days</span>
          </div>
          <div style="padding: 14px 20px">
            <div class="chart-container" style="height: 150px">
              <canvas ref="donutChartRef" role="img" aria-label="Sales breakdown by menu category"></canvas>
            </div>
            <div style="display:flex;flex-direction:column;gap:0;margin-top:12px">
              <div v-for="cat in categoryBreakdown" :key="cat.label" class="cat-row">
                <span class="cat-label">
                  <span class="cat-dot" :style="{ background: cat.color }"></span>
                  {{ cat.label }}
                </span>
                <span class="cat-value">{{ cat.pct }}% · Rs {{ formatCurrency(cat.amount) }}</span>
              </div>
            </div>
          </div>
        </div>

      </div>

    </div><!-- /content -->
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick, watch } from 'vue'
import { useAuthStore } from '@/stores/auth'
import axios from 'axios'
import Chart from 'chart.js/auto'

const auth = useAuthStore()

// ─── Theme ───────────────────────────────────────────────────────────────────
const isDarkTheme = ref(localStorage.getItem('theme') !== 'light')

// ─── State ───────────────────────────────────────────────────────────────────
const loading    = ref(false)
const stats      = ref({
  totalSales: 0, todaySales: 0, weekSales: 0, monthSales: 0, yesterdaySales: 0,
  totalOrders: 0, todayOrders: 0, weekOrders: 0, monthOrders: 0, yesterdayOrders: 0,
  occupiedTables: 0, totalTables: 0, activeStaff: 0,
})
const recentOrders  = ref([])
const tables        = ref([])
const hourlyToday   = ref(new Array(24).fill(0))
const hourlyYest    = ref(new Array(24).fill(0))

// Tab state
const activeTab      = ref('daily')
const dashboardData  = ref({})
const currentTabData = ref({})

// Chart refs
const revChartRef    = ref(null)
const donutChartRef  = ref(null)
const statusChartRef = ref(null)
let revChartInst     = null
let donutChartInst   = null
let statusChartInst  = null

// ─── Category data ────────────────────────────────────────────────────────────
const categoryBreakdown = ref([
  { label: 'Rice Dishes', pct: 42, color: '#D85A30', amount: 0 },
  { label: 'Curries',     pct: 28, color: '#3b82f6', amount: 0 },
  { label: 'Drinks',      pct: 18, color: '#f59e0b', amount: 0 },
  { label: 'Desserts',    pct: 12, color: '#a78bfa', amount: 0 },
])

// ─── Order status breakdown (today) ──────────────────────────────────────────
const orderStatusBreakdown = ref([
  { label: 'Completed', color: '#22c55e', count: 0, pct: 0 },
  { label: 'Preparing', color: '#3b82f6', count: 0, pct: 0 },
  { label: 'Ready',     color: '#f59e0b', count: 0, pct: 0 },
  { label: 'Pending',   color: '#D85A30', count: 0, pct: 0 },
  { label: 'Cancelled', color: '#ef4444', count: 0, pct: 0 },
])

const totalTodayOrders = computed(() => orderStatusBreakdown.value.reduce((s, x) => s + x.count, 0))

// ─── Computed ────────────────────────────────────────────────────────────────
const greeting = computed(() => {
  const h = new Date().getHours()
  if (h < 12) return 'Good morning'
  if (h < 17) return 'Good afternoon'
  return 'Good evening'
})

const todayLabel = computed(() =>
  new Date().toLocaleDateString('en-US', { weekday: 'long', month: 'short', day: 'numeric' })
)

const avgOrderValue = computed(() => {
  if (!stats.value.todayOrders) return 0
  return stats.value.todaySales / stats.value.todayOrders
})

// ─── Chart theme tokens ───────────────────────────────────────────────────────
const chartColors = computed(() => {
  const dark = isDarkTheme.value
  return {
    grid:          dark ? 'rgba(255,255,255,0.04)' : 'rgba(0,0,0,0.06)',
    tick:          dark ? '#6b6762'                : '#8C7B6B',
    tooltipBg:     dark ? '#1e1e1e'                : '#FFFFFF',
    tooltipTitle:  dark ? '#f0ede8'                : '#1A1410',
    tooltipBody:   dark ? '#9a9590'                : '#5C4F42',
    tooltipBorder: dark ? '#2a2a2a'                : '#DDD5C8',
    donutBorder:   dark ? '#161616'                : '#FFFFFF',
  }
})

// ─── Helpers ──────────────────────────────────────────────────────────────────
function formatCurrency(amount) {
  return Math.round(amount || 0).toLocaleString()
}

function formatTime(timestamp) {
  const diff = Math.floor((Date.now() - new Date(timestamp)) / 60000)
  if (diff < 1)  return 'Just now'
  if (diff < 60) return `${diff} min ago`
  return new Date(timestamp).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })
}

function getStatusClass(status) {
  return { pending: 's-pending', preparing: 's-preparing', ready: 's-ready', completed: 's-completed', cancelled: 's-cancelled' }[status] || 's-pending'
}

// ─── Chart builders ───────────────────────────────────────────────────────────
function buildRevChart() {
  if (revChartInst) revChartInst.destroy()
  const c = chartColors.value
  const hours = Array.from({ length: 24 }, (_, i) => `${i}:00`)
  revChartInst = new Chart(revChartRef.value, {
    type: 'line',
    data: {
      labels: hours,
      datasets: [
        { label: 'Today', data: hourlyToday.value, borderColor: '#D85A30', backgroundColor: 'rgba(216,90,48,0.08)', borderWidth: 2, pointRadius: 0, tension: 0.4, fill: true },
        { label: 'Yesterday', data: hourlyYest.value, borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,0.05)', borderWidth: 1.5, pointRadius: 0, tension: 0.4, fill: true, borderDash: [4, 3] },
      ],
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: { mode: 'index', intersect: false, backgroundColor: c.tooltipBg, borderColor: c.tooltipBorder, borderWidth: 1, titleColor: c.tooltipTitle, bodyColor: c.tooltipBody, callbacks: { label: ctx => 'Rs ' + Math.round(ctx.raw).toLocaleString() } },
      },
      scales: {
        x: { grid: { color: c.grid }, ticks: { maxTicksLimit: 8, font: { size: 11 }, color: c.tick } },
        y: { grid: { color: c.grid }, ticks: { font: { size: 11 }, color: c.tick, callback: v => 'Rs ' + (v / 1000).toFixed(0) + 'k' } },
      },
    },
  })
}

function buildDonutChart() {
  if (donutChartInst) donutChartInst.destroy()
  const c = chartColors.value
  donutChartInst = new Chart(donutChartRef.value, {
    type: 'doughnut',
    data: {
      labels: categoryBreakdown.value.map(x => x.label),
      datasets: [{ data: categoryBreakdown.value.map(x => x.pct), backgroundColor: categoryBreakdown.value.map(x => x.color), borderColor: c.donutBorder, borderWidth: 3, hoverOffset: 4 }],
    },
    options: {
      responsive: true, maintainAspectRatio: false, cutout: '68%',
      plugins: { legend: { display: false }, tooltip: { backgroundColor: c.tooltipBg, borderColor: c.tooltipBorder, borderWidth: 1, titleColor: c.tooltipTitle, bodyColor: c.tooltipBody } },
    },
  })
}

function buildStatusChart() {
  if (statusChartInst) statusChartInst.destroy()
  const c = chartColors.value
  const data = orderStatusBreakdown.value
  statusChartInst = new Chart(statusChartRef.value, {
    type: 'doughnut',
    data: {
      labels: data.map(x => x.label),
      datasets: [{ data: data.map(x => x.count || 1), backgroundColor: data.map(x => x.color), borderColor: c.donutBorder, borderWidth: 3, hoverOffset: 4 }],
    },
    options: {
      responsive: true, maintainAspectRatio: false, cutout: '72%',
      plugins: { legend: { display: false }, tooltip: { backgroundColor: c.tooltipBg, borderColor: c.tooltipBorder, borderWidth: 1, titleColor: c.tooltipTitle, bodyColor: c.tooltipBody } },
    },
  })
}

// ─── Derive order status breakdown from recent orders ────────────────────────
function deriveStatusBreakdown(orders) {
  const counts = { completed: 0, preparing: 0, ready: 0, pending: 0, cancelled: 0 }
  orders.forEach(o => { if (counts[o.status] !== undefined) counts[o.status]++ })
  const total = Object.values(counts).reduce((a, b) => a + b, 0) || 1
  orderStatusBreakdown.value = [
    { label: 'Completed', color: '#22c55e', count: counts.completed, pct: Math.round(counts.completed / total * 100) },
    { label: 'Preparing', color: '#3b82f6', count: counts.preparing, pct: Math.round(counts.preparing / total * 100) },
    { label: 'Ready',     color: '#f59e0b', count: counts.ready,     pct: Math.round(counts.ready     / total * 100) },
    { label: 'Pending',   color: '#D85A30', count: counts.pending,   pct: Math.round(counts.pending   / total * 100) },
    { label: 'Cancelled', color: '#ef4444', count: counts.cancelled, pct: Math.round(counts.cancelled / total * 100) },
  ]
}

// ─── Data loading ─────────────────────────────────────────────────────────────
async function loadDashboardData() {
  loading.value = true
  try {
    const response = await axios.get('/dashboard')
    const data = response.data

    stats.value = {
      totalSales:      data.summary.total_revenue,
      todaySales:      data.daily.revenue,
      weekSales:       data.weekly.revenue,
      monthSales:      data.monthly.revenue,
      yesterdaySales:  0,
      totalOrders:     data.summary.total_orders,
      todayOrders:     data.daily.orders,
      weekOrders:      data.weekly.orders,
      monthOrders:     data.monthly.orders,
      yesterdayOrders: 0,
      occupiedTables:  0,
      totalTables:     10,
      activeStaff:     0,
    }

    dashboardData.value  = data
    currentTabData.value = data.daily
    recentOrders.value   = data.daily.recent_orders || []
    hourlyToday.value    = (data.daily.hourly || []).map(h => typeof h === 'object' ? h.revenue : h)

    // Derive status breakdown from today's recent orders
    deriveStatusBreakdown(recentOrders.value)

    categoryBreakdown.value = (data.monthly.top_items || []).map((item, index) => {
      const colors = ['#D85A30', '#3b82f6', '#f59e0b', '#a78bfa', '#10b981', '#ef4444']
      return {
        id: item.id, label: item.name, orders: item.total_qty, amount: item.total_revenue,
        pct:   data.summary.total_revenue > 0 ? Math.round((item.total_revenue / data.summary.total_revenue) * 100) : 0,
        color: colors[index % colors.length],
      }
    })

    await nextTick()
    buildRevChart()
    buildDonutChart()
    buildStatusChart()

  } catch (error) {
    console.error('Failed to load dashboard data:', error)
  } finally {
    loading.value = false
  }
}

// ─── Tab switching ────────────────────────────────────────────────────────────
function switchTab(tabName) {
  activeTab.value      = tabName
  currentTabData.value = dashboardData.value[tabName] || {}
  recentOrders.value   = currentTabData.value.recent_orders || []

  if (tabName === 'daily') {
    hourlyToday.value = (currentTabData.value.hourly || []).map(h => typeof h === 'object' ? h.revenue : h)
    deriveStatusBreakdown(recentOrders.value)
  }

  nextTick(() => { buildRevChart(); buildDonutChart(); buildStatusChart() })
}

// ─── Theme ────────────────────────────────────────────────────────────────────
function watchThemeChanges() {
  setInterval(() => {
    const shouldBeDark = localStorage.getItem('theme') !== 'light'
    if (isDarkTheme.value !== shouldBeDark) isDarkTheme.value = shouldBeDark
  }, 500)
}

watch(isDarkTheme, async () => {
  await nextTick()
  buildRevChart(); buildDonutChart(); buildStatusChart()
})

async function refreshData() { await loadDashboardData() }

function openWebsite() {
  window.open('/', '_blank')
}

onMounted(async () => {
  await loadDashboardData()
  watchThemeChanges()
})
</script>

<style scoped>
/* ═══ DESIGN TOKENS ══════════════════════════════════════════════════════════ */
.dashboard-container {
  --bg:       #0f0f0f;
  --bg2:      #161616;
  --bg3:      #1e1e1e;
  --bg4:      #252525;
  --border:   #2a2a2a;
  --border2:  #333;
  --text:     #f0ede8;
  --text2:    #9a9590;
  --text3:    #6b6762;
  --accent:   #D85A30;
  --accent2:  #e8733f;
  --accentbg: rgba(216, 90, 48, 0.1);
  --green:    #22c55e;
  --greenbg:  rgba(34, 197, 94, 0.1);
  --blue:     #3b82f6;
  --bluebg:   rgba(59, 130, 246, 0.1);
  --amber:    #f59e0b;
  --amberbg:  rgba(245, 158, 11, 0.1);
  --purple:   #a78bfa;
  --purplebg: rgba(167, 139, 250, 0.1);
  --r:  10px;
  --r2: 14px;

  font-family: 'DM Sans', sans-serif;
  max-width: 1200px;
  margin: 0 auto;
  min-height: 100vh;
  background: var(--bg);
  color: var(--text);
}

/* ═══ LIGHT THEME ════════════════════════════════════════════════════════════ */
.dashboard-container.light-theme {
  --bg:       #F5F0E8;
  --bg2:      #FFFFFF;
  --bg3:      #F0EBE1;
  --bg4:      #E8E0D4;
  --border:   #DDD5C8;
  --border2:  #C8BBAA;
  --text:     #1A1410;
  --text2:    #5C4F42;
  --text3:    #8C7B6B;
  --accent:   #C4441A;
  --accent2:  #D85A30;
  --accentbg: rgba(196, 68, 26, 0.10);
  --green:    #1A7A3C;
  --greenbg:  rgba(26, 122, 60, 0.10);
  --blue:     #1A5FC4;
  --bluebg:   rgba(26, 95, 196, 0.10);
  --amber:    #B8760A;
  --amberbg:  rgba(184, 118, 10, 0.10);
  --purple:   #6B3FCC;
  --purplebg: rgba(107, 63, 204, 0.10);
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

/* ═══ TOPBAR ═════════════════════════════════════════════════════════════════ */
.topbar {
  display: flex; align-items: center; justify-content: space-between;
  padding: 18px 28px;
  border-bottom: 1.5px solid var(--border);
  background: var(--bg2);
  position: sticky; top: 0; z-index: 20;
}
.light-theme .topbar { box-shadow: 0 1px 3px rgba(100,80,60,0.08); }

.page-title       { font-size: 16px; font-weight: 600; letter-spacing: -0.02em; color: var(--text); }
.title-sep        { color: var(--text3); margin: 0 6px; font-weight: 300; }
.page-sub         { font-size: 11.5px; color: var(--text3); margin-top: 2px; }
.topbar-right     { display: flex; align-items: center; gap: 10px; }

.pill {
  display: flex; align-items: center; gap: 6px;
  padding: 6px 13px;
  background: var(--greenbg);
  border: 1.5px solid rgba(34,197,94,0.2);
  border-radius: 999px;
  font-size: 12px; color: var(--green); font-weight: 500;
}
.light-theme .pill { background: #EEF8F2; border-color: #B8DFC8; }
.pill-dot { width: 7px; height: 7px; border-radius: 50%; background: var(--green); }

.btn-refresh {
  display: flex; align-items: center; gap: 6px;
  padding: 7px 15px;
  background: var(--accent); border: none; border-radius: 8px;
  font-size: 12.5px; font-weight: 600; color: #fff; cursor: pointer;
  font-family: inherit; transition: 0.15s;
}
.btn-refresh:hover:not(:disabled) { background: var(--accent2); }
.btn-refresh:disabled { opacity: 0.6; cursor: not-allowed; }

.spinning { animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* ═══ QUICK ACTIONS STRIP ════════════════════════════════════════════════════ */
.quick-strip {
  display: flex; align-items: center; gap: 16px;
  padding: 12px 28px;
  background: var(--bg2);
  border-bottom: 1.5px solid var(--border);
  position: sticky; top: 57px; z-index: 19;
  overflow-x: auto;
}
.light-theme .quick-strip { background: #FDFAF6; border-color: #EDE5D8; box-shadow: 0 1px 2px rgba(100,80,60,0.05); }

.qs-label {
  font-size: 10.5px; font-weight: 700; letter-spacing: 0.08em;
  text-transform: uppercase; color: var(--text3);
  white-space: nowrap; flex-shrink: 0;
}

.qs-actions { display: flex; gap: 8px; flex-wrap: nowrap; }

.qs-btn {
  display: inline-flex; align-items: center; gap: 7px;
  padding: 7px 14px;
  border-radius: 8px;
  font-size: 12.5px; font-weight: 600;
  text-decoration: none; white-space: nowrap;
  border: 1.5px solid transparent;
  transition: 0.15s; cursor: pointer;
  font-family: inherit;
}

.qs-orange { background: var(--accentbg); color: var(--accent); border-color: rgba(216,90,48,0.2); }
.qs-orange:hover { background: rgba(216,90,48,0.18); border-color: var(--accent); }

.qs-blue { background: var(--bluebg); color: var(--blue); border-color: rgba(59,130,246,0.2); }
.qs-blue:hover { background: rgba(59,130,246,0.18); border-color: var(--blue); }

.qs-amber { background: var(--amberbg); color: var(--amber); border-color: rgba(245,158,11,0.2); }
.qs-amber:hover { background: rgba(245,158,11,0.18); border-color: var(--amber); }

.qs-green { background: var(--greenbg); color: var(--green); border-color: rgba(34,197,94,0.2); }
.qs-green:hover { background: rgba(34,197,94,0.18); border-color: var(--green); }

.qs-purple { background: var(--purplebg); color: var(--purple); border-color: rgba(167,139,250,0.2); }
.qs-purple:hover { background: rgba(167,139,250,0.18); border-color: var(--purple); }

.qs-muted { background: var(--bg4); color: var(--text2); border-color: var(--border2); }
.qs-muted:hover { background: var(--bg3); color: var(--text); }

.qs-website { background: var(--bg-tertiary); color: var(--accent-color); border-color: var(--border-color); }
.qs-website:hover { background: var(--bg-secondary); color: var(--accent-hover); }

/* ═══ CONTENT ════════════════════════════════════════════════════════════════ */
.content {
  padding: 22px 28px;
  display: flex; flex-direction: column; gap: 18px;
}

/* ═══ PERIOD BAR ═════════════════════════════════════════════════════════════ */
.period-bar {
  display: flex; align-items: center; justify-content: space-between; gap: 20px;
  background: var(--bg2);
  border: 1.5px solid var(--border);
  border-radius: var(--r2);
  padding: 10px 16px;
  animation: fade-up 0.35s ease both;
}
.light-theme .period-bar { background: #FDFAF6; border-color: #EDE5D8; }

.period-tabs { display: flex; gap: 4px; }

.ptab {
  padding: 6px 18px;
  background: transparent; border: 1.5px solid transparent;
  border-radius: 7px;
  font-size: 12.5px; font-weight: 600; color: var(--text2);
  cursor: pointer; transition: 0.15s; font-family: inherit;
}
.ptab:hover { background: var(--bg4); color: var(--text); }
.ptab.active { background: var(--accent); color: #fff; border-color: var(--accent); }

.period-summary { display: flex; align-items: center; gap: 0; }
.ps-sep { width: 1px; height: 28px; background: var(--border); margin: 0 16px; }

.ps-item { display: flex; flex-direction: column; align-items: flex-end; gap: 1px; }
.ps-val  { font-size: 15px; font-weight: 700; color: var(--text); letter-spacing: -0.02em; white-space: nowrap; }
.ps-range { font-size: 12px; font-weight: 500; color: var(--text2); }
.ps-key  { font-size: 10px; font-weight: 600; color: var(--text3); text-transform: uppercase; letter-spacing: 0.06em; }

/* ═══ STATS ROW ══════════════════════════════════════════════════════════════ */
.stats-row {
  display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px;
}

.stat {
  background: var(--bg2); border: 1.5px solid var(--border); border-radius: var(--r2);
  padding: 18px 20px; display: flex; flex-direction: column; gap: 10px;
  transition: 0.2s; animation: fade-up 0.4s ease both;
}
.stat:hover { border-color: var(--border2); transform: translateY(-1px); }
.stat:nth-child(2) { animation-delay: 0.06s; }
.stat:nth-child(3) { animation-delay: 0.12s; }
.stat:nth-child(4) { animation-delay: 0.18s; }

.light-theme .stat-1 { background: linear-gradient(135deg,#FFF4EF 0%,#FFE8DF 100%); border-color: #FACDB8; }
.light-theme .stat-2 { background: linear-gradient(135deg,#EEF3FF 0%,#DDE7FF 100%); border-color: #BDCEF9; }
.light-theme .stat-3 { background: linear-gradient(135deg,#FFFAEE 0%,#FFF0C8 100%); border-color: #F5DFA0; }
.light-theme .stat-4 { background: linear-gradient(135deg,#F3EEFF 0%,#E8DBFF 100%); border-color: #D0B8FA; }

.stat-header { display: flex; align-items: flex-start; justify-content: space-between; }
.stat-label  { font-size: 11px; font-weight: 600; color: var(--text3); letter-spacing: 0.05em; text-transform: uppercase; }
.light-theme .stat-1 .stat-label { color: #A83010; }
.light-theme .stat-2 .stat-label { color: #1045A8; }
.light-theme .stat-3 .stat-label { color: #8A5A08; }
.light-theme .stat-4 .stat-label { color: #5028B0; }

.stat-badge { padding: 3px 9px; border-radius: 6px; font-size: 11px; font-weight: 600; font-family: 'DM Mono', monospace; }
.stat-badge.up      { background: var(--greenbg); color: var(--green); }
.stat-badge.down    { background: rgba(239,68,68,0.1); color: #ef4444; }
.stat-badge.neutral { background: var(--bluebg); color: var(--blue); }
.light-theme .stat-badge.up      { background: #D6F0E0; color: #1A7A3C; }
.light-theme .stat-badge.neutral { background: #DDE7FF; color: #1045A8; }

.stat-value { font-size: 26px; font-weight: 700; letter-spacing: -0.03em; line-height: 1; color: var(--text); }
.stat-meta  { font-size: 12px; color: var(--text3); }
.light-theme .stat-meta { color: #6B5B4E; }

.stat-bar      { height: 3px; background: var(--bg4); border-radius: 999px; overflow: hidden; }
.light-theme .stat-bar { background: rgba(0,0,0,0.08); }
.stat-bar-fill { height: 100%; border-radius: 999px; transition: width 1s ease; }

/* ═══ TWO-COL ════════════════════════════════════════════════════════════════ */
.two-col {
  display: grid; grid-template-columns: 1fr 360px; gap: 14px;
  animation: fade-up 0.4s 0.22s ease both;
}

/* ═══ CARD BASE ══════════════════════════════════════════════════════════════ */
.card { background: var(--bg2); border: 1.5px solid var(--border); border-radius: var(--r2); overflow: hidden; }

.card-header {
  display: flex; align-items: center; justify-content: space-between;
  padding: 13px 20px;
  border-bottom: 1.5px solid var(--border);
  background: var(--bg2);
}
.light-theme .card-header { background: #FDFAF6; border-color: #EDE5D8; }

.card-title  { font-size: 13.5px; font-weight: 600; letter-spacing: -0.01em; color: var(--text); }
.card-sub    { font-size: 11.5px; color: var(--text3); font-weight: 500; }
.card-action { font-size: 12px; color: var(--accent); cursor: pointer; font-weight: 500; text-decoration: none; }
.card-action:hover { color: var(--accent2); }

/* ═══ CHART ══════════════════════════════════════════════════════════════════ */
.chart-wrap      { padding: 18px 20px; }
.chart-legend    { display: flex; gap: 16px; margin-bottom: 14px; }
.legend-item     { display: flex; align-items: center; gap: 6px; font-size: 12px; color: var(--text2); }
.legend-dot      { width: 8px; height: 8px; border-radius: 2px; flex-shrink: 0; }
.chart-container { position: relative; width: 100%; height: 200px; }

/* ═══ ORDER LIST ═════════════════════════════════════════════════════════════ */
.order-list  { display: flex; flex-direction: column; }
.no-activity { text-align: center; color: var(--text3); padding: 40px 0; font-style: italic; font-size: 13px; }

.order-item {
  display: flex; align-items: center; gap: 12px;
  padding: 10px 20px;
  border-bottom: 1px solid var(--border); transition: 0.15s;
}
.order-item:last-child { border-bottom: none; }
.order-item:hover      { background: var(--bg3); }
.light-theme .order-item { border-color: #F0E8DC; }

.order-num    { font-family: 'DM Mono', monospace; font-size: 12px; color: var(--text3); min-width: 44px; }
.order-info   { flex: 1; min-width: 0; }
.order-name   { font-size: 13px; font-weight: 500; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.order-time   { font-size: 11.5px; color: var(--text3); margin-top: 2px; }
.order-amount { font-family: 'DM Mono', monospace; font-size: 13px; font-weight: 600; color: var(--text); white-space: nowrap; }

.status-chip {
  padding: 3px 8px; border-radius: 5px;
  font-size: 10.5px; font-weight: 600;
  letter-spacing: 0.04em; text-transform: uppercase; white-space: nowrap;
}
.s-pending   { background: rgba(245,158,11,0.1);  color: #f59e0b; }
.s-preparing { background: rgba(59,130,246,0.1);  color: #3b82f6; }
.s-ready     { background: rgba(34,197,94,0.1);   color: #22c55e; }
.s-completed { background: var(--bg4);            color: var(--text3); }
.s-cancelled { background: rgba(239,68,68,0.1);   color: #ef4444; }
.light-theme .s-pending   { background: #FFF5DC; color: #B8760A; }
.light-theme .s-preparing { background: #EEF3FF; color: #1A5FC4; }
.light-theme .s-ready     { background: #E8F5EE; color: #1A7A3C; }
.light-theme .s-completed { background: #EDE5D8; color: #8C7B6B; }
.light-theme .s-cancelled { background: #FFEDED; color: #B81A1A; }

/* ═══ THREE-COL ══════════════════════════════════════════════════════════════ */
.three-col {
  display: grid; grid-template-columns: 1fr 1fr 340px; gap: 14px;
  animation: fade-up 0.4s 0.3s ease both;
}

/* ═══ FLOOR PLAN ═════════════════════════════════════════════════════════════ */
.tables-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 8px; padding: 16px 20px; }

.table-cell {
  aspect-ratio: 1; border-radius: 8px;
  display: flex; flex-direction: column; align-items: center; justify-content: center;
  gap: 2px; font-size: 10px; font-weight: 500;
  cursor: pointer; transition: 0.15s; border: 1px solid transparent; color: var(--text3);
}
.table-cell.occupied { background: var(--accentbg); border-color: var(--accent); color: var(--accent); }
.table-cell.free     { background: var(--bg4); }
.table-cell.occupied:hover { background: rgba(216,90,48,0.18); }
.table-cell.free:hover     { background: var(--bg3); color: var(--text2); }
.light-theme .table-cell.occupied { background: #FFF0EB; border-color: #FACDB8; color: #C4441A; }
.light-theme .table-cell.free     { background: #F0EBE1; border-color: #E0D5C8; color: #8C7B6B; }
.table-num { font-size: 14px; font-weight: 700; }

/* ═══ ORDER STATUS CARD ══════════════════════════════════════════════════════ */
.status-body { padding: 16px 20px; display: flex; flex-direction: column; gap: 14px; position: relative; }

.donut-center {
  position: absolute;
  top: 50%; left: 50%; transform: translate(-50%, -50%);
  text-align: center; pointer-events: none;
}
.donut-val { font-size: 22px; font-weight: 700; color: var(--text); letter-spacing: -0.03em; line-height: 1; }
.donut-lbl { font-size: 10px; color: var(--text3); font-weight: 600; letter-spacing: 0.05em; text-transform: uppercase; }

.status-legend { display: flex; flex-direction: column; gap: 8px; }

.sl-row { display: flex; align-items: center; gap: 8px; }
.sl-dot  { width: 8px; height: 8px; border-radius: 2px; flex-shrink: 0; }
.sl-label { font-size: 12px; color: var(--text2); font-weight: 500; min-width: 68px; }
.light-theme .sl-label { color: #3C3028; }

.sl-bar-wrap { flex: 1; height: 5px; background: var(--bg4); border-radius: 999px; overflow: hidden; }
.light-theme .sl-bar-wrap { background: rgba(0,0,0,0.08); }
.sl-bar-fill { height: 100%; border-radius: 999px; transition: width 0.8s ease; }

.sl-count { font-family: 'DM Mono', monospace; font-size: 11.5px; font-weight: 700; color: var(--text); min-width: 20px; text-align: right; }

/* ═══ CATEGORY ═══════════════════════════════════════════════════════════════ */
.cat-row {
  display: flex; align-items: center; justify-content: space-between;
  font-size: 12px; padding: 7px 0; border-bottom: 1px solid var(--border);
}
.cat-row:last-child { border-bottom: none; }
.light-theme .cat-row { border-color: #F0E8DC; }
.cat-label { display: flex; align-items: center; gap: 6px; color: var(--text2); font-weight: 500; }
.light-theme .cat-label { color: #3C3028; }
.cat-dot   { width: 8px; height: 8px; border-radius: 2px; display: inline-block; flex-shrink: 0; }
.cat-value { color: var(--text); font-family: 'DM Mono', monospace; font-size: 11px; font-weight: 600; }

/* ═══ ANIMATIONS ═════════════════════════════════════════════════════════════ */
@keyframes fade-up {
  from { opacity: 0; transform: translateY(12px); }
  to   { opacity: 1; transform: translateY(0); }
}

/* ═══ RESPONSIVE ═════════════════════════════════════════════════════════════ */
@media (max-width: 1200px) {
  .three-col { grid-template-columns: 1fr 1fr; }
  .three-col > .card:last-child { grid-column: 1 / -1; }
}
@media (max-width: 1024px) {
  .stats-row { grid-template-columns: repeat(2, 1fr); }
  .two-col   { grid-template-columns: 1fr; }
  .period-bar { flex-direction: column; align-items: flex-start; gap: 12px; }
  .period-summary { flex-wrap: wrap; gap: 0; }
}
@media (max-width: 768px) {
  .content    { padding: 16px; }
  .topbar     { padding: 14px 16px; }
  .quick-strip { padding: 10px 16px; top: 53px; }
  .stats-row  { grid-template-columns: 1fr 1fr; gap: 10px; }
  .three-col  { grid-template-columns: 1fr; }
  .ps-item { align-items: flex-start; }
}
</style>