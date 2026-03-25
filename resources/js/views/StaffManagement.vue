<template>
  <div style="display:flex; flex-direction:column; height:100%; background:#0A0C10; overflow:hidden;">

    <!-- Header -->
    <div style="padding:12px 20px; border-bottom:1px solid #252B38; background:#12151C;
                display:flex; align-items:center; justify-content:space-between; flex-shrink:0;">
      <h1 style="font-size:20px; font-weight:700; color:#F1F5F9; margin:0;">👥 Staff Management</h1>
      <button @click="openStaffModal(null)"
        style="padding:7px 16px; background:#10B981; color:#fff; border:none;
               border-radius:8px; font-size:13px; font-weight:600; cursor:pointer;">
        + Add Staff
      </button>
    </div>

    <!-- Tabs -->
    <div style="display:flex; gap:2px; padding:10px 16px; background:#12151C;
                border-bottom:1px solid #252B38; flex-shrink:0;">
      <button v-for="tab in tabs" :key="tab.value" @click="activeTab = tab.value"
        style="padding:7px 16px; border-radius:7px; font-size:13px; font-weight:600;
               border:none; cursor:pointer; transition:all 0.15s;"
        :style="{
          background: activeTab === tab.value ? '#F59E0B' : 'transparent',
          color:      activeTab === tab.value ? '#000'    : '#64748B',
        }">
        {{ tab.label }}
      </button>
    </div>

    <!-- ══ OVERVIEW TAB ══ -->
    <div v-if="activeTab === 'overview'" style="flex:1; overflow-y:auto; padding:16px 20px;">

      <!-- Stats cards -->
      <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:10px; margin-bottom:18px;">
        <div v-for="card in overviewCards" :key="card.label"
          style="background:#1A1E28; border:1px solid #252B38; border-radius:12px; padding:16px;">
          <div style="font-size:11px; color:#64748B; text-transform:uppercase;
                      letter-spacing:0.06em; margin-bottom:6px;">{{ card.label }}</div>
          <div style="font-size:26px; font-weight:700;" :style="{ color: card.color }">
            {{ card.value }}
          </div>
          <div v-if="card.sub" style="font-size:11px; color:#64748B; margin-top:3px;">
            {{ card.sub }}
          </div>
        </div>
      </div>

      <!-- Service charge this month -->
      <div style="background:#1A1E28; border:1px solid #252B38; border-radius:12px;
                  padding:16px; margin-bottom:16px;">
        <div style="font-size:13px; font-weight:700; color:#F1F5F9; margin-bottom:12px;">
          💰 Service Charge — {{ currentMonthLabel }}
        </div>
        <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:10px;">
          <div style="background:#12151C; border-radius:8px; padding:12px; text-align:center;">
            <div style="font-size:11px; color:#64748B; margin-bottom:4px;">Total Collected</div>
            <div style="font-size:20px; font-weight:700; color:#F59E0B;">
              {{ currency(overview?.service_charge_month) }}
            </div>
          </div>
          <div style="background:#12151C; border-radius:8px; padding:12px; text-align:center;">
            <div style="font-size:11px; color:#64748B; margin-bottom:4px;">Active Staff</div>
            <div style="font-size:20px; font-weight:700; color:#3B82F6;">
              {{ overview?.active_staff_month ?? 0 }}
            </div>
          </div>
          <div style="background:#12151C; border-radius:8px; padding:12px; text-align:center;">
            <div style="font-size:11px; color:#64748B; margin-bottom:4px;">Per Staff (Equal Split)</div>
            <div style="font-size:20px; font-weight:700; color:#10B981;">
              {{ currency(overview?.per_staff_share) }}
            </div>
          </div>
        </div>
      </div>

      <!-- Today's shifts -->
      <div style="background:#1A1E28; border:1px solid #252B38; border-radius:12px; padding:16px;">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px;">
          <div style="font-size:13px; font-weight:700; color:#F1F5F9;">
            🕐 Today's Shifts — {{ today }}
          </div>
          <div style="font-size:12px; color:#64748B;">
            {{ overview?.on_shift_today ?? 0 }} on shift
          </div>
        </div>
        <div v-if="!overview?.today_shifts?.length"
          style="text-align:center; padding:24px; color:#64748B; font-size:13px;">
          No shifts today
        </div>
        <div v-else style="display:flex; flex-direction:column; gap:6px;">
          <div v-for="shift in overview.today_shifts" :key="shift.id"
            style="display:flex; align-items:center; justify-content:space-between;
                   padding:10px 12px; background:#12151C; border-radius:8px; border:1px solid #252B38;">
            <div style="display:flex; align-items:center; gap:10px;">
              <div style="width:32px; height:32px; border-radius:50%; background:#252B38;
                          display:flex; align-items:center; justify-content:center;
                          font-size:12px; font-weight:700; color:#F59E0B;">
                {{ initials(shift.name) }}
              </div>
              <div>
                <div style="font-size:13px; font-weight:600; color:#F1F5F9;">{{ shift.name }}</div>
                <div style="font-size:11px; color:#64748B; text-transform:capitalize;">{{ shift.role }}</div>
              </div>
            </div>
            <div style="display:flex; align-items:center; gap:16px;">
              <div style="text-align:right;">
                <div style="font-size:12px; color:#F1F5F9;">
                  {{ shift.clock_in }} — {{ shift.clock_out ?? '...' }}
                </div>
                <div style="font-size:11px; color:#64748B;">{{ shift.hours }}</div>
              </div>
              <div style="font-size:11px; font-weight:700; padding:3px 8px; border-radius:5px;"
                :style="{
                  background: shift.status === 'active' ? 'rgba(16,185,129,0.12)' : 'rgba(100,116,139,0.12)',
                  color:      shift.status === 'active' ? '#10B981' : '#64748B',
                }">
                {{ shift.status === 'active' ? '● Active' : '✓ Done' }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ══ STAFF LIST TAB ══ -->
    <div v-if="activeTab === 'staff'" style="flex:1; display:flex; flex-direction:column; overflow:hidden;">

      <!-- Filter bar -->
      <div style="padding:10px 16px; border-bottom:1px solid #252B38;
                  display:flex; gap:8px; align-items:center; flex-shrink:0;">
        <select v-model="filterRole" @change="loadStaff"
          style="padding:6px 10px; background:#1A1E28; border:1px solid #252B38;
                 border-radius:7px; color:#F1F5F9; font-size:13px; outline:none;">
          <option value="">All Roles</option>
          <option v-for="r in roles" :key="r.value" :value="r.value">{{ r.label }}</option>
        </select>
        <input v-model="searchQuery" placeholder="Search name / ID / phone..."
          style="flex:1; max-width:240px; padding:6px 10px; background:#1A1E28;
                 border:1px solid #252B38; border-radius:7px; color:#F1F5F9;
                 font-size:13px; outline:none;"
          @input="loadStaff"
          @focus="e => e.target.style.borderColor='#F59E0B'"
          @blur="e => e.target.style.borderColor='#252B38'"
        />
      </div>

      <!-- Staff grid -->
      <div style="flex:1; overflow-y:auto; padding:14px 16px;">
        <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(300px,1fr)); gap:10px;">
          <div v-for="s in staffList" :key="s.id"
            style="background:#1A1E28; border:1px solid #252B38; border-radius:12px; padding:16px;
                   transition:border-color 0.15s; cursor:pointer;"
            @click="openDetail(s)"
            @mouseenter="e => e.currentTarget.style.borderColor='#334155'"
            @mouseleave="e => e.currentTarget.style.borderColor='#252B38'"
          >
            <!-- Top row -->
            <div style="display:flex; align-items:start; justify-content:space-between; margin-bottom:12px;">
              <div style="display:flex; align-items:center; gap:10px;">
                <!-- Avatar -->
                <div style="width:44px; height:44px; border-radius:50%; flex-shrink:0;
                            display:flex; align-items:center; justify-content:center;
                            font-size:16px; font-weight:700; color:#000;"
                  :style="{ background: roleColor(s.role) }">
                  {{ initials(s.name) }}
                </div>
                <div>
                  <div style="font-size:14px; font-weight:700; color:#F1F5F9;">{{ s.name }}</div>
                  <div style="font-size:11px; color:#64748B; margin-top:1px;">
                    {{ s.employee_id }} · {{ s.role_label }}
                  </div>
                </div>
              </div>
              <div style="font-size:10px; font-weight:700; padding:3px 8px; border-radius:5px;"
                :style="{
                  background: s.is_active ? 'rgba(16,185,129,0.1)' : 'rgba(239,68,68,0.1)',
                  color:      s.is_active ? '#10B981' : '#EF4444',
                }">
                {{ s.is_active ? 'Active' : 'Inactive' }}
              </div>
            </div>

            <!-- Details -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:6px; margin-bottom:12px;">
              <div style="background:#12151C; border-radius:7px; padding:8px 10px;">
                <div style="font-size:10px; color:#64748B; margin-bottom:2px;">Salary</div>
                <div style="font-size:13px; font-weight:700; color:#F59E0B;">
                  {{ currency(s.base_salary) }}
                  <span style="font-size:10px; color:#64748B; font-weight:400;">
                    /{{ s.salary_type === 'monthly' ? 'mo' : s.salary_type === 'daily' ? 'day' : 'hr' }}
                  </span>
                </div>
              </div>
              <div style="background:#12151C; border-radius:7px; padding:8px 10px;">
                <div style="font-size:10px; color:#64748B; margin-bottom:2px;">Service Share</div>
                <div style="font-size:13px; font-weight:700; color:#8B5CF6;">
                  {{ s.service_charge_pct > 0 ? s.service_charge_pct + '%' : 'Equal Split' }}
                </div>
              </div>
              <div style="background:#12151C; border-radius:7px; padding:8px 10px;">
                <div style="font-size:10px; color:#64748B; margin-bottom:2px;">Phone</div>
                <div style="font-size:12px; color:#F1F5F9;">{{ s.phone ?? '—' }}</div>
              </div>
              <div style="background:#12151C; border-radius:7px; padding:8px 10px;">
                <div style="font-size:10px; color:#64748B; margin-bottom:2px;">Experience</div>
                <div style="font-size:12px; color:#F1F5F9;">{{ s.years_of_service }} yrs</div>
              </div>
            </div>

            <!-- Actions -->
            <div style="display:flex; gap:6px;" @click.stop>
              <button @click="openStaffModal(s)"
                style="flex:1; padding:6px; background:rgba(59,130,246,0.1); color:#3B82F6;
                       border:1px solid rgba(59,130,246,0.2); border-radius:6px;
                       font-size:11px; font-weight:600; cursor:pointer;">
                ✏️ Edit
              </button>
              <button @click="openPayrollModal(s)"
                style="flex:1; padding:6px; background:rgba(245,158,11,0.1); color:#F59E0B;
                       border:1px solid rgba(245,158,11,0.2); border-radius:6px;
                       font-size:11px; font-weight:600; cursor:pointer;">
                💰 Payroll
              </button>
              <button @click="toggleActive(s)"
                style="flex:1; padding:6px; border-radius:6px; font-size:11px;
                       font-weight:600; cursor:pointer; border:1px solid;"
                :style="{
                  background:  s.is_active ? 'rgba(239,68,68,0.1)' : 'rgba(16,185,129,0.1)',
                  color:       s.is_active ? '#EF4444' : '#10B981',
                  borderColor: s.is_active ? 'rgba(239,68,68,0.2)' : 'rgba(16,185,129,0.2)',
                }">
                {{ s.is_active ? 'Disable' : 'Enable' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ══ PAYROLL TAB ══ -->
    <div v-if="activeTab === 'payroll'" style="flex:1; overflow-y:auto; padding:16px 20px;">

      <!-- Month selector -->
      <div style="display:flex; gap:10px; align-items:center; margin-bottom:16px;">
        <select v-model="payrollMonth"
          style="padding:7px 10px; background:#1A1E28; border:1px solid #252B38;
                 border-radius:7px; color:#F1F5F9; font-size:13px; outline:none;">
          <option v-for="m in months" :key="m.value" :value="m.value">{{ m.label }}</option>
        </select>
        <select v-model="payrollYear"
          style="padding:7px 10px; background:#1A1E28; border:1px solid #252B38;
                 border-radius:7px; color:#F1F5F9; font-size:13px; outline:none;">
          <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
        </select>
        <button @click="generateAllPayrolls"
          style="padding:7px 16px; background:#F59E0B; color:#000; border:none;
                 border-radius:7px; font-size:13px; font-weight:700; cursor:pointer;">
          ⚡ Calculate All
        </button>
        <button @click="openServiceChargeDistribution"
          style="padding:7px 16px; background:#8B5CF6; color:#fff; border:none;
                 border-radius:7px; font-size:13px; font-weight:700; cursor:pointer;">
          💰 Service Charge
        </button>
        <div style="font-size:12px; color:#64748B;">
          Service collected: <span style="color:#F59E0B; font-weight:700;">{{ currency(monthServiceCharge) }}</span>
          · Per staff: <span style="color:#10B981; font-weight:700;">{{ currency(perStaffServiceCharge) }}</span>
        </div>
      </div>

      <!-- Payroll table -->
      <div style="background:#1A1E28; border:1px solid #252B38; border-radius:12px; overflow:hidden;">
        <div style="display:grid; grid-template-columns:2fr 1fr 1fr 1fr 1fr 1fr 1fr 120px;
                    padding:10px 14px; border-bottom:1px solid #252B38;
                    font-size:11px; color:#64748B; font-weight:600; text-transform:uppercase; letter-spacing:0.05em;">
          <div>Staff</div>
          <div style="text-align:right;">Base</div>
          <div style="text-align:right;">Svc Charge</div>
          <div style="text-align:right;">Tips</div>
          <div style="text-align:right;">Bonus</div>
          <div style="text-align:right;">Deductions</div>
          <div style="text-align:right;">Net Pay</div>
          <div style="text-align:center;">Status</div>
        </div>

        <div v-if="payrollList.length === 0"
          style="text-align:center; padding:40px; color:#64748B; font-size:13px;">
          No payroll records. Click "Calculate All" to generate.
        </div>

        <div v-for="p in payrollList" :key="p.id"
          style="display:grid; grid-template-columns:2fr 1fr 1fr 1fr 1fr 1fr 1fr 120px;
                 padding:12px 14px; border-bottom:1px solid #1A1E28; align-items:center;
                 transition:background 0.15s;"
          @mouseenter="e => e.currentTarget.style.background='rgba(255,255,255,0.02)'"
          @mouseleave="e => e.currentTarget.style.background='transparent'"
        >
          <div>
            <div style="font-size:13px; font-weight:600; color:#F1F5F9;">{{ p.user_name }}</div>
            <div style="font-size:11px; color:#64748B;">
              {{ p.days_worked }} days · {{ p.hours_worked }}h
            </div>
          </div>
          <div style="text-align:right; font-size:13px; color:#94A3B8;">{{ currency(p.base_salary) }}</div>
          <div style="text-align:right; font-size:13px; color:#8B5CF6;">{{ currency(p.service_charge_share) }}</div>
          <div style="text-align:right; font-size:13px; color:#10B981;">{{ currency(p.tips) }}</div>
          <div style="text-align:right; font-size:13px; color:#3B82F6;">{{ currency(p.bonus) }}</div>
          <div style="text-align:right; font-size:13px; color:#EF4444;">{{ currency(p.deductions) }}</div>
          <div style="text-align:right; font-size:15px; font-weight:700; color:#F59E0B;">
            {{ currency(p.net_pay) }}
          </div>
          <div style="text-align:center; display:flex; gap:4px; justify-content:center;">
            <select
              :value="p.status"
              @change="updatePayrollStatus(p, $event.target.value)"
              style="padding:3px 6px; background:#12151C; border:1px solid #252B38;
                     border-radius:5px; color:#F1F5F9; font-size:11px; outline:none; cursor:pointer;"
            >
              <option value="draft">Draft</option>
              <option value="approved">Approved</option>
              <option value="paid">Paid</option>
            </select>
          </div>
        </div>

        <!-- Totals row -->
        <div v-if="payrollList.length > 0"
          style="display:grid; grid-template-columns:2fr 1fr 1fr 1fr 1fr 1fr 1fr 120px;
                 padding:12px 14px; background:#12151C; border-top:1px solid #252B38;
                 font-size:13px; font-weight:700; color:#F1F5F9;">
          <div>Total ({{ payrollList.length }} staff)</div>
          <div style="text-align:right;">{{ currency(payrollTotals.base) }}</div>
          <div style="text-align:right; color:#8B5CF6;">{{ currency(payrollTotals.service) }}</div>
          <div style="text-align:right; color:#10B981;">{{ currency(payrollTotals.tips) }}</div>
          <div style="text-align:right; color:#3B82F6;">{{ currency(payrollTotals.bonus) }}</div>
          <div style="text-align:right; color:#EF4444;">{{ currency(payrollTotals.deductions) }}</div>
          <div style="text-align:right; color:#F59E0B;">{{ currency(payrollTotals.net) }}</div>
          <div></div>
        </div>
      </div>
    </div>

    <!-- ══ LEAVE TAB ══ -->
    <div v-if="activeTab === 'leave'" style="flex:1; overflow-y:auto; padding:16px 20px;">

      <div style="background:#1A1E28; border:1px solid #252B38; border-radius:12px; overflow:hidden;">
        <div style="padding:12px 16px; border-bottom:1px solid #252B38;
                    display:flex; align-items:center; justify-content:space-between;">
          <div style="font-size:13px; font-weight:700; color:#F1F5F9;">Leave Requests</div>
          <div style="display:flex; align-items:center; gap:12px;">
            <div style="font-size:12px; color:#64748B;">
              {{ allLeaves.filter(l => l.status === 'pending').length }} pending
            </div>
            <button @click="openLeaveModal(null)"
              style="padding:6px 12px; background:#10B981; color:#fff; border:none;
                     border-radius:7px; font-size:12px; font-weight:600; cursor:pointer;">
              + Add Leave
            </button>
          </div>
        </div>

        <div v-if="allLeaves.length === 0"
          style="text-align:center; padding:40px; color:#64748B; font-size:13px;">
          No leave requests
        </div>

        <div v-for="leave in allLeaves" :key="leave.id"
          style="display:flex; align-items:center; justify-content:space-between;
                 padding:12px 16px; border-bottom:1px solid #1A1E28;"
        >
          <div style="display:flex; align-items:center; gap:10px; flex:1;">
            <div style="width:34px; height:34px; border-radius:50%; background:#252B38;
                        display:flex; align-items:center; justify-content:center;
                        font-size:12px; font-weight:700; color:#F59E0B; flex-shrink:0;">
              {{ initials(leave.user_name) }}
            </div>
            <div>
              <div style="font-size:13px; font-weight:600; color:#F1F5F9;">
                {{ leave.user_name }}
                <span style="font-size:11px; font-weight:400; color:#64748B; margin-left:4px;">
                  · {{ leave.type }}
                </span>
              </div>
              <div style="font-size:11px; color:#64748B; margin-top:1px;">
                {{ leave.from_date }} → {{ leave.to_date }}
                · {{ leave.days }} day{{ leave.days > 1 ? 's' : '' }}
              </div>
              <div v-if="leave.reason" style="font-size:11px; color:#475569; margin-top:1px; font-style:italic;">
                "{{ leave.reason }}"
              </div>
            </div>
          </div>

          <div style="display:flex; gap:6px; align-items:center; flex-shrink:0;">
            <div v-if="leave.status !== 'pending'"
              style="font-size:11px; font-weight:700; padding:3px 10px; border-radius:5px;"
              :style="{
                background: leave.status === 'approved' ? 'rgba(16,185,129,0.1)' : 'rgba(239,68,68,0.1)',
                color:      leave.status === 'approved' ? '#10B981' : '#EF4444',
              }">
              {{ leave.status }}
            </div>
            <template v-else>
              <button @click="updateLeaveStatus(leave, 'approved')"
                style="padding:4px 10px; background:rgba(16,185,129,0.12); color:#10B981;
                       border:1px solid rgba(16,185,129,0.2); border-radius:5px;
                       font-size:11px; font-weight:600; cursor:pointer;">
                ✓ Approve
              </button>
              <button @click="updateLeaveStatus(leave, 'rejected')"
                style="padding:4px 10px; background:rgba(239,68,68,0.1); color:#EF4444;
                       border:1px solid rgba(239,68,68,0.2); border-radius:5px;
                       font-size:11px; font-weight:600; cursor:pointer;">
                ✕ Reject
              </button>
            </template>
          </div>
        </div>
      </div>
    </div>

    <!-- ══ STAFF DETAIL MODAL ══ -->
    <Teleport to="body">
      <div v-if="detailStaff"
        style="position:fixed; inset:0; background:rgba(0,0,0,0.65); z-index:100;
               display:flex; align-items:center; justify-content:center; padding:16px;"
        @click.self="detailStaff = null">
        <div style="background:#1A1E28; border:1px solid #252B38; border-radius:16px;
                    width:520px; max-height:90vh; display:flex; flex-direction:column; overflow:hidden;">

          <!-- Detail header -->
          <div style="padding:16px 20px; border-bottom:1px solid #252B38;
                      display:flex; align-items:center; justify-content:space-between; flex-shrink:0;">
            <div style="display:flex; align-items:center; gap:12px;">
              <div style="width:44px; height:44px; border-radius:50%; display:flex; align-items:center;
                          justify-content:center; font-size:17px; font-weight:700; color:#000;"
                :style="{ background: roleColor(detailStaff.role) }">
                {{ initials(detailStaff.name) }}
              </div>
              <div>
                <div style="font-size:16px; font-weight:700; color:#F1F5F9;">{{ detailStaff.name }}</div>
                <div style="font-size:12px; color:#64748B;">
                  {{ detailStaff.employee_id }} · {{ detailStaff.role_label }}
                </div>
              </div>
            </div>
            <button @click="detailStaff = null"
              style="background:none; border:none; color:#64748B; font-size:20px; cursor:pointer;">×</button>
          </div>

          <div style="flex:1; overflow-y:auto; padding:16px 20px;">

            <!-- This month summary -->
            <div style="background:#12151C; border-radius:10px; padding:14px; margin-bottom:14px;">
              <div style="font-size:12px; font-weight:700; color:#F1F5F9; margin-bottom:10px;">
                This Month
              </div>
              <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:8px;">
                <div style="text-align:center;">
                  <div style="font-size:18px; font-weight:700; color:#3B82F6;">
                    {{ detailData?.this_month?.days_worked ?? 0 }}
                  </div>
                  <div style="font-size:10px; color:#64748B;">Days</div>
                </div>
                <div style="text-align:center;">
                  <div style="font-size:18px; font-weight:700; color:#10B981;">
                    {{ detailData?.this_month?.hours_worked ?? 0 }}h
                  </div>
                  <div style="font-size:10px; color:#64748B;">Hours</div>
                </div>
                <div style="text-align:center;">
                  <div style="font-size:18px; font-weight:700; color:#F59E0B;">
                    {{ currency(detailData?.this_month?.total_tips) }}
                  </div>
                  <div style="font-size:10px; color:#64748B;">Tips</div>
                </div>
              </div>
            </div>

            <!-- Salary info -->
            <div style="background:#12151C; border-radius:10px; padding:14px; margin-bottom:14px;">
              <div style="font-size:12px; font-weight:700; color:#F1F5F9; margin-bottom:10px;">
                Compensation
              </div>
              <div style="display:flex; flex-direction:column; gap:6px;">
                <div style="display:flex; justify-content:space-between;">
                  <span style="font-size:12px; color:#64748B;">Salary Type</span>
                  <span style="font-size:12px; color:#F1F5F9; text-transform:capitalize;">
                    {{ detailStaff.salary_type }}
                  </span>
                </div>
                <div style="display:flex; justify-content:space-between;">
                  <span style="font-size:12px; color:#64748B;">Base Salary</span>
                  <span style="font-size:13px; font-weight:700; color:#F59E0B;">
                    {{ currency(detailStaff.base_salary) }}
                    /{{ detailStaff.salary_type === 'monthly' ? 'month' : detailStaff.salary_type }}
                  </span>
                </div>
                <div v-if="detailStaff.hourly_rate > 0" style="display:flex; justify-content:space-between;">
                  <span style="font-size:12px; color:#64748B;">Hourly Rate</span>
                  <span style="font-size:12px; color:#F1F5F9;">{{ currency(detailStaff.hourly_rate) }}/hr</span>
                </div>
                <div style="display:flex; justify-content:space-between;">
                  <span style="font-size:12px; color:#64748B;">Service Charge Share</span>
                  <span style="font-size:12px; color:#8B5CF6; font-weight:600;">
                    {{ detailStaff.service_charge_pct > 0
                       ? detailStaff.service_charge_pct + '% (fixed)'
                       : 'Equal split with active staff' }}
                  </span>
                </div>
                <div v-if="detailStaff.bank_name" style="display:flex; justify-content:space-between;">
                  <span style="font-size:12px; color:#64748B;">Bank</span>
                  <span style="font-size:12px; color:#F1F5F9;">
                    {{ detailStaff.bank_name }} · {{ detailStaff.bank_account }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Recent payrolls -->
            <div v-if="detailData?.latest_payroll"
              style="background:#12151C; border-radius:10px; padding:14px;">
              <div style="font-size:12px; font-weight:700; color:#F1F5F9; margin-bottom:10px;">
                Latest Payroll
              </div>
              <div style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                  <div style="font-size:13px; color:#F1F5F9;">
                    {{ detailData.latest_payroll.month_label }}
                  </div>
                  <div style="font-size:11px; color:#64748B; margin-top:2px;">
                    {{ detailData.latest_payroll.days_worked }} days ·
                    Base ${{ fmt(detailData.latest_payroll.base_salary) }} +
                    Svc ${{ fmt(detailData.latest_payroll.service_charge_share) }}
                  </div>
                </div>
                <div style="text-align:right;">
                  <div style="font-size:18px; font-weight:700; color:#F59E0B;">
                    ${{ fmt(detailData.latest_payroll.net_pay) }}
                  </div>
                  <div style="font-size:10px; font-weight:700; padding:2px 7px; border-radius:4px;
                               display:inline-block;"
                    :style="{
                      background: detailData.latest_payroll.status === 'paid' ? 'rgba(16,185,129,0.1)' : 'rgba(245,158,11,0.1)',
                      color:      detailData.latest_payroll.status === 'paid' ? '#10B981' : '#F59E0B',
                    }">
                    {{ detailData.latest_payroll.status }}
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Detail footer -->
          <div style="padding:14px 20px; border-top:1px solid #252B38;
                      display:flex; gap:8px; flex-shrink:0;">
            <button @click="openStaffModal(detailStaff); detailStaff = null"
              style="flex:1; padding:10px; background:rgba(59,130,246,0.1); color:#3B82F6;
                     border:1px solid rgba(59,130,246,0.2); border-radius:8px;
                     font-size:13px; font-weight:600; cursor:pointer;">
              ✏️ Edit Profile
            </button>
            <button @click="openPayrollModal(detailStaff); detailStaff = null"
              style="flex:1; padding:10px; background:#F59E0B; color:#000;
                     border:none; border-radius:8px; font-size:13px; font-weight:700; cursor:pointer;">
              💰 Calculate Payroll
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- ══ STAFF FORM MODAL ══ -->
    <Teleport to="body">
      <div v-if="showStaffModal"
        style="position:fixed; inset:0; background:rgba(0,0,0,0.65); z-index:100;
               display:flex; align-items:center; justify-content:center; padding:16px;"
        @click.self="showStaffModal = false">
        <div style="background:#1A1E28; border:1px solid #252B38; border-radius:16px;
                    width:540px; max-height:90vh; display:flex; flex-direction:column; overflow:hidden;">

          <div style="padding:16px 20px; border-bottom:1px solid #252B38;
                      display:flex; align-items:center; justify-content:space-between; flex-shrink:0;">
            <div style="font-size:16px; font-weight:700; color:#F1F5F9;">
              {{ editingStaff ? 'Edit Staff' : 'Add Staff Member' }}
            </div>
            <button @click="showStaffModal = false"
              style="background:none; border:none; color:#64748B; font-size:20px; cursor:pointer;">×</button>
          </div>

          <div style="flex:1; overflow-y:auto; padding:18px 20px;">
            <div style="display:flex; flex-direction:column; gap:14px;">

              <!-- Name + Role -->
              <div style="display:flex; gap:12px;">
                <div style="flex:2;">
                  <label style="font-size:11px; color:#64748B; display:block; margin-bottom:5px;
                                 text-transform:uppercase; letter-spacing:0.05em;">Full Name *</label>
                  <input v-model="staffForm.name" placeholder="John Smith"
                    style="width:100%; padding:9px 12px; background:#12151C; border:1px solid #252B38;
                           border-radius:8px; color:#F1F5F9; font-size:14px; outline:none;"
                    @focus="e => e.target.style.borderColor='#F59E0B'"
                    @blur="e => e.target.style.borderColor='#252B38'" />
                </div>
                <div style="flex:1;">
                  <label style="font-size:11px; color:#64748B; display:block; margin-bottom:5px;
                                 text-transform:uppercase; letter-spacing:0.05em;">Role *</label>
                  <select v-model="staffForm.role"
                    style="width:100%; padding:9px 12px; background:#12151C; border:1px solid #252B38;
                           border-radius:8px; color:#F1F5F9; font-size:14px; outline:none;">
                    <option v-for="r in roles" :key="r.value" :value="r.value">{{ r.label }}</option>
                  </select>
                </div>
              </div>

              <!-- PIN + Phone -->
              <div style="display:flex; gap:12px;">
                <div style="flex:1;">
                  <label style="font-size:11px; color:#64748B; display:block; margin-bottom:5px;
                                 text-transform:uppercase; letter-spacing:0.05em;">
                    PIN {{ editingStaff ? '(leave blank to keep)' : '*' }}
                  </label>
                  <input v-model="staffForm.pin" type="password" maxlength="6"
                    placeholder="4-6 digit PIN"
                    style="width:100%; padding:9px 12px; background:#12151C; border:1px solid #252B38;
                           border-radius:8px; color:#F1F5F9; font-size:14px; outline:none; letter-spacing:4px;"
                    @focus="e => e.target.style.borderColor='#F59E0B'"
                    @blur="e => e.target.style.borderColor='#252B38'" />
                </div>
                <div style="flex:1;">
                  <label style="font-size:11px; color:#64748B; display:block; margin-bottom:5px;
                                 text-transform:uppercase; letter-spacing:0.05em;">Phone</label>
                  <input v-model="staffForm.phone" placeholder="+1 234 567 8900"
                    style="width:100%; padding:9px 12px; background:#12151C; border:1px solid #252B38;
                           border-radius:8px; color:#F1F5F9; font-size:14px; outline:none;"
                    @focus="e => e.target.style.borderColor='#F59E0B'"
                    @blur="e => e.target.style.borderColor='#252B38'" />
                </div>
              </div>

              <!-- Email + Join date -->
              <div style="display:flex; gap:12px;">
                <div style="flex:1;">
                  <label style="font-size:11px; color:#64748B; display:block; margin-bottom:5px;
                                 text-transform:uppercase; letter-spacing:0.05em;">Email</label>
                  <input v-model="staffForm.email" type="email" placeholder="john@example.com"
                    style="width:100%; padding:9px 12px; background:#12151C; border:1px solid #252B38;
                           border-radius:8px; color:#F1F5F9; font-size:14px; outline:none;"
                    @focus="e => e.target.style.borderColor='#F59E0B'"
                    @blur="e => e.target.style.borderColor='#252B38'" />
                </div>
                <div style="flex:1;">
                  <label style="font-size:11px; color:#64748B; display:block; margin-bottom:5px;
                                 text-transform:uppercase; letter-spacing:0.05em;">Join Date</label>
                  <input v-model="staffForm.join_date" type="date"
                    style="width:100%; padding:9px 12px; background:#12151C; border:1px solid #252B38;
                           border-radius:8px; color:#F1F5F9; font-size:14px; outline:none;"
                    @focus="e => e.target.style.borderColor='#F59E0B'"
                    @blur="e => e.target.style.borderColor='#252B38'" />
                </div>
              </div>

              <!-- Address + DOB -->
              <div style="display:flex; gap:12px;">
                <div style="flex:2;">
                  <label style="font-size:11px; color:#64748B; display:block; margin-bottom:5px;
                                 text-transform:uppercase; letter-spacing:0.05em;">Address</label>
                  <input v-model="staffForm.address" placeholder="Street, City"
                    style="width:100%; padding:9px 12px; background:#12151C; border:1px solid #252B38;
                           border-radius:8px; color:#F1F5F9; font-size:14px; outline:none;"
                    @focus="e => e.target.style.borderColor='#F59E0B'"
                    @blur="e => e.target.style.borderColor='#252B38'" />
                </div>
                <div style="flex:1;">
                  <label style="font-size:11px; color:#64748B; display:block; margin-bottom:5px;
                                 text-transform:uppercase; letter-spacing:0.05em;">Date of Birth</label>
                  <input v-model="staffForm.date_of_birth" type="date"
                    style="width:100%; padding:9px 12px; background:#12151C; border:1px solid #252B38;
                           border-radius:8px; color:#F1F5F9; font-size:14px; outline:none;"
                    @focus="e => e.target.style.borderColor='#F59E0B'"
                    @blur="e => e.target.style.borderColor='#252B38'" />
                </div>
              </div>

              <!-- Salary section -->
              <div style="border-top:1px solid #252B38; padding-top:14px;">
                <div style="font-size:12px; font-weight:700; color:#F1F5F9; margin-bottom:10px;
                             text-transform:uppercase; letter-spacing:0.06em;">💰 Compensation</div>
                <div style="display:flex; gap:12px; margin-bottom:10px;">
                  <div style="flex:1;">
                    <label style="font-size:11px; color:#64748B; display:block; margin-bottom:5px;
                                   text-transform:uppercase; letter-spacing:0.05em;">Salary Type</label>
                    <select v-model="staffForm.salary_type"
                      style="width:100%; padding:9px 12px; background:#12151C; border:1px solid #252B38;
                             border-radius:8px; color:#F1F5F9; font-size:14px; outline:none;">
                      <option value="monthly">Monthly</option>
                      <option value="daily">Daily</option>
                      <option value="hourly">Hourly</option>
                    </select>
                  </div>
                  <div style="flex:1;">
                    <label style="font-size:11px; color:#64748B; display:block; margin-bottom:5px;
                                   text-transform:uppercase; letter-spacing:0.05em;">
                      {{ staffForm.salary_type === 'hourly' ? 'Hourly Rate' : 'Base Salary' }} ($)
                    </label>
                    <input v-model.number="staffForm.base_salary" type="number" step="0.01" placeholder="0.00"
                      style="width:100%; padding:9px 12px; background:#12151C; border:1px solid #252B38;
                             border-radius:8px; color:#F59E0B; font-size:15px; font-weight:700; outline:none;"
                      @focus="e => e.target.style.borderColor='#F59E0B'"
                      @blur="e => e.target.style.borderColor='#252B38'" />
                  </div>
                </div>

                <div style="display:flex; gap:12px;">
                  <div style="flex:1;">
                    <label style="font-size:11px; color:#64748B; display:block; margin-bottom:5px;
                                   text-transform:uppercase; letter-spacing:0.05em;">
                      Service Charge % (0 = equal split)
                    </label>
                    <input v-model.number="staffForm.service_charge_pct" type="number"
                      step="0.5" min="0" max="100" placeholder="0"
                      style="width:100%; padding:9px 12px; background:#12151C; border:1px solid #252B38;
                             border-radius:8px; color:#8B5CF6; font-size:15px; font-weight:700; outline:none;"
                      @focus="e => e.target.style.borderColor='#8B5CF6'"
                      @blur="e => e.target.style.borderColor='#252B38'" />
                    <div style="font-size:10px; color:#334155; margin-top:3px;">
                      0 = share equally with all active staff
                    </div>
                  </div>
                  <div v-if="staffForm.salary_type === 'hourly'" style="flex:1;">
                    <label style="font-size:11px; color:#64748B; display:block; margin-bottom:5px;
                                   text-transform:uppercase; letter-spacing:0.05em;">Hourly Rate ($)</label>
                    <input v-model.number="staffForm.hourly_rate" type="number" step="0.01" placeholder="0.00"
                      style="width:100%; padding:9px 12px; background:#12151C; border:1px solid #252B38;
                             border-radius:8px; color:#F59E0B; font-size:15px; font-weight:700; outline:none;"
                      @focus="e => e.target.style.borderColor='#F59E0B'"
                      @blur="e => e.target.style.borderColor='#252B38'" />
                  </div>
                </div>
              </div>

              <!-- Bank details -->
              <div style="border-top:1px solid #252B38; padding-top:14px;">
                <div style="font-size:12px; font-weight:700; color:#F1F5F9; margin-bottom:10px;
                             text-transform:uppercase; letter-spacing:0.06em;">🏦 Bank Details</div>
                <div style="display:flex; gap:12px;">
                  <div style="flex:1;">
                    <label style="font-size:11px; color:#64748B; display:block; margin-bottom:5px;
                                   text-transform:uppercase; letter-spacing:0.05em;">Bank Name</label>
                    <input v-model="staffForm.bank_name" placeholder="Bank name"
                      style="width:100%; padding:9px 12px; background:#12151C; border:1px solid #252B38;
                             border-radius:8px; color:#F1F5F9; font-size:14px; outline:none;"
                      @focus="e => e.target.style.borderColor='#F59E0B'"
                      @blur="e => e.target.style.borderColor='#252B38'" />
                  </div>
                  <div style="flex:1;">
                    <label style="font-size:11px; color:#64748B; display:block; margin-bottom:5px;
                                   text-transform:uppercase; letter-spacing:0.05em;">Account Number</label>
                    <input v-model="staffForm.bank_account" placeholder="Account number"
                      style="width:100%; padding:9px 12px; background:#12151C; border:1px solid #252B38;
                             border-radius:8px; color:#F1F5F9; font-size:14px; outline:none;"
                      @focus="e => e.target.style.borderColor='#F59E0B'"
                      @blur="e => e.target.style.borderColor='#252B38'" />
                  </div>
                </div>
              </div>

              <!-- Notes -->
              <div>
                <label style="font-size:11px; color:#64748B; display:block; margin-bottom:5px;
                               text-transform:uppercase; letter-spacing:0.05em;">Notes</label>
                <textarea v-model="staffForm.notes" rows="2" placeholder="Any additional notes..."
                  style="width:100%; padding:9px 12px; background:#12151C; border:1px solid #252B38;
                         border-radius:8px; color:#F1F5F9; font-size:14px; resize:none;
                         font-family:inherit; outline:none;"
                  @focus="e => e.target.style.borderColor='#F59E0B'"
                  @blur="e => e.target.style.borderColor='#252B38'"></textarea>
              </div>

              <!-- Error -->
              <div v-if="formError"
                style="padding:10px 14px; background:rgba(239,68,68,0.08);
                       border:1px solid rgba(239,68,68,0.2); border-radius:8px; color:#EF4444; font-size:13px;">
                ⚠️ {{ formError }}
              </div>
            </div>
          </div>

          <!-- Modal footer -->
          <div style="padding:14px 20px; border-top:1px solid #252B38;
                      display:flex; gap:8px; flex-shrink:0;">
            <button @click="showStaffModal = false"
              style="flex:1; padding:11px; background:transparent; color:#64748B;
                     border:1px solid #252B38; border-radius:8px; cursor:pointer; font-size:13px;">
              Cancel
            </button>
            <button @click="saveStaff" :disabled="saving"
              style="flex:2; padding:11px; background:#F59E0B; color:#000; border:none;
                     border-radius:8px; cursor:pointer; font-weight:700; font-size:14px;"
              :style="{ opacity: saving ? '0.6' : '1' }">
              {{ saving ? 'Saving...' : (editingStaff ? 'Update Staff' : 'Add Staff Member') }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- ══ PAYROLL MODAL ══ -->
    <Teleport to="body">
      <div v-if="showPayrollModal"
        style="position:fixed; inset:0; background:rgba(0,0,0,0.65); z-index:110;
               display:flex; align-items:center; justify-content:center; padding:16px;"
        @click.self="showPayrollModal = false">
        <div style="background:#1A1E28; border:1px solid #252B38; border-radius:16px;
                    width:440px; overflow:hidden;">

          <div style="padding:16px 20px; border-bottom:1px solid #252B38;
                      display:flex; align-items:center; justify-content:space-between;">
            <div style="font-size:16px; font-weight:700; color:#F1F5F9;">
              💰 Payroll — {{ payrollTarget?.name }}
            </div>
            <button @click="showPayrollModal = false"
              style="background:none; border:none; color:#64748B; font-size:20px; cursor:pointer;">×</button>
          </div>

          <div style="padding:18px 20px;">

            <!-- Month/Year -->
            <div style="display:flex; gap:10px; margin-bottom:14px;">
              <div style="flex:1;">
                <label style="font-size:11px; color:#64748B; display:block; margin-bottom:5px;
                               text-transform:uppercase; letter-spacing:0.05em;">Month</label>
                <select v-model="payrollForm.month"
                  style="width:100%; padding:9px 12px; background:#12151C; border:1px solid #252B38;
                         border-radius:8px; color:#F1F5F9; font-size:14px; outline:none;">
                  <option v-for="m in months" :key="m.value" :value="m.value">{{ m.label }}</option>
                </select>
              </div>
              <div style="flex:1;">
                <label style="font-size:11px; color:#64748B; display:block; margin-bottom:5px;
                               text-transform:uppercase; letter-spacing:0.05em;">Year</label>
                <select v-model="payrollForm.year"
                  style="width:100%; padding:9px 12px; background:#12151C; border:1px solid #252B38;
                         border-radius:8px; color:#F1F5F9; font-size:14px; outline:none;">
                  <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
                </select>
              </div>
            </div>

            <!-- Adjustments -->
            <div style="display:flex; gap:10px; margin-bottom:14px;">
              <div style="flex:1;">
                <label style="font-size:11px; color:#64748B; display:block; margin-bottom:5px;
                               text-transform:uppercase; letter-spacing:0.05em;">Bonus ($)</label>
                <input v-model.number="payrollForm.bonus" type="number" step="0.01" placeholder="0.00"
                  style="width:100%; padding:9px 12px; background:#12151C; border:1px solid #252B38;
                         border-radius:8px; color:#10B981; font-size:15px; font-weight:700; outline:none;"
                  @focus="e => e.target.style.borderColor='#10B981'"
                  @blur="e => e.target.style.borderColor='#252B38'" />
              </div>
              <div style="flex:1;">
                <label style="font-size:11px; color:#64748B; display:block; margin-bottom:5px;
                               text-transform:uppercase; letter-spacing:0.05em;">Deductions ($)</label>
                <input v-model.number="payrollForm.deductions" type="number" step="0.01" placeholder="0.00"
                  style="width:100%; padding:9px 12px; background:#12151C; border:1px solid #252B38;
                         border-radius:8px; color:#EF4444; font-size:15px; font-weight:700; outline:none;"
                  @focus="e => e.target.style.borderColor='#EF4444'"
                  @blur="e => e.target.style.borderColor='#252B38'" />
              </div>
            </div>

            <div>
              <label style="font-size:11px; color:#64748B; display:block; margin-bottom:5px;
                             text-transform:uppercase; letter-spacing:0.05em;">Notes</label>
              <textarea v-model="payrollForm.notes" rows="2" placeholder="Payroll notes..."
                style="width:100%; padding:9px 12px; background:#12151C; border:1px solid #252B38;
                       border-radius:8px; color:#F1F5F9; font-size:14px; resize:none;
                       font-family:inherit; outline:none;"></textarea>
            </div>

            <!-- Payroll result preview -->
            <div v-if="calculatedPayroll"
              style="margin-top:14px; background:#12151C; border:1px solid #252B38;
                     border-radius:10px; padding:14px;">
              <div style="font-size:12px; font-weight:700; color:#F1F5F9; margin-bottom:10px;">
                Calculated Payroll
              </div>
              <div style="display:flex; flex-direction:column; gap:5px;">
                <div style="display:flex; justify-content:space-between;">
                  <span style="font-size:12px; color:#64748B;">
                    Base ({{ calculatedPayroll.days_worked }} days, {{ calculatedPayroll.hours_worked }}h)
                  </span>
                  <span style="font-size:12px; color:#F1F5F9;">{{ currency(calculatedPayroll.base_salary) }}</span>
                </div>
                <div style="display:flex; justify-content:space-between;">
                  <span style="font-size:12px; color:#64748B;">
                    Service Charge
                    <span style="font-size:10px;">({{ calculatedPayroll.active_staff_count }} staff)</span>
                  </span>
                  <span style="font-size:12px; color:#8B5CF6;">{{ currency(calculatedPayroll.service_charge_share) }}</span>
                </div>
                <div style="display:flex; justify-content:space-between;">
                  <span style="font-size:12px; color:#64748B;">Tips</span>
                  <span style="font-size:12px; color:#10B981;">{{ currency(calculatedPayroll.tips) }}</span>
                </div>
                <div v-if="calculatedPayroll.bonus > 0" style="display:flex; justify-content:space-between;">
                  <span style="font-size:12px; color:#64748B;">Bonus</span>
                  <span style="font-size:12px; color:#3B82F6;">{{ currency(calculatedPayroll.bonus) }}</span>
                </div>
                <div v-if="calculatedPayroll.deductions > 0" style="display:flex; justify-content:space-between;">
                  <span style="font-size:12px; color:#64748B;">Deductions</span>
                  <span style="font-size:12px; color:#EF4444;">-Rs. {{ fmt(calculatedPayroll.deductions) }}</span>
                </div>
                <div style="display:flex; justify-content:space-between; padding-top:8px;
                            border-top:1px solid #252B38; margin-top:4px;">
                  <span style="font-size:14px; font-weight:700; color:#F1F5F9;">Net Pay</span>
                  <span style="font-size:20px; font-weight:700; color:#F59E0B;">
                    Rs. {{ fmt(calculatedPayroll.net_pay) }}
                  </span>
                </div>
              </div>
            </div>

          </div>

          <div style="padding:14px 20px; border-top:1px solid #252B38; display:flex; gap:8px;">
            <button @click="showPayrollModal = false"
              style="flex:1; padding:11px; background:transparent; color:#64748B;
                     border:1px solid #252B38; border-radius:8px; cursor:pointer; font-size:13px;">
              Cancel
            </button>
            <button @click="calculatePayroll" :disabled="calculatingPayroll"
              style="flex:2; padding:11px; background:#F59E0B; color:#000; border:none;
                     border-radius:8px; cursor:pointer; font-weight:700; font-size:14px;"
              :style="{ opacity: calculatingPayroll ? '0.6' : '1' }">
              {{ calculatingPayroll ? 'Calculating...' : '⚡ Calculate Payroll' }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- ══ LEAVE MODAL ══ -->
    <Teleport to="body">
      <div v-if="showLeaveModal"
        style="position:fixed; inset:0; background:rgba(0,0,0,0.65); z-index:100;
               display:flex; align-items:center; justify-content:center; padding:16px;"
        @click.self="showLeaveModal = false">
        <div style="background:#1A1E28; border:1px solid #252B38; border-radius:16px;
                    width:480px; max-height:90vh; display:flex; flex-direction:column; overflow:hidden;">

          <div style="padding:16px 20px; border-bottom:1px solid #252B38;
                      display:flex; align-items:center; justify-content:space-between; flex-shrink:0;">
            <div style="font-size:16px; font-weight:700; color:#F1F5F9;">
              🗓️ {{ editingLeave ? 'Edit Leave' : 'Add Leave Request' }}
            </div>
            <button @click="showLeaveModal = false"
              style="background:none; border:none; color:#64748B; font-size:20px; cursor:pointer;">×</button>
          </div>

          <div style="flex:1; overflow-y:auto; padding:18px 20px;">
            <div style="display:flex; flex-direction:column; gap:14px;">

              <!-- Staff selection -->
              <div>
                <label style="font-size:11px; color:#64748B; display:block; margin-bottom:5px;
                               text-transform:uppercase; letter-spacing:0.05em;">Staff Member *</label>
                <select v-model="leaveForm.user_id"
                  style="width:100%; padding:9px 12px; background:#12151C; border:1px solid #252B38;
                         border-radius:8px; color:#F1F5F9; font-size:14px; outline:none;">
                  <option value="">Select staff member</option>
                  <option v-for="s in staffList.filter(st => st.is_active)" :key="s.id" :value="s.id">
                    {{ s.name }} ({{ s.role_label }})
                  </option>
                </select>
              </div>

              <!-- Leave type + dates -->
              <div style="display:flex; gap:12px;">
                <div style="flex:1;">
                  <label style="font-size:11px; color:#64748B; display:block; margin-bottom:5px;
                                 text-transform:uppercase; letter-spacing:0.05em;">Leave Type *</label>
                  <select v-model="leaveForm.type"
                    style="width:100%; padding:9px 12px; background:#12151C; border:1px solid #252B38;
                           border-radius:8px; color:#F1F5F9; font-size:14px; outline:none;">
                    <option value="sick">Sick Leave</option>
                    <option value="annual">Annual Leave</option>
                    <option value="unpaid">Unpaid Leave</option>
                    <option value="other">Other</option>
                  </select>
                </div>
                <div style="flex:1;">
                  <label style="font-size:11px; color:#64748B; display:block; margin-bottom:5px;
                                 text-transform:uppercase; letter-spacing:0.05em;">Duration</label>
                  <div style="background:#12151C; border:1px solid #252B38; border-radius:8px; padding:9px 12px;
                           font-size:12px; color:#10B981; font-weight:600; text-align:center;">
                    {{ calculatedDays }} day{{ calculatedDays > 1 ? 's' : '' }}
                  </div>
                </div>
              </div>

              <!-- Date range -->
              <div style="display:flex; gap:12px;">
                <div style="flex:1;">
                  <label style="font-size:11px; color:#64748B; display:block; margin-bottom:5px;
                                 text-transform:uppercase; letter-spacing:0.05em;">From Date *</label>
                  <input v-model="leaveForm.from_date" type="date"
                    style="width:100%; padding:9px 12px; background:#12151C; border:1px solid #252B38;
                           border-radius:8px; color:#F1F5F9; font-size:14px; outline:none;"
                    @focus="e => e.target.style.borderColor='#F59E0B'"
                    @blur="e => e.target.style.borderColor='#252B38'" />
                </div>
                <div style="flex:1;">
                  <label style="font-size:11px; color:#64748B; display:block; margin-bottom:5px;
                                 text-transform:uppercase; letter-spacing:0.05em;">To Date *</label>
                  <input v-model="leaveForm.to_date" type="date"
                    style="width:100%; padding:9px 12px; background:#12151C; border:1px solid #252B38;
                           border-radius:8px; color:#F1F5F9; font-size:14px; outline:none;"
                    @focus="e => e.target.style.borderColor='#F59E0B'"
                    @blur="e => e.target.style.borderColor='#252B38'" />
                </div>
              </div>

              <!-- Reason -->
              <div>
                <label style="font-size:11px; color:#64748B; display:block; margin-bottom:5px;
                               text-transform:uppercase; letter-spacing:0.05em;">Reason</label>
                <textarea v-model="leaveForm.reason" rows="3" placeholder="Optional reason for leave..."
                  style="width:100%; padding:9px 12px; background:#12151C; border:1px solid #252B38;
                         border-radius:8px; color:#F1F5F9; font-size:14px; resize:none;
                         font-family:inherit; outline:none;"
                  @focus="e => e.target.style.borderColor='#F59E0B'"
                  @blur="e => e.target.style.borderColor='#252B38'"></textarea>
              </div>

              <!-- Error -->
              <div v-if="leaveFormError"
                style="padding:10px 14px; background:rgba(239,68,68,0.08);
                       border:1px solid rgba(239,68,68,0.2); border-radius:8px; color:#EF4444; font-size:13px;">
                ⚠️ {{ leaveFormError }}
              </div>
            </div>
          </div>

          <div style="padding:14px 20px; border-top:1px solid #252B38;
                      display:flex; gap:8px; flex-shrink:0;">
            <button @click="showLeaveModal = false"
              style="flex:1; padding:11px; background:transparent; color:#64748B;
                     border:1px solid #252B38; border-radius:8px; cursor:pointer; font-size:13px;">
              Cancel
            </button>
            <button @click="saveLeave" :disabled="savingLeave"
              style="flex:2; padding:11px; background:#10B981; color:#fff; border:none;
                     border-radius:8px; cursor:pointer; font-weight:700; font-size:14px;"
              :style="{ opacity: savingLeave ? '0.6' : '1' }">
              {{ savingLeave ? 'Saving...' : (editingLeave ? 'Update Leave' : 'Add Leave Request') }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- ══ SERVICE CHARGE DISTRIBUTION MODAL ══ -->
    <Teleport to="body">
      <div v-if="showServiceChargeModal"
        style="position:fixed; inset:0; background:rgba(0,0,0,0.65); z-index:120;
               display:flex; align-items:center; justify-content:center; padding:16px;"
        @click.self="showServiceChargeModal = false">
        <div style="background:#1A1E28; border:1px solid #252B38; border-radius:16px;
                    width:680px; max-height:90vh; display:flex; flex-direction:column; overflow:hidden;">

          <div style="padding:16px 20px; border-bottom:1px solid #252B38;
                      display:flex; align-items:center; justify-content:space-between; flex-shrink:0;">
            <div style="font-size:16px; font-weight:700; color:#F1F5F9;">
              💰 Service Charge Distribution — {{ currentMonthLabel }}
            </div>
            <button @click="showServiceChargeModal = false"
              style="background:none; border:none; color:#64748B; font-size:20px; cursor:pointer;">×</button>
          </div>

          <div style="flex:1; overflow-y:auto; padding:18px 20px;">

            <!-- Summary -->
            <div style="background:#12151C; border-radius:10px; padding:16px; margin-bottom:16px;">
              <div style="font-size:12px; font-weight:700; color:#F1F5F9; margin-bottom:12px;">
                Distribution Summary
              </div>
              <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:12px;">
                <div style="text-align:center;">
                  <div style="font-size:20px; font-weight:700; color:#F59E0B;">
                    Rs. {{ fmt(serviceChargeData.total_collected) }}
                  </div>
                  <div style="font-size:11px; color:#64748B;">Total Collected</div>
                </div>
                <div style="text-align:center;">
                  <div style="font-size:20px; font-weight:700; color:#3B82F6;">
                    {{ serviceChargeData.active_staff_count }}
                  </div>
                  <div style="font-size:11px; color:#64748B;">Active Staff</div>
                </div>
                <div style="text-align:center;">
                  <div style="font-size:20px; font-weight:700; color:#10B981;">
                    Rs. {{ fmt(serviceChargeData.equal_share) }}
                  </div>
                  <div style="font-size:11px; color:#64748B;">Equal Share</div>
                </div>
              </div>
            </div>

            <!-- Distribution method -->
            <div style="margin-bottom:16px;">
              <div style="font-size:12px; font-weight:700; color:#F1F5F9; margin-bottom:8px;">
                Distribution Method
              </div>
              <div style="display:flex; gap:12px;">
                <label style="flex:1; display:flex; align-items:center; gap:8px; padding:12px;
                              background:#12151C; border:1px solid #252B38; border-radius:8px; cursor:pointer;
                              font-size:13px; color:#F1F5F9;">
                  <input type="radio" v-model="distributionMethod" value="equal" 
                         style="accent-color:#F59E0B;">
                  <span>Equal Split (Default)</span>
                </label>
                <label style="flex:1; display:flex; align-items:center; gap:8px; padding:12px;
                              background:#12151C; border:1px solid #252B38; border-radius:8px; cursor:pointer;
                              font-size:13px; color:#F1F5F9;">
                  <input type="radio" v-model="distributionMethod" value="custom" 
                         style="accent-color:#F59E0B;">
                  <span>Custom Percentages</span>
                </label>
              </div>
            </div>

            <!-- Staff distribution list -->
            <div style="background:#1A1E28; border:1px solid #252B38; border-radius:12px; overflow:hidden;">
              <div style="padding:10px 14px; background:#12151C; border-bottom:1px solid #252B38;
                          font-size:11px; color:#64748B; font-weight:600; text-transform:uppercase; letter-spacing:0.05em;">
                <div style="display:grid; grid-template-columns:2fr 1fr 1fr 1fr 80px; gap:8px; align-items:center;">
                  <div>Staff Member</div>
                  <div style="text-align:center;">Days Worked</div>
                  <div style="text-align:center;">Percentage</div>
                  <div style="text-align:right;">Amount</div>
                  <div></div>
                </div>
              </div>

              <div style="max-height:300px; overflow-y:auto;">
                <div v-for="staff in serviceChargeData.staff_distribution" :key="staff.id"
                  style="padding:12px 14px; border-bottom:1px solid #1A1E28;">
                  <div style="display:grid; grid-template-columns:2fr 1fr 1fr 1fr 80px; gap:8px; align-items:center;">
                    <div>
                      <div style="font-size:13px; font-weight:600; color:#F1F5F9;">{{ staff.name }}</div>
                      <div style="font-size:11px; color:#64748B;">{{ staff.role_label }}</div>
                    </div>
                    <div style="text-align:center; font-size:13px; color:#F1F5F9;">
                      {{ staff.days_worked }}
                    </div>
                    <div style="text-align:center;">
                      <input v-if="distributionMethod === 'custom'"
                             v-model.number="staff.custom_percentage" 
                             type="number" step="0.5" min="0" max="100"
                             style="width:70px; padding:4px 6px; background:#252B38; border:1px solid #334155;
                                    border-radius:4px; color:#F1F5F9; font-size:12px; text-align:center; outline:none;">
                      <span v-else style="font-size:13px; color:#8B5CF6; font-weight:600;">
                        {{ staff.percentage }}%
                      </span>
                    </div>
                    <div style="text-align:right; font-size:14px; font-weight:700; color:#10B981;">
                      Rs. {{ fmt(staff.amount) }}
                    </div>
                    <div style="text-align:center;">
                      <button v-if="distributionMethod === 'custom'"
                              @click="resetStaffPercentage(staff)"
                              style="padding:4px 8px; background:rgba(100,116,139,0.1); color:#64748B;
                                     border:1px solid rgba(100,116,139,0.2); border-radius:4px;
                                     font-size:11px; cursor:pointer;">
                        Reset
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Total row -->
              <div style="padding:12px 14px; background:#12151C; border-top:1px solid #252B38;">
                <div style="display:grid; grid-template-columns:2fr 1fr 1fr 1fr 80px; gap:8px; align-items:center;">
                  <div style="font-size:13px; font-weight:700; color:#F1F5F9;">Total</div>
                  <div></div>
                  <div style="text-align:center; font-size:13px; font-weight:700; color:#8B5CF6;">
                    {{ totalPercentage.toFixed(1) }}%
                  </div>
                  <div style="text-align:right; font-size:16px; font-weight:700; color:#10B981;">
                    Rs. {{ fmt(totalDistributed) }}
                  </div>
                  <div></div>
                </div>
              </div>
            </div>

            <!-- Quick bonus -->
            <div style="margin-bottom:16px;">
              <div style="font-size:12px; font-weight:700; color:#F1F5F9; margin-bottom:8px;">
                Quick Bonus Distribution
              </div>
              <div style="display:flex; gap:8px; align-items:center;">
                <input v-model.number="quickBonusAmount" type="number" step="0.01" placeholder="Amount"
                       style="flex:1; max-width:200px; padding:8px 12px; background:#12151C; border:1px solid #252B38;
                              border-radius:8px; color:#10B981; font-size:14px; font-weight:600; outline:none;">
                <select v-model="selectedBonusStaff" 
                        style="flex:2; padding:8px 12px; background:#12151C; border:1px solid #252B38;
                               border-radius:8px; color:#F1F5F9; font-size:14px; outline:none;">
                  <option value="">Select staff for bonus...</option>
                  <option v-for="s in staffList.filter(st => st.is_active)" :key="s.id" :value="s.id">
                    {{ s.name }} ({{ s.role_label }})
                  </option>
                </select>
                <button @click="addQuickBonus" :disabled="!quickBonusAmount || !selectedBonusStaff"
                        style="padding:8px 16px; background:#3B82F6; color:#fff; border:none;
                               border-radius:8px; font-size:13px; font-weight:600; cursor:pointer;">
                  Add Bonus
                </button>
              </div>
            </div>

          </div>

          <div style="padding:14px 20px; border-top:1px solid #252B38;
                      display:flex; gap:8px; flex-shrink:0;">
            <button @click="showServiceChargeModal = false"
              style="flex:1; padding:11px; background:transparent; color:#64748B;
                     border:1px solid #252B38; border-radius:8px; cursor:pointer; font-size:13px;">
              Cancel
            </button>
            <button @click="saveServiceChargeDistribution" :disabled="savingDistribution"
              style="flex:2; padding:11px; background:#F59E0B; color:#000; border:none;
                     border-radius:8px; cursor:pointer; font-weight:700; font-size:14px;"
              :style="{ opacity: savingDistribution ? '0.6' : '1' }">
              {{ savingDistribution ? 'Saving...' : '💾 Save Distribution' }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Toast -->
    <Teleport to="body">
      <div v-if="toast.show"
        style="position:fixed; bottom:24px; right:24px; z-index:200;
               padding:12px 20px; border-radius:9px; font-weight:600; font-size:13px;
               display:flex; align-items:center; gap:8px;"
        :style="{ background: toast.type === 'success' ? '#10B981' : '#EF4444', color: '#fff' }">
        {{ toast.type === 'success' ? '✅' : '⚠️' }} {{ toast.message }}
      </div>
    </Teleport>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import axios from 'axios'

// ── Tabs ──────────────────────────────────────────────
const tabs = [
  { value: 'overview', label: '📊 Overview'  },
  { value: 'staff',    label: '👥 Staff'     },
  { value: 'payroll',  label: '💰 Payroll'   },
  { value: 'leave',    label: '🗓️ Leave'    },
]
const activeTab = ref('overview')

// ── State ──────────────────────────────────────────────
const staffList      = ref([])
const overview       = ref(null)
const allLeaves      = ref([])
const payrollList    = ref([])
const detailStaff    = ref(null)
const detailData     = ref(null)
const filterRole     = ref('')
const searchQuery    = ref('')
const saving         = ref(false)
const formError      = ref('')
const showStaffModal = ref(false)
const showPayrollModal = ref(false)
const editingStaff   = ref(null)
const payrollTarget  = ref(null)
const calculatedPayroll = ref(null)
const calculatingPayroll = ref(false)
const toast          = ref({ show: false, message: '', type: 'success' })

// ── Leave modal state ─────────────────────────────────────
const showLeaveModal = ref(false)
const editingLeave   = ref(null)
const savingLeave   = ref(false)
const leaveFormError = ref('')

// ── Service charge modal state ───────────────────────────
const showServiceChargeModal = ref(false)
const savingDistribution = ref(false)
const distributionMethod = ref('equal')
const serviceChargeData = ref({
  total_collected: 0,
  active_staff_count: 0,
  equal_share: 0,
  staff_distribution: []
})
const quickBonusAmount = ref(0)
const selectedBonusStaff = ref('')

// ── Leave form ─────────────────────────────────────────
const defaultLeaveForm = () => ({
  user_id: '',
  type: 'sick',
  from_date: '',
  to_date: '',
  reason: '',
})

const leaveForm = ref(defaultLeaveForm())

// ── Payroll state ─────────────────────────────────────
const now            = new Date()
const payrollMonth   = ref(now.getMonth() + 1)
const payrollYear    = ref(now.getFullYear())
const monthServiceCharge = ref(0)
const perStaffServiceCharge = ref(0)

// ── Forms ─────────────────────────────────────────────
const defaultStaffForm = () => ({
  name: '', pin: '', role: 'waiter', phone: '', email: '',
  address: '', date_of_birth: '', join_date: '',
  salary_type: 'monthly', base_salary: 0, hourly_rate: 0,
  service_charge_pct: 0, bank_name: '', bank_account: '', notes: '',
})

const defaultPayrollForm = () => ({
  month: now.getMonth() + 1,
  year:  now.getFullYear(),
  bonus: 0,
  deductions: 0,
  notes: '',
})

const staffForm   = ref(defaultStaffForm())
const payrollForm = ref(defaultPayrollForm())

// ── Static data ───────────────────────────────────────
const roles = [
  { value: 'admin',     label: 'Administrator' },
  { value: 'manager',   label: 'Manager'       },
  { value: 'cashier',   label: 'Cashier'       },
  { value: 'waiter',    label: 'Waiter'        },
  { value: 'kitchen',   label: 'Kitchen Staff' },
  { value: 'bartender', label: 'Bartender'     },
  { value: 'delivery',  label: 'Delivery'      },
]

const months = [
  { value: 1,  label: 'January'   }, { value: 2,  label: 'February'  },
  { value: 3,  label: 'March'     }, { value: 4,  label: 'April'     },
  { value: 5,  label: 'May'       }, { value: 6,  label: 'June'      },
  { value: 7,  label: 'July'      }, { value: 8,  label: 'August'    },
  { value: 9,  label: 'September' }, { value: 10, label: 'October'   },
  { value: 11, label: 'November'  }, { value: 12, label: 'December'  },
]

const years = Array.from({ length: 5 }, (_, i) => now.getFullYear() - i)

const today = new Date().toLocaleDateString('en-US', { weekday:'long', year:'numeric', month:'long', day:'numeric' })

const currentMonthLabel = months.find(m => m.value === now.getMonth() + 1)?.label + ' ' + now.getFullYear()

// ── Computed ───────────────────────────────────────────
const overviewCards = computed(() => [
  {
    label: 'Total Staff',
    value: overview.value?.total_staff ?? 0,
    color: '#3B82F6',
    sub:   `${overview.value?.on_shift_today ?? 0} on shift today`,
  },
  {
    label: 'On Shift Now',
    value: overview.value?.on_shift_today ?? 0,
    color: '#10B981',
  },
  {
    label: 'Leave Pending',
    value: overview.value?.pending_leave ?? 0,
    color: '#F59E0B',
    sub:   'awaiting approval',
  },
  {
    label: 'Payroll Draft',
    value: overview.value?.pending_payroll ?? 0,
    color: '#8B5CF6',
    sub:   'not yet approved',
  },
])

const payrollTotals = computed(() => ({
  base:       payrollList.value.reduce((s, p) => s + parseFloat(p.base_salary), 0),
  service:    payrollList.value.reduce((s, p) => s + parseFloat(p.service_charge_share), 0),
  tips:       payrollList.value.reduce((s, p) => s + parseFloat(p.tips), 0),
  bonus:      payrollList.value.reduce((s, p) => s + parseFloat(p.bonus), 0),
  deductions: payrollList.value.reduce((s, p) => s + parseFloat(p.deductions), 0),
  net:        payrollList.value.reduce((s, p) => s + parseFloat(p.net_pay), 0),
}))

// ── Leave modal computed ───────────────────────────────────
const calculatedDays = computed(() => {
  if (!leaveForm.value.from_date || !leaveForm.value.to_date) return 0
  const from = new Date(leaveForm.value.from_date)
  const to = new Date(leaveForm.value.to_date)
  const diffTime = Math.abs(to - from)
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
  return diffDays + 1
})

// ── Service charge computed ───────────────────────────────────
const totalPercentage = computed(() => {
  return serviceChargeData.value.staff_distribution.reduce((sum, staff) => {
    return sum + (staff.custom_percentage || staff.percentage || 0)
  }, 0)
})

const totalDistributed = computed(() => {
  return serviceChargeData.value.staff_distribution.reduce((sum, staff) => {
    return sum + (staff.amount || 0)
  }, 0)
})

// ── Methods ────────────────────────────────────────────
function fmt(n) {
  const num = parseFloat(n ?? 0)
  return isNaN(num) ? '0.00' : num.toFixed(2)
}

function currency(n) {
  return `Rs. ${fmt(n)}`
}

function initials(name) {
  if (!name) return '?'
  return name.trim().split(' ').map(p => p[0]).join('').substring(0, 2).toUpperCase()
}

function roleColor(role) {
  return {
    admin:     '#F59E0B', manager:   '#EF4444', cashier: '#3B82F6',
    waiter:    '#10B981', kitchen:   '#8B5CF6', bartender: '#F97316',
    delivery:  '#06B6D4',
  }[role] ?? '#64748B'
}

// ── Data loading ──────────────────────────────────────
async function loadOverview() {
  try {
    const { data } = await axios.get('/staff/overview')
    overview.value = data
  } catch(e) { console.error(e) }
}

async function loadStaff() {
  try {
    const { data } = await axios.get('/staff', {
      params: { role: filterRole.value, search: searchQuery.value }
    })
    staffList.value = data
  } catch(e) { console.error(e) }
}

async function loadLeaves() {
  try {
    // Load all leaves across all staff
    const { data } = await axios.get('/staff/leaves')
    allLeaves.value = data
  } catch(e) { console.error(e) }
}

async function loadPayrollList() {
  try {
    const { data } = await axios.get('/staff/payroll-list', {
      params: { month: payrollMonth.value, year: payrollYear.value }
    })
    payrollList.value        = data.payrolls
    monthServiceCharge.value = data.service_charge_total
    perStaffServiceCharge.value = data.per_staff_share
  } catch(e) { console.error(e) }
}

// ── Staff actions ─────────────────────────────────────
function openStaffModal(staff) {
  editingStaff.value = staff
  formError.value    = ''
  staffForm.value = staff ? {
    name:               staff.name,
    pin:                '',
    role:               staff.role,
    phone:              staff.phone ?? '',
    email:              staff.email ?? '',
    address:            staff.address ?? '',
    date_of_birth:      staff.date_of_birth ?? '',
    join_date:          staff.join_date ?? '',
    salary_type:        staff.salary_type ?? 'monthly',
    base_salary:        parseFloat(staff.base_salary ?? 0),
    hourly_rate:        parseFloat(staff.hourly_rate ?? 0),
    service_charge_pct: parseFloat(staff.service_charge_pct ?? 0),
    bank_name:          staff.bank_name ?? '',
    bank_account:       staff.bank_account ?? '',
    notes:              staff.notes ?? '',
  } : defaultStaffForm()
  showStaffModal.value = true
}

async function saveStaff() {
  formError.value = ''
  if (!staffForm.value.name.trim()) { formError.value = 'Name is required'; return }
  if (!editingStaff.value && !staffForm.value.pin) { formError.value = 'PIN is required'; return }

  saving.value = true
  try {
    const payload = { ...staffForm.value }
    if (!payload.pin) delete payload.pin

    if (editingStaff.value) {
      await axios.put(`/staff/${editingStaff.value.id}`, payload)
      showToast('Staff updated ✓')
    } else {
      await axios.post('/staff', payload)
      showToast('Staff added ✓')
    }
    showStaffModal.value = false
    await loadStaff()
    await loadOverview()
  } catch(e) {
    formError.value = e.response?.data?.message ?? 'Failed to save'
  } finally {
    saving.value = false
  }
}

async function toggleActive(staff) {
  try {
    const { data } = await axios.patch(`/staff/${staff.id}/toggle-active`)
    staff.is_active = data.is_active
    showToast(data.is_active ? 'Staff activated' : 'Staff deactivated')
    await loadOverview()
  } catch(e) {
    showToast('Failed to update', 'error')
  }
}

async function openDetail(staff) {
  detailStaff.value = staff
  try {
    const { data } = await axios.get(`/staff/${staff.id}`)
    detailData.value = data
  } catch(e) { console.error(e) }
}

// ── Payroll actions ───────────────────────────────────
function openPayrollModal(staff) {
  payrollTarget.value     = staff
  calculatedPayroll.value = null
  payrollForm.value       = defaultPayrollForm()
  showPayrollModal.value  = true
}

async function calculatePayroll() {
  calculatingPayroll.value = true
  try {
    const { data } = await axios.post(
      `/staff/${payrollTarget.value.id}/calculate-payroll`,
      payrollForm.value
    )
    calculatedPayroll.value = data
    showToast('Payroll calculated ✓')
    await loadPayrollList()
  } catch(e) {
    showToast(e.response?.data?.message ?? 'Calculation failed', 'error')
  } finally {
    calculatingPayroll.value = false
  }
}

async function generateAllPayrolls() {
  if (!confirm(`Calculate payroll for ALL staff for ${months.find(m => m.value === payrollMonth.value)?.label} ${payrollYear.value}?`)) return
  try {
    const { data } = await axios.post('/staff/generate-all-payrolls', {
      month: payrollMonth.value,
      year:  payrollYear.value,
    })
    showToast(`Payroll generated for ${data.count} staff`)
    await loadPayrollList()
  } catch(e) {
    showToast(e.response?.data?.message ?? 'Failed', 'error')
  }
}

async function updatePayrollStatus(payroll, status) {
  try {
    await axios.patch(`/staff/${payroll.user_id}/payroll/${payroll.id}`, { status })
    payroll.status = status
    showToast(`Payroll marked as ${status}`)
  } catch(e) {
    showToast('Failed to update', 'error')
  }
}

// ── Leave actions ─────────────────────────────────────
function openLeaveModal(leave) {
  editingLeave.value = leave
  leaveFormError.value = ''
  leaveForm.value = leave ? {
    user_id: leave.user_id,
    type: leave.type,
    from_date: leave.from_date,
    to_date: leave.to_date,
    reason: leave.reason,
  } : defaultLeaveForm()
  showLeaveModal.value = true
}

async function saveLeave() {
  leaveFormError.value = ''
  if (!leaveForm.value.user_id) { leaveFormError.value = 'Please select a staff member'; return }
  if (!leaveForm.value.from_date || !leaveForm.value.to_date) { leaveFormError.value = 'Both dates are required'; return }
  if (new Date(leaveForm.value.to_date) < new Date(leaveForm.value.from_date)) { leaveFormError.value = 'To date must be after from date'; return }

  savingLeave.value = true
  try {
    if (editingLeave.value) {
      // Update existing leave (not implemented in backend yet)
      showToast('Leave updated ✓')
    } else {
      // Create new leave request
      await axios.post(`/staff/${leaveForm.value.user_id}/leave`, leaveForm.value)
      showToast('Leave request submitted ✓')
    }
    showLeaveModal.value = false
    await loadLeaves()
    await loadOverview()
  } catch(e) {
    leaveFormError.value = e.response?.data?.message ?? 'Failed to save leave'
  } finally {
    savingLeave.value = false
  }
}

async function updateLeaveStatus(leave, status) {
  try {
    await axios.patch(`/staff/${leave.user_id}/leave/${leave.id}`, { status })
    leave.status = status
    showToast(`Leave ${status}`)
    await loadOverview()
  } catch(e) {
    showToast('Failed to update', 'error')
  }
}

// ── Service charge actions ───────────────────────────────────
function openServiceChargeDistribution() {
  distributionMethod.value = 'equal'
  quickBonusAmount.value = 0
  selectedBonusStaff.value = ''
  
  // Calculate service charge distribution
  const totalCollected = monthServiceCharge.value || 0
  const activeStaff = staffList.value.filter(s => s.is_active)
  const equalShare = activeStaff.length > 0 ? totalCollected / activeStaff.length : 0
  
  serviceChargeData.value = {
    total_collected: totalCollected,
    active_staff_count: activeStaff.length,
    equal_share: equalShare,
    staff_distribution: activeStaff.map(staff => {
      // Calculate days worked from existing payroll or estimate
      const existingPayroll = payrollList.value.find(p => p.user_id === staff.id)
      const daysWorked = existingPayroll?.days_worked || 0
      
      return {
        id: staff.id,
        name: staff.name,
        role_label: staff.role_label,
        days_worked: daysWorked,
        percentage: parseFloat((100 / activeStaff.length).toFixed(2)),
        custom_percentage: 0,
        amount: staff.service_charge_pct > 0 
          ? totalCollected * (staff.service_charge_pct / 100)
          : equalShare
      }
    })
  }
  
  showServiceChargeModal.value = true
}

async function addQuickBonus() {
  if (!quickBonusAmount.value || !selectedBonusStaff.value) return
  
  try {
    // Find current month payroll for selected staff and update with bonus
    const staff = staffList.value.find(s => s.id === selectedBonusStaff.value)
    if (staff) {
      payrollForm.value.bonus = quickBonusAmount.value
      payrollTarget.value = staff
      await calculatePayroll()
      showToast(`Bonus of ${currency(quickBonusAmount.value)} added to ${staff.name}`)
      quickBonusAmount.value = 0
      selectedBonusStaff.value = ''
    }
  } catch(e) {
    showToast('Failed to add bonus', 'error')
  }
}

function resetStaffPercentage(staff) {
  staff.custom_percentage = 0
  // Recalculate amount based on equal split
  const equalShare = serviceChargeData.value.equal_share
  staff.amount = equalShare
}

async function saveServiceChargeDistribution() {
  savingDistribution.value = true
  try {
    // Create or update payroll records with service charge distribution
    const distributionPromises = serviceChargeData.value.staff_distribution.map(async (staff) => {
      // Calculate payroll for this staff member with service charge
      const payrollData = {
        year: payrollYear.value,
        month: payrollMonth.value,
        bonus: 0,
        deductions: 0,
        notes: `Service charge distribution: ${staff.percentage}% (${currency(staff.amount)})`,
      }

      const response = await axios.post(
        `/staff/${staff.id}/calculate-payroll`,
        payrollData
      )
      
      return response.data
    })

    await Promise.all(distributionPromises)
    
    showToast('Service charge distributed to all staff ✓')
    showServiceChargeModal.value = false
    
    // Refresh payroll list and overview
    await loadPayrollList()
    await loadOverview()
  } catch(e) {
    showToast('Failed to distribute service charge', 'error')
  } finally {
    savingDistribution.value = false
  }
}

// ── Toast ─────────────────────────────────────────────
function showToast(message, type = 'success') {
  toast.value = { show: true, message, type }
  setTimeout(() => { toast.value.show = false }, 3000)
}

// ── Watchers ───────────────────────────────────────────
watch([payrollMonth, payrollYear], () => {
  loadPayrollList()
  // Clear service charge data when month/year changes to force recalculation
  serviceChargeData.value = null
})

// ── Init ──────────────────────────────────────────────
onMounted(async () => {
  await Promise.all([
    loadOverview(),
    loadStaff(),
    loadLeaves(),
    loadPayrollList(),
  ])
})
</script>