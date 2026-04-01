<template>
  <div style="display:flex; flex-direction:column; height:100%; background:#0A0C10; overflow:hidden;">

    <!-- Header -->
    <div style="padding:12px 20px; border-bottom:1px solid #252B38; background:#12151C;
                display:flex; align-items:center; justify-content:space-between; flex-shrink:0;">
      <h1 style="font-size:20px; font-weight:700; color:#F1F5F9; margin:0;">📊 Reports</h1>

      <!-- Date range picker -->
      <div style="display:flex; align-items:center; gap:8px;">
        <!-- Quick presets -->
        <div style="display:flex; gap:4px;">
          <button
            v-for="p in presets" :key="p.label"
            @click="applyPreset(p)"
            style="padding:5px 10px; border-radius:6px; font-size:11px; font-weight:600;
                   border:1px solid #252B38; cursor:pointer; transition:all 0.15s;"
            :style="{
              background: activePreset === p.label ? '#F59E0B'    : 'transparent',
              color:      activePreset === p.label ? '#000'       : '#64748B',
              borderColor:activePreset === p.label ? '#F59E0B'    : '#252B38',
            }"
          >{{ p.label }}</button>
        </div>

        <!-- Custom date range -->
        <div style="display:flex; align-items:center; gap:6px;">
          <input
            v-model="fromDate" type="date"
            style="padding:5px 8px; background:#1A1E28; border:1px solid #252B38;
                   border-radius:6px; color:#F1F5F9; font-size:12px; outline:none;"
            @change="loadReport"
          />
          <span style="color:#64748B; font-size:12px;">→</span>
          <input
            v-model="toDate" type="date"
            style="padding:5px 8px; background:#1A1E28; border:1px solid #252B38;
                   border-radius:6px; color:#F1F5F9; font-size:12px; outline:none;"
            @change="loadReport"
          />
        </div>

        <button @click="loadReport"
          style="padding:5px 12px; background:#F59E0B; color:#000; border:none;
                 border-radius:6px; font-size:12px; font-weight:700; cursor:pointer;">
          Load
        </button>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading"
      style="flex:1; display:flex; align-items:center; justify-content:center; color:#64748B; gap:10px;">
      <div style="font-size:13px;">Loading report...</div>
    </div>

    <!-- Content -->
    <div v-else style="flex:1; overflow-y:auto; padding:16px 20px;">

      <!-- ── SUMMARY CARDS ── -->
      <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:10px; margin-bottom:20px;">

        <div v-for="card in summaryCards" :key="card.label"
          style="background:#1A1E28; border:1px solid #252B38; border-radius:12px; padding:16px;">
          <div style="font-size:11px; color:#64748B; text-transform:uppercase;
                      letter-spacing:0.06em; margin-bottom:8px; font-weight:600;">
            {{ card.label }}
          </div>
          <div style="font-size:24px; font-weight:700;" :style="{ color: card.color }">
            {{ card.value }}
          </div>
          <div v-if="card.sub" style="font-size:11px; color:#64748B; margin-top:4px;">
            {{ card.sub }}
          </div>
          <div v-if="card.change !== undefined && card.change !== null"
            style="font-size:11px; margin-top:4px; font-weight:600;"
            :style="{ color: card.change >= 0 ? '#10B981' : '#EF4444' }">
            {{ card.change >= 0 ? '↑' : '↓' }} {{ Math.abs(card.change) }}% vs yesterday
          </div>
        </div>
      </div>

      <!-- ── ROW 1: Daily trend + Payment methods ── -->
      <div style="display:grid; grid-template-columns:1fr 340px; gap:12px; margin-bottom:12px;">

        <!-- Daily revenue chart -->
        <div style="background:#1A1E28; border:1px solid #252B38; border-radius:12px; padding:16px;">
          <div style="font-size:13px; font-weight:700; color:#F1F5F9; margin-bottom:14px;">
            💰 Revenue Trend
          </div>

          <div v-if="report?.daily?.length === 1">
            <!-- Single day — show hourly -->
            <div style="display:flex; align-items:end; gap:3px; height:120px; padding-bottom:4px;">
              <div
                v-for="h in visibleHours" :key="h.hour"
                style="flex:1; display:flex; flex-direction:column; align-items:center; gap:3px; min-width:0;"
              >
                <div
                  style="width:100%; border-radius:3px 3px 0 0; transition:height 0.3s; min-height:2px;"
                  :style="{
                    height: maxHourlyRevenue > 0
                      ? Math.max(2, (h.revenue / maxHourlyRevenue) * 100) + 'px'
                      : '2px',
                    background: h.revenue > 0 ? '#F59E0B' : '#252B38',
                  }"
                  :title="`${h.label}: Rs. ${h.revenue}`"
                ></div>
              </div>
            </div>
            <div style="display:flex; justify-content:space-between; margin-top:4px;">
              <span style="font-size:9px; color:#334155;">00:00</span>
              <span style="font-size:9px; color:#334155;">06:00</span>
              <span style="font-size:9px; color:#334155;">12:00</span>
              <span style="font-size:9px; color:#334155;">18:00</span>
              <span style="font-size:9px; color:#334155;">23:00</span>
            </div>
            <div style="font-size:11px; color:#64748B; margin-top:4px; text-align:center;">
              Hourly revenue for {{ fromDate }}
            </div>
          </div>

          <div v-else>
            <!-- Multi day bars -->
            <div style="display:flex; align-items:end; gap:4px; height:120px; padding-bottom:4px; overflow-x:auto;">
              <div
                v-for="d in report?.daily" :key="d.date"
                style="flex:1; display:flex; flex-direction:column; align-items:center; gap:3px; min-width:28px;"
              >
                <div style="font-size:9px; color:#F59E0B; white-space:nowrap;">
                  {{ d.revenue > 0 ? 'Rs. ' + formatNum(d.revenue) : '' }}
                </div>
                <div
                  style="width:100%; border-radius:3px 3px 0 0; transition:height 0.3s; min-height:2px;"
                  :style="{
                    height: maxDailyRevenue > 0
                      ? Math.max(2, (d.revenue / maxDailyRevenue) * 110) + 'px'
                      : '2px',
                    background: d.revenue > 0 ? '#F59E0B' : '#252B38',
                  }"
                  :title="`${d.date}: Rs. ${d.revenue} (${d.orders} orders)`"
                ></div>
                <div style="font-size:9px; color:#64748B; white-space:nowrap;">
                  {{ formatShortDate(d.date) }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Payment methods -->
        <div style="background:#1A1E28; border:1px solid #252B38; border-radius:12px; padding:16px;">
          <div style="font-size:13px; font-weight:700; color:#F1F5F9; margin-bottom:14px;">
            💳 Payment Methods
          </div>
          <div v-if="report?.by_method?.length === 0"
            style="color:#64748B; font-size:12px; text-align:center; padding:20px 0;">
            No payments in this period
          </div>
          <div v-else style="display:flex; flex-direction:column; gap:8px;">
            <div v-for="m in report?.by_method" :key="m.method">
              <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                <div style="display:flex; align-items:center; gap:6px;">
                  <span style="font-size:14px;">{{ methodIcon(m.method) }}</span>
                  <span style="font-size:12px; font-weight:600; color:#F1F5F9;">{{ m.method_label }}</span>
                </div>
                <div style="text-align:right;">
                  <div style="font-size:13px; font-weight:700; color:#F59E0B;">
                    Rs. {{ formatNum(m.total) }}
                  </div>
                  <div style="font-size:10px; color:#64748B;">{{ m.count }} txn</div>
                </div>
              </div>
              <!-- Progress bar -->
              <div style="height:4px; background:#252B38; border-radius:2px; overflow:hidden;">
                <div
                  style="height:100%; border-radius:2px; transition:width 0.5s;"
                  :style="{
                    width: totalPayments > 0 ? (m.total / totalPayments * 100) + '%' : '0%',
                    background: methodColor(m.method),
                  }"
                ></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ── ROW 2: Hourly heatmap + Order types ── -->
      <div style="display:grid; grid-template-columns:1fr 280px; gap:12px; margin-bottom:12px;">

        <!-- Hourly heatmap -->
        <div style="background:#1A1E28; border:1px solid #252B38; border-radius:12px; padding:16px;">
          <div style="font-size:13px; font-weight:700; color:#F1F5F9; margin-bottom:14px;">
            ⏰ Busiest Hours
          </div>
          <div style="display:grid; grid-template-columns:repeat(12,1fr); gap:4px; margin-bottom:6px;">
            <div
              v-for="h in report?.hourly?.slice(0,12)" :key="h.hour"
              style="border-radius:4px; padding:6px 2px; text-align:center; transition:all 0.3s; cursor:default;"
              :style="{
                background: heatColor(h.revenue),
                opacity: h.revenue > 0 ? 1 : 0.3,
              }"
              :title="`${h.label} — Rs. ${h.revenue} (${h.orders} orders)`"
            >
              <div style="font-size:9px; color:#64748B; margin-bottom:2px;">{{ h.hour }}h</div>
              <div style="font-size:10px; font-weight:700;"
                :style="{ color: h.revenue > 0 ? '#F59E0B' : '#334155' }">
                {{ h.orders > 0 ? h.orders : '' }}
              </div>
            </div>
          </div>
          <div style="display:grid; grid-template-columns:repeat(12,1fr); gap:4px;">
            <div
              v-for="h in report?.hourly?.slice(12)" :key="h.hour"
              style="border-radius:4px; padding:6px 2px; text-align:center; transition:all 0.3s; cursor:default;"
              :style="{
                background: heatColor(h.revenue),
                opacity: h.revenue > 0 ? 1 : 0.3,
              }"
              :title="`${h.label} — Rs. ${h.revenue} (${h.orders} orders)`"
            >
              <div style="font-size:9px; color:#64748B; margin-bottom:2px;">{{ h.hour }}h</div>
              <div style="font-size:10px; font-weight:700;"
                :style="{ color: h.revenue > 0 ? '#F59E0B' : '#334155' }">
                {{ h.orders > 0 ? h.orders : '' }}
              </div>
            </div>
          </div>
          <div style="display:flex; align-items:center; gap:6px; margin-top:8px;">
            <span style="font-size:10px; color:#64748B;">Low</span>
            <div style="flex:1; height:4px; border-radius:2px;
                        background:linear-gradient(to right, #1A1E28, #92400E, #F59E0B);"></div>
            <span style="font-size:10px; color:#64748B;">High</span>
          </div>
        </div>

        <!-- Order types -->
        <div style="background:#1A1E28; border:1px solid #252B38; border-radius:12px; padding:16px;">
          <div style="font-size:13px; font-weight:700; color:#F1F5F9; margin-bottom:14px;">
            🍽️ Order Types
          </div>
          <div v-if="!report?.by_type?.length"
            style="color:#64748B; font-size:12px; text-align:center; padding:20px 0;">
            No data
          </div>
          <div v-else style="display:flex; flex-direction:column; gap:10px;">
            <div v-for="t in report?.by_type" :key="t.type">
              <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                <div style="display:flex; align-items:center; gap:6px;">
                  <span style="font-size:14px;">{{ typeIcon(t.type) }}</span>
                  <span style="font-size:12px; font-weight:600; color:#F1F5F9; text-transform:capitalize;">
                    {{ t.type?.replace('_', ' ') }}
                  </span>
                </div>
                <div style="text-align:right;">
                  <div style="font-size:13px; font-weight:700; color:#3B82F6;">
                    Rs. {{ formatNum(t.total) }}
                  </div>
                  <div style="font-size:10px; color:#64748B;">{{ t.count }} orders</div>
                </div>
              </div>
              <div style="height:4px; background:#252B38; border-radius:2px; overflow:hidden;">
                <div
                  style="height:100%; border-radius:2px; background:#3B82F6; transition:width 0.5s;"
                  :style="{
                    width: totalOrderRevenue > 0
                      ? (t.total / totalOrderRevenue * 100) + '%'
                      : '0%'
                  }"
                ></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ── ROW 3: Top items + Table performance ── -->
      <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:12px;">

        <!-- Top selling items -->
        <div style="background:#1A1E28; border:1px solid #252B38; border-radius:12px; padding:16px;">
          <div style="font-size:13px; font-weight:700; color:#F1F5F9; margin-bottom:14px;">
            🏆 Top Selling Items
          </div>
          <div v-if="!report?.top_items?.length"
            style="color:#64748B; font-size:12px; text-align:center; padding:20px 0;">
            No sales data
          </div>
          <div v-else>
            <div
              v-for="(item, idx) in report?.top_items" :key="item.item_name"
              style="display:flex; align-items:center; gap:10px; padding:8px 0;
                     border-bottom:1px solid #252B38;"
              :style="{ borderBottom: idx === report.top_items.length - 1 ? 'none' : '1px solid #252B38' }"
            >
              <!-- Rank -->
              <div
                style="width:24px; height:24px; border-radius:6px; display:flex; align-items:center;
                       justify-content:center; font-size:11px; font-weight:700; flex-shrink:0;"
                :style="{
                  background: idx === 0 ? 'rgba(245,158,11,0.2)'
                            : idx === 1 ? 'rgba(100,116,139,0.2)'
                            : idx === 2 ? 'rgba(180,83,9,0.2)'
                            : '#252B38',
                  color: idx === 0 ? '#F59E0B'
                       : idx === 1 ? '#94A3B8'
                       : idx === 2 ? '#B45309'
                       : '#64748B',
                }"
              >{{ idx + 1 }}</div>

              <!-- Name + bar -->
              <div style="flex:1; min-width:0;">
                <div style="font-size:12px; font-weight:500; color:#F1F5F9; white-space:nowrap;
                             overflow:hidden; text-overflow:ellipsis; margin-bottom:3px;">
                  {{ item.item_name }}
                </div>
                <div style="height:3px; background:#252B38; border-radius:2px; overflow:hidden;">
                  <div
                    style="height:100%; border-radius:2px; background:#F59E0B; transition:width 0.5s;"
                    :style="{
                      width: maxItemQty > 0
                        ? (item.total_qty / maxItemQty * 100) + '%'
                        : '0%'
                    }"
                  ></div>
                </div>
              </div>

              <!-- Stats -->
              <div style="text-align:right; flex-shrink:0;">
                <div style="font-size:12px; font-weight:700; color:#F59E0B;">
                  Rs. {{ formatNum(item.total_revenue) }}
                </div>
                <div style="font-size:10px; color:#64748B;">×{{ item.total_qty }} sold</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Table performance -->
        <div style="background:#1A1E28; border:1px solid #252B38; border-radius:12px; padding:16px;">
          <div style="font-size:13px; font-weight:700; color:#F1F5F9; margin-bottom:14px;">
            🪑 Table Performance
          </div>
          <div v-if="!report?.table_perf?.length"
            style="color:#64748B; font-size:12px; text-align:center; padding:20px 0;">
            No table data
          </div>
          <div v-else>
            <div
              v-for="(t, idx) in report?.table_perf" :key="t.table_id"
              style="display:flex; align-items:center; gap:10px; padding:8px 0;
                     border-bottom:1px solid #252B38;"
              :style="{ borderBottom: idx === report.table_perf.length - 1 ? 'none' : '1px solid #252B38' }"
            >
              <!-- Rank -->
              <div
                style="width:24px; height:24px; border-radius:6px; display:flex; align-items:center;
                       justify-content:center; font-size:11px; font-weight:700; flex-shrink:0;"
                :style="{
                  background: idx === 0 ? 'rgba(59,130,246,0.2)' : '#252B38',
                  color:      idx === 0 ? '#3B82F6' : '#64748B',
                }"
              >{{ idx + 1 }}</div>

              <!-- Table name + bar -->
              <div style="flex:1; min-width:0;">
                <div style="display:flex; align-items:center; gap:6px; margin-bottom:3px;">
                  <span style="font-size:12px; font-weight:600; color:#F1F5F9;">
                    {{ t.table_name }}
                  </span>
                  <span style="font-size:10px; color:#64748B;">{{ t.section }}</span>
                </div>
                <div style="height:3px; background:#252B38; border-radius:2px; overflow:hidden;">
                  <div
                    style="height:100%; border-radius:2px; background:#3B82F6; transition:width 0.5s;"
                    :style="{
                      width: maxTableRevenue > 0
                        ? (t.revenue / maxTableRevenue * 100) + '%'
                        : '0%'
                    }"
                  ></div>
                </div>
              </div>

              <!-- Stats -->
              <div style="text-align:right; flex-shrink:0;">
                <div style="font-size:12px; font-weight:700; color:#3B82F6;">
                  Rs. {{ formatNum(t.revenue) }}
                </div>
                <div style="font-size:10px; color:#64748B;">
                  {{ t.order_count }} orders · avg Rs. {{ formatNum(t.avg_order) }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ── ROW 4: Recent Transactions ── -->
      <div style="background:#1A1E28; border:1px solid #252B38; border-radius:12px; padding:16px;">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
          <div style="font-size:13px; font-weight:700; color:#F1F5F9;">
            💰 Recent Transactions
          </div>
          <div style="display:flex; align-items:center; gap:8px;">
            <!-- Filters -->
            <select v-model="transactionFilters.method" @change="loadTransactions"
              style="padding:4px 8px; background:#252B38; border:1px solid #334155;
                     border-radius:4px; color:#F1F5F9; font-size:11px; outline:none;">
              <option value="">All Methods</option>
              <option value="cash">Cash</option>
              <option value="card">Card</option>
              <option value="mobile">Mobile</option>
              <option value="voucher">Voucher</option>
              <option value="complimentary">Complimentary</option>
            </select>
            <input v-model="transactionFilters.date" type="date" @change="loadTransactions"
              style="padding:4px 8px; background:#252B38; border:1px solid #334155;
                     border-radius:4px; color:#F1F5F9; font-size:11px; outline:none;">
            <button @click="loadTransactions"
              style="padding:4px 8px; background:#F59E0B; color:#000; border:none;
                     border-radius:4px; font-size:11px; font-weight:600; cursor:pointer;">
              Refresh
            </button>
          </div>
        </div>

        <!-- Transaction summary -->
        <div v-if="transactionsData?.summary" style="display:flex; gap:12px; margin-bottom:12px;">
          <div style="flex:1; background:#12151C; border-radius:8px; padding:8px 12px;">
            <div style="font-size:10px; color:#64748B; margin-bottom:2px;">Total Count</div>
            <div style="font-size:16px; font-weight:700; color:#3B82F6;">
              {{ transactionsData.summary.total_count }}
            </div>
          </div>
          <div style="flex:1; background:#12151C; border-radius:8px; padding:8px 12px;">
            <div style="font-size:10px; color:#64748B; margin-bottom:2px;">Total Amount</div>
            <div style="font-size:16px; font-weight:700; color:#F59E0B;">
              Rs. {{ formatNum(transactionsData.summary.total_amount) }}
            </div>
          </div>
          <div style="flex:1; background:#12151C; border-radius:8px; padding:8px 12px;">
            <div style="font-size:10px; color:#64748B; margin-bottom:2px;">Average</div>
            <div style="font-size:16px; font-weight:700; color:#10B981;">
              Rs. {{ formatNum(transactionsData.summary.avg_amount) }}
            </div>
          </div>
        </div>

        <!-- Transactions table -->
        <div v-if="transactionsLoading" style="text-align:center; padding:20px; color:#64748B;">
          Loading transactions...
        </div>
        <div v-else-if="!transactionsData?.transactions?.length" 
          style="text-align:center; padding:20px; color:#64748B;">
          No transactions found for selected filters
        </div>
        <div v-else style="overflow-x:auto;">
          <table style="width:100%; border-collapse:collapse;">
            <thead>
              <tr style="background:#12151C;">
                <th style="padding:8px; text-align:left; font-size:11px; color:#64748B; font-weight:600;">Time</th>
                <th style="padding:8px; text-align:left; font-size:11px; color:#64748B; font-weight:600;">Receipt</th>
                <th style="padding:8px; text-align:left; font-size:11px; color:#64748B; font-weight:600;">Order</th>
                <th style="padding:8px; text-align:left; font-size:11px; color:#64748B; font-weight:600;">Table</th>
                <th style="padding:8px; text-align:left; font-size:11px; color:#64748B; font-weight:600;">Method</th>
                <th style="padding:8px; text-align:right; font-size:11px; color:#64748B; font-weight:600;">Amount</th>
                <th style="padding:8px; text-align:right; font-size:11px; color:#64748B; font-weight:600;">Change</th>
                <th style="padding:8px; text-align:left; font-size:11px; color:#64748B; font-weight:600;">Cashier</th>
                <th style="padding:8px; text-align:left; font-size:11px; color:#64748B; font-weight:600;">Status</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="txn in transactionsData.transactions" :key="txn.id"
                style="border-bottom:1px solid #252B38;">
                <td style="padding:8px; font-size:12px; color:#F1F5F9;">
                  {{ txn.paid_at_time }}
                </td>
                <td style="padding:8px; font-size:12px; color:#94A3B8;">
                  {{ txn.receipt_number }}
                </td>
                <td style="padding:8px;">
                  <div style="font-size:12px; color:#F1F5F9;">#{{ txn.order.order_number }}</div>
                  <div style="font-size:10px; color:#64748B;">{{ txn.order.customer_name || 'Guest' }}</div>
                </td>
                <td style="padding:8px; font-size:12px; color:#F1F5F9;">
                  {{ txn.order.table?.name || 'N/A' }}
                </td>
                <td style="padding:8px;">
                  <div style="display:flex; align-items:center; gap:4px;">
                    <span style="font-size:12px;">{{ methodIcon(txn.method) }}</span>
                    <span style="font-size:12px; color:#F1F5F9;">{{ txn.method_label }}</span>
                  </div>
                </td>
                <td style="padding:8px; text-align:right; font-size:12px; font-weight:600; color:#F59E0B;">
                  Rs. {{ formatNum(txn.amount) }}
                </td>
                <td style="padding:8px; text-align:right; font-size:12px; color:#10B981;">
                  {{ txn.change_amount > 0 ? 'Rs. ' + formatNum(txn.change_amount) : '-' }}
                </td>
                <td style="padding:8px; font-size:12px; color:#94A3B8;">
                  {{ txn.cashier || '-' }}
                </td>
                <td style="padding:8px;">
                  <span style="font-size:11px; padding:2px 6px; border-radius:4px; font-weight:600;"
                    :style="{
                      background: txn.order.payment_status === 'paid' ? 'rgba(16,185,129,0.2)' : 'rgba(245,158,11,0.2)',
                      color: txn.order.payment_status === 'paid' ? '#10B981' : '#F59E0B',
                    }">
                    {{ txn.order.payment_status }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- ── Period info ── -->
      <div style="text-align:center; padding:8px 0; font-size:11px; color:#334155;">
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
const loading           = ref(false)
const report            = ref(null)
const fromDate          = ref(today())
const toDate            = ref(today())
const activePreset      = ref('Today')
const transactionsLoading = ref(false)
const transactionsData  = ref(null)
const transactionFilters = ref({
  method: '',
  date: today(),
})

// ── Presets ────────────────────────────────────────────
const presets = [
  {
    label: 'Today',
    from:  () => today(),
    to:    () => today(),
  },
  {
    label: 'Yesterday',
    from:  () => offsetDay(-1),
    to:    () => offsetDay(-1),
  },
  {
    label: 'This Week',
    from:  () => startOfWeek(),
    to:    () => today(),
  },
  {
    label: 'This Month',
    from:  () => startOfMonth(),
    to:    () => today(),
  },
  {
    label: 'Last 30 Days',
    from:  () => offsetDay(-29),
    to:    () => today(),
  },
]

// ── Computed ───────────────────────────────────────────
const summaryCards = computed(() => {
  if (!report.value) return []
  const s = report.value.summary
  return [
    {
      label:  'Total Revenue',
      value:  'Rs. ' + formatNum(s.total_revenue),
      color:  '#F59E0B',
      change: report.value.period.days === 1 ? null : null,
      sub:    report.value.period.days > 1
        ? `${s.total_orders} paid orders`
        : null,
    },
    {
      label: 'Orders',
      value: s.total_orders,
      color: '#3B82F6',
      sub:   `Avg Rs. ${formatNum(s.avg_order_value)} each`,
    },
    {
      label: 'Avg Order Value',
      value: 'Rs. ' + formatNum(s.avg_order_value),
      color: '#10B981',
      sub:   `${s.total_payments} payments`,
    },
    {
      label: 'Service Revenue',
      value: 'Rs. ' + formatNum(
        report.value.daily?.reduce((s, d) => s + d.revenue, 0) > 0
          ? Math.round(
              (report.value.summary.total_revenue - (
                report.value.top_items?.reduce((s, i) => s + parseFloat(i.total_revenue), 0) ?? 0
              )) * 100
            ) / 100
          : 0
      ),
      color:  '#8B5CF6',
      sub:    report.value.by_method?.length
        ? report.value.by_method.map(m => m.method_label).join(' · ')
        : 'No payments',
    },
  ]
})

const totalPayments = computed(() =>
  report.value?.by_method?.reduce((s, m) => s + m.total, 0) ?? 0
)

const totalOrderRevenue = computed(() =>
  report.value?.by_type?.reduce((s, t) => s + parseFloat(t.total), 0) ?? 0
)

const maxHourlyRevenue = computed(() =>
  Math.max(...(report.value?.hourly?.map(h => h.revenue) ?? [0]))
)

const maxDailyRevenue = computed(() =>
  Math.max(...(report.value?.daily?.map(d => d.revenue) ?? [0]))
)

const maxItemQty = computed(() =>
  Math.max(...(report.value?.top_items?.map(i => i.total_qty) ?? [0]))
)

const maxTableRevenue = computed(() =>
  Math.max(...(report.value?.table_perf?.map(t => t.revenue) ?? [0]))
)

const visibleHours = computed(() =>
  report.value?.hourly ?? []
)

// ── Methods ────────────────────────────────────────────
function today() {
  return new Date().toISOString().split('T')[0]
}

function offsetDay(n) {
  const d = new Date()
  d.setDate(d.getDate() + n)
  return d.toISOString().split('T')[0]
}

function startOfWeek() {
  const d = new Date()
  d.setDate(d.getDate() - d.getDay() + 1) // Monday
  return d.toISOString().split('T')[0]
}

function startOfMonth() {
  const d = new Date()
  d.setDate(1)
  return d.toISOString().split('T')[0]
}

function applyPreset(preset) {
  activePreset.value = preset.label
  fromDate.value     = preset.from()
  toDate.value       = preset.to()
  loadReport()
}

function formatNum(n) {
  const num = parseFloat(n ?? 0)
  if (isNaN(num)) return '0.00'
  // Show full amount with comma separators, no abbreviations
  return num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

function formatShortDate(dateStr) {
  const d = new Date(dateStr)
  return (d.getMonth() + 1) + '/' + d.getDate()
}

function methodIcon(method) {
  return { cash: '💵', card: '💳', mobile: '📱', voucher: '🎫', complimentary: '🎁' }[method] ?? '💰'
}

function methodColor(method) {
  return { cash: '#10B981', card: '#3B82F6', mobile: '#8B5CF6', voucher: '#F59E0B', complimentary: '#EF4444' }[method] ?? '#64748B'
}

function typeIcon(type) {
  return { dine_in: '🍽️', takeaway: '🥡', delivery: '🚚', bar: '🍺', counter: '🏪' }[type] ?? '📦'
}

function heatColor(revenue) {
  if (revenue === 0) return '#12151C'
  const max = maxHourlyRevenue.value
  if (max === 0) return '#12151C'
  const ratio = revenue / max
  if (ratio < 0.25) return 'rgba(245,158,11,0.1)'
  if (ratio < 0.5)  return 'rgba(245,158,11,0.25)'
  if (ratio < 0.75) return 'rgba(245,158,11,0.45)'
  return 'rgba(245,158,11,0.7)'
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
    const params = {
      limit: 50,
      ...transactionFilters.value
    }
    
    // Remove empty values
    Object.keys(params).forEach(key => {
      if (params[key] === '') delete params[key]
    })
    
    const { data } = await axios.get('/reports/transactions', { params })
    transactionsData.value = data
  } catch (e) {
    console.error('Failed to load transactions:', e)
    transactionsData.value = null
  } finally {
    transactionsLoading.value = false
  }
}

onMounted(() => {
  loadReport()
  loadTransactions()
})

// Refresh when component becomes active (e.g., when navigating back from payment)
onActivated(() => {
  console.log('Reports activated - refreshing data...')
  loadReport()
  loadTransactions()
})
</script>

<style scoped>
/* ── Mobile Responsive ── */
@media (max-width: 768px) {
  .reports-header {
    flex-direction: column;
    gap: 12px;
    align-items: stretch;
  }
  
  .date-range-picker {
    flex-direction: column;
    gap: 8px;
    align-items: stretch;
  }
  
  .quick-presets {
    justify-content: center;
    flex-wrap: wrap;
  }
  
  .quick-presets button {
    font-size: 10px;
    padding: 4px 8px;
  }
  
  .custom-date-range {
    justify-content: center;
    gap: 6px;
  }
  
  .custom-date-range input {
    font-size: 12px;
    padding: 4px 6px;
  }
  
  .load-button {
    width: 100%;
    margin-top: 8px;
  }
  
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 8px;
    padding: 8px;
  }
  
  .stat-card {
    padding: 12px;
    border-radius: 12px;
  }
  
  .stat-label {
    font-size: 10px;
  }
  
  .stat-value {
    font-size: 14px;
  }
  
  .stat-sub {
    font-size: 8px;
  }
  
  .charts-grid {
    grid-template-columns: 1fr;
    gap: 12px;
  }
  
  .chart-card {
    padding: 12px;
    border-radius: 12px;
  }
  
  .chart-title {
    font-size: 12px;
  }
  
  .data-tables {
    grid-template-columns: 1fr;
    gap: 12px;
  }
  
  .table-card {
    padding: 12px;
    border-radius: 12px;
  }
  
  .table-title {
    font-size: 12px;
  }
  
  .table-content {
    font-size: 10px;
  }
  
  .transactions-modal {
    width: 95%;
    max-width: 95%;
    margin: 2.5% auto;
    max-height: 95vh;
  }
  
  .modal-body {
    padding: 12px;
  }
  
  .transaction-item {
    padding: 8px;
    border-radius: 8px;
  }
  
  .transaction-amount {
    font-size: 12px;
  }
  
  .transaction-details {
    font-size: 10px;
  }
}

@media (max-width: 480px) {
  .reports-header {
    padding: 8px 12px;
  }
  
  h1 {
    font-size: 16px !important;
  }
  
  .quick-presets {
    gap: 2px;
  }
  
  .quick-presets button {
    font-size: 9px;
    padding: 3px 6px;
  }
  
  .custom-date-range input {
    font-size: 11px;
    padding: 3px 5px;
  }
  
  .load-button {
    font-size: 11px;
    padding: 6px 12px;
  }
  
  .stats-grid {
    grid-template-columns: 1fr;
    gap: 6px;
    padding: 6px;
  }
  
  .stat-card {
    padding: 10px;
    border-radius: 10px;
  }
  
  .stat-label {
    font-size: 9px;
  }
  
  .stat-value {
    font-size: 13px;
  }
  
  .stat-sub {
    font-size: 7px;
  }
  
  .chart-card {
    padding: 10px;
    border-radius: 10px;
  }
  
  .chart-title {
    font-size: 11px;
  }
  
  .table-card {
    padding: 10px;
    border-radius: 10px;
  }
  
  .table-title {
    font-size: 11px;
  }
  
  .table-content {
    font-size: 9px;
  }
  
  .transactions-modal {
    width: 98%;
    max-width: 98%;
    margin: 1% auto;
    max-height: 98vh;
  }
  
  .modal-body {
    padding: 8px;
  }
  
  .transaction-item {
    padding: 6px;
    border-radius: 6px;
  }
  
  .transaction-amount {
    font-size: 11px;
  }
  
  .transaction-details {
    font-size: 9px;
  }
}
</style>