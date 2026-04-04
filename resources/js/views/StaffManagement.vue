<template>
  <div class="sms-root">

    <!-- ══ HEADER ══ -->
    <header class="sms-header">
      <div class="sms-header-inner">
        <div class="sms-logo">
          <span class="sms-logo-icon">👥</span>
          <span class="sms-logo-title">Staff Management</span>
        </div>
        <button class="sms-btn-primary" @click="openStaffModal(null)">
          <span>＋</span> Add Staff
        </button>
      </div>
    </header>

    <!-- ══ TABS ══ -->
    <nav class="sms-tabs">
      <button
        v-for="tab in tabs" :key="tab.value"
        :class="['sms-tab', { active: activeTab === tab.value }]"
        @click="activeTab = tab.value"
      >
        <span class="sms-tab-icon">{{ tab.icon }}</span>
        <span class="sms-tab-label">{{ tab.label }}</span>
      </button>
    </nav>

    <!-- ══ MAIN ══ -->
    <main class="sms-main">

      <!-- ── OVERVIEW ── -->
      <section v-if="activeTab === 'overview'" class="sms-section">
        <div class="sms-stats-grid">
          <div v-for="c in overviewCards" :key="c.label" :class="['sms-stat', c.accent]">
            <div class="sms-stat-label">{{ c.label }}</div>
            <div class="sms-stat-value">{{ c.value }}</div>
            <div v-if="c.sub" class="sms-stat-sub">{{ c.sub }}</div>
          </div>
        </div>

        <!-- Service Charge Card -->
        <div class="sms-sc-card">
          <div class="sms-sc-header">
            <span class="sms-sc-title">💰 Service Charge — {{ currentMonthLabel }}</span>
            <div class="sms-sc-actions">
              <button class="sms-btn-sm blue" @click="openServiceChargeModal">⚙️ Configure & Distribute</button>
            </div>
          </div>
          <div class="sms-sc-stats">
            <div class="sms-sc-stat">
              <div class="sms-sc-stat-label">Total Collected</div>
              <div class="sms-sc-stat-value amber">{{ currency(serviceChargeTotal) }}</div>
              <div class="sms-sc-stat-sub">From table orders (10% tax)</div>
            </div>
            <div class="sms-sc-stat">
              <div class="sms-sc-stat-label">Active Staff</div>
              <div class="sms-sc-stat-value blue">{{ overview.active_staff_month ?? activeStaffCount }}</div>
              <div class="sms-sc-stat-sub">Worked this month</div>
            </div>
            <div class="sms-sc-stat">
              <div class="sms-sc-stat-label">Per Staff (Equal)</div>
              <div class="sms-sc-stat-value green">{{ currency(perStaffShare) }}</div>
              <div class="sms-sc-stat-sub">Equal distribution</div>
            </div>
          </div>
        </div>

        <!-- Quick nav -->
        <div class="sms-quick-nav">
          <button class="sms-quick-btn" @click="activeTab = 'payroll'">
            <span>💰</span><span>Payroll</span>
          </button>
          <button class="sms-quick-btn" @click="activeTab = 'advances'">
            <span>💵</span><span>Advances</span>
          </button>
          <button class="sms-quick-btn" @click="activeTab = 'leaves'">
            <span>📅</span><span>Leaves</span>
          </button>
          <button class="sms-quick-btn" @click="activeTab = 'staff'">
            <span>👥</span><span>Staff List</span>
          </button>
        </div>

        <!-- Today shifts -->
        <div class="sms-card" style="margin-top:16px">
          <div class="sms-card-header">
            <span class="sms-section-title">Today's Shifts</span>
            <span style="font-size:12px;color:var(--t3)">{{ overview.on_shift_today ?? todayShifts.length }} on shift</span>
          </div>
          <div v-if="!todayShifts.length" class="sms-empty">No shifts recorded today</div>
          <div v-else class="sms-shift-list">
            <div v-for="shift in todayShifts" :key="shift.id" class="sms-shift-item">
              <div class="sms-avatar sm" :style="{ background: roleColor(shift.role) }">
                {{ initials(shift.name) }}
              </div>
              <div class="sms-shift-info">
                <div class="sms-shift-name">{{ shift.name }}</div>
                <div class="sms-shift-role">{{ shift.role }}</div>
              </div>
              <div class="sms-shift-time">{{ shift.clock_in }} – {{ shift.clock_out ?? '...' }}</div>
              <span :class="['sms-badge', shift.status === 'active' ? 'active' : 'paid']">
                {{ shift.status === 'active' ? '● Active' : '✓ Done' }}
              </span>
            </div>
          </div>
        </div>
      </section>

      <!-- ── STAFF LIST ── -->
      <section v-if="activeTab === 'staff'" class="sms-section">
        <div class="sms-filters">
          <input
            v-model="staffSearch" type="text"
            placeholder="🔍 Search name / phone..."
            class="sms-input" style="flex:1;min-width:160px"
            @input="filterStaffList"
          />
          <select v-model="filterRole" class="sms-select" @change="filterStaffList">
            <option value="">All Roles</option>
            <option v-for="r in roles" :key="r.value" :value="r.value">{{ r.label }}</option>
          </select>
          <select v-model="filterStatus" class="sms-select" @change="filterStaffList">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>

        <div class="sms-staff-grid">
          <div
            v-for="s in filteredStaffList" :key="s.id"
            class="sms-staff-card"
            @click="openDetail(s)"
          >
            <div class="sms-staff-card-top">
              <div class="sms-avatar lg" :style="{ background: s.avatar_url ? 'transparent' : roleColor(s.role) }">
                <img v-if="s.avatar_url" :src="s.avatar_url" :alt="s.name" class="sms-avatar-img" />
                <span v-else>{{ initials(s.name) }}</span>
              </div>
              <span :class="['sms-badge', s.is_active ? 'active' : 'rejected']">
                {{ s.is_active ? 'Active' : 'Inactive' }}
              </span>
            </div>
            <div class="sms-staff-name">{{ s.name }}</div>
            <div class="sms-staff-role">{{ getRoleLabel(s.role) }}</div>
            <div class="sms-staff-meta">{{ s.employee_id }} · {{ s.phone ?? '—' }}</div>
            <div class="sms-staff-salary">{{ currency(s.base_salary) }}
              <span style="font-size:10px;color:var(--t3)">/{{ s.salary_type === 'monthly' ? 'mo' : s.salary_type === 'daily' ? 'day' : 'hr' }}</span>
            </div>
            <div class="sms-staff-actions" @click.stop>
              <button class="sms-btn-xs blue" @click="openStaffModal(s)">✏️ Edit</button>
              <button class="sms-btn-xs amber" @click="openPayrollModal(s)">💰 Payroll</button>
              <button :class="['sms-btn-xs', s.is_active ? 'red' : 'green']" @click="toggleActive(s)">
                {{ s.is_active ? 'Disable' : 'Enable' }}
              </button>
            </div>
          </div>
        </div>
        <div v-if="!filteredStaffList.length" class="sms-empty">
          <div style="font-size:32px;margin-bottom:8px">👥</div>
          No staff found
        </div>
      </section>

      <!-- ── PAYROLL ── -->
      <section v-if="activeTab === 'payroll'" class="sms-section">
        <div class="sms-toolbar">
          <select v-model="payrollMonth" class="sms-select" @change="loadPayrollList">
            <option v-for="m in months" :key="m.value" :value="m.value">{{ m.label }}</option>
          </select>
          <select v-model="payrollYear" class="sms-select" @change="loadPayrollList">
            <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
          </select>
          <button class="sms-btn-primary" @click="generateAllPayrolls" :disabled="generatingAll">
            {{ generatingAll ? '⏳ Generating...' : '⚡ Generate All' }}
          </button>
          <button class="sms-btn-sm blue" @click="openServiceChargeModal">💰 Service Charge</button>
          <div class="sms-sc-info">
            Svc: <strong class="amber">{{ currency(serviceChargeTotal) }}</strong>
            · Per staff: <strong class="green">{{ currency(perStaffShare) }}</strong>
          </div>
        </div>

        <div v-if="!payrollList.length" class="sms-empty">
          <div style="font-size:32px;margin-bottom:8px">💰</div>
          <div style="font-weight:700;margin-bottom:6px;color:var(--t2)">No payrolls for {{ currentPayrollMonthLabel }}</div>
          <div style="font-size:13px;margin-bottom:16px">Click "Generate All" to create payrolls for all active staff</div>
          <button class="sms-btn-primary" @click="generateAllPayrolls" :disabled="generatingAll">
            {{ generatingAll ? '⏳ Generating...' : '⚡ Generate All Payrolls' }}
          </button>
        </div>

        <div v-else class="sms-payroll-list">
          <div v-for="p in payrollList" :key="p.id" class="sms-payroll-card">
            <div class="sms-payroll-head">
              <div class="sms-payroll-staff">
                <div class="sms-avatar sm" :style="{ background: roleColor(p.role) }">
                  {{ initials(p.user_name) }}
                </div>
                <div>
                  <div class="sms-payroll-name">{{ p.user_name }}</div>
                  <div class="sms-payroll-meta">{{ getRoleLabel(p.role) }} · {{ p.days_worked }}d {{ p.hours_worked }}h</div>
                </div>
              </div>
              <div style="display:flex;align-items:center;gap:8px">
                <span v-if="hasActiveAdvance(p.user_id)" class="sms-badge amber">💵 Advance</span>
                <select
                  :value="p.status"
                  @change="updatePayrollStatus(p, $event.target.value)"
                  class="sms-select-sm"
                >
                  <option value="draft">Draft</option>
                  <option value="approved">Approved</option>
                  <option value="paid">Paid</option>
                </select>
              </div>
            </div>

            <div class="sms-payroll-breakdown">
              <div class="sms-bk-row">
                <span>Base Salary</span>
                <input
                  type="number" class="sms-inline-input"
                  v-model.number="p.base_salary"
                  @change="recalcPayroll(p)"
                />
              </div>
              <div class="sms-bk-row positive">
                <span>Service Charge</span>
                <span>+ {{ currency(p.service_charge_share) }}</span>
              </div>
              <div class="sms-bk-row positive">
                <span>Tips</span>
                <span>+ {{ currency(p.tips) }}</span>
              </div>
              <div class="sms-bk-row positive">
                <span>Bonus</span>
                <input
                  type="number" class="sms-inline-input green"
                  v-model.number="p.bonus"
                  @change="recalcPayroll(p)"
                />
              </div>
              <div class="sms-bk-row negative">
                <span>Advance Deduction</span>
                <input
                  type="number" class="sms-inline-input red"
                  v-model.number="p.deductions"
                  @change="recalcPayroll(p)"
                />
              </div>
              <div class="sms-bk-row total">
                <span>Net Pay</span>
                <span>{{ currency(p.net_pay) }}</span>
              </div>
            </div>

            <div class="sms-payroll-actions">
              <button
                v-if="hasActiveAdvance(p.user_id)"
                class="sms-btn-xs amber"
                @click="applyAdvanceDeduction(p)"
              >💵 Apply Advance</button>
              <button class="sms-btn-xs blue" @click="savePayrollChanges(p)">💾 Save</button>
              <button class="sms-btn-xs" style="background:rgba(255,255,255,0.05);color:var(--t2);border-color:var(--border)" @click="downloadPayslip(p)">📄 Payslip</button>
            </div>
          </div>

          <!-- Totals -->
          <div class="sms-payroll-totals">
            <span>Total ({{ payrollList.length }} staff)</span>
            <div class="sms-totals-row">
              <span>Base: <strong>{{ currency(payrollTotals.base) }}</strong></span>
              <span>Svc: <strong class="purple">{{ currency(payrollTotals.service) }}</strong></span>
              <span>Tips: <strong class="green">{{ currency(payrollTotals.tips) }}</strong></span>
              <span>Bonus: <strong class="blue">{{ currency(payrollTotals.bonus) }}</strong></span>
              <span>Deductions: <strong class="red">{{ currency(payrollTotals.deductions) }}</strong></span>
              <span>Net: <strong class="amber" style="font-size:16px">{{ currency(payrollTotals.net) }}</strong></span>
            </div>
          </div>
        </div>
      </section>

      <!-- ── ADVANCES ── -->
      <section v-if="activeTab === 'advances'" class="sms-section">
        <div class="sms-toolbar">
          <button class="sms-btn-primary amber" @click="openAdvanceModal">＋ Add Advance / Loan</button>
          <select v-model="advanceFilter" class="sms-select">
            <option value="">All Employees</option>
            <option v-for="s in staffList" :key="s.id" :value="s.id">{{ s.name }}</option>
          </select>
        </div>

        <div v-if="!filteredAdvances.length" class="sms-empty">
          <div style="font-size:32px;margin-bottom:8px">💵</div>
          No advances recorded
        </div>

        <div class="sms-advances-list">
          <div v-for="adv in filteredAdvances" :key="adv.id" class="sms-advance-card">
            <div class="sms-advance-head">
              <div>
                <div class="sms-advance-name">{{ adv.employee_name }}</div>
                <div class="sms-advance-type">{{ adv.type }} — Issued {{ adv.date }}</div>
              </div>
              <div class="sms-advance-total amber">{{ currency(adv.amount) }}</div>
            </div>
            <div class="sms-advance-body">
              <div class="sms-advance-row">
                <span>Remaining Balance</span>
                <span class="amber"><strong>{{ currency(adv.remaining_balance) }}</strong></span>
              </div>
              <div class="sms-advance-row">
                <span>Monthly Deduction</span>
                <span>{{ currency(adv.monthly_deduction) }}</span>
              </div>
              <div class="sms-advance-row">
                <span>Repaid So Far</span>
                <span class="green">{{ currency(adv.amount - adv.remaining_balance) }}</span>
              </div>
              <div class="sms-progress">
                <div
                  class="sms-progress-fill"
                  :style="{ width: Math.round((adv.amount - adv.remaining_balance) / adv.amount * 100) + '%' }"
                ></div>
              </div>
              <div class="sms-progress-labels">
                <span>{{ Math.round((adv.amount - adv.remaining_balance) / adv.amount * 100) }}% repaid</span>
                <span>{{ Math.ceil(adv.remaining_balance / (adv.monthly_deduction || 1)) }} months left</span>
              </div>
            </div>
            <div style="display:flex;gap:8px;margin-top:10px">
              <button class="sms-btn-xs amber" @click="editAdvance(adv)">✏️ Edit</button>
              <button class="sms-btn-xs red" @click="removeAdvance(adv)">🗑 Remove</button>
            </div>
          </div>
        </div>
      </section>

      <!-- ── LEAVES ── -->
      <section v-if="activeTab === 'leaves'" class="sms-section">
        <div class="sms-leave-balance-grid">
          <div v-for="lb in leaveBalances" :key="lb.type" class="sms-lb-card">
            <div class="sms-lb-type">{{ lb.type }}</div>
            <div class="sms-lb-count" :style="{ color: lb.color }">{{ lb.remaining }}</div>
            <div class="sms-lb-total">/ {{ lb.total }} days</div>
          </div>
        </div>

        <div class="sms-toolbar">
          <button class="sms-btn-primary green" @click="openLeaveModal">＋ Request Leave</button>
          <div class="sms-seg">
            <button :class="['sms-seg-btn', { active: leaveFilter === 'all' }]" @click="leaveFilter = 'all'">All</button>
            <button :class="['sms-seg-btn', { active: leaveFilter === 'pending' }]" @click="leaveFilter = 'pending'">Pending</button>
            <button :class="['sms-seg-btn', { active: leaveFilter === 'approved' }]" @click="leaveFilter = 'approved'">Approved</button>
            <button :class="['sms-seg-btn', { active: leaveFilter === 'rejected' }]" @click="leaveFilter = 'rejected'">Rejected</button>
          </div>
        </div>

        <div v-if="!filteredLeaves.length" class="sms-empty">
          <div style="font-size:32px;margin-bottom:8px">📅</div>
          No leave requests
        </div>

        <div class="sms-leaves-list">
          <div v-for="leave in filteredLeaves" :key="leave.id" class="sms-leave-card">
            <div class="sms-leave-head">
              <div style="display:flex;align-items:center;gap:10px">
                <div class="sms-avatar sm" :style="{ background: roleColor(leave.role) }">
                  {{ initials(leave.user_name) }}
                </div>
                <div>
                  <div class="sms-leave-name">{{ leave.user_name }}
                    <span class="sms-badge paid" style="margin-left:6px">{{ leave.type }}</span>
                  </div>
                  <div class="sms-leave-dates">📅 {{ leave.from_date }} → {{ leave.to_date }}
                    <span style="color:var(--t3)">({{ leave.days }} days)</span>
                  </div>
                  <div v-if="leave.reason" class="sms-leave-reason">📝 {{ leave.reason }}</div>
                </div>
              </div>
              <div>
                <span :class="['sms-badge', leaveBadgeClass(leave.status)]">{{ leave.status }}</span>
              </div>
            </div>
            <div v-if="leave.status === 'pending'" class="sms-leave-actions">
              <button class="sms-btn-xs green" @click="updateLeaveStatus(leave, 'approved')">✅ Approve</button>
              <button class="sms-btn-xs red" @click="updateLeaveStatus(leave, 'rejected')">❌ Reject</button>
            </div>
          </div>
        </div>
      </section>

      <!-- ── HISTORY ── -->
      <section v-if="activeTab === 'history'" class="sms-section">
        <div class="sms-filters">
          <select v-model="historyEmployee" class="sms-select">
            <option value="">All Employees</option>
            <option v-for="s in staffList" :key="s.id" :value="s.id">{{ s.name }}</option>
          </select>
          <div class="sms-seg">
            <button :class="['sms-seg-btn', { active: historyType === '' }]" @click="historyType = ''">All</button>
            <button :class="['sms-seg-btn', { active: historyType === 'Salary' }]" @click="historyType = 'Salary'">Salary</button>
            <button :class="['sms-seg-btn', { active: historyType === 'Bonus' }]" @click="historyType = 'Bonus'">Bonus</button>
            <button :class="['sms-seg-btn', { active: historyType === 'Advance' }]" @click="historyType = 'Advance'">Advance</button>
          </div>
        </div>
        <div v-if="!filteredHistory.length" class="sms-empty">
          <div style="font-size:32px;margin-bottom:8px">📜</div>
          No payment records found
        </div>
        <div class="sms-history-list">
          <div v-for="h in filteredHistory" :key="h.id" class="sms-history-card">
            <div class="sms-hist-icon" :class="h.iconClass">{{ h.icon }}</div>
            <div style="flex:1">
              <div class="sms-hist-name">{{ h.employee_name }}</div>
              <div class="sms-hist-sub">{{ h.type }} · {{ h.method }}</div>
              <div v-if="h.reference" class="sms-hist-ref">Ref: {{ h.reference }}</div>
            </div>
            <div style="text-align:right">
              <div class="sms-hist-amount">{{ currency(h.amount) }}</div>
              <div class="sms-hist-date">{{ h.date }}</div>
              <span v-if="h.status" :class="['sms-badge', h.status.toLowerCase()]">{{ h.status }}</span>
            </div>
          </div>
        </div>
      </section>

      <!-- ── DOCUMENTS ── -->
      <section v-if="activeTab === 'documents'" class="sms-section">
        <div class="sms-docs-grid">
          <div v-for="s in staffList" :key="s.id" class="sms-doc-card">
            <div class="sms-doc-head">
              <div style="display:flex;align-items:center;gap:10px">
                <div class="sms-avatar md" :style="{ background: s.avatar_url ? 'transparent' : roleColor(s.role) }">
                  <img v-if="s.avatar_url" :src="s.avatar_url" :alt="s.name" class="sms-avatar-img" />
                  <span v-else>{{ initials(s.name) }}</span>
                </div>
                <div>
                  <div class="sms-staff-name">{{ s.name }}</div>
                  <div class="sms-staff-role">{{ getRoleLabel(s.role) }}</div>
                </div>
              </div>
              <label class="sms-btn-xs blue" style="cursor:pointer">
                📤 Upload
                <input type="file" accept="image/*" style="display:none" @change="e => uploadProfileImage(s, e)" />
              </label>
            </div>
            <div class="sms-doc-items">
              <div v-if="s.avatar_url" class="sms-doc-item">
                <span>👤</span><span style="flex:1">Profile Photo</span>
                <button class="sms-btn-xs green" @click="viewDocument(s.avatar_url)">View</button>
                <label class="sms-btn-xs amber" style="cursor:pointer">
                  Change
                  <input type="file" accept="image/*" style="display:none" @change="e => uploadProfileImage(s, e)" />
                </label>
              </div>
              <div v-if="!s.avatar_url" class="sms-doc-empty">No profile photo uploaded</div>
            </div>
          </div>
        </div>
      </section>

    </main>

    <!-- ══ STAFF MODAL ══ -->
    <Teleport to="body">
      <Transition name="modal-fade">
        <div v-if="showStaffModal" class="sms-overlay" @click.self="showStaffModal = false">
          <div class="sms-modal" @click.stop>
            <div class="sms-modal-header">
              <div class="sms-modal-title-wrap">
                <div class="sms-modal-icon" style="background:linear-gradient(135deg,var(--purple),var(--blue))">👤</div>
                <span class="sms-modal-title">{{ editingStaff ? 'Edit Staff Member' : 'Add New Staff' }}</span>
              </div>
              <button class="sms-modal-close" @click="showStaffModal = false">✕</button>
            </div>

            <div class="sms-modal-body">
              <!-- Avatar Upload -->
              <div class="sms-avatar-upload" @click="$refs.avatarInput.click()">
                <div class="sms-avatar xl" :style="{ background: (avatarPreview || editingStaff?.avatar_url) ? 'transparent' : roleColor(staffForm.role) }">
                  <img v-if="avatarPreview || editingStaff?.avatar_url" :src="avatarPreview || editingStaff?.avatar_url" class="sms-avatar-img" />
                  <span v-else>{{ staffForm.name ? staffForm.name.charAt(0).toUpperCase() : '?' }}</span>
                  <div class="sms-avatar-overlay">📷</div>
                </div>
                <span class="sms-avatar-hint">Click to upload photo</span>
                <input ref="avatarInput" type="file" accept="image/*" style="display:none" @change="onAvatarSelect" />
              </div>

              <div class="sms-form-grid">
                <div class="sms-fg sms-span2">
                  <label class="sms-label">Full Name <span class="sms-required">*</span></label>
                  <input class="sms-input" v-model="staffForm.name" placeholder="e.g. Kasun Perera" />
                </div>
                <div class="sms-fg">
                  <label class="sms-label">Role <span class="sms-required">*</span></label>
                  <select class="sms-select sms-full" v-model="staffForm.role">
                    <option v-for="r in roles" :key="r.value" :value="r.value">{{ r.label }}</option>
                  </select>
                </div>
                <div class="sms-fg">
                  <label class="sms-label">PIN {{ editingStaff ? '(blank = keep)' : '' }} <span v-if="!editingStaff" class="sms-required">*</span></label>
                  <input class="sms-input" type="password" maxlength="6" v-model="staffForm.pin" placeholder="4–6 digits" />
                </div>
                <div class="sms-fg">
                  <label class="sms-label">Phone</label>
                  <input class="sms-input" v-model="staffForm.phone" placeholder="+94 77 ..." />
                </div>
                <div class="sms-fg">
                  <label class="sms-label">Email</label>
                  <input class="sms-input" type="email" v-model="staffForm.email" placeholder="name@example.com" />
                </div>
                <div class="sms-fg">
                  <label class="sms-label">Join Date</label>
                  <input class="sms-input" type="date" v-model="staffForm.join_date" />
                </div>
                <div class="sms-fg">
                  <label class="sms-label">Salary Type</label>
                  <select class="sms-select sms-full" v-model="staffForm.salary_type">
                    <option value="monthly">Monthly</option>
                    <option value="daily">Daily</option>
                    <option value="hourly">Hourly</option>
                  </select>
                </div>
                <div class="sms-fg">
                  <label class="sms-label">{{ staffForm.salary_type === 'hourly' ? 'Hourly Rate' : 'Base Salary' }} (Rs.)</label>
                  <input class="sms-input" type="number" v-model.number="staffForm.base_salary" placeholder="0" />
                </div>
                <div class="sms-fg">
                  <label class="sms-label">Service Charge % <span class="sms-hint">(0 = equal split)</span></label>
                  <input class="sms-input" type="number" v-model.number="staffForm.service_charge_pct" min="0" max="100" placeholder="0" />
                </div>
                <div class="sms-fg">
                  <label class="sms-label">Bank Name</label>
                  <input class="sms-input" v-model="staffForm.bank_name" placeholder="Bank name" />
                </div>
                <div class="sms-fg">
                  <label class="sms-label">Account Number</label>
                  <input class="sms-input" v-model="staffForm.bank_account" placeholder="Account number" />
                </div>
                <div class="sms-fg sms-span2">
                  <label class="sms-label">Address</label>
                  <input class="sms-input" v-model="staffForm.address" placeholder="Street, City" />
                </div>
              </div>
            </div>

            <div v-if="formError" class="sms-form-error">⚠️ {{ formError }}</div>

            <div class="sms-modal-footer">
              <button class="sms-btn-ghost" @click="showStaffModal = false">Cancel</button>
              <button class="sms-btn-primary" :disabled="saving" @click="saveStaff">
                <span v-if="saving" class="sms-spin">⏳</span>
                {{ saving ? 'Saving...' : (editingStaff ? '✓ Update Staff' : '＋ Add Staff') }}
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- ══ STAFF DETAIL MODAL ══ -->
    <Teleport to="body">
      <Transition name="modal-fade">
        <div v-if="detailStaff" class="sms-overlay" @click.self="detailStaff = null">
          <div class="sms-modal" @click.stop>
            <div class="sms-modal-header">
              <div class="sms-modal-title-wrap">
                <div class="sms-modal-icon" :style="{ background: roleColor(detailStaff.role) }">{{ initials(detailStaff.name) }}</div>
                <span class="sms-modal-title">Staff Profile</span>
              </div>
              <button class="sms-modal-close" @click="detailStaff = null">✕</button>
            </div>
            <div class="sms-modal-body">
              <div class="sms-detail-hero">
                <div class="sms-avatar xl" :style="{ background: detailStaff.avatar_url ? 'transparent' : roleColor(detailStaff.role) }">
                  <img v-if="detailStaff.avatar_url" :src="detailStaff.avatar_url" class="sms-avatar-img" />
                  <span v-else>{{ initials(detailStaff.name) }}</span>
                </div>
                <div>
                  <div class="sms-detail-name">{{ detailStaff.name }}</div>
                  <div class="sms-detail-role">{{ getRoleLabel(detailStaff.role) }}</div>
                  <span :class="['sms-badge', detailStaff.is_active ? 'active' : 'rejected']">
                    {{ detailStaff.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </div>
              </div>
              <div class="sms-detail-grid">
                <div class="sms-detail-row"><span>Employee ID</span><span>{{ detailStaff.employee_id }}</span></div>
                <div class="sms-detail-row"><span>Phone</span><span>{{ detailStaff.phone ?? '—' }}</span></div>
                <div class="sms-detail-row"><span>Email</span><span>{{ detailStaff.email ?? '—' }}</span></div>
                <div class="sms-detail-row"><span>Salary Type</span><span style="text-transform:capitalize">{{ detailStaff.salary_type }}</span></div>
                <div class="sms-detail-row"><span>Base Salary</span><span class="green">{{ currency(detailStaff.base_salary) }}</span></div>
                <div class="sms-detail-row"><span>Svc Charge %</span><span>{{ detailStaff.service_charge_pct > 0 ? detailStaff.service_charge_pct + '%' : 'Equal split' }}</span></div>
                <div class="sms-detail-row"><span>Joined</span><span>{{ detailStaff.join_date ?? '—' }}</span></div>
                <div class="sms-detail-row"><span>Bank</span><span>{{ detailStaff.bank_name ? detailStaff.bank_name + ' · ' + detailStaff.bank_account : '—' }}</span></div>
              </div>
            </div>
            <div class="sms-modal-footer">
              <button class="sms-btn-ghost" @click="detailStaff = null">Close</button>
              <button class="sms-btn-sm blue" @click="openStaffModal(detailStaff); detailStaff = null">✏️ Edit</button>
              <button class="sms-btn-primary" @click="openPayrollModal(detailStaff); detailStaff = null">💰 Payroll</button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- ══ PAYROLL MODAL ══ -->
    <Teleport to="body">
      <Transition name="modal-fade">
        <div v-if="showPayrollModal" class="sms-overlay" @click.self="showPayrollModal = false">
          <div class="sms-modal" @click.stop>
            <div class="sms-modal-header">
              <div class="sms-modal-title-wrap">
                <div class="sms-modal-icon" style="background:linear-gradient(135deg,var(--amber),#e09610)">💰</div>
                <span class="sms-modal-title">Payroll — {{ payrollTarget?.name }}</span>
              </div>
              <button class="sms-modal-close" @click="showPayrollModal = false">✕</button>
            </div>
            <div class="sms-modal-body">
              <div class="sms-form-grid">
                <div class="sms-fg">
                  <label class="sms-label">Month</label>
                  <select class="sms-select sms-full" v-model="payrollForm.month">
                    <option v-for="m in months" :key="m.value" :value="m.value">{{ m.label }}</option>
                  </select>
                </div>
                <div class="sms-fg">
                  <label class="sms-label">Year</label>
                  <select class="sms-select sms-full" v-model="payrollForm.year">
                    <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
                  </select>
                </div>
                <div class="sms-fg">
                  <label class="sms-label">Bonus (Rs.)</label>
                  <input class="sms-input sms-input-green" type="number" v-model.number="payrollForm.bonus" placeholder="0" />
                </div>
                <div class="sms-fg">
                  <label class="sms-label">Deductions (Rs.)</label>
                  <input class="sms-input sms-input-red" type="number" v-model.number="payrollForm.deductions" placeholder="0" />
                </div>
                <div class="sms-fg sms-span2">
                  <label class="sms-label">Notes</label>
                  <textarea class="sms-input" rows="2" v-model="payrollForm.notes" style="resize:none;font-family:inherit"></textarea>
                </div>
              </div>

              <!-- Calculated result preview -->
              <div v-if="calculatedPayroll" class="sms-payroll-preview">
                <div class="sms-preview-title">✅ Calculated Payroll</div>
                <div class="sms-preview-row"><span>Base ({{ calculatedPayroll.days_worked }}d {{ calculatedPayroll.hours_worked }}h)</span><span>{{ currency(calculatedPayroll.base_salary) }}</span></div>
                <div class="sms-preview-row purple"><span>Service Charge</span><span>{{ currency(calculatedPayroll.service_charge_share) }}</span></div>
                <div class="sms-preview-row green"><span>Tips</span><span>{{ currency(calculatedPayroll.tips) }}</span></div>
                <div class="sms-preview-row blue"><span>Bonus</span><span>{{ currency(calculatedPayroll.bonus) }}</span></div>
                <div class="sms-preview-row red"><span>Deductions</span><span>{{ currency(calculatedPayroll.deductions) }}</span></div>
                <div class="sms-preview-row total"><span>Net Pay</span><span>{{ currency(calculatedPayroll.net_pay) }}</span></div>
              </div>
            </div>

            <div class="sms-modal-footer">
              <button class="sms-btn-ghost" @click="showPayrollModal = false">Cancel</button>
              <button class="sms-btn-primary" :disabled="calculatingPayroll" @click="calculatePayroll">
                {{ calculatingPayroll ? '⏳ Calculating...' : '⚡ Calculate' }}
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- ══ ADVANCE MODAL ══ -->
    <Teleport to="body">
      <Transition name="modal-fade">
        <div v-if="showAdvanceModal" class="sms-overlay" @click.self="showAdvanceModal = false">
          <div class="sms-modal" @click.stop>
            <div class="sms-modal-header">
              <div class="sms-modal-title-wrap">
                <div class="sms-modal-icon" style="background:linear-gradient(135deg,var(--amber),#b87c10)">💵</div>
                <span class="sms-modal-title">{{ editingAdvance ? 'Edit Advance' : 'New Advance / Loan' }}</span>
              </div>
              <button class="sms-modal-close" @click="showAdvanceModal = false">✕</button>
            </div>
            <div class="sms-modal-body">
              <div class="sms-form-grid">
                <div class="sms-fg sms-span2">
                  <label class="sms-label">Employee <span class="sms-required">*</span></label>
                  <select class="sms-select sms-full" v-model="advanceForm.employee_id">
                    <option value="" disabled>Select employee</option>
                    <option v-for="s in staffList" :key="s.id" :value="s.id">{{ s.name }}</option>
                  </select>
                </div>
                <div class="sms-fg">
                  <label class="sms-label">Type</label>
                  <select class="sms-select sms-full" v-model="advanceForm.type">
                    <option>Salary Advance</option>
                    <option>Personal Loan</option>
                    <option>Emergency Advance</option>
                  </select>
                </div>
                <div class="sms-fg">
                  <label class="sms-label">Amount (Rs.) <span class="sms-required">*</span></label>
                  <input class="sms-input" type="number" v-model.number="advanceForm.amount" placeholder="0" />
                </div>
                <div class="sms-fg">
                  <label class="sms-label">Monthly Deduction (Rs.)</label>
                  <input class="sms-input" type="number" v-model.number="advanceForm.monthly_deduction" placeholder="0" />
                </div>
                <div class="sms-fg">
                  <label class="sms-label">Issue Date</label>
                  <input class="sms-input" type="date" v-model="advanceForm.date" />
                </div>
              </div>
            </div>
            <div v-if="advanceError" class="sms-form-error">⚠️ {{ advanceError }}</div>
            <div class="sms-modal-footer">
              <button class="sms-btn-ghost" @click="showAdvanceModal = false">Cancel</button>
              <button class="sms-btn-primary amber" @click="saveAdvance">
                {{ editingAdvance ? '✓ Update Advance' : '＋ Record Advance' }}
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- ══ LEAVE MODAL ══ -->
    <Teleport to="body">
      <Transition name="modal-fade">
        <div v-if="showLeaveModal" class="sms-overlay" @click.self="showLeaveModal = false">
          <div class="sms-modal" @click.stop>
            <div class="sms-modal-header">
              <div class="sms-modal-title-wrap">
                <div class="sms-modal-icon" style="background:linear-gradient(135deg,var(--green),#15a87d)">📅</div>
                <span class="sms-modal-title">Leave Request</span>
              </div>
              <button class="sms-modal-close" @click="showLeaveModal = false">✕</button>
            </div>
            <div class="sms-modal-body">
              <div class="sms-form-grid">
                <div class="sms-fg sms-span2">
                  <label class="sms-label">Employee <span class="sms-required">*</span></label>
                  <select class="sms-select sms-full" v-model="leaveForm.employee_id">
                    <option value="" disabled>Select employee</option>
                    <option v-for="s in staffList" :key="s.id" :value="s.id">{{ s.name }}</option>
                  </select>
                </div>
                <div class="sms-fg">
                  <label class="sms-label">Leave Type</label>
                  <select class="sms-select sms-full" v-model="leaveForm.type">
                    <option value="sick">Sick Leave</option>
                    <option value="annual">Annual Leave</option>
                    <option value="unpaid">Unpaid Leave</option>
                    <option value="other">Other</option>
                  </select>
                </div>
                <div class="sms-fg">
                  <label class="sms-label">Duration</label>
                  <div class="sms-duration-display">
                    {{ calculatedLeaveDays }} day{{ calculatedLeaveDays !== 1 ? 's' : '' }}
                  </div>
                </div>
                <div class="sms-fg">
                  <label class="sms-label">From Date <span class="sms-required">*</span></label>
                  <input class="sms-input" type="date" v-model="leaveForm.from_date" />
                </div>
                <div class="sms-fg">
                  <label class="sms-label">To Date <span class="sms-required">*</span></label>
                  <input class="sms-input" type="date" v-model="leaveForm.to_date" />
                </div>
                <div class="sms-fg sms-span2">
                  <label class="sms-label">Reason</label>
                  <input class="sms-input" v-model="leaveForm.reason" placeholder="Optional reason..." />
                </div>
              </div>
            </div>
            <div v-if="leaveError" class="sms-form-error">⚠️ {{ leaveError }}</div>
            <div class="sms-modal-footer">
              <button class="sms-btn-ghost" @click="showLeaveModal = false">Cancel</button>
              <button class="sms-btn-primary green" @click="saveLeave">📅 Submit Request</button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- ══ SERVICE CHARGE MODAL ══ -->
    <Teleport to="body">
      <Transition name="modal-fade">
        <div v-if="showSCModal" class="sms-overlay" @click.self="showSCModal = false">
          <div class="sms-modal sms-modal-wide" @click.stop>
            <div class="sms-modal-header">
              <div class="sms-modal-title-wrap">
                <div class="sms-modal-icon" style="background:linear-gradient(135deg,#e09610,var(--amber))">💰</div>
                <span class="sms-modal-title">Service Charge Distribution — {{ currentPayrollMonthLabel }}</span>
              </div>
              <button class="sms-modal-close" @click="showSCModal = false">✕</button>
            </div>

            <div class="sms-modal-body">
              <div class="sms-sc-summary">
                <div class="sms-sc-sum-item">
                  <div class="amber" style="font-size:22px;font-weight:800">{{ currency(serviceChargeTotal) }}</div>
                  <div style="font-size:11px;color:var(--t3)">Total Collected</div>
                </div>
                <div class="sms-sc-sum-item">
                  <div class="blue" style="font-size:22px;font-weight:800">{{ scDistribution.length }}</div>
                  <div style="font-size:11px;color:var(--t3)">Active Staff</div>
                </div>
                <div class="sms-sc-sum-item">
                  <div class="green" style="font-size:22px;font-weight:800">{{ currency(perStaffShare) }}</div>
                  <div style="font-size:11px;color:var(--t3)">Equal Share</div>
                </div>
              </div>

              <div style="margin-bottom:14px">
                <label class="sms-label" style="margin-bottom:8px;display:block">Distribution Method</label>
                <div style="display:flex;gap:8px;">
                  <label class="sms-radio-label" :class="{ active: scMethod === 'equal' }">
                    <input type="radio" v-model="scMethod" value="equal" /> Equal Split
                  </label>
                  <label class="sms-radio-label" :class="{ active: scMethod === 'custom' }">
                    <input type="radio" v-model="scMethod" value="custom" /> Custom %
                  </label>
                </div>
              </div>

              <div class="sms-sc-table">
                <div class="sms-sc-table-head">
                  <span>Staff</span>
                  <span style="text-align:center">Days</span>
                  <span style="text-align:center">%</span>
                  <span style="text-align:right">Amount</span>
                </div>
                <div v-for="item in scDistribution" :key="item.id" class="sms-sc-table-row">
                  <div style="display:flex;align-items:center;gap:8px">
                    <div class="sms-avatar sm" :style="{ background: roleColor(item.role) }">{{ initials(item.name) }}</div>
                    <div>
                      <div style="font-size:13px;font-weight:600;color:var(--text)">{{ item.name }}</div>
                      <div style="font-size:11px;color:var(--t3)">{{ item.role_label }}</div>
                    </div>
                  </div>
                  <div style="text-align:center;font-size:13px;color:var(--t2)">{{ item.days_worked }}</div>
                  <div style="text-align:center">
                    <input
                      v-if="scMethod === 'custom'"
                      type="number" v-model.number="item.custom_pct"
                      @input="recalcScDistribution"
                      class="sms-input" style="width:70px;text-align:center;padding:4px 6px;font-size:12px"
                    />
                    <span v-else class="purple" style="font-size:13px;font-weight:600">{{ item.pct.toFixed(1) }}%</span>
                  </div>
                  <div class="green" style="text-align:right;font-size:14px;font-weight:700">{{ currency(item.amount) }}</div>
                </div>
              </div>

              <div style="margin-top:16px">
                <label class="sms-label" style="margin-bottom:8px;display:block">Quick Bonus</label>
                <div style="display:flex;gap:8px;flex-wrap:wrap">
                  <input type="number" v-model.number="quickBonus" class="sms-input" style="max-width:150px" placeholder="Amount (Rs.)" />
                  <select v-model="quickBonusStaff" class="sms-select" style="flex:1;min-width:140px">
                    <option value="">Select staff...</option>
                    <option v-for="s in staffList.filter(x => x.is_active)" :key="s.id" :value="s.id">{{ s.name }}</option>
                  </select>
                  <button class="sms-btn-sm blue" @click="applyQuickBonus">＋ Add Bonus</button>
                </div>
              </div>
            </div>

            <div class="sms-modal-footer">
              <button class="sms-btn-ghost" @click="showSCModal = false">Cancel</button>
              <button class="sms-btn-primary" :disabled="savingDistribution" @click="saveDistribution">
                {{ savingDistribution ? '⏳ Saving...' : '💾 Save Distribution' }}
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- ══ GENERATE ALL MODAL ══ -->
    <Teleport to="body">
      <Transition name="modal-fade">
        <div v-if="showGenerateModal" class="sms-overlay" @click.self="showGenerateModal = false">
          <div class="sms-modal sms-modal-sm" @click.stop>
            <div class="sms-modal-header">
              <div class="sms-modal-title-wrap">
                <div class="sms-modal-icon" style="background:linear-gradient(135deg,var(--purple),var(--blue))">⚡</div>
                <span class="sms-modal-title">Generate Payrolls</span>
              </div>
              <button class="sms-modal-close" @click="showGenerateModal = false">✕</button>
            </div>
            <div class="sms-modal-body">
              <div class="sms-generate-info">
                <div class="sms-gen-row"><span>Period</span><strong class="amber">{{ currentPayrollMonthLabel }}</strong></div>
                <div class="sms-gen-row"><span>Active Staff</span><strong class="blue">{{ activeStaffCount }}</strong></div>
                <div class="sms-gen-row"><span>Service Charge Pool</span><strong class="green">{{ currency(serviceChargeTotal) }}</strong></div>
                <div class="sms-gen-row"><span>Per Staff Share</span><strong class="purple">{{ currency(perStaffShare) }}</strong></div>
              </div>
              <p style="font-size:13px;color:var(--t3);margin-top:14px;line-height:1.6">
                This will create payroll entries for all <strong style="color:var(--text)">{{ activeStaffCount }} active staff</strong> members.
                Existing payrolls for this period will be updated.
              </p>
            </div>
            <div class="sms-modal-footer">
              <button class="sms-btn-ghost" @click="showGenerateModal = false">Cancel</button>
              <button class="sms-btn-primary" :disabled="generatingAll" @click="confirmGenerateAll">
                {{ generatingAll ? '⏳ Generating...' : '⚡ Generate All Payrolls' }}
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- ══ TOAST ══ -->
    <Teleport to="body">
      <Transition name="toast">
        <div v-if="toast.show" :class="['sms-toast', toast.type]">
          {{ toast.type === 'success' ? '✅' : toast.type === 'error' ? '⚠️' : 'ℹ️' }}
          {{ toast.message }}
        </div>
      </Transition>
    </Teleport>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'

// ── TABS ──────────────────────────────────────────────────────────────────
const tabs = [
  { value: 'overview',   label: 'Overview',   icon: '📊' },
  { value: 'staff',      label: 'Staff',      icon: '👥' },
  { value: 'payroll',    label: 'Payroll',    icon: '💰' },
  { value: 'advances',   label: 'Advances',   icon: '💵' },
  { value: 'leaves',     label: 'Leaves',     icon: '📅' },
  { value: 'history',    label: 'History',    icon: '📜' },
  { value: 'documents',  label: 'Documents',  icon: '📁' },
]
const activeTab = ref('overview')

// ── STATE ─────────────────────────────────────────────────────────────────
const now = new Date()
const staffList         = ref([])
const payrollList       = ref([])
const advancesList      = ref([])
const leavesList        = ref([])
const paymentHistory    = ref([])
const overview          = ref({ today_shifts: [] })

// Filter state
const staffSearch      = ref('')
const filterRole       = ref('')
const filterStatus     = ref('')
const advanceFilter    = ref('')
const leaveFilter      = ref('all')
const historyEmployee  = ref('')
const historyType      = ref('')

// Payroll
const payrollMonth         = ref(now.getMonth() + 1)
const payrollYear          = ref(now.getFullYear())
const serviceChargeTotal   = ref(0)
const generatingAll        = ref(false)
const showGenerateModal    = ref(false)

// Modals
const showStaffModal       = ref(false)
const showPayrollModal     = ref(false)
const showAdvanceModal     = ref(false)
const showLeaveModal       = ref(false)
const showSCModal          = ref(false)
const editingStaff         = ref(null)
const editingAdvance       = ref(null)
const detailStaff          = ref(null)
const payrollTarget        = ref(null)
const calculatedPayroll    = ref(null)
const calculatingPayroll   = ref(false)
const saving               = ref(false)
const savingDistribution   = ref(false)
const formError            = ref('')
const advanceError         = ref('')
const leaveError           = ref('')

// Avatar
const avatarPreview = ref('')
const avatarFile    = ref(null)

// Service charge distribution
const scDistribution  = ref([])
const scMethod        = ref('equal')
const quickBonus      = ref(0)
const quickBonusStaff = ref('')

// Toast
const toast = ref({ show: false, message: '', type: 'success' })

// ── STATIC DATA ───────────────────────────────────────────────────────────
const roles = [
  { value: 'admin',     label: 'Administrator' },
  { value: 'manager',   label: 'Manager'       },
  { value: 'cashier',   label: 'Cashier'       },
  { value: 'waiter',    label: 'Waiter'        },
  { value: 'kitchen',   label: 'Kitchen Staff' },
  { value: 'bartender', label: 'Bartender'     },
  { value: 'delivery',  label: 'Delivery'      },
  { value: 'chef',      label: 'Chef'          },
  { value: 'cleaner',   label: 'Cleaner'       },
]

const months = [
  { value: 1, label: 'January' },   { value: 2,  label: 'February' },
  { value: 3, label: 'March' },     { value: 4,  label: 'April' },
  { value: 5, label: 'May' },       { value: 6,  label: 'June' },
  { value: 7, label: 'July' },      { value: 8,  label: 'August' },
  { value: 9, label: 'September' }, { value: 10, label: 'October' },
  { value: 11, label: 'November' }, { value: 12, label: 'December' },
]

const years = Array.from({ length: 5 }, (_, i) => now.getFullYear() - 2 + i)

const leaveBalances = ref([
  { type: 'Casual',  total: 7,  remaining: 5,  color: '#4F8EFF' },
  { type: 'Sick',    total: 14, remaining: 11, color: '#F5A623' },
  { type: 'Annual',  total: 14, remaining: 10, color: '#22D3A0' },
])

// ── FORMS ─────────────────────────────────────────────────────────────────
const defaultStaffForm = () => ({
  name: '', pin: '', role: 'waiter', phone: '', email: '',
  address: '', join_date: '', salary_type: 'monthly',
  base_salary: 0, service_charge_pct: 0,
  bank_name: '', bank_account: '',
})
const defaultAdvanceForm = () => ({
  employee_id: '', type: 'Salary Advance', amount: 0, monthly_deduction: 0,
  date: new Date().toISOString().split('T')[0],
})
const defaultLeaveForm = () => ({
  employee_id: '', type: 'sick', from_date: '', to_date: '', reason: '',
})
const defaultPayrollForm = () => ({
  month: now.getMonth() + 1, year: now.getFullYear(), bonus: 0, deductions: 0, notes: '',
})

const staffForm   = ref(defaultStaffForm())
const advanceForm = ref(defaultAdvanceForm())
const leaveForm   = ref(defaultLeaveForm())
const payrollForm = ref(defaultPayrollForm())

// ── COMPUTED ──────────────────────────────────────────────────────────────
const currentMonthLabel = computed(() => {
  const m = months.find(x => x.value === now.getMonth() + 1)
  return `${m?.label} ${now.getFullYear()}`
})

const currentPayrollMonthLabel = computed(() => {
  const m = months.find(x => x.value === payrollMonth.value)
  return `${m?.label} ${payrollYear.value}`
})

const activeStaffCount = computed(() => staffList.value.filter(s => s.is_active).length)

const perStaffShare = computed(() => {
  const count = activeStaffCount.value
  return count > 0 ? serviceChargeTotal.value / count : 0
})

const todayShifts = computed(() => overview.value.today_shifts ?? [])

const overviewCards = computed(() => [
  { label: 'Total Staff',      value: staffList.value.length,                                          sub: `${activeStaffCount.value} active`,   accent: 'green'  },
  { label: 'On Shift Today',   value: overview.value.on_shift_today ?? todayShifts.value.length,       accent: 'blue'                                              },
  { label: 'Active Advances',  value: advancesList.value.filter(a => a.remaining_balance > 0).length,  sub: 'Pending deductions',                 accent: 'amber'  },
  { label: 'Pending Payroll',  value: payrollList.value.filter(p => p.status === 'draft').length,      sub: 'Not yet approved',                   accent: 'purple' },
])

const filteredStaffList = computed(() => {
  return staffList.value.filter(s => {
    const matchSearch = !staffSearch.value ||
      s.name.toLowerCase().includes(staffSearch.value.toLowerCase()) ||
      (s.phone && s.phone.includes(staffSearch.value))
    const matchRole = !filterRole.value || s.role === filterRole.value
    const matchStatus = !filterStatus.value ||
      (filterStatus.value === 'active' && s.is_active) ||
      (filterStatus.value === 'inactive' && !s.is_active)
    return matchSearch && matchRole && matchStatus
  })
})

const filteredAdvances = computed(() => {
  if (!advanceFilter.value) return advancesList.value
  return advancesList.value.filter(a => a.employee_id === advanceFilter.value)
})

const filteredLeaves = computed(() => {
  if (leaveFilter.value === 'all') return leavesList.value
  return leavesList.value.filter(l => l.status?.toLowerCase() === leaveFilter.value.toLowerCase())
})

const filteredHistory = computed(() => {
  return paymentHistory.value.filter(p => {
    const matchE = !historyEmployee.value || p.employee_id === historyEmployee.value
    const matchT = !historyType.value || p.type === historyType.value
    return matchE && matchT
  })
})

const payrollTotals = computed(() => ({
  base:       payrollList.value.reduce((s, p) => s + (parseFloat(p.base_salary)         || 0), 0),
  service:    payrollList.value.reduce((s, p) => s + (parseFloat(p.service_charge_share) || 0), 0),
  tips:       payrollList.value.reduce((s, p) => s + (parseFloat(p.tips)                 || 0), 0),
  bonus:      payrollList.value.reduce((s, p) => s + (parseFloat(p.bonus)                || 0), 0),
  deductions: payrollList.value.reduce((s, p) => s + (parseFloat(p.deductions)           || 0), 0),
  net:        payrollList.value.reduce((s, p) => s + (parseFloat(p.net_pay)              || 0), 0),
}))

const calculatedLeaveDays = computed(() => {
  if (!leaveForm.value.from_date || !leaveForm.value.to_date) return 0
  const diff = new Date(leaveForm.value.to_date) - new Date(leaveForm.value.from_date)
  return diff >= 0 ? Math.ceil(diff / 86400000) + 1 : 0
})

// ── HELPERS ───────────────────────────────────────────────────────────────
function fmt(n) {
  const num = parseFloat(n ?? 0)
  return isNaN(num) ? '0.00' : num.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}
function currency(n) { return `Rs. ${fmt(n)}` }
function initials(name) {
  if (!name) return '?'
  return name.trim().split(' ').map(p => p[0]).join('').substring(0, 2).toUpperCase()
}
function roleColor(role) {
  const map = {
    admin: '#F59E0B', manager: '#EF4444', cashier: '#3B82F6', waiter: '#10B981',
    kitchen: '#8B5CF6', bartender: '#F97316', delivery: '#06B6D4', chef: '#EC4899', cleaner: '#64748B'
  }
  return map[role] ?? '#94A3B8'
}
function getRoleLabel(role) {
  return roles.find(r => r.value === role)?.label ?? role ?? '—'
}
function leaveBadgeClass(status) {
  return { pending: 'pending', approved: 'active', rejected: 'rejected' }[status?.toLowerCase()] || 'inactive'
}
function hasActiveAdvance(userId) {
  return advancesList.value.some(a => a.employee_id === userId && a.remaining_balance > 0)
}
function showToast(message, type = 'success') {
  toast.value = { show: true, message, type }
  setTimeout(() => { toast.value.show = false }, 3500)
}
function filterStaffList() { /* reactive via computed */ }
let staffIdCounter = 100
function makeEmployeeId() { return `EMP-${String(++staffIdCounter).padStart(4, '0')}` }

// ── DATA LOADING ──────────────────────────────────────────────────────────
async function loadOverview() {
  try {
    const res = await fetch('/api/staff/overview')
    if (res.ok) overview.value = await res.json()
  } catch(e) { /* silently use defaults */ }
}

async function loadStaff() {
  try {
    const params = new URLSearchParams()
    if (filterRole.value)   params.set('role', filterRole.value)
    if (staffSearch.value)  params.set('search', staffSearch.value)
    if (filterStatus.value) params.set('active', filterStatus.value === 'active' ? 'true' : 'false')
    const res = await fetch(`/api/staff?${params}`)
    if (res.ok) staffList.value = await res.json()
  } catch(e) { /* silently ignore */ }
}

async function loadPayrollList() {
  try {
    const res = await fetch(`/api/staff/payroll-list?month=${payrollMonth.value}&year=${payrollYear.value}`)
    if (!res.ok) { payrollList.value = []; return }
    const data = await res.json()
    payrollList.value = (data.payrolls || []).map(p => ({
      ...p,
      base_salary:          parseFloat(p.base_salary          || 0),
      service_charge_share: parseFloat(p.service_charge_share  || 0),
      tips:                 parseFloat(p.tips                  || 0),
      bonus:                parseFloat(p.bonus                 || 0),
      deductions:           parseFloat(p.deductions            || 0),
      net_pay:              parseFloat(p.net_pay               || 0),
    }))
    serviceChargeTotal.value = parseFloat(data.service_charge_total || 0)
  } catch(e) { payrollList.value = [] }
}

async function loadLeaves() {
  try {
    const res = await fetch('/api/staff/leaves')
    if (res.ok) {
      const data = await res.json()
      leavesList.value = data.map(l => ({ ...l, status: l.status || 'pending' }))
    }
  } catch(e) { /* silently ignore */ }
}

// ── STAFF ACTIONS ─────────────────────────────────────────────────────────
function openStaffModal(staff = null) {
  editingStaff.value = staff
  formError.value    = ''
  avatarPreview.value = ''
  avatarFile.value   = null
  staffForm.value = staff ? {
    name:               staff.name              || '',
    pin:                '',
    role:               staff.role              || 'waiter',
    phone:              staff.phone             || '',
    email:              staff.email             || '',
    address:            staff.address           || '',
    join_date:          staff.join_date         || '',
    salary_type:        staff.salary_type       || 'monthly',
    base_salary:        parseFloat(staff.base_salary        || 0),
    service_charge_pct: parseFloat(staff.service_charge_pct || 0),
    bank_name:          staff.bank_name         || '',
    bank_account:       staff.bank_account      || '',
  } : defaultStaffForm()
  showStaffModal.value = true
}

function onAvatarSelect(e) {
  const file = e.target.files[0]
  if (!file) return
  avatarFile.value = file
  const reader = new FileReader()
  reader.onload = ev => { avatarPreview.value = ev.target.result }
  reader.readAsDataURL(file)
}

async function saveStaff() {
  formError.value = ''
  if (!staffForm.value.name.trim()) { formError.value = 'Name is required'; return }
  if (!editingStaff.value && !staffForm.value.pin) { formError.value = 'PIN is required for new staff'; return }

  saving.value = true
  try {
    let body, headers = {}
    if (avatarFile.value) {
      body = new FormData()
      Object.entries(staffForm.value).forEach(([k, v]) => {
        if (v !== '' && v !== null && v !== undefined) body.append(k, v)
      })
      body.append('avatar', avatarFile.value)
    } else {
      const payload = { ...staffForm.value }
      if (!payload.pin) delete payload.pin
      body = JSON.stringify(payload)
      headers['Content-Type'] = 'application/json'
    }

    const url    = editingStaff.value ? `/api/staff/${editingStaff.value.id}` : '/api/staff'
    const method = editingStaff.value ? 'PUT' : 'POST'
    const res    = await fetch(url, { method, headers, body })

    if (res.ok) {
      await loadStaff()
      await loadOverview()
      showStaffModal.value = false
      showToast(editingStaff.value ? 'Staff updated ✓' : 'Staff added ✓')
    } else {
      // If API not available, update locally
      const err = await res.json().catch(() => ({}))
      if (res.status >= 500 || !navigator.onLine) {
        saveStaffLocally()
      } else {
        formError.value = err.message || 'Failed to save staff'
      }
    }
  } catch(e) {
    saveStaffLocally()
  } finally {
    saving.value = false
  }
}

function saveStaffLocally() {
  if (editingStaff.value) {
    const idx = staffList.value.findIndex(s => s.id === editingStaff.value.id)
    if (idx !== -1) {
      staffList.value[idx] = {
        ...staffList.value[idx],
        ...staffForm.value,
        role_label: getRoleLabel(staffForm.value.role),
        avatar_url: avatarPreview.value || staffList.value[idx].avatar_url,
      }
    }
    showToast('Staff updated (local) ✓')
  } else {
    const newStaff = {
      id:          Date.now(),
      employee_id: makeEmployeeId(),
      is_active:   true,
      avatar_url:  avatarPreview.value || '',
      role_label:  getRoleLabel(staffForm.value.role),
      ...staffForm.value,
    }
    staffList.value.push(newStaff)
    showToast('Staff added (local) ✓')
  }
  showStaffModal.value = false
}

async function toggleActive(staff) {
  try {
    const res = await fetch(`/api/staff/${staff.id}/toggle-active`, { method: 'PATCH' })
    if (res.ok) {
      const data = await res.json()
      staff.is_active = data.is_active
    } else {
      staff.is_active = !staff.is_active
    }
  } catch(e) {
    staff.is_active = !staff.is_active
  }
  showToast(staff.is_active ? 'Staff activated' : 'Staff deactivated')
  await loadOverview().catch(() => {})
}

function openDetail(staff) {
  detailStaff.value = staff
}

// ── PAYROLL ACTIONS ───────────────────────────────────────────────────────
function openPayrollModal(staff) {
  payrollTarget.value    = staff
  calculatedPayroll.value = null
  payrollForm.value = {
    ...defaultPayrollForm(),
    month: payrollMonth.value,
    year:  payrollYear.value,
  }
  showPayrollModal.value = true
}

async function calculatePayroll() {
  calculatingPayroll.value = true
  try {
    const res = await fetch(`/api/staff/${payrollTarget.value.id}/calculate-payroll`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payrollForm.value),
    })
    if (res.ok) {
      calculatedPayroll.value = await res.json()
      showToast('Payroll calculated ✓')
      await loadPayrollList()
    } else {
      // Fallback: calculate locally
      calculatePayrollLocally()
    }
  } catch(e) {
    calculatePayrollLocally()
  } finally {
    calculatingPayroll.value = false
  }
}

function calculatePayrollLocally() {
  const staff    = payrollTarget.value
  const daysInMonth = new Date(payrollForm.value.year, payrollForm.value.month, 0).getDate()
  const base     = parseFloat(staff.base_salary || 0)
  const sc       = perStaffShare.value
  const bonus    = payrollForm.value.bonus    || 0
  const deduct   = payrollForm.value.deductions || 0
  const net      = Math.max(0, base + sc + bonus - deduct)

  calculatedPayroll.value = {
    days_worked:          daysInMonth,
    hours_worked:         daysInMonth * 8,
    base_salary:          base,
    service_charge_share: sc,
    tips:                 0,
    bonus,
    deductions:           deduct,
    net_pay:              net,
  }

  // Push into payroll list locally
  const existing = payrollList.value.find(p =>
    (p.user_id === staff.id || p.user_name === staff.name) &&
    p.month === payrollForm.value.month &&
    p.year  === payrollForm.value.year
  )
  if (existing) {
    existing.bonus      = bonus
    existing.deductions = deduct
    recalcPayroll(existing)
  } else {
    payrollList.value.push({
      id:                   Date.now(),
      user_id:              staff.id,
      user_name:            staff.name,
      role:                 staff.role,
      month:                payrollForm.value.month,
      year:                 payrollForm.value.year,
      status:               'draft',
      days_worked:          daysInMonth,
      hours_worked:         daysInMonth * 8,
      base_salary:          base,
      service_charge_share: sc,
      tips:                 0,
      bonus,
      deductions:           deduct,
      net_pay:              net,
    })
  }
  showToast('Payroll calculated ✓')
}

function generateAllPayrolls() {
  showGenerateModal.value = true
}

async function confirmGenerateAll() {
  generatingAll.value = true
  try {
    const res = await fetch('/api/staff/generate-all-payrolls', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ month: payrollMonth.value, year: payrollYear.value }),
    })
    if (res.ok) {
      const data = await res.json()
      showToast(data.message || 'Payrolls generated ✓')
      await loadPayrollList()
    } else {
      generatePayrollsLocally()
    }
  } catch(e) {
    generatePayrollsLocally()
  } finally {
    generatingAll.value = false
    showGenerateModal.value = false
  }
}

function generatePayrollsLocally() {
  const active       = staffList.value.filter(s => s.is_active)
  const daysInMonth  = new Date(payrollYear.value, payrollMonth.value, 0).getDate()
  const scPerPerson  = perStaffShare.value

  const newPayrolls = active.map(staff => {
    const existing = payrollList.value.find(p =>
      (p.user_id === staff.id || p.user_name === staff.name)
    )
    if (existing) {
      existing.service_charge_share = scPerPerson
      recalcPayroll(existing)
      return null
    }
    const base = parseFloat(staff.base_salary || 0)
    const net  = Math.max(0, base + scPerPerson)
    return {
      id:                   Date.now() + Math.random(),
      user_id:              staff.id,
      user_name:            staff.name,
      role:                 staff.role,
      month:                payrollMonth.value,
      year:                 payrollYear.value,
      status:               'draft',
      days_worked:          daysInMonth,
      hours_worked:         daysInMonth * 8,
      base_salary:          base,
      service_charge_share: scPerPerson,
      tips:                 0,
      bonus:                0,
      deductions:           0,
      net_pay:              net,
    }
  }).filter(Boolean)

  payrollList.value = [...payrollList.value, ...newPayrolls]
  showToast(`Payrolls generated for ${active.length} staff ✓`)
}

function recalcPayroll(p) {
  const gross = (p.base_salary || 0) + (p.service_charge_share || 0) + (p.tips || 0) + (p.bonus || 0)
  p.net_pay   = Math.max(0, gross - (p.deductions || 0))
}

async function savePayrollChanges(payroll) {
  try {
    const staff = staffList.value.find(s => s.id === payroll.user_id || s.name === payroll.user_name)
    if (!staff) { showToast('Staff not found', 'error'); return }
    const res = await fetch(`/api/staff/${staff.id}/payroll/${payroll.id}`, {
      method: 'PATCH',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ base_salary: payroll.base_salary, bonus: payroll.bonus, deductions: payroll.deductions }),
    })
    showToast(res.ok ? 'Payroll saved ✓' : 'Saved locally ✓')
  } catch(e) { showToast('Saved locally ✓') }
}

async function updatePayrollStatus(payroll, status) {
  try {
    const staff = staffList.value.find(s => s.id === payroll.user_id || s.name === payroll.user_name)
    if (staff) {
      await fetch(`/api/staff/${staff.id}/payroll/${payroll.id}`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ status }),
      })
    }
    payroll.status = status
    if (status === 'paid') {
      paymentHistory.value.unshift({
        id:            Date.now(),
        employee_id:   payroll.user_id,
        employee_name: payroll.user_name,
        type:          'Salary',
        amount:        payroll.net_pay,
        method:        'Bank Transfer',
        date:          new Date().toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }),
        icon:          '💰',
        iconClass:     'hist-salary',
        status:        'Completed',
      })
    }
    showToast(`Payroll marked as ${status}`)
  } catch(e) { payroll.status = status; showToast(`Status updated`) }
}

function applyAdvanceDeduction(payroll) {
  const advance = advancesList.value.find(a => a.employee_id === payroll.user_id && a.remaining_balance > 0)
  if (!advance) { showToast('No active advance found', 'error'); return }
  const deduction = Math.min(advance.monthly_deduction, advance.remaining_balance)
  payroll.deductions = (payroll.deductions || 0) + deduction
  advance.remaining_balance = Math.max(0, advance.remaining_balance - deduction)
  recalcPayroll(payroll)
  showToast(`Applied Rs. ${deduction.toLocaleString()} deduction`)
}

function downloadPayslip(payroll) {
  showToast(`Generating payslip for ${payroll.user_name}...`, 'info')
}

// ── ADVANCE ACTIONS ───────────────────────────────────────────────────────
function openAdvanceModal() {
  editingAdvance.value = null
  advanceError.value   = ''
  advanceForm.value    = defaultAdvanceForm()
  showAdvanceModal.value = true
}

function editAdvance(adv) {
  editingAdvance.value = adv
  advanceError.value   = ''
  advanceForm.value = {
    employee_id:       adv.employee_id,
    type:              adv.type,
    amount:            adv.amount,
    monthly_deduction: adv.monthly_deduction,
    date:              adv.date,
  }
  showAdvanceModal.value = true
}

function saveAdvance() {
  advanceError.value = ''
  if (!advanceForm.value.employee_id)                                    { advanceError.value = 'Select an employee'; return }
  if (!advanceForm.value.amount || advanceForm.value.amount <= 0)        { advanceError.value = 'Enter a valid amount'; return }
  if (!advanceForm.value.monthly_deduction || advanceForm.value.monthly_deduction <= 0) { advanceError.value = 'Enter monthly deduction'; return }

  const emp = staffList.value.find(s => s.id === advanceForm.value.employee_id)
  if (!emp) { advanceError.value = 'Employee not found'; return }

  if (editingAdvance.value) {
    const repaid = editingAdvance.value.amount - editingAdvance.value.remaining_balance
    Object.assign(editingAdvance.value, {
      employee_name:     emp.name,
      type:              advanceForm.value.type,
      amount:            advanceForm.value.amount,
      remaining_balance: Math.max(0, advanceForm.value.amount - repaid),
      monthly_deduction: advanceForm.value.monthly_deduction,
      date:              advanceForm.value.date,
    })
    showToast('Advance updated ✓')
  } else {
    advancesList.value.push({
      id:                Date.now(),
      employee_id:       advanceForm.value.employee_id,
      employee_name:     emp.name,
      type:              advanceForm.value.type,
      amount:            advanceForm.value.amount,
      remaining_balance: advanceForm.value.amount,
      monthly_deduction: advanceForm.value.monthly_deduction,
      date:              advanceForm.value.date,
      status:            'Active',
    })
    paymentHistory.value.unshift({
      id:            Date.now() + 1,
      employee_id:   emp.id,
      employee_name: emp.name,
      type:          'Advance',
      amount:        advanceForm.value.amount,
      method:        'Cash',
      date:          new Date().toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }),
      icon:          '💵',
      iconClass:     'hist-advance',
      reference:     `ADV-${new Date().getFullYear()}-${String(Date.now()).slice(-4)}`,
      status:        'Completed',
    })
    showToast('Advance recorded ✓')
  }
  showAdvanceModal.value = false
}

function removeAdvance(adv) {
  if (!confirm(`Remove advance for ${adv.employee_name}?`)) return
  advancesList.value = advancesList.value.filter(a => a.id !== adv.id)
  showToast('Advance removed')
}

// ── LEAVE ACTIONS ─────────────────────────────────────────────────────────
function openLeaveModal() {
  leaveError.value  = ''
  leaveForm.value   = defaultLeaveForm()
  showLeaveModal.value = true
}

async function saveLeave() {
  leaveError.value = ''
  if (!leaveForm.value.employee_id)  { leaveError.value = 'Select an employee'; return }
  if (!leaveForm.value.from_date)    { leaveError.value = 'From date is required'; return }
  if (!leaveForm.value.to_date)      { leaveError.value = 'To date is required'; return }
  if (new Date(leaveForm.value.to_date) < new Date(leaveForm.value.from_date)) {
    leaveError.value = 'To date must be after from date'; return
  }
  try {
    const res = await fetch(`/api/staff/${leaveForm.value.employee_id}/leave`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ type: leaveForm.value.type, from_date: leaveForm.value.from_date, to_date: leaveForm.value.to_date, reason: leaveForm.value.reason }),
    })
    if (res.ok) {
      showLeaveModal.value = false
      await loadLeaves()
      showToast('Leave request submitted ✓')
      return
    }
  } catch(e) { /* fallback below */ }

  // Local fallback
  const emp = staffList.value.find(s => s.id === leaveForm.value.employee_id)
  leavesList.value.unshift({
    id:         Date.now(),
    user_id:    leaveForm.value.employee_id,
    user_name:  emp?.name || '',
    role:       emp?.role || '',
    type:       leaveForm.value.type,
    from_date:  leaveForm.value.from_date,
    to_date:    leaveForm.value.to_date,
    days:       calculatedLeaveDays.value,
    reason:     leaveForm.value.reason,
    status:     'pending',
  })
  showLeaveModal.value = false
  showToast('Leave request submitted ✓')
}

async function updateLeaveStatus(leave, status) {
  try {
    await fetch(`/api/staff/${leave.user_id}/leave/${leave.id}`, {
      method: 'PATCH',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ status }),
    })
  } catch(e) { /* ignore */ }
  leave.status = status
  showToast(`Leave ${status}`)
}

// ── PROFILE IMAGE ─────────────────────────────────────────────────────────
async function uploadProfileImage(staff, event) {
  const file = event.target.files[0]
  if (!file) return
  const reader = new FileReader()
  reader.onload = ev => { staff.avatar_url = ev.target.result }
  reader.readAsDataURL(file)
  try {
    const formData = new FormData()
    formData.append('avatar', file)
    await fetch(`/api/staff/${staff.id}`, { method: 'PUT', body: formData })
  } catch(e) { /* ignore, already shown locally */ }
  showToast(`Profile photo updated for ${staff.name} ✓`)
  event.target.value = ''
}

function viewDocument(url) {
  window.open(url, '_blank')
}

// ── SERVICE CHARGE ────────────────────────────────────────────────────────
function openServiceChargeModal() {
  scMethod.value        = 'equal'
  quickBonus.value      = 0
  quickBonusStaff.value = ''
  buildScDistribution()
  showSCModal.value = true
}

function buildScDistribution() {
  const active   = staffList.value.filter(s => s.is_active)
  const total    = serviceChargeTotal.value
  const equalPct = active.length > 0 ? 100 / active.length : 0
  const equalAmt = active.length > 0 ? total / active.length : 0

  scDistribution.value = active.map(s => {
    const payroll = payrollList.value.find(p => p.user_id === s.id || p.user_name === s.name)
    const customPct = parseFloat(s.service_charge_pct || 0)
    return {
      id:          s.id,
      name:        s.name,
      role:        s.role,
      role_label:  getRoleLabel(s.role),
      days_worked: payroll?.days_worked ?? 0,
      pct:         customPct > 0 ? customPct : equalPct,
      custom_pct:  customPct > 0 ? customPct : equalPct,
      amount:      customPct > 0 ? total * (customPct / 100) : equalAmt,
    }
  })
}

function recalcScDistribution() {
  if (scMethod.value !== 'custom') return
  const total    = serviceChargeTotal.value
  const totalPct = scDistribution.value.reduce((s, x) => s + (x.custom_pct || 0), 0)
  scDistribution.value.forEach(item => {
    item.amount = totalPct > 0 ? (total * ((item.custom_pct || 0) / 100)) : 0
  })
}

watch(scMethod, () => {
  if (scMethod.value === 'equal') buildScDistribution()
})

function applyQuickBonus() {
  if (!quickBonus.value || !quickBonusStaff.value) { showToast('Select staff and enter amount', 'error'); return }
  const payroll = payrollList.value.find(p => p.user_id === quickBonusStaff.value)
  if (payroll) { payroll.bonus = (payroll.bonus || 0) + quickBonus.value; recalcPayroll(payroll) }
  const staff = staffList.value.find(s => s.id === quickBonusStaff.value)
  showToast(`Bonus ${currency(quickBonus.value)} queued for ${staff?.name}`)
  quickBonus.value      = 0
  quickBonusStaff.value = ''
}

async function saveDistribution() {
  savingDistribution.value = true
  try {
    for (const item of scDistribution.value) {
      const payroll = payrollList.value.find(p => p.user_id === item.id || p.user_name === item.name)
      if (payroll) {
        payroll.service_charge_share = item.amount
        recalcPayroll(payroll)
        try {
          const staff = staffList.value.find(s => s.id === item.id)
          if (staff && payroll.id) {
            await fetch(`/api/staff/${staff.id}/payroll/${payroll.id}`, {
              method: 'PATCH',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({ service_charge_share: item.amount }),
            })
          }
        } catch(_) {}
      }
    }
    showSCModal.value = false
    showToast('Service charge distributed ✓')
  } catch(e) {
    showToast('Distribution saved locally ✓')
    showSCModal.value = false
  } finally {
    savingDistribution.value = false
  }
}

// ── WATCHERS & INIT ───────────────────────────────────────────────────────
watch([payrollMonth, payrollYear], () => loadPayrollList())

onMounted(async () => {
  await Promise.all([loadOverview(), loadStaff()])
  await loadPayrollList()
  await loadLeaves()
})
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap');

/* ══════════════════════════════════════════
   CSS VARIABLES
══════════════════════════════════════════ */
.sms-root {
  --bg:       #080B12;
  --surface:  #0F1420;
  --surface2: #161C2D;
  --surface3: #1C2438;
  --border:   #1E2840;
  --border2:  #253050;
  --border3:  #2E3D65;
  --text:     #E8EDF8;
  --t2:       #7A8BAF;
  --t3:       #4A5880;
  --green:    #22D3A0;
  --blue:     #4F8EFF;
  --amber:    #F5A623;
  --red:      #FF5C6A;
  --purple:   #9B6DFF;
  --input-bg: #0D1220;

  display: flex;
  flex-direction: column;
  height: 100vh;
  background: var(--bg);
  font-family: 'Sora', sans-serif;
  font-size: 14px;
  color: var(--text);
  overflow: hidden;
}

/* ══════════════════════════════════════════
   HEADER
══════════════════════════════════════════ */
.sms-header {
  background: var(--surface);
  border-bottom: 1px solid var(--border);
  height: 54px;
  flex-shrink: 0;
  position: relative;
}
.sms-header::after {
  content: '';
  position: absolute;
  bottom: 0; left: 0; right: 0;
  height: 1px;
  background: linear-gradient(90deg, transparent, var(--purple), var(--blue), transparent);
  opacity: 0.5;
}
.sms-header-inner {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 20px;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.sms-logo { display: flex; align-items: center; gap: 10px; }
.sms-logo-icon {
  width: 30px; height: 30px;
  border-radius: 8px;
  background: linear-gradient(135deg, var(--purple), var(--blue));
  display: flex; align-items: center; justify-content: center;
  font-size: 15px;
}
.sms-logo-title {
  font-size: 16px; font-weight: 800; letter-spacing: -0.02em;
  background: linear-gradient(135deg, var(--text), var(--t2));
  -webkit-background-clip: text; -webkit-text-fill-color: transparent;
}

/* ══════════════════════════════════════════
   TABS
══════════════════════════════════════════ */
.sms-tabs {
  background: var(--surface);
  border-bottom: 1px solid var(--border);
  display: flex;
  overflow-x: auto;
  flex-shrink: 0;
  padding: 0 16px;
  scrollbar-width: none;
}
.sms-tabs::-webkit-scrollbar { display: none; }
.sms-tab {
  display: flex; align-items: center; gap: 5px;
  padding: 0 14px; height: 42px;
  border: none; background: transparent;
  color: var(--t3); font-size: 12px; font-weight: 600; font-family: 'Sora', sans-serif;
  white-space: nowrap; cursor: pointer; position: relative;
  transition: color 0.2s;
}
.sms-tab:hover { color: var(--t2); }
.sms-tab.active { color: var(--text); }
.sms-tab.active::after {
  content: '';
  position: absolute;
  bottom: 0; left: 14px; right: 14px;
  height: 2px;
  background: linear-gradient(90deg, var(--purple), var(--blue));
  border-radius: 2px 2px 0 0;
}
.sms-tab-icon { font-size: 13px; }

/* ══════════════════════════════════════════
   MAIN / SECTIONS
══════════════════════════════════════════ */
.sms-main { flex: 1; overflow-y: auto; padding: 20px; }
.sms-main::-webkit-scrollbar { width: 4px; }
.sms-main::-webkit-scrollbar-thumb { background: var(--border2); border-radius: 4px; }
.sms-section { display: flex; flex-direction: column; gap: 14px; }

/* ══════════════════════════════════════════
   STATS
══════════════════════════════════════════ */
.sms-stats-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; }
.sms-stat {
  background: var(--surface); border: 1px solid var(--border);
  border-radius: 12px; padding: 16px; position: relative; overflow: hidden;
  transition: border-color 0.2s, transform 0.15s;
}
.sms-stat:hover { border-color: var(--border2); transform: translateY(-2px); }
.sms-stat::before {
  content: ''; position: absolute; top: 0; left: 0; right: 0; height: 2px;
  border-radius: 12px 12px 0 0;
}
.sms-stat.green::before  { background: var(--green);  }
.sms-stat.blue::before   { background: var(--blue);   }
.sms-stat.amber::before  { background: var(--amber);  }
.sms-stat.purple::before { background: var(--purple); }
.sms-stat-label {
  font-size: 10px; color: var(--t3); text-transform: uppercase;
  letter-spacing: 0.08em; font-weight: 600; margin-bottom: 5px;
}
.sms-stat-value { font-size: 24px; font-weight: 800; margin-bottom: 3px; font-family: 'JetBrains Mono', monospace; }
.sms-stat.green .sms-stat-value  { color: var(--green);  }
.sms-stat.blue .sms-stat-value   { color: var(--blue);   }
.sms-stat.amber .sms-stat-value  { color: var(--amber);  }
.sms-stat.purple .sms-stat-value { color: var(--purple); }
.sms-stat-sub { font-size: 11px; color: var(--t3); }

/* ══════════════════════════════════════════
   SERVICE CHARGE CARD
══════════════════════════════════════════ */
.sms-sc-card {
  background: linear-gradient(135deg, #0F1A35, var(--surface));
  border: 1px solid var(--border2); border-radius: 12px; padding: 18px;
}
.sms-sc-header {
  display: flex; justify-content: space-between; align-items: center;
  margin-bottom: 14px; flex-wrap: wrap; gap: 8px;
}
.sms-sc-title { font-size: 14px; font-weight: 700; }
.sms-sc-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
.sms-sc-stat { background: var(--surface2); border-radius: 8px; padding: 12px; text-align: center; }
.sms-sc-stat-label { font-size: 10px; color: var(--t3); text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 4px; }
.sms-sc-stat-value { font-size: 20px; font-weight: 800; font-family: 'JetBrains Mono', monospace; }
.sms-sc-stat-value.amber { color: var(--amber); }
.sms-sc-stat-value.blue  { color: var(--blue);  }
.sms-sc-stat-value.green { color: var(--green); }
.sms-sc-stat-sub { font-size: 10px; color: var(--t3); margin-top: 2px; }

/* ══════════════════════════════════════════
   QUICK NAV
══════════════════════════════════════════ */
.sms-quick-nav { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; }
.sms-quick-btn {
  display: flex; flex-direction: column; align-items: center; gap: 6px;
  background: var(--surface2); border: 1px solid var(--border); border-radius: 12px;
  padding: 16px 8px; cursor: pointer;
  transition: border-color 0.2s, transform 0.15s;
  font-size: 12px; font-weight: 600; color: var(--text); font-family: 'Sora', sans-serif;
}
.sms-quick-btn:hover { border-color: var(--border2); transform: translateY(-2px); }
.sms-quick-btn span:first-child { font-size: 22px; }

/* ══════════════════════════════════════════
   CARD
══════════════════════════════════════════ */
.sms-card { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; padding: 16px; }
.sms-card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
.sms-section-title { font-size: 12px; font-weight: 700; color: var(--t2); text-transform: uppercase; letter-spacing: 0.08em; }

/* ══════════════════════════════════════════
   SHIFTS
══════════════════════════════════════════ */
.sms-shift-list { display: flex; flex-direction: column; gap: 8px; }
.sms-shift-item {
  display: flex; align-items: center; gap: 10px;
  background: var(--surface2); border-radius: 8px; padding: 10px 12px;
}
.sms-shift-info { flex: 1; }
.sms-shift-name { font-size: 13px; font-weight: 600; }
.sms-shift-role { font-size: 11px; color: var(--t3); text-transform: capitalize; }
.sms-shift-time { font-size: 12px; color: var(--t2); margin-right: 8px; }

/* ══════════════════════════════════════════
   AVATAR
══════════════════════════════════════════ */
.sms-avatar {
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-weight: 800; color: #000; flex-shrink: 0; overflow: hidden; position: relative;
}
.sms-avatar.sm { width: 32px; height: 32px; font-size: 12px; }
.sms-avatar.md { width: 42px; height: 42px; font-size: 16px; }
.sms-avatar.lg { width: 52px; height: 52px; font-size: 19px; }
.sms-avatar.xl { width: 68px; height: 68px; font-size: 26px; }
.sms-avatar-img {
  width: 100%; height: 100%; object-fit: cover;
  position: absolute; inset: 0;
}
.sms-avatar-overlay {
  position: absolute; inset: 0;
  background: rgba(0,0,0,0.55);
  display: flex; align-items: center; justify-content: center;
  font-size: 20px; opacity: 0; transition: opacity 0.2s;
}
.sms-avatar-upload:hover .sms-avatar-overlay { opacity: 1; }

/* ══════════════════════════════════════════
   BADGES
══════════════════════════════════════════ */
.sms-badge {
  display: inline-flex; align-items: center;
  padding: 2px 8px; border-radius: 5px;
  font-size: 10px; font-weight: 700; letter-spacing: 0.04em;
  text-transform: uppercase; font-family: 'Sora', sans-serif;
}
.sms-badge.active   { background: rgba(34,211,160,0.12);  color: var(--green);  }
.sms-badge.pending  { background: rgba(245,166,35,0.12);  color: var(--amber);  }
.sms-badge.paid     { background: rgba(79,142,255,0.12);  color: var(--blue);   }
.sms-badge.rejected { background: rgba(255,92,106,0.12);  color: var(--red);    }
.sms-badge.inactive { background: rgba(122,139,175,0.12); color: var(--t2);     }
.sms-badge.amber    { background: rgba(245,166,35,0.15);  color: var(--amber);  }
.sms-badge.purple   { background: rgba(155,109,255,0.15); color: var(--purple); }
.sms-badge.completed { background: rgba(34,211,160,0.12); color: var(--green);  }

/* ══════════════════════════════════════════
   BUTTONS
══════════════════════════════════════════ */
.sms-btn-primary {
  background: linear-gradient(135deg, var(--purple), var(--blue));
  color: #fff; border: none; border-radius: 8px; padding: 9px 18px;
  font-size: 13px; font-weight: 600; cursor: pointer; font-family: 'Sora', sans-serif;
  transition: opacity 0.2s, transform 0.15s; white-space: nowrap;
}
.sms-btn-primary:hover:not(:disabled) { opacity: 0.88; transform: translateY(-1px); }
.sms-btn-primary:disabled { opacity: 0.5; cursor: not-allowed; }
.sms-btn-primary.amber { background: linear-gradient(135deg, var(--amber), #c88512); color: #1A0E00; }
.sms-btn-primary.green { background: linear-gradient(135deg, var(--green), #15b886); color: #051810; }

.sms-btn-sm {
  background: var(--surface2); border: 1px solid var(--border); border-radius: 8px;
  padding: 7px 14px; font-size: 12px; font-weight: 600; cursor: pointer;
  color: var(--t2); font-family: 'Sora', sans-serif; transition: all 0.2s; white-space: nowrap;
}
.sms-btn-sm:hover { border-color: var(--border2); color: var(--text); }
.sms-btn-sm.blue  { background: rgba(79,142,255,0.1); color: var(--blue); border-color: rgba(79,142,255,0.25); }
.sms-btn-sm.blue:hover { background: rgba(79,142,255,0.2); }

.sms-btn-xs {
  padding: 5px 10px; border-radius: 6px; font-size: 11px; font-weight: 600;
  cursor: pointer; border: 1px solid; font-family: 'Sora', sans-serif; transition: all 0.15s;
  white-space: nowrap; display: inline-flex; align-items: center; gap: 3px;
}
.sms-btn-xs.blue   { background: rgba(79,142,255,0.1);  color: var(--blue);   border-color: rgba(79,142,255,0.25); }
.sms-btn-xs.amber  { background: rgba(245,166,35,0.1);  color: var(--amber);  border-color: rgba(245,166,35,0.25); }
.sms-btn-xs.red    { background: rgba(255,92,106,0.1);  color: var(--red);    border-color: rgba(255,92,106,0.25); }
.sms-btn-xs.green  { background: rgba(34,211,160,0.1);  color: var(--green);  border-color: rgba(34,211,160,0.25); }
.sms-btn-xs:hover  { filter: brightness(1.15); }

.sms-btn-ghost {
  background: transparent; border: 1px solid var(--border2); border-radius: 8px;
  padding: 9px 18px; font-size: 13px; font-weight: 600; color: var(--t2);
  cursor: pointer; font-family: 'Sora', sans-serif; transition: all 0.2s;
}
.sms-btn-ghost:hover { border-color: var(--border3); color: var(--text); background: var(--surface2); }

/* ══════════════════════════════════════════
   FILTERS / TOOLBAR
══════════════════════════════════════════ */
.sms-filters, .sms-toolbar {
  display: flex; gap: 10px; flex-wrap: wrap; align-items: center;
}
.sms-sc-info { font-size: 12px; color: var(--t3); }
.sms-sc-info .amber { color: var(--amber); }
.sms-sc-info .green { color: var(--green); }

.sms-seg {
  display: flex;
  background: var(--surface2);
  border: 1px solid var(--border);
  border-radius: 8px; padding: 3px; gap: 2px;
}
.sms-seg-btn {
  padding: 5px 12px; border: none; background: transparent; color: var(--t3);
  border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer;
  font-family: 'Sora', sans-serif; transition: all 0.2s;
}
.sms-seg-btn.active { background: var(--surface3); color: var(--text); }

/* ══════════════════════════════════════════
   INPUTS & SELECTS
══════════════════════════════════════════ */
.sms-input, .sms-select {
  background: var(--input-bg);
  border: 1px solid var(--border2);
  border-radius: 8px;
  padding: 10px 14px;
  color: var(--text);
  font-size: 13px;
  font-family: 'Sora', sans-serif;
  outline: none;
  transition: border-color 0.2s, box-shadow 0.2s;
  width: 100%;
  box-sizing: border-box;
}
.sms-input:focus, .sms-select:focus {
  border-color: var(--blue);
  box-shadow: 0 0 0 3px rgba(79, 142, 255, 0.15);
}
.sms-input::placeholder { color: var(--t3); opacity: 0.7; }
.sms-input-green { color: var(--green); font-weight: 700; }
.sms-input-red   { color: var(--red);   font-weight: 700; }
.sms-select option { background: #161C2D; color: var(--text); }
.sms-full { width: 100%; }

.sms-select-sm {
  padding: 6px 10px;
  background: var(--input-bg);
  border: 1px solid var(--border2);
  border-radius: 6px;
  color: var(--text);
  font-size: 11px;
  font-family: 'Sora', sans-serif;
  outline: none; cursor: pointer;
  transition: border-color 0.2s;
}
.sms-select-sm:hover  { border-color: var(--border3); }
.sms-select-sm:focus  { border-color: var(--blue); outline: none; }

.sms-duration-display {
  background: var(--surface3);
  border: 1px solid var(--border2);
  border-radius: 8px;
  padding: 10px 14px;
  color: var(--green);
  font-weight: 700;
  font-size: 14px;
}

/* ══════════════════════════════════════════
   STAFF GRID
══════════════════════════════════════════ */
.sms-staff-grid { display: grid; grid-template-columns: 1fr; gap: 10px; }
.sms-staff-card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 12px; padding: 16px; cursor: pointer;
  transition: border-color 0.2s, transform 0.15s;
}
.sms-staff-card:hover { border-color: var(--border2); transform: translateY(-1px); }
.sms-staff-card-top {
  display: flex; justify-content: space-between;
  align-items: flex-start; margin-bottom: 10px;
}
.sms-staff-name  { font-size: 14px; font-weight: 700; margin-bottom: 2px; }
.sms-staff-role  { font-size: 12px; color: var(--blue); font-weight: 600; margin-bottom: 2px; }
.sms-staff-meta  { font-size: 11px; color: var(--t3); margin-bottom: 6px; }
.sms-staff-salary {
  font-size: 14px; font-weight: 700; color: var(--amber);
  margin-bottom: 10px; font-family: 'JetBrains Mono', monospace;
}
.sms-staff-actions { display: flex; gap: 6px; flex-wrap: wrap; }

/* ══════════════════════════════════════════
   PAYROLL
══════════════════════════════════════════ */
.sms-payroll-list { display: flex; flex-direction: column; gap: 14px; }
.sms-payroll-card {
  background: var(--surface); border: 1px solid var(--border);
  border-radius: 12px; padding: 18px;
}
.sms-payroll-head {
  display: flex; justify-content: space-between; align-items: center;
  margin-bottom: 14px; flex-wrap: wrap; gap: 8px;
}
.sms-payroll-staff { display: flex; align-items: center; gap: 10px; }
.sms-payroll-name  { font-size: 14px; font-weight: 700; }
.sms-payroll-meta  { font-size: 12px; color: var(--t3); text-transform: capitalize; }
.sms-payroll-breakdown {
  background: var(--bg); border-radius: 8px; padding: 12px; margin-bottom: 12px;
  border: 1px solid var(--border);
}
.sms-bk-row {
  display: flex; justify-content: space-between; align-items: center;
  padding: 7px 0; border-bottom: 1px solid var(--border); font-size: 13px;
}
.sms-bk-row:last-child { border-bottom: none; }
.sms-bk-row span:first-child { color: var(--t2); }
.sms-bk-row.positive span:last-child { color: var(--green); }
.sms-bk-row.negative span:last-child { color: var(--red); }
.sms-bk-row.total {
  border-top: 2px solid var(--border2);
  padding-top: 10px; margin-top: 4px; border-bottom: none;
}
.sms-bk-row.total span:first-child { font-size: 14px; font-weight: 700; color: var(--text); }
.sms-bk-row.total span:last-child  { font-size: 16px; font-weight: 800; color: var(--green); font-family: 'JetBrains Mono', monospace; }
.sms-inline-input {
  background: rgba(255,255,255,0.04);
  border: 1px solid var(--border2);
  border-radius: 5px;
  padding: 4px 8px;
  color: var(--text);
  font-size: 13px; font-weight: 600;
  font-family: 'JetBrains Mono', monospace;
  text-align: right; width: 130px;
  transition: border-color 0.2s, background 0.2s; outline: none;
}
.sms-inline-input:focus { border-color: var(--blue); background: var(--surface2); }
.sms-inline-input.green { color: var(--green); }
.sms-inline-input.red   { color: var(--red);   }
.sms-payroll-actions { display: flex; gap: 8px; flex-wrap: wrap; }
.sms-payroll-totals {
  background: var(--surface); border: 1px solid var(--border2);
  border-radius: 12px; padding: 16px;
  display: flex; justify-content: space-between; align-items: center;
  flex-wrap: wrap; gap: 10px; font-size: 13px; font-weight: 600; color: var(--t2);
}
.sms-totals-row { display: flex; gap: 16px; flex-wrap: wrap; align-items: center; }
.sms-totals-row .amber  { color: var(--amber);  }
.sms-totals-row .green  { color: var(--green);  }
.sms-totals-row .blue   { color: var(--blue);   }
.sms-totals-row .red    { color: var(--red);    }
.sms-totals-row .purple { color: var(--purple); }

/* ══════════════════════════════════════════
   ADVANCES
══════════════════════════════════════════ */
.sms-advances-list { display: flex; flex-direction: column; gap: 14px; }
.sms-advance-card {
  background: var(--surface); border: 1px solid var(--border);
  border-radius: 12px; padding: 18px;
}
.sms-advance-head {
  display: flex; justify-content: space-between; align-items: flex-start;
  margin-bottom: 14px;
}
.sms-advance-name  { font-size: 14px; font-weight: 700; margin-bottom: 3px; }
.sms-advance-type  { font-size: 12px; color: var(--t3); }
.sms-advance-total { font-size: 18px; font-weight: 800; font-family: 'JetBrains Mono', monospace; }
.sms-advance-body  { background: var(--bg); border-radius: 8px; padding: 12px; border: 1px solid var(--border); }
.sms-advance-row   { display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 8px; }
.sms-advance-row span:first-child { color: var(--t2); }
.sms-progress { width: 100%; height: 6px; background: var(--border); border-radius: 3px; overflow: hidden; margin-top: 10px; }
.sms-progress-fill {
  height: 100%;
  background: linear-gradient(90deg, var(--purple), var(--green));
  border-radius: 3px; transition: width 0.5s ease;
}
.sms-progress-labels {
  display: flex; justify-content: space-between;
  font-size: 11px; color: var(--t3); margin-top: 5px;
}

/* ══════════════════════════════════════════
   LEAVES
══════════════════════════════════════════ */
.sms-leave-balance-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }
.sms-lb-card {
  background: var(--surface2); border: 1px solid var(--border);
  border-radius: 10px; padding: 12px; text-align: center;
}
.sms-lb-type  { font-size: 10px; color: var(--t3); text-transform: uppercase; letter-spacing: 0.06em; font-weight: 600; margin-bottom: 4px; }
.sms-lb-count { font-size: 22px; font-weight: 800; font-family: 'JetBrains Mono', monospace; }
.sms-lb-total { font-size: 11px; color: var(--t3); margin-top: 2px; }
.sms-leaves-list  { display: flex; flex-direction: column; gap: 10px; }
.sms-leave-card   { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; padding: 16px; }
.sms-leave-head   { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px; flex-wrap: wrap; gap: 8px; }
.sms-leave-name   { font-size: 13px; font-weight: 700; margin-bottom: 3px; }
.sms-leave-dates  { font-size: 12px; color: var(--t2); margin-bottom: 2px; }
.sms-leave-reason { font-size: 12px; color: var(--t3); font-style: italic; margin-bottom: 6px; }
.sms-leave-actions { display: flex; gap: 8px; margin-top: 10px; }

/* ══════════════════════════════════════════
   HISTORY
══════════════════════════════════════════ */
.sms-history-list { display: flex; flex-direction: column; gap: 8px; }
.sms-history-card {
  display: flex; align-items: center; gap: 12px;
  background: var(--surface); border: 1px solid var(--border);
  border-radius: 10px; padding: 14px;
}
.sms-hist-icon  { width: 38px; height: 38px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; }
.hist-salary    { background: rgba(79,142,255,0.12); }
.hist-bonus     { background: rgba(245,166,35,0.12); }
.hist-advance   { background: rgba(155,109,255,0.12); }
.sms-hist-name  { font-size: 13px; font-weight: 600; margin-bottom: 2px; }
.sms-hist-sub   { font-size: 11px; color: var(--t3); }
.sms-hist-ref   { font-size: 11px; color: var(--t3); font-family: 'JetBrains Mono', monospace; }
.sms-hist-amount { font-size: 14px; font-weight: 800; color: var(--green); font-family: 'JetBrains Mono', monospace; }
.sms-hist-date  { font-size: 11px; color: var(--t3); margin-top: 2px; }

/* ══════════════════════════════════════════
   DOCUMENTS
══════════════════════════════════════════ */
.sms-docs-grid  { display: grid; grid-template-columns: 1fr; gap: 14px; }
.sms-doc-card   { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; padding: 18px; }
.sms-doc-head   { display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; flex-wrap: wrap; gap: 8px; }
.sms-doc-items  { display: flex; flex-direction: column; gap: 6px; }
.sms-doc-item   { display: flex; align-items: center; gap: 8px; background: var(--bg); border-radius: 7px; padding: 9px 12px; font-size: 12px; color: var(--t2); }
.sms-doc-empty  { font-size: 12px; color: var(--t3); padding: 10px; text-align: center; }

/* ══════════════════════════════════════════
   MODAL OVERLAY
══════════════════════════════════════════ */
.sms-overlay {
  position: fixed; inset: 0; z-index: 1000;
  background: rgba(4, 6, 14, 0.88);
  backdrop-filter: blur(16px);
  display: flex; align-items: center; justify-content: center;
  padding: 16px;
}

/* ══════════════════════════════════════════
   MODAL WINDOW — fully rebuilt dark theme
══════════════════════════════════════════ */
.sms-modal {
  --modal-bg:      #0C1120;
  --modal-surface: #111827;
  --modal-border:  #1F2D4A;
  --modal-border2: #263350;

  background: var(--modal-bg);
  border: 1px solid var(--modal-border);
  border-radius: 18px;
  width: 100%;
  max-width: 580px;
  max-height: 92dvh;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  box-shadow:
    0 0 0 1px rgba(255,255,255,0.04),
    0 32px 96px rgba(0,0,0,0.75),
    0 8px 32px rgba(0,0,0,0.5);
}
.sms-modal-wide { max-width: 680px; }
.sms-modal-sm   { max-width: 440px; }

/* ── Modal Header ── */
.sms-modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 24px 18px;
  border-bottom: 1px solid var(--modal-border);
  background: var(--modal-surface);
  flex-shrink: 0;
}
.sms-modal-title-wrap {
  display: flex; align-items: center; gap: 12px;
}
.sms-modal-icon {
  width: 36px; height: 36px;
  border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  font-size: 16px; font-weight: 800; flex-shrink: 0;
  color: #fff;
}
.sms-modal-title {
  font-size: 17px; font-weight: 800; color: var(--text);
  letter-spacing: -0.02em;
}
.sms-modal-close {
  background: rgba(255,255,255,0.06);
  border: 1px solid var(--modal-border2);
  color: var(--t2);
  width: 34px; height: 34px;
  border-radius: 9px; cursor: pointer;
  font-size: 13px; font-weight: 700;
  display: flex; align-items: center; justify-content: center;
  transition: all 0.2s; flex-shrink: 0;
  font-family: 'Sora', sans-serif;
}
.sms-modal-close:hover {
  background: rgba(255,92,106,0.15);
  border-color: rgba(255,92,106,0.4);
  color: var(--red);
}

/* ── Modal Body ── */
.sms-modal-body {
  flex: 1;
  overflow-y: auto;
  padding: 22px 24px;
  scrollbar-width: thin;
  scrollbar-color: var(--modal-border2) transparent;
}
.sms-modal-body::-webkit-scrollbar       { width: 4px; }
.sms-modal-body::-webkit-scrollbar-thumb { background: var(--modal-border2); border-radius: 4px; }

/* ── Modal Footer ── */
.sms-modal-footer {
  display: flex; gap: 10px; justify-content: flex-end;
  padding: 18px 24px;
  border-top: 1px solid var(--modal-border);
  background: var(--modal-surface);
  flex-shrink: 0;
}

/* ══════════════════════════════════════════
   FORM LAYOUT INSIDE MODALS
══════════════════════════════════════════ */
.sms-form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
  margin-bottom: 6px;
}
.sms-fg { display: flex; flex-direction: column; gap: 7px; }
.sms-span2 { grid-column: 1 / -1; }

.sms-label {
  font-size: 11px; font-weight: 700;
  color: var(--t2);
  text-transform: uppercase;
  letter-spacing: 0.07em;
  display: flex; align-items: center; gap: 4px;
}
.sms-required { color: var(--red); }
.sms-hint { color: var(--t3); font-weight: 400; text-transform: none; font-size: 11px; }

/* Override input inside modal to use modal vars */
.sms-modal .sms-input,
.sms-modal .sms-select {
  background: rgba(255,255,255,0.04);
  border: 1px solid var(--modal-border2);
  color: var(--text);
}
.sms-modal .sms-input:focus,
.sms-modal .sms-select:focus {
  background: rgba(79,142,255,0.06);
  border-color: var(--blue);
  box-shadow: 0 0 0 3px rgba(79,142,255,0.12);
}
.sms-modal .sms-select option { background: #0C1120; }

/* ── Avatar Upload ── */
.sms-avatar-upload {
  display: flex; flex-direction: column; align-items: center; gap: 8px;
  background: rgba(255,255,255,0.03);
  border: 2px dashed var(--modal-border2);
  border-radius: 14px;
  padding: 20px 16px;
  cursor: pointer; margin-bottom: 20px;
  transition: border-color 0.2s, background 0.2s;
}
.sms-avatar-upload:hover {
  border-color: var(--blue);
  background: rgba(79,142,255,0.05);
}
.sms-avatar-hint { font-size: 12px; color: var(--t3); }

/* ── Form Error ── */
.sms-form-error {
  margin: 0 24px 4px;
  padding: 12px 16px;
  background: rgba(255,92,106,0.08);
  border: 1px solid rgba(255,92,106,0.3);
  border-radius: 10px;
  color: var(--red); font-size: 13px;
}

/* ── Detail ── */
.sms-detail-hero {
  display: flex; align-items: center; gap: 16px;
  background: rgba(255,255,255,0.03);
  border: 1px solid var(--modal-border);
  border-radius: 12px; padding: 16px; margin-bottom: 18px;
}
.sms-detail-name { font-size: 18px; font-weight: 800; margin-bottom: 3px; }
.sms-detail-role { font-size: 13px; color: var(--blue); margin-bottom: 7px; }
.sms-detail-grid {
  background: rgba(255,255,255,0.02);
  border: 1px solid var(--modal-border);
  border-radius: 10px; padding: 4px 14px; margin-bottom: 4px;
}
.sms-detail-row {
  display: flex; justify-content: space-between;
  padding: 9px 0; border-bottom: 1px solid var(--modal-border);
  font-size: 13px;
}
.sms-detail-row:last-child { border-bottom: none; }
.sms-detail-row span:first-child { color: var(--t2); }
.sms-detail-row .green { color: var(--green); font-weight: 700; }

/* ── Payroll Preview ── */
.sms-payroll-preview {
  background: rgba(255,255,255,0.03);
  border: 1px solid var(--modal-border2);
  border-radius: 12px; padding: 16px; margin-top: 18px;
}
.sms-preview-title {
  font-size: 12px; font-weight: 700; color: var(--green);
  margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.06em;
}
.sms-preview-row {
  display: flex; justify-content: space-between;
  font-size: 13px; padding: 7px 0;
  border-bottom: 1px solid var(--modal-border);
}
.sms-preview-row:last-child { border-bottom: none; }
.sms-preview-row span:first-child { color: var(--t2); }
.sms-preview-row.purple span:last-child { color: var(--purple); font-weight: 600; }
.sms-preview-row.green  span:last-child { color: var(--green);  font-weight: 600; }
.sms-preview-row.blue   span:last-child { color: var(--blue);   font-weight: 600; }
.sms-preview-row.red    span:last-child { color: var(--red);    font-weight: 600; }
.sms-preview-row.total  {
  border-top: 2px solid var(--modal-border2);
  padding-top: 12px; margin-top: 4px;
}
.sms-preview-row.total span:first-child { font-weight: 700; color: var(--text); font-size: 14px; }
.sms-preview-row.total span:last-child  { font-size: 20px; font-weight: 800; color: var(--amber); font-family: 'JetBrains Mono', monospace; }

/* ── SC Modal ── */
.sms-sc-summary {
  display: grid; grid-template-columns: repeat(3, 1fr);
  gap: 10px;
  background: rgba(255,255,255,0.03);
  border: 1px solid var(--modal-border);
  border-radius: 12px; padding: 16px;
  margin-bottom: 20px; text-align: center;
}
.sms-radio-label {
  display: flex; align-items: center; gap: 8px;
  padding: 10px 16px;
  background: rgba(255,255,255,0.04);
  border: 1px solid var(--modal-border2);
  border-radius: 8px; cursor: pointer;
  font-size: 13px; font-weight: 500; flex: 1;
  transition: all 0.2s;
}
.sms-radio-label.active { border-color: var(--amber); background: rgba(245,166,35,0.08); color: var(--amber); }
.sms-radio-label input  { accent-color: var(--amber); }
.sms-sc-table {
  background: rgba(255,255,255,0.02);
  border: 1px solid var(--modal-border);
  border-radius: 12px; overflow: hidden; margin-bottom: 6px;
}
.sms-sc-table-head {
  display: grid; grid-template-columns: 2fr 1fr 1fr 1fr;
  padding: 10px 16px;
  background: var(--modal-surface);
  border-bottom: 1px solid var(--modal-border);
  font-size: 10px; color: var(--t3); font-weight: 700;
  text-transform: uppercase; letter-spacing: 0.07em; gap: 8px;
}
.sms-sc-table-row {
  display: grid; grid-template-columns: 2fr 1fr 1fr 1fr;
  padding: 12px 16px;
  border-bottom: 1px solid var(--modal-border);
  align-items: center; gap: 8px;
  transition: background 0.15s;
}
.sms-sc-table-row:last-child { border-bottom: none; }
.sms-sc-table-row:hover { background: rgba(255,255,255,0.03); }

/* ── Generate Info ── */
.sms-generate-info {
  background: rgba(255,255,255,0.03);
  border: 1px solid var(--modal-border);
  border-radius: 10px; padding: 4px 14px; margin-bottom: 4px;
}
.sms-gen-row {
  display: flex; justify-content: space-between; align-items: center;
  padding: 10px 0; border-bottom: 1px solid var(--modal-border);
  font-size: 13px;
}
.sms-gen-row:last-child { border-bottom: none; }
.sms-gen-row span:first-child { color: var(--t2); }

/* ══════════════════════════════════════════
   MODAL TRANSITION
══════════════════════════════════════════ */
.modal-fade-enter-active {
  transition: all 0.28s cubic-bezier(0.16, 1, 0.3, 1);
}
.modal-fade-leave-active {
  transition: all 0.2s ease;
}
.modal-fade-enter-from {
  opacity: 0;
}
.modal-fade-enter-from .sms-modal {
  transform: scale(0.94) translateY(24px);
  opacity: 0;
}
.modal-fade-leave-to {
  opacity: 0;
}
.modal-fade-leave-to .sms-modal {
  transform: scale(0.96) translateY(12px);
  opacity: 0;
}

/* ══════════════════════════════════════════
   TOAST
══════════════════════════════════════════ */
.sms-toast {
  position: fixed; top: 72px; right: 16px; z-index: 3000;
  padding: 12px 20px; border-radius: 10px;
  font-size: 13px; font-weight: 600; font-family: 'Sora', sans-serif;
  box-shadow: 0 8px 30px rgba(0,0,0,0.5);
  display: flex; align-items: center; gap: 8px;
}
.sms-toast.success { background: var(--green); color: #051810; }
.sms-toast.error   { background: var(--red);   color: #fff; }
.sms-toast.info    { background: var(--blue);  color: #fff; }
.toast-enter-active, .toast-leave-active { transition: all 0.3s ease; }
.toast-enter-from, .toast-leave-to { transform: translateX(120%); opacity: 0; }

/* ══════════════════════════════════════════
   EMPTY STATE
══════════════════════════════════════════ */
.sms-empty {
  text-align: center; padding: 48px 20px;
  color: var(--t3); font-size: 14px;
  display: flex; flex-direction: column; align-items: center;
}

/* ══════════════════════════════════════════
   COLOR HELPERS
══════════════════════════════════════════ */
.amber  { color: var(--amber);  }
.green  { color: var(--green);  }
.blue   { color: var(--blue);   }
.red    { color: var(--red);    }
.purple { color: var(--purple); }

/* ══════════════════════════════════════════
   RESPONSIVE
══════════════════════════════════════════ */
@media (min-width: 600px) {
  .sms-stats-grid { grid-template-columns: repeat(4, 1fr); }
  .sms-staff-grid { grid-template-columns: repeat(2, 1fr); }
  .sms-docs-grid  { grid-template-columns: repeat(2, 1fr); }
}
@media (min-width: 900px) {
  .sms-staff-grid { grid-template-columns: repeat(3, 1fr); }
}
@media (max-width: 599px) {
  .sms-tab-label  { display: none; }
  .sms-tab        { padding: 0 10px; }
  .sms-form-grid  { grid-template-columns: 1fr; }
  .sms-span2      { grid-column: 1; }
  .sms-quick-nav  { grid-template-columns: repeat(4, 1fr); }
  .sms-sc-stats   { grid-template-columns: 1fr; gap: 6px; }
  .sms-toolbar    { flex-direction: column; align-items: stretch; }
  .sms-sc-table-head,
  .sms-sc-table-row { grid-template-columns: 2fr 0.6fr 0.6fr 1fr; }
  .sms-modal { border-radius: 14px; }
  .sms-modal-header { padding: 16px 18px 14px; }
  .sms-modal-body   { padding: 16px 18px; }
  .sms-modal-footer { padding: 14px 18px; }
}
</style>