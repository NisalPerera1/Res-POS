<template>
  <div class="sm-root">

    <!-- ── Sidebar ──────────────────────────────────────── -->
    <aside class="sm-sidebar">
      <div class="sm-brand">
        <span class="sm-brand-icon">⚑</span>
        <span class="sm-brand-text">Staff Hub</span>
      </div>

      <nav class="sm-nav">
        <button
          v-for="tab in tabs" :key="tab.key"
          @click="activeTab = tab.key"
          :class="['sm-nav-btn', { 'sm-nav-btn--active': activeTab === tab.key }]"
        >
          <span class="sm-nav-icon">{{ tab.icon }}</span>
          <span class="sm-nav-label">{{ tab.label }}</span>
          <span v-if="tab.count" class="sm-nav-badge">{{ tab.count }}</span>
        </button>
      </nav>

      <!-- Month selector (shared across tabs) -->
      <div class="sm-month-picker">
        <p class="sm-month-label">Period</p>
        <div class="sm-month-row">
          <select v-model="selectedMonth" @change="onPeriodChange" class="sm-select">
            <option v-for="m in months" :key="m.value" :value="m.value">{{ m.short }}</option>
          </select>
          <select v-model="selectedYear" @change="onPeriodChange" class="sm-select">
            <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
          </select>
        </div>
      </div>
    </aside>

    <!-- ── Main ─────────────────────────────────────────── -->
    <main class="sm-main">

      <!-- ══════════════════════ STAFF TAB ══════════════════════ -->
      <section v-if="activeTab === 'staff'" class="sm-section">
        <div class="sm-topbar">
          <div>
            <h1 class="sm-h1">Team Members</h1>
            <p class="sm-sub">{{ staffList.length }} total · {{ staffList.filter(s=>s.is_active).length }} active</p>
          </div>
          <button @click="openStaffModal()" class="sm-btn sm-btn--primary">
            + Add Member
          </button>
        </div>

        <!-- Search + filters -->
        <div class="sm-filter-row">
          <input v-model="staffSearch" id="staff-search" name="staff_search" placeholder="Search by name or ID…" class="sm-search" />
          <select v-model="staffRoleFilter" class="sm-select">
            <option value="">All Roles</option>
            <option v-for="r in roles" :key="r.value" :value="r.value">{{ r.label }}</option>
          </select>
          <select v-model="staffStatusFilter" class="sm-select">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>

        <!-- Grid -->
        <div class="sm-staff-grid">
          <div v-if="filteredStaff.length === 0" class="sm-empty">No staff members found.</div>
          <article
            v-for="s in filteredStaff" :key="s.id"
            class="sm-staff-card"
            :class="{ 'sm-staff-card--inactive': !s.is_active }"
          >
            <div class="sm-staff-avatar" :style="{ background: roleColor(s.role) }">
              {{ initials(s.name) }}
            </div>
            <div class="sm-staff-info">
              <h3 class="sm-staff-name">{{ s.name }}</h3>
              <p class="sm-staff-id">{{ s.employee_id }}</p>
              <span class="sm-role-pill" :style="{ background: roleColor(s.role) + '22', color: roleColor(s.role) }">
                {{ s.role }}
              </span>
            </div>
            <dl class="sm-staff-meta">
              <div><dt>Salary</dt><dd>Rs. {{ fmt(s.base_salary) }}</dd></div>
              <div><dt>Svc. %</dt><dd>{{ s.service_charge_pct }}%</dd></div>
              <div><dt>Type</dt><dd>{{ s.salary_type }}</dd></div>
            </dl>
            <div class="sm-staff-actions">
              <span class="sm-status-dot" :class="s.is_active ? 'sm-status-dot--on' : 'sm-status-dot--off'"></span>
              <button @click="openStaffModal(s)" class="sm-icon-btn" title="Edit">✎</button>
              <button @click="deleteStaff(s)" class="sm-icon-btn sm-icon-btn--danger" title="Delete">✕</button>
            </div>
          </article>
        </div>
      </section>

      <!-- ══════════════════════ LEAVES TAB ═════════════════════ -->
      <section v-if="activeTab === 'leaves'" class="sm-section">
        <div class="sm-topbar">
          <div>
            <h1 class="sm-h1">Leave Records</h1>
            <p class="sm-sub">{{ selectedMonthLabel }} {{ selectedYear }}</p>
          </div>
          <button @click="openLeaveModal()" class="sm-btn sm-btn--primary">+ Add Leave</button>
        </div>

        <!-- Summary pills -->
        <div class="sm-pill-row">
          <div class="sm-pill">
            <span class="sm-pill-num">{{ leaveStats.full }}</span>
            <span class="sm-pill-lbl">Full Days</span>
          </div>
          <div class="sm-pill">
            <span class="sm-pill-num">{{ leaveStats.half }}</span>
            <span class="sm-pill-lbl">Half Days</span>
          </div>
          <div class="sm-pill sm-pill--warn">
            <span class="sm-pill-num">{{ leaveStats.unpaid }}</span>
            <span class="sm-pill-lbl">Unpaid</span>
          </div>
        </div>

        <!-- Filter by staff -->
        <div class="sm-filter-row">
          <select v-model="leaveStaffFilter" class="sm-select">
            <option value="">All Staff</option>
            <option v-for="s in staffList" :key="s.id" :value="s.id">{{ s.name }}</option>
          </select>
        </div>

        <div class="sm-table-wrap">
          <table class="sm-table">
            <thead>
              <tr>
                <th>Staff</th><th>Date</th><th>Type</th><th>Paid?</th><th>Reason</th><th></th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="filteredLeaves.length === 0"><td colspan="6" class="sm-empty-td">No leave records.</td></tr>
              <tr v-for="lv in filteredLeaves" :key="lv.id">
                <td>
                  <strong>{{ lv.staff?.name }}</strong>
                  <span class="sm-muted">{{ lv.staff?.employee_id }}</span>
                </td>
                <td>{{ formatDate(lv.date) }}</td>
                <td>
                  <span :class="['sm-tag', lv.type === 'full_day' ? 'sm-tag--blue' : 'sm-tag--amber']">
                    {{ lv.type === 'full_day' ? 'Full Day' : 'Half Day' }}
                  </span>
                </td>
                <td>
                  <span :class="['sm-tag', lv.is_paid ? 'sm-tag--green' : 'sm-tag--red']">
                    {{ lv.is_paid ? 'Paid' : 'Unpaid' }}
                  </span>
                </td>
                <td class="sm-muted">{{ lv.reason || '—' }}</td>
                <td class="sm-row-actions">
                  <button @click="openLeaveModal(lv)" class="sm-icon-btn">✎</button>
                  <button @click="deleteLeave(lv)" class="sm-icon-btn sm-icon-btn--danger">✕</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <!-- ══════════════════════ ADVANCES TAB ═══════════════════ -->
      <section v-if="activeTab === 'advances'" class="sm-section">
        <div class="sm-topbar">
          <div>
            <h1 class="sm-h1">Salary Advances</h1>
            <p class="sm-sub">{{ selectedMonthLabel }} {{ selectedYear }}</p>
          </div>
          <button @click="openAdvanceModal()" class="sm-btn sm-btn--primary">+ Add Advance</button>
        </div>

        <div class="sm-pill-row">
          <div class="sm-pill">
            <span class="sm-pill-num">Rs. {{ fmt(advanceStats.total) }}</span>
            <span class="sm-pill-lbl">Total Advances</span>
          </div>
          <div class="sm-pill sm-pill--warn">
            <span class="sm-pill-num">{{ advanceStats.pending }}</span>
            <span class="sm-pill-lbl">Pending</span>
          </div>
          <div class="sm-pill sm-pill--ok">
            <span class="sm-pill-num">{{ advanceStats.approved }}</span>
            <span class="sm-pill-lbl">Approved</span>
          </div>
        </div>

        <div class="sm-table-wrap">
          <table class="sm-table">
            <thead>
              <tr><th>Staff</th><th>Date</th><th>Amount</th><th>Deduct Month</th><th>Status</th><th>Reason</th><th></th></tr>
            </thead>
            <tbody>
              <tr v-if="advances.length === 0"><td colspan="7" class="sm-empty-td">No advance records.</td></tr>
              <tr v-for="adv in advances" :key="adv.id">
                <td><strong>{{ adv.staff?.name }}</strong></td>
                <td>{{ formatDate(adv.date) }}</td>
                <td><strong>Rs. {{ fmt(adv.amount) }}</strong></td>
                <td>{{ adv.deduct_month ? months[adv.deduct_month-1].label + ' ' + adv.deduct_year : '—' }}</td>
                <td>
                  <span :class="['sm-tag', advStatusClass(adv.status)]">{{ adv.status }}</span>
                </td>
                <td class="sm-muted">{{ adv.reason || '—' }}</td>
                <td class="sm-row-actions">
                  <button @click="openAdvanceModal(adv)" class="sm-icon-btn">✎</button>
                  <button @click="deleteAdvance(adv)" class="sm-icon-btn sm-icon-btn--danger">✕</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <!-- ══════════════════════ PAYROLL TAB ════════════════════ -->
      <section v-if="activeTab === 'payroll'" class="sm-section">
        <div class="sm-topbar">
          <div>
            <h1 class="sm-h1">Payroll</h1>
            <p class="sm-sub">{{ selectedMonthLabel }} {{ selectedYear }}</p>
          </div>
          <button @click="openGenerateModal()" class="sm-btn sm-btn--primary">⚙ Generate</button>
        </div>

        <!-- Summary bar -->
        <div v-if="payrollSummary" class="sm-payroll-summary">
          <div class="sm-summary-item">
            <span>Total Gross</span>
            <strong>Rs. {{ fmt(payrollSummary.total_gross) }}</strong>
          </div>
          <div class="sm-summary-divider"></div>
          <div class="sm-summary-item sm-summary-item--red">
            <span>Total Deductions</span>
            <strong>Rs. {{ fmt(payrollSummary.total_deductions) }}</strong>
          </div>
          <div class="sm-summary-divider"></div>
          <div class="sm-summary-item sm-summary-item--green">
            <span>Net Payable</span>
            <strong>Rs. {{ fmt(payrollSummary.total_net) }}</strong>
          </div>
          <div class="sm-summary-divider"></div>
          <div class="sm-summary-item">
            <span>Service Charge</span>
            <strong>Rs. {{ fmt(payrollSummary.total_service) }}</strong>
          </div>
        </div>

        <!-- Payroll cards -->
        <div class="sm-payroll-list">
          <div v-if="payrolls.length === 0" class="sm-empty">No payroll records for this period.</div>
          <article v-for="p in payrolls" :key="p.id" class="sm-payroll-card">
            <div class="sm-payroll-head">
              <div class="sm-payroll-who">
                <span class="sm-payroll-avatar" :style="{ background: roleColor(p.staff?.role) }">
                  {{ initials(p.staff?.name || '') }}
                </span>
                <div>
                  <h3>{{ p.staff?.name }}</h3>
                  <p>{{ p.staff?.employee_id }} · {{ p.staff?.role }}</p>
                </div>
              </div>
              <span :class="['sm-tag sm-tag--lg', payrollStatusClass(p.status)]">
                {{ p.status.toUpperCase() }}
              </span>
            </div>

            <div class="sm-payroll-body">
              <div class="sm-payroll-col sm-payroll-col--earn">
                <p class="sm-col-head">Earnings</p>
                <dl>
                  <div><dt>Base</dt><dd>Rs. {{ fmt(p.base_salary) }}</dd></div>
                  <div><dt>Service</dt><dd>Rs. {{ fmt(p.service_charge) }}</dd></div>
                  <div><dt>Bonus</dt><dd>Rs. {{ fmt(p.bonus) }}</dd></div>
                  <div class="sm-total"><dt>Gross</dt><dd>Rs. {{ fmt(p.gross_pay) }}</dd></div>
                </dl>
              </div>
              <div class="sm-payroll-col sm-payroll-col--deduct">
                <p class="sm-col-head">Deductions</p>
                <dl>
                  <div><dt>Leaves</dt><dd>Rs. {{ fmt(p.leave_deductions) }}</dd></div>
                  <div><dt>Advances</dt><dd>Rs. {{ fmt(p.advance_deductions) }}</dd></div>
                  <div><dt>Other</dt><dd>Rs. {{ fmt(p.other_deductions) }}</dd></div>
                  <div class="sm-total"><dt>Total</dt><dd>Rs. {{ fmt(p.total_deductions) }}</dd></div>
                </dl>
              </div>
              <div class="sm-payroll-col sm-payroll-col--net">
                <p class="sm-col-head">Net Pay</p>
                <p class="sm-net-amount">Rs. {{ fmt(p.net_pay) }}</p>
                <p class="sm-leave-info">{{ p.leaves_taken }}d + {{ p.half_leaves_taken }}½ leaves</p>
              </div>
            </div>

            <div class="sm-payroll-foot">
              <button @click="openPayrollEditModal(p)" class="sm-btn sm-btn--ghost">Edit</button>
              <button
                v-if="p.status === 'draft'"
                @click="approvePayroll(p)"
                class="sm-btn sm-btn--ok"
              >✓ Approve</button>
              <button
                v-if="p.status === 'approved'"
                @click="markPaid(p)"
                class="sm-btn sm-btn--primary"
              >Mark Paid</button>
            </div>
          </article>
        </div>
      </section>

      <!-- ══════════════════════ SERVICE CHARGE TAB ═════════════ -->
      <section v-if="activeTab === 'service-charge'" class="sm-section">
        <div class="sm-topbar">
          <div>
            <h1 class="sm-h1">Service Charge</h1>
            <p class="sm-sub">{{ selectedMonthLabel }} {{ selectedYear }}</p>
          </div>
          <div class="sm-btn-group">
            <button @click="distributeServiceCharge()" class="sm-btn sm-btn--secondary">Distribute from Orders</button>
            <button @click="openSvcModal()" class="sm-btn sm-btn--primary">Update Pool</button>
          </div>
        </div>

        <div v-if="svcDist" class="sm-svc-wrap">
          <div class="sm-svc-total">
            <span class="sm-svc-total-label">Total Pool</span>
            <span class="sm-svc-total-amt">Rs. {{ fmt(svcDist.total_service_charge) }}</span>
          </div>

          <!-- Bar chart -->
          <div class="sm-svc-bars">
            <div
              v-for="item in svcDist.distribution" :key="item.staff_id"
              class="sm-svc-bar-row"
            >
              <span class="sm-svc-bar-name">{{ item.name }}</span>
              <div class="sm-svc-bar-track">
                <div
                  class="sm-svc-bar-fill"
                  :style="{ width: item.service_charge_pct + '%' }"
                ></div>
              </div>
              <span class="sm-svc-bar-pct">{{ item.service_charge_pct }}%</span>
              <span class="sm-svc-bar-amt">Rs. {{ fmt(item.share_amount) }}</span>
            </div>
          </div>
        </div>
        <div v-else class="sm-empty">No service charge data for this period.</div>
      </section>

    </main>

    <!-- ═════════════════════════════════════════════════════
         MODALS
    ════════════════════════════════════════════════════════ -->

    <!-- Staff Modal -->
    <Teleport to="body">
      <div v-if="showStaffModal" class="sm-overlay" @click.self="closeStaffModal">
        <div class="sm-modal">
          <div class="sm-modal-head">
            <h2>{{ editingStaff ? 'Edit Member' : 'New Member' }}</h2>
            <button @click="closeStaffModal" class="sm-modal-close">✕</button>
          </div>
          <form @submit.prevent="saveStaff" class="sm-modal-body">
            <div class="sm-form-grid sm-form-grid--2">
              <label class="sm-label">
                Name *
                <input v-model="staffForm.name" id="staff-name" name="name" required class="sm-input" autocomplete="name" />
              </label>
              <label class="sm-label">
                Phone
                <input v-model="staffForm.phone" id="staff-phone" name="phone" class="sm-input" autocomplete="tel" />
              </label>
              <label class="sm-label">
                Email
                <input v-model="staffForm.email" id="staff-email" name="email" type="email" class="sm-input" autocomplete="email" />
              </label>
              <label class="sm-label">
                NIC
                <input v-model="staffForm.nic" id="staff-nic" name="nic" class="sm-input" autocomplete="off" />
              </label>
              <label class="sm-label">
                Role *
                <select v-model="staffForm.role" required class="sm-input">
                  <option value="">Select role</option>
                  <option v-for="r in roles" :key="r.value" :value="r.value">{{ r.label }}</option>
                </select>
              </label>
              <label class="sm-label">
                Joined Date *
                <input v-model="staffForm.joined_date" id="staff-joined-date" name="joined_date" type="date" required class="sm-input" autocomplete="off" />
              </label>
              <label class="sm-label">
                Salary Type *
                <select v-model="staffForm.salary_type" required class="sm-input">
                  <option value="monthly">Monthly</option>
                  <option value="daily">Daily</option>
                  <option value="hourly">Hourly</option>
                </select>
              </label>
              <label class="sm-label">
                Base Salary (Rs.) *
                <input v-model.number="staffForm.base_salary" id="staff-base-salary" name="base_salary" type="number" step="0.01" min="0" required class="sm-input" autocomplete="off" />
              </label>
                            <label class="sm-label">
                Bank Name
                <input v-model="staffForm.bank_name" id="staff-bank-name" name="bank_name" class="sm-input" autocomplete="organization" />
              </label>
              <label class="sm-label" style="grid-column: 1/-1">
                Address
                <input v-model="staffForm.address" id="staff-address" name="address" class="sm-input" autocomplete="street-address" />
              </label>
              <label class="sm-checkbox-label">
                <input v-model="staffForm.is_active" id="staff-is-active" name="is_active" type="checkbox" autocomplete="off" />
                Active employee
              </label>
            </div>
            <div class="sm-modal-foot">
              <button type="button" @click="closeStaffModal" class="sm-btn sm-btn--ghost">Cancel</button>
              <button type="submit" class="sm-btn sm-btn--primary">
                {{ editingStaff ? 'Update' : 'Save Member' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

    <!-- Leave Modal -->
    <Teleport to="body">
      <div v-if="showLeaveModal" class="sm-overlay" @click.self="closeLeaveModal">
        <div class="sm-modal sm-modal--sm">
          <div class="sm-modal-head">
            <h2>{{ editingLeave ? 'Edit Leave' : 'Record Leave' }}</h2>
            <button @click="closeLeaveModal" class="sm-modal-close">✕</button>
          </div>
          <form @submit.prevent="saveLeave" class="sm-modal-body">
            <div class="sm-form-grid sm-form-grid--2">
              <label class="sm-label" style="grid-column:1/-1">
                Staff Member *
                <select v-model="leaveForm.staff_id" required class="sm-input">
                  <option value="">Select staff</option>
                  <option v-for="s in staffList" :key="s.id" :value="s.id">{{ s.name }}</option>
                </select>
              </label>
              <label class="sm-label">
                Date *
                <input v-model="leaveForm.date" id="leave-date" name="date" type="date" required class="sm-input" autocomplete="off" />
              </label>
              <label class="sm-label">
                Type *
                <select v-model="leaveForm.type" required class="sm-input">
                  <option value="full_day">Full Day</option>
                  <option value="half_day">Half Day</option>
                </select>
              </label>
              <label class="sm-label" v-if="leaveForm.type === 'half_day'">
                Period
                <select v-model="leaveForm.half_day_period" class="sm-input">
                  <option value="morning">Morning</option>
                  <option value="afternoon">Afternoon</option>
                </select>
              </label>
              <label class="sm-label" style="grid-column:1/-1">
                Reason
                <input v-model="leaveForm.reason" id="leave-reason" name="reason" class="sm-input" autocomplete="off" />
              </label>
              <label class="sm-checkbox-label">
                <input v-model="leaveForm.is_paid" id="leave-is-paid" name="is_paid" type="checkbox" autocomplete="off" />
                Paid leave
              </label>
            </div>
            <div class="sm-modal-foot">
              <button type="button" @click="closeLeaveModal" class="sm-btn sm-btn--ghost">Cancel</button>
              <button type="submit" class="sm-btn sm-btn--primary">
                {{ editingLeave ? 'Update' : 'Save Leave' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

    <!-- Advance Modal -->
    <Teleport to="body">
      <div v-if="showAdvanceModal" class="sm-overlay" @click.self="closeAdvanceModal">
        <div class="sm-modal sm-modal--sm">
          <div class="sm-modal-head">
            <h2>{{ editingAdvance ? 'Edit Advance' : 'Record Advance' }}</h2>
            <button @click="closeAdvanceModal" class="sm-modal-close">✕</button>
          </div>
          <form @submit.prevent="saveAdvance" class="sm-modal-body">
            <div class="sm-form-grid sm-form-grid--2">
              <label class="sm-label" style="grid-column:1/-1">
                Staff Member *
                <select v-model="advanceForm.staff_id" required class="sm-input">
                  <option value="">Select staff</option>
                  <option v-for="s in staffList" :key="s.id" :value="s.id">{{ s.name }}</option>
                </select>
              </label>
              <label class="sm-label">
                Date *
                <input v-model="advanceForm.date" id="advance-date" name="date" type="date" required class="sm-input" autocomplete="off" />
              </label>
              <label class="sm-label">
                Amount (Rs.) *
                <input v-model.number="advanceForm.amount" id="advance-amount" name="amount" type="number" min="1" required class="sm-input" autocomplete="off" />
              </label>
              <label class="sm-label">
                Deduct Month
                <select v-model.number="advanceForm.deduct_month" class="sm-input">
                  <option value="">—</option>
                  <option v-for="m in months" :key="m.value" :value="m.value">{{ m.label }}</option>
                </select>
              </label>
              <label class="sm-label">
                Deduct Year
                <select v-model.number="advanceForm.deduct_year" class="sm-input">
                  <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
                </select>
              </label>
                            <label class="sm-label" style="grid-column:1/-1">
                Reason
                <input v-model="advanceForm.reason" id="advance-reason" name="reason" class="sm-input" autocomplete="off" />
              </label>
              <label class="sm-label" style="grid-column:1/-1">
                Notes
                <input v-model="advanceForm.notes" id="advance-notes" name="notes" class="sm-input" placeholder="Additional notes..." autocomplete="off" />
              </label>
            </div>
            <div class="sm-modal-foot">
              <button type="button" @click="closeAdvanceModal" class="sm-btn sm-btn--ghost">Cancel</button>
              <button type="submit" class="sm-btn sm-btn--primary">
                {{ editingAdvance ? 'Update' : 'Save Advance' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

    <!-- Generate Payroll Modal -->
    <Teleport to="body">
      <div v-if="showGenerateModal" class="sm-overlay" @click.self="closeGenerateModal">
        <div class="sm-modal sm-modal--sm">
          <div class="sm-modal-head">
            <h2>Generate Payroll</h2>
            <button @click="closeGenerateModal" class="sm-modal-close">✕</button>
          </div>
          <form @submit.prevent="confirmGenerate" class="sm-modal-body">
            <div class="sm-form-grid sm-form-grid--2">
              <label class="sm-label">
                Month *
                <select v-model.number="generateForm.month" required class="sm-input">
                  <option v-for="m in months" :key="m.value" :value="m.value">{{ m.label }}</option>
                </select>
              </label>
              <label class="sm-label">
                Year *
                <select v-model.number="generateForm.year" required class="sm-input">
                  <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
                </select>
              </label>
            </div>
            <div class="sm-staff-checkboxes">
              <p class="sm-label-text">Select staff members</p>
              <label class="sm-checkbox-label sm-checkbox-all">
                <input type="checkbox" @change="toggleAllStaff" :checked="generateForm.staff_ids.length === staffList.length" />
                Select All
              </label>
              <label
                v-for="s in staffList" :key="s.id"
                class="sm-checkbox-label"
              >
                <input v-model="generateForm.staff_ids" :value="s.id" type="checkbox" />
                {{ s.name }} <span class="sm-muted">({{ s.employee_id }})</span>
              </label>
            </div>
            <div class="sm-modal-foot">
              <button type="button" @click="closeGenerateModal" class="sm-btn sm-btn--ghost">Cancel</button>
              <button type="submit" class="sm-btn sm-btn--primary" :disabled="generateForm.staff_ids.length === 0">
                Generate ({{ generateForm.staff_ids.length }})
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

    <!-- Payroll Edit Modal -->
    <Teleport to="body">
      <div v-if="showPayrollEditModal" class="sm-overlay" @click.self="closePayrollEditModal">
        <div class="sm-modal sm-modal--sm">
          <div class="sm-modal-head">
            <h2>Edit Payroll</h2>
            <button @click="closePayrollEditModal" class="sm-modal-close">✕</button>
          </div>
          <form @submit.prevent="savePayrollEdit" class="sm-modal-body">
            <div class="sm-form-grid sm-form-grid--2">
              <label class="sm-label">
                Bonus (Rs.)
                <input v-model.number="payrollEditForm.bonus" id="payroll-bonus" name="bonus" type="number" min="0" step="0.01" class="sm-input" autocomplete="off" />
              </label>
              <label class="sm-label">
                Overtime Pay (Rs.)
                <input v-model.number="payrollEditForm.overtime_pay" id="payroll-overtime-pay" name="overtime_pay" type="number" min="0" step="0.01" class="sm-input" autocomplete="off" />
              </label>
              <label class="sm-label" style="grid-column:1/-1">
                Other Deductions (Rs.)
                <input v-model.number="payrollEditForm.other_deductions" id="payroll-other-deductions" name="other_deductions" type="number" min="0" step="0.01" class="sm-input" autocomplete="off" />
              </label>
              <label class="sm-label" style="grid-column:1/-1">
                Notes
                <textarea v-model="payrollEditForm.notes" class="sm-input sm-textarea"></textarea>
              </label>
            </div>
            <div class="sm-modal-foot">
              <button type="button" @click="closePayrollEditModal" class="sm-btn sm-btn--ghost">Cancel</button>
              <button type="submit" class="sm-btn sm-btn--primary">Save Changes</button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

    <!-- Service Charge Pool Modal -->
    <Teleport to="body">
      <div v-if="showSvcModal" class="sm-overlay" @click.self="closeSvcModal">
        <div class="sm-modal sm-modal--sm">
          <div class="sm-modal-head">
            <h2>Update Service Charge Pool</h2>
            <button @click="closeSvcModal" class="sm-modal-close">✕</button>
          </div>
          <form @submit.prevent="saveSvcPool" class="sm-modal-body">
            <label class="sm-label">
              Total Service Charge Collected (Rs.) *
              <input v-model.number="svcForm.total_amount" id="svc-total-amount" name="total_amount" type="number" min="0" step="0.01" required class="sm-input" autocomplete="off" />
            </label>
            <p class="sm-hint">
              This will be distributed among active staff according to their service charge percentages.
            </p>
            <div class="sm-modal-foot">
              <button type="button" @click="closeSvcModal" class="sm-btn sm-btn--ghost">Cancel</button>
              <button type="submit" class="sm-btn sm-btn--primary">Update & Distribute</button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

    <!-- Toast notifications -->
    <div class="sm-toast-stack">
      <transition-group name="toast">
        <div v-for="t in toasts" :key="t.id" :class="['sm-toast', 'sm-toast--' + t.type]">
          {{ t.msg }}
        </div>
      </transition-group>
    </div>

  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import axios from 'axios'

// ── Period ────────────────────────────────────────────────
const selectedMonth = ref(new Date().getMonth() + 1)
const selectedYear  = ref(new Date().getFullYear())

// ── Tab state ─────────────────────────────────────────────
const activeTab = ref('staff')

const tabs = computed(() => [
  { key: 'staff',          icon: '👥', label: 'Staff',          count: staffList.value.filter(s => s.is_active).length },
  { key: 'leaves',         icon: '📅', label: 'Leaves',         count: null },
  { key: 'advances',       icon: '💸', label: 'Advances',       count: null },
  { key: 'payroll',        icon: '🧾', label: 'Payroll',        count: null },
  { key: 'service-charge', icon: '⚡', label: 'Service Charge', count: null },
])

// ── Data ──────────────────────────────────────────────────
const staffList    = ref([])
const leaves       = ref([])
const advances     = ref([])
const payrolls     = ref([])
const payrollSummary = ref(null)
const svcDist      = ref(null)

// ── Filters ───────────────────────────────────────────────
const staffSearch       = ref('')
const staffRoleFilter   = ref('')
const staffStatusFilter = ref('')
const leaveStaffFilter  = ref('')

// ── Modal visibility ──────────────────────────────────────
const showStaffModal       = ref(false)
const showLeaveModal       = ref(false)
const showAdvanceModal     = ref(false)
const showGenerateModal    = ref(false)
const showPayrollEditModal = ref(false)
const showSvcModal         = ref(false)

// ── Editing targets ───────────────────────────────────────
const editingStaff    = ref(null)
const editingLeave    = ref(null)
const editingAdvance  = ref(null)
const editingPayroll  = ref(null)

// ── Forms ─────────────────────────────────────────────────
const staffForm = reactive({
  name: '', phone: '', email: '', nic: '', address: '',
  joined_date: '', role: '', salary_type: 'monthly',
  base_salary: '', is_active: true,
  bank_name: '', bank_account: '',
})

const leaveForm = reactive({
  staff_id: '', date: '', type: 'full_day',
  half_day_period: 'morning', reason: '', is_paid: false,
})

const advanceForm = reactive({
  staff_id: '', date: '', amount: '',
  reason: '', deduct_month: new Date().getMonth() + 1,
  deduct_year: new Date().getFullYear(),
  notes: '',
})

const generateForm = reactive({
  month: new Date().getMonth() + 1,
  year: new Date().getFullYear(),
  staff_ids: [],
})

const payrollEditForm = reactive({
  bonus: 0, overtime_pay: 0, other_deductions: 0, notes: '',
})

const svcForm = reactive({ total_amount: 0 })

// ── Static data ───────────────────────────────────────────
const roles = [
  { value: 'admin',     label: 'Admin' },
  { value: 'manager',   label: 'Manager' },
  { value: 'cashier',   label: 'Cashier' },
  { value: 'waiter',    label: 'Waiter' },
  { value: 'kitchen',   label: 'Kitchen' },
  { value: 'bartender', label: 'Bartender' },
  { value: 'delivery',  label: 'Delivery' },
]

const months = [
  { value: 1, label: 'January',   short: 'Jan' },
  { value: 2, label: 'February',  short: 'Feb' },
  { value: 3, label: 'March',     short: 'Mar' },
  { value: 4, label: 'April',     short: 'Apr' },
  { value: 5, label: 'May',       short: 'May' },
  { value: 6, label: 'June',      short: 'Jun' },
  { value: 7, label: 'July',      short: 'Jul' },
  { value: 8, label: 'August',    short: 'Aug' },
  { value: 9, label: 'September', short: 'Sep' },
  { value: 10, label: 'October',  short: 'Oct' },
  { value: 11, label: 'November', short: 'Nov' },
  { value: 12, label: 'December', short: 'Dec' },
]

const years = computed(() => {
  const y = new Date().getFullYear()
  return [y - 2, y - 1, y, y + 1]
})

const selectedMonthLabel = computed(() => months.find(m => m.value === selectedMonth.value)?.label ?? '')

// ── Computed filters ──────────────────────────────────────
const filteredStaff = computed(() => {
  return staffList.value.filter(s => {
    const matchSearch = !staffSearch.value ||
      s.name.toLowerCase().includes(staffSearch.value.toLowerCase()) ||
      s.employee_id.toLowerCase().includes(staffSearch.value.toLowerCase())
    const matchRole   = !staffRoleFilter.value || s.role === staffRoleFilter.value
    const matchStatus = !staffStatusFilter.value ||
      (staffStatusFilter.value === 'active' ? s.is_active : !s.is_active)
    return matchSearch && matchRole && matchStatus
  })
})

const filteredLeaves = computed(() => {
  return leaves.value.filter(lv =>
    !leaveStaffFilter.value || lv.staff_id === leaveStaffFilter.value
  )
})

const leaveStats = computed(() => ({
  full:   filteredLeaves.value.filter(l => l.type === 'full_day').length,
  half:   filteredLeaves.value.filter(l => l.type === 'half_day').length,
  unpaid: filteredLeaves.value.filter(l => !l.is_paid).length,
}))

const advanceStats = computed(() => ({
  total:    advances.value.reduce((s, a) => s + parseFloat(a.amount || 0), 0),
  pending:  advances.value.filter(a => a.status === 'pending').length,
  approved: advances.value.filter(a => a.status === 'approved').length,
}))

// ── Toast ─────────────────────────────────────────────────
const toasts = ref([])
function toast(msg, type = 'success') {
  const id = Date.now()
  toasts.value.push({ id, msg, type })
  setTimeout(() => toasts.value = toasts.value.filter(t => t.id !== id), 3500)
}

// ── API calls ─────────────────────────────────────────────
async function loadStaff() {
  try {
    const { data } = await axios.get('/staff')
    staffList.value = data
  } catch { toast('Failed to load staff', 'error') }
}

async function loadLeaves() {
  try {
    const { data } = await axios.get('/staff/leaves/all', {
      params: { month: selectedMonth.value, year: selectedYear.value }
    })
    leaves.value = data
  } catch { toast('Failed to load leaves', 'error') }
}

async function loadAdvances() {
  try {
    console.log('Loading advances', { month: selectedMonth.value, year: selectedYear.value })
    const { data } = await axios.get('/staff/advances/all', {
      params: { month: selectedMonth.value, year: selectedYear.value }
    })
    console.log('Advances loaded', data)
    advances.value = data
  } catch (e) { 
    console.error('Load advances error', e)
    toast('Failed to load advances', 'error') 
  }
}

async function loadPayrolls() {
  try {
    const [pRes, sRes] = await Promise.all([
      axios.get('/staff/payrolls', {
        params: { month: selectedMonth.value, year: selectedYear.value }
      }),
      axios.get('/staff/payrolls/summary', {
        params: { month: selectedMonth.value, year: selectedYear.value }
      }),
    ])
    payrolls.value      = pRes.data
    payrollSummary.value = sRes.data
  } catch { toast('Failed to load payrolls', 'error') }
}

async function loadSvcDist() {
  try {
    const { data } = await axios.get('/staff/service-charge-distribution', {
      params: { month: selectedMonth.value, year: selectedYear.value }
    })
    svcDist.value = data
  } catch { toast('Failed to load service charge data', 'error') }
}

function onPeriodChange() {
  const tab = activeTab.value
  if (tab === 'leaves')         loadLeaves()
  if (tab === 'advances')       loadAdvances()
  if (tab === 'payroll')        loadPayrolls()
  if (tab === 'service-charge') loadSvcDist()
}

watch(activeTab, (tab) => {
  if (tab === 'leaves')         loadLeaves()
  if (tab === 'advances')       loadAdvances()
  if (tab === 'payroll')        loadPayrolls()
  if (tab === 'service-charge') loadSvcDist()
})

// ── Staff CRUD ────────────────────────────────────────────
function openStaffModal(s = null) {
  editingStaff.value = s
  if (s) Object.assign(staffForm, s)
  else resetForm(staffForm, { name: '', phone: '', email: '', nic: '', address: '',
    joined_date: '', role: '', salary_type: 'monthly',
    base_salary: '', is_active: true,
    bank_name: '', bank_account: '' })
  showStaffModal.value = true
}
function closeStaffModal() { showStaffModal.value = false; editingStaff.value = null }

async function saveStaff() {
  try {
    if (editingStaff.value) {
      await axios.put(`/staff/${editingStaff.value.id}`, staffForm)
      toast('Staff member updated')
    } else {
      await axios.post('/staff', staffForm)
      toast('Staff member added')
    }
    closeStaffModal()
    await loadStaff()
  } catch (e) {
    toast(e.response?.data?.message || 'Failed to save', 'error')
  }
}

async function deleteStaff(s) {
  if (!confirm(`Remove ${s.name} from staff?`)) return
  try {
    await axios.delete(`/staff/${s.id}`)
    toast('Staff member removed')
    await loadStaff()
  } catch { toast('Failed to delete', 'error') }
}

// ── Leave CRUD ────────────────────────────────────────────
function openLeaveModal(lv = null) {
  editingLeave.value = lv
  if (lv) Object.assign(leaveForm, lv)
  else resetForm(leaveForm, { staff_id: '', date: '', type: 'full_day', half_day_period: 'morning', reason: '', is_paid: false })
  showLeaveModal.value = true
}
function closeLeaveModal() { showLeaveModal.value = false; editingLeave.value = null }

async function saveLeave() {
  try {
    if (editingLeave.value) {
      await axios.put(`/staff/leaves/${editingLeave.value.id}`, leaveForm)
      toast('Leave updated')
    } else {
      await axios.post('/staff/leaves', leaveForm)
      toast('Leave recorded')
    }
    closeLeaveModal()
    await loadLeaves()
  } catch (e) { toast(e.response?.data?.message || 'Failed to save', 'error') }
}

async function deleteLeave(lv) {
  if (!confirm('Delete this leave record?')) return
  try {
    await axios.delete(`/staff/leaves/${lv.id}`)
    toast('Leave deleted')
    await loadLeaves()
  } catch { toast('Failed to delete', 'error') }
}

// ── Advance CRUD ──────────────────────────────────────────
function openAdvanceModal(adv = null) {
  editingAdvance.value = adv
  if (adv) Object.assign(advanceForm, adv)
  else resetForm(advanceForm, {
    staff_id: '', date: '', amount: '', reason: '',
    deduct_month: selectedMonth.value, deduct_year: selectedYear.value, notes: '',
  })
  showAdvanceModal.value = true
}
function closeAdvanceModal() { showAdvanceModal.value = false; editingAdvance.value = null }

async function saveAdvance() {
  try {
    console.log('saveAdvance called', { editing: editingAdvance.value, formData: advanceForm })
    
    if (editingAdvance.value) {
      console.log('Updating advance', editingAdvance.value.id)
      const response = await axios.put(`/staff/advances/${editingAdvance.value.id}`, advanceForm)
      console.log('Update response', response)
      toast('Advance updated')
    } else {
      console.log('Creating new advance')
      const response = await axios.post('/staff/advances', advanceForm)
      console.log('Create response', response)
      toast('Advance recorded')
    }
    closeAdvanceModal()
    await loadAdvances()
  } catch (e) { 
    console.error('Save advance error', e)
    toast(e.response?.data?.message || 'Failed to save', 'error') 
  }
}

async function deleteAdvance(adv) {
  if (!confirm('Delete this advance record?')) return
  try {
    await axios.delete(`/staff/advances/${adv.id}`)
    toast('Advance deleted')
    await loadAdvances()
  } catch { toast('Failed to delete', 'error') }
}

// ── Payroll ───────────────────────────────────────────────
function openGenerateModal() {
  generateForm.month = selectedMonth.value
  generateForm.year  = selectedYear.value
  generateForm.staff_ids = []
  showGenerateModal.value = true
}
function closeGenerateModal() { showGenerateModal.value = false }
function toggleAllStaff(e) {
  generateForm.staff_ids = e.target.checked ? staffList.value.map(s => s.id) : []
}

async function confirmGenerate() {
  try {
    const { data } = await axios.post('/staff/payrolls/generate', generateForm)
    toast(data.message)
    closeGenerateModal()
    await loadPayrolls()
  } catch (e) { toast(e.response?.data?.message || 'Generation failed', 'error') }
}

function openPayrollEditModal(p) {
  editingPayroll.value = p
  Object.assign(payrollEditForm, {
    bonus: p.bonus, overtime_pay: p.overtime_pay,
    other_deductions: p.other_deductions, notes: p.notes || '',
  })
  showPayrollEditModal.value = true
}
function closePayrollEditModal() { showPayrollEditModal.value = false; editingPayroll.value = null }

async function savePayrollEdit() {
  try {
    await axios.put(`/staff/payrolls/${editingPayroll.value.id}`, payrollEditForm)
    toast('Payroll updated')
    closePayrollEditModal()
    await loadPayrolls()
  } catch { toast('Failed to update', 'error') }
}

async function approvePayroll(p) {
  try {
    await axios.put(`/staff/payrolls/${p.id}`, { status: 'approved' })
    toast('Payroll approved')
    await loadPayrolls()
  } catch { toast('Failed to approve', 'error') }
}

async function markPaid(p) {
  try {
    await axios.put(`/staff/payrolls/${p.id}`, {
      status: 'paid',
      paid_date: new Date().toISOString().split('T')[0],
    })
    toast('Payroll marked as paid')
    await loadPayrolls()
  } catch { toast('Failed to update', 'error') }
}

// ── Service Charge ────────────────────────────────────────
function openSvcModal() {
  svcForm.total_amount = svcDist.value?.total_service_charge || 0
  showSvcModal.value = true
}
function closeSvcModal() { showSvcModal.value = false }

async function saveSvcPool() {
  try {
    await axios.post('/staff/service-charge-pool', {
      month: selectedMonth.value,
      year: selectedYear.value,
      total_amount: svcForm.total_amount,
    })
    toast('Service charge pool updated')
    closeSvcModal()
    await loadSvcDist()
  } catch { toast('Failed to update', 'error') }
}

async function distributeServiceCharge() {
  try {
    console.log('Distributing service charge for', { month: selectedMonth.value, year: selectedYear.value })
    
    const response = await axios.post('/staff/service-charge-distribute', {
      month: selectedMonth.value,
      year: selectedYear.value,
    })
    
    console.log('Distribution response', response.data)
    
    toast(`Service charge distributed successfully! Total: Rs.${response.data.total_amount.toLocaleString()}, Staff: ${response.data.staff_count}, Share per staff: Rs.${response.data.share_per_staff.toLocaleString()}`)
    
    await loadSvcDist()
  } catch (e) { 
    console.error('Distribute service charge error', e)
    toast(e.response?.data?.message || 'Failed to distribute service charge', 'error') 
  }
}

// ── Utilities ─────────────────────────────────────────────
function resetForm(form, defaults) { Object.assign(form, defaults) }

function fmt(v) {
  return Number(v || 0).toLocaleString('en-LK', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

function formatDate(d) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('en-LK', { year: 'numeric', month: 'short', day: 'numeric' })
}

function initials(name) {
  return name.split(' ').slice(0, 2).map(w => w[0]).join('').toUpperCase()
}

const roleColors = {
  admin: '#7C3AED', manager: '#0891B2', cashier: '#D97706',
  waiter: '#059669', kitchen: '#DC2626', bartender: '#DB2777', delivery: '#2563EB',
}
function roleColor(role) { return roleColors[role] || '#6B7280' }

function advStatusClass(s) {
  return { pending: 'sm-tag--amber', approved: 'sm-tag--green', rejected: 'sm-tag--red', deducted: 'sm-tag--gray' }[s] || ''
}
function payrollStatusClass(s) {
  return { draft: 'sm-tag--amber', approved: 'sm-tag--blue', paid: 'sm-tag--green' }[s] || ''
}

// ── Init ──────────────────────────────────────────────────
onMounted(async () => {
  await loadStaff()
})
</script>

<style scoped>
/* ────────────────────────────────────────────────────────────
   LAYOUT
──────────────────────────────────────────────────────────── */
.sm-root {
  display: flex;
  min-height: 100vh;
  background: #0f0f12;
  color: #e2e2e8;
  font-family: 'DM Sans', 'Inter', system-ui, sans-serif;
}

/* ── Sidebar ─────────────────────────────────────────────── */
.sm-sidebar {
  width: 220px;
  flex-shrink: 0;
  background: #16161c;
  border-right: 1px solid #2a2a35;
  display: flex;
  flex-direction: column;
  padding: 0 0 24px;
  position: sticky;
  top: 0;
  height: 100vh;
  overflow-y: auto;
}

.sm-brand {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 22px 20px 20px;
  border-bottom: 1px solid #2a2a35;
  margin-bottom: 12px;
}
.sm-brand-icon { font-size: 20px; }
.sm-brand-text { font-size: 15px; font-weight: 700; letter-spacing: .02em; color: #fff; }

.sm-nav { display: flex; flex-direction: column; gap: 2px; padding: 0 10px; }

.sm-nav-btn {
  display: flex; align-items: center; gap: 10px;
  padding: 9px 12px;
  border: none; border-radius: 8px;
  background: transparent; cursor: pointer;
  color: #9090a0; font-size: 13.5px;
  text-align: left; transition: background .15s, color .15s;
}
.sm-nav-btn:hover { background: #22222e; color: #e2e2e8; }
.sm-nav-btn--active { background: #1e1e2e; color: #fff; font-weight: 600; }

.sm-nav-icon { font-size: 15px; width: 20px; text-align: center; }
.sm-nav-label { flex: 1; }
.sm-nav-badge {
  background: #D85A30; color: #fff;
  font-size: 11px; font-weight: 700;
  padding: 1px 6px; border-radius: 99px;
}

.sm-month-picker {
  margin-top: auto; padding: 16px 14px 0;
  border-top: 1px solid #2a2a35;
}
.sm-month-label { font-size: 11px; text-transform: uppercase; letter-spacing: .08em; color: #606070; margin-bottom: 8px; }
.sm-month-row { display: flex; gap: 6px; }

/* ── Main ────────────────────────────────────────────────── */
.sm-main {
  flex: 1; min-width: 0;
  padding: 32px 36px;
  overflow-y: auto;
}

/* ── Topbar ──────────────────────────────────────────────── */
.sm-topbar {
  display: flex; justify-content: space-between;
  align-items: flex-start; margin-bottom: 24px;
}
.sm-h1 { font-size: 24px; font-weight: 700; color: #fff; margin: 0; }
.sm-sub { font-size: 13px; color: #606070; margin: 4px 0 0; }

/* ── Filters ─────────────────────────────────────────────── */
.sm-filter-row {
  display: flex; gap: 10px; flex-wrap: wrap;
  margin-bottom: 20px;
}
.sm-search {
  flex: 1; min-width: 200px;
  padding: 9px 14px; border-radius: 8px;
  background: #1c1c26; border: 1px solid #2e2e3e;
  color: #e2e2e8; font-size: 13.5px;
  outline: none; transition: border-color .15s;
}
.sm-search:focus { border-color: #D85A30; }

/* ── Shared input / select ───────────────────────────────── */
.sm-select, .sm-input {
  padding: 9px 12px; border-radius: 8px;
  background: #1c1c26; border: 1px solid #2e2e3e;
  color: #e2e2e8; font-size: 13.5px;
  outline: none; transition: border-color .15s;
  width: 100%;
}
.sm-select:focus, .sm-input:focus { border-color: #D85A30; }
.sm-textarea { min-height: 80px; resize: vertical; }

/* ── Buttons ─────────────────────────────────────────────── */
.sm-btn {
  padding: 9px 18px; border-radius: 8px; border: none;
  font-size: 13.5px; font-weight: 600; cursor: pointer;
  transition: opacity .15s, transform .1s;
}
.sm-btn:hover { opacity: .88; }
.sm-btn:active { transform: scale(.97); }

.sm-btn--primary { background: #D85A30; color: #fff; }
.sm-btn--secondary { background: #3a3a4a; color: #fff; }
.sm-btn--secondary:hover { background: #4a4a5a; }

.sm-btn-group {
  display: flex;
  gap: 8px;
}
.sm-btn--ghost {
  background: transparent; color: #9090a0;
  border: 1px solid #2e2e3e;
}
.sm-btn--ghost:hover { color: #fff; border-color: #D85A30; }
.sm-btn--ok { background: #059669; color: #fff; }
.sm-btn:disabled { opacity: .4; cursor: not-allowed; }

.sm-icon-btn {
  width: 30px; height: 30px; border-radius: 6px;
  border: 1px solid #2e2e3e; background: transparent;
  color: #9090a0; font-size: 13px; cursor: pointer;
  display: inline-flex; align-items: center; justify-content: center;
  transition: all .15s;
}
.sm-icon-btn:hover { background: #22222e; color: #fff; }
.sm-icon-btn--danger:hover { background: #3d1a1a; color: #f87171; border-color: #f87171; }

/* ── Staff grid ──────────────────────────────────────────── */
.sm-section { animation: fadeIn .2s ease; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; } }

.sm-staff-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 16px;
}

.sm-staff-card {
  background: #16161c; border: 1px solid #2a2a35;
  border-radius: 12px; padding: 18px;
  display: flex; flex-direction: column; gap: 14px;
  transition: border-color .2s, transform .15s;
}
.sm-staff-card:hover { border-color: #D85A30; transform: translateY(-2px); }
.sm-staff-card--inactive { opacity: .55; }

.sm-staff-avatar {
  width: 44px; height: 44px; border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  font-size: 16px; font-weight: 700; color: #fff;
  flex-shrink: 0;
}
.sm-staff-info { flex: 1; }
.sm-staff-name { font-size: 15px; font-weight: 600; color: #fff; margin: 0 0 3px; }
.sm-staff-id { font-size: 12px; color: #606070; margin: 0 0 6px; }

.sm-role-pill {
  display: inline-block; padding: 2px 8px; border-radius: 99px;
  font-size: 11px; font-weight: 600; text-transform: capitalize;
}

.sm-staff-meta {
  display: flex; gap: 16px; flex-wrap: wrap;
}
.sm-staff-meta div { display: flex; flex-direction: column; gap: 2px; }
.sm-staff-meta dt { font-size: 11px; color: #606070; text-transform: uppercase; letter-spacing: .06em; }
.sm-staff-meta dd { font-size: 13px; color: #e2e2e8; margin: 0; }

.sm-staff-actions {
  display: flex; align-items: center; gap: 8px;
  border-top: 1px solid #2a2a35; padding-top: 12px;
}
.sm-status-dot {
  width: 8px; height: 8px; border-radius: 50%;
  margin-right: auto;
}
.sm-status-dot--on { background: #10b981; box-shadow: 0 0 0 3px #10b98122; }
.sm-status-dot--off { background: #6b7280; }

/* ── Pill row ─────────────────────────────────────────────── */
.sm-pill-row { display: flex; gap: 12px; margin-bottom: 20px; flex-wrap: wrap; }
.sm-pill {
  background: #16161c; border: 1px solid #2a2a35;
  border-radius: 10px; padding: 14px 20px;
  display: flex; flex-direction: column; gap: 4px;
  min-width: 110px;
}
.sm-pill--warn { border-color: #f59e0b44; }
.sm-pill--ok   { border-color: #10b98144; }
.sm-pill-num { font-size: 22px; font-weight: 700; color: #fff; }
.sm-pill--warn .sm-pill-num { color: #f59e0b; }
.sm-pill--ok   .sm-pill-num { color: #10b981; }
.sm-pill-lbl { font-size: 12px; color: #606070; }

/* ── Table ───────────────────────────────────────────────── */
.sm-table-wrap { overflow-x: auto; border-radius: 10px; border: 1px solid #2a2a35; }
.sm-table { width: 100%; border-collapse: collapse; font-size: 13.5px; }
.sm-table th {
  text-align: left; padding: 11px 14px;
  font-size: 11px; font-weight: 600;
  text-transform: uppercase; letter-spacing: .07em;
  color: #606070; background: #16161c;
  border-bottom: 1px solid #2a2a35;
}
.sm-table td {
  padding: 11px 14px; border-bottom: 1px solid #1e1e28;
  vertical-align: middle; color: #c8c8d8;
}
.sm-table tr:last-child td { border-bottom: none; }
.sm-table tr:hover td { background: #1c1c26; }
.sm-muted { color: #606070; font-size: 12px; display: block; }
.sm-empty-td { text-align: center; color: #606070; padding: 32px; }
.sm-row-actions { display: flex; gap: 6px; }

/* ── Tags ────────────────────────────────────────────────── */
.sm-tag {
  display: inline-flex; padding: 3px 8px; border-radius: 6px;
  font-size: 11.5px; font-weight: 600;
}
.sm-tag--lg { font-size: 12px; padding: 4px 10px; }
.sm-tag--blue  { background: #1e3a5f; color: #60a5fa; }
.sm-tag--amber { background: #3d2a0a; color: #fbbf24; }
.sm-tag--green { background: #0d2e1c; color: #34d399; }
.sm-tag--red   { background: #3d1010; color: #f87171; }
.sm-tag--gray  { background: #22222e; color: #9090a0; }

/* ── Payroll ─────────────────────────────────────────────── */
.sm-payroll-summary {
  display: flex; gap: 0; flex-wrap: wrap;
  background: #16161c; border: 1px solid #2a2a35;
  border-radius: 12px; margin-bottom: 24px; overflow: hidden;
}
.sm-summary-item {
  flex: 1; padding: 16px 20px;
  display: flex; flex-direction: column; gap: 4px; min-width: 140px;
}
.sm-summary-item span { font-size: 12px; color: #606070; }
.sm-summary-item strong { font-size: 18px; font-weight: 700; color: #fff; }
.sm-summary-item--red strong  { color: #f87171; }
.sm-summary-item--green strong { color: #34d399; }
.sm-summary-divider { width: 1px; background: #2a2a35; }

.sm-payroll-list { display: flex; flex-direction: column; gap: 16px; }

.sm-payroll-card {
  background: #16161c; border: 1px solid #2a2a35;
  border-radius: 12px; overflow: hidden;
  transition: border-color .2s;
}
.sm-payroll-card:hover { border-color: #3a3a4a; }

.sm-payroll-head {
  display: flex; justify-content: space-between; align-items: center;
  padding: 16px 20px; border-bottom: 1px solid #2a2a35;
}
.sm-payroll-who { display: flex; align-items: center; gap: 12px; }
.sm-payroll-avatar {
  width: 38px; height: 38px; border-radius: 8px;
  display: flex; align-items: center; justify-content: center;
  font-size: 14px; font-weight: 700; color: #fff; flex-shrink: 0;
}
.sm-payroll-who h3 { margin: 0; font-size: 14.5px; color: #fff; }
.sm-payroll-who p  { margin: 3px 0 0; font-size: 12px; color: #606070; }

.sm-payroll-body {
  display: grid; grid-template-columns: 1fr 1fr 1fr;
  gap: 0;
}
.sm-payroll-col { padding: 16px 20px; }
.sm-payroll-col + .sm-payroll-col { border-left: 1px solid #2a2a35; }
.sm-payroll-col--earn  { background: #0d1f0d; }
.sm-payroll-col--deduct { background: #1f0d0d; }
.sm-payroll-col--net   { background: #0d1020; display: flex; flex-direction: column; justify-content: center; align-items: center; }

.sm-col-head { font-size: 11px; text-transform: uppercase; letter-spacing: .07em; color: #606070; margin: 0 0 10px; }
.sm-payroll-col dl { margin: 0; display: flex; flex-direction: column; gap: 6px; }
.sm-payroll-col dt { font-size: 12px; color: #808090; }
.sm-payroll-col dd { font-size: 13.5px; color: #c8c8d8; margin: 0; }
.sm-total { border-top: 1px solid #2e2e3e; padding-top: 8px; margin-top: 4px; }
.sm-total dt { color: #e2e2e8; font-weight: 600; }
.sm-total dd { color: #fff; font-weight: 700; }

.sm-net-amount { font-size: 22px; font-weight: 700; color: #60a5fa; margin: 0; }
.sm-leave-info { font-size: 11px; color: #606070; margin-top: 6px; }

.sm-payroll-foot {
  display: flex; gap: 8px; justify-content: flex-end;
  padding: 12px 20px; border-top: 1px solid #2a2a35;
}

/* ── Service Charge ──────────────────────────────────────── */
.sm-svc-wrap {
  background: #16161c; border: 1px solid #2a2a35;
  border-radius: 12px; overflow: hidden;
}
.sm-svc-total {
  display: flex; align-items: center; justify-content: space-between;
  padding: 20px 24px; border-bottom: 1px solid #2a2a35;
  background: #1a1a28;
}
.sm-svc-total-label { font-size: 13px; color: #808090; }
.sm-svc-total-amt { font-size: 22px; font-weight: 700; color: #D85A30; }

.sm-svc-bars { padding: 20px 24px; display: flex; flex-direction: column; gap: 14px; }
.sm-svc-bar-row { display: grid; grid-template-columns: 160px 1fr 50px 120px; align-items: center; gap: 12px; }
.sm-svc-bar-name { font-size: 13.5px; color: #c8c8d8; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.sm-svc-bar-track { background: #2a2a35; border-radius: 99px; height: 8px; overflow: hidden; }
.sm-svc-bar-fill { background: #D85A30; height: 100%; border-radius: 99px; transition: width .5s ease; }
.sm-svc-bar-pct { font-size: 12px; color: #808090; text-align: right; }
.sm-svc-bar-amt { font-size: 13px; font-weight: 600; color: #fff; text-align: right; }

/* ── Modal ───────────────────────────────────────────────── */
.sm-overlay {
  position: fixed; inset: 0; z-index: 500;
  background: rgba(0,0,0,.65);
  display: flex; align-items: center; justify-content: center;
  padding: 16px;
  backdrop-filter: blur(4px);
}

.sm-modal {
  background: #16161c; border: 1px solid #2e2e3e;
  border-radius: 16px; width: 100%; max-width: 680px;
  max-height: 90vh; overflow-y: auto;
  box-shadow: 0 24px 60px rgba(0,0,0,.6);
}
.sm-modal--sm { max-width: 460px; }

.sm-modal-head {
  display: flex; justify-content: space-between; align-items: center;
  padding: 20px 24px; border-bottom: 1px solid #2a2a35;
}
.sm-modal-head h2 { margin: 0; font-size: 17px; color: #fff; }
.sm-modal-close {
  background: none; border: none; color: #606070;
  font-size: 16px; cursor: pointer; padding: 4px;
}
.sm-modal-close:hover { color: #fff; }

.sm-modal-body { padding: 24px; }
.sm-modal-foot {
  display: flex; gap: 10px; justify-content: flex-end;
  padding: 16px 24px; border-top: 1px solid #2a2a35;
  margin: 0 -24px -24px;
}

.sm-form-grid { display: grid; gap: 16px; }
.sm-form-grid--2 { grid-template-columns: 1fr 1fr; }

.sm-label { display: flex; flex-direction: column; gap: 6px; font-size: 12.5px; color: #808090; }

.sm-checkbox-label {
  display: flex; align-items: center; gap: 8px;
  font-size: 13.5px; color: #c8c8d8; cursor: pointer;
}
.sm-checkbox-label input { accent-color: #D85A30; }

.sm-staff-checkboxes {
  margin: 16px 0;
  display: flex; flex-direction: column; gap: 8px;
  max-height: 200px; overflow-y: auto;
  padding: 12px; background: #1c1c26; border-radius: 8px;
}
.sm-checkbox-all { font-weight: 600; padding-bottom: 8px; border-bottom: 1px solid #2a2a35; }
.sm-label-text { font-size: 12.5px; color: #808090; margin-bottom: 4px; }

.sm-hint { font-size: 12px; color: #606070; margin: 8px 0; }

/* ── Toast ───────────────────────────────────────────────── */
.sm-toast-stack {
  position: fixed; bottom: 24px; right: 24px;
  display: flex; flex-direction: column; gap: 8px; z-index: 999;
}
.sm-toast {
  padding: 12px 18px; border-radius: 10px;
  font-size: 13.5px; font-weight: 500;
  box-shadow: 0 8px 24px rgba(0,0,0,.4);
  max-width: 320px;
}
.sm-toast--success { background: #0d2e1c; color: #34d399; border: 1px solid #10b98144; }
.sm-toast--error   { background: #3d1010; color: #f87171; border: 1px solid #ef444444; }

.toast-enter-active, .toast-leave-active { transition: all .25s ease; }
.toast-enter-from  { opacity: 0; transform: translateY(8px); }
.toast-leave-to    { opacity: 0; transform: translateX(20px); }

/* ── Empty state ─────────────────────────────────────────── */
.sm-empty {
  text-align: center; color: #606070; font-size: 14px;
  padding: 48px; background: #16161c; border-radius: 12px;
  border: 1px dashed #2a2a35;
}

/* ── Responsive ──────────────────────────────────────────── */
@media (max-width: 768px) {
  .sm-root { flex-direction: column; }
  .sm-sidebar { width: 100%; height: auto; position: static; flex-direction: row; flex-wrap: wrap; padding: 12px; gap: 8px; }
  .sm-brand { border: none; margin: 0; padding: 0; }
  .sm-nav { flex-direction: row; padding: 0; flex-wrap: wrap; }
  .sm-month-picker { margin-top: 0; border: none; padding: 0; }
  .sm-month-row { flex-direction: row; }
  .sm-main { padding: 20px 16px; }
  .sm-payroll-body { grid-template-columns: 1fr; }
  .sm-payroll-col + .sm-payroll-col { border-left: none; border-top: 1px solid #2a2a35; }
  .sm-form-grid--2 { grid-template-columns: 1fr; }
  .sm-svc-bar-row { grid-template-columns: 120px 1fr 40px; }
  .sm-svc-bar-amt { display: none; }
  .sm-summary-divider { display: none; }
}
</style>