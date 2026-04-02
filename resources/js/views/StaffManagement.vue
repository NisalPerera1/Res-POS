<template>
  <div class="staff-management-container">
    <!-- Header -->
    <header class="staff-header">
      <div class="header-content">
        <h1 class="header-title">👥 Staff Management</h1>
        <button @click="openStaffModal(null)" class="add-staff-btn">
          <span class="btn-icon">+</span>
          <span class="btn-text">Add Staff</span>
        </button>
      </div>
    </header>

    <!-- Navigation Tabs -->
    <nav class="tab-navigation">
      <button 
        v-for="tab in tabs" 
        :key="tab.value" 
        @click="activeTab = tab.value"
        :class="['tab-btn', { 'tab-active': activeTab === tab.value }]"
      >
        <span class="tab-icon">{{ tab.icon }}</span>
        <span class="tab-label">{{ tab.label }}</span>
      </button>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
      <!-- OVERVIEW TAB -->
      <div v-if="activeTab === 'overview'" class="tab-content">
        <!-- Stats Grid -->
        <div class="stats-grid">
          <div v-for="card in overviewCards" :key="card.label" class="stat-card">
            <div class="stat-label">{{ card.label }}</div>
            <div class="stat-value" :style="{ color: card.color }">{{ card.value }}</div>
            <div v-if="card.sub" class="stat-sub">{{ card.sub }}</div>
          </div>
        </div>

        <!-- Service Charge Section -->
        <div class="service-charge-card">
          <h3 class="section-title">This Month's Service Charge</h3>
          <div class="charge-amount">Rs. {{ monthlyServiceCharge.toLocaleString() }}</div>
          <button @click="distributeServiceCharge" class="distribute-btn">
            📊 Distribute Now
          </button>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
          <button @click="activeTab = 'payroll'" class="action-card">
            <span class="action-icon">💰</span>
            <span class="action-title">Payroll</span>
            <span class="action-subtitle">Generate salaries</span>
          </button>
          <button @click="activeTab = 'advances'" class="action-card">
            <span class="action-icon">💵</span>
            <span class="action-title">Advances</span>
            <span class="action-subtitle">Manage loans</span>
          </button>
          <button @click="activeTab = 'attendance'" class="action-card">
            <span class="action-icon">📅</span>
            <span class="action-title">Attendance</span>
            <span class="action-subtitle">Track hours</span>
          </button>
        </div>
      </div>

      <!-- STAFF LIST TAB -->
      <div v-if="activeTab === 'staff'" class="tab-content">
        <div class="staff-filters">
          <input 
            v-model="staffSearch" 
            type="text" 
            placeholder="Search staff..." 
            class="search-input"
          />
          <select v-model="roleFilter" class="filter-select">
            <option value="">All Roles</option>
            <option value="Chef">Chef</option>
            <option value="Waiter">Waiter</option>
            <option value="Cashier">Cashier</option>
            <option value="Manager">Manager</option>
          </select>
        </div>

        <div class="staff-grid">
          <div 
            v-for="staff in filteredStaff" 
            :key="staff.id" 
            class="staff-card"
            @click="openStaffDetails(staff)"
          >
            <div class="staff-avatar">
              <img v-if="staff.profile_image" :src="staff.profile_image" :alt="staff.name" />
              <span v-else class="avatar-placeholder">{{ staff.name.charAt(0) }}</span>
            </div>
            <div class="staff-info">
              <h3 class="staff-name">{{ staff.name }}</h3>
              <p class="staff-role">{{ staff.role }}</p>
              <p class="staff-contact">{{ staff.contact }}</p>
            </div>
            <div class="staff-status">
              <span :class="['status-badge', staff.status.toLowerCase()]">
                {{ staff.status }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- PAYROLL TAB -->
      <div v-if="activeTab === 'payroll'" class="tab-content">
        <div class="payroll-controls">
          <select v-model="payrollMonth" class="month-select">
            <option v-for="month in months" :key="month.value" :value="month.value">
              {{ month.label }}
            </option>
          </select>
          <select v-model="payrollYear" class="year-select">
            <option v-for="year in years" :key="year" :value="year">{{ year }}</option>
          </select>
          <button @click="generatePayroll" class="generate-btn">
            📄 Generate Payroll
          </button>
        </div>

        <div class="payroll-list">
          <div v-for="payroll in payrollList" :key="payroll.id" class="payroll-card">
            <div class="payroll-header">
              <div class="employee-info">
                <h4>{{ payroll.employee_name }}</h4>
                <p>{{ payroll.role }}</p>
              </div>
              <div class="payroll-status">
                <span :class="['status-badge', payroll.status.toLowerCase()]">
                  {{ payroll.status }}
                </span>
              </div>
            </div>
            <div class="payroll-breakdown">
              <div class="breakdown-item">
                <span>Basic Salary</span>
                <span>Rs. {{ payroll.basic_salary }}</span>
              </div>
              <div class="breakdown-item">
                <span>Overtime</span>
                <span>Rs. {{ payroll.overtime }}</span>
              </div>
              <div class="breakdown-item">
                <span>Service Charge</span>
                <span>Rs. {{ payroll.service_charge }}</span>
              </div>
              <div class="breakdown-item">
                <span>Bonuses</span>
                <span>Rs. {{ payroll.bonuses }}</span>
              </div>
              <div class="breakdown-item negative">
                <span>Deductions</span>
                <span>-Rs. {{ payroll.deductions }}</span>
              </div>
              <div class="breakdown-item total">
                <span>Net Salary</span>
                <span>Rs. {{ payroll.net_salary }}</span>
              </div>
            </div>
            <div class="payroll-actions">
              <button @click="downloadPayslip(payroll)" class="download-btn">
                📄 Download Payslip
              </button>
              <button 
                v-if="payroll.status === 'Pending'" 
                @click="markAsPaid(payroll)" 
                class="mark-paid-btn"
              >
                ✅ Mark as Paid
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- ADVANCES & LOANS TAB -->
      <div v-if="activeTab === 'advances'" class="tab-content">
        <button @click="openAdvanceModal(null)" class="add-advance-btn">
          <span>+</span> Add Advance/Loan
        </button>

        <div class="advances-list">
          <div v-for="advance in advancesList" :key="advance.id" class="advance-card">
            <div class="advance-header">
              <div class="employee-info">
                <h4>{{ advance.employee_name }}</h4>
                <p>{{ advance.type }} - {{ advance.date }}</p>
              </div>
              <div class="advance-amount">
                Rs. {{ advance.amount.toLocaleString() }}
              </div>
            </div>
            <div class="advance-details">
              <div class="detail-row">
                <span>Remaining Balance:</span>
                <span>Rs. {{ advance.remaining_balance.toLocaleString() }}</span>
              </div>
              <div class="detail-row">
                <span>Monthly Deduction:</span>
                <span>Rs. {{ advance.monthly_deduction.toLocaleString() }}</span>
              </div>
              <div class="progress-bar">
                <div 
                  class="progress-fill" 
                  :style="{ width: ((advance.amount - advance.remaining_balance) / advance.amount * 100) + '%' }"
                ></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- PAYMENT HISTORY TAB -->
      <div v-if="activeTab === 'history'" class="tab-content">
        <div class="history-filters">
          <select v-model="historyEmployee" class="employee-select">
            <option value="">All Employees</option>
            <option v-for="staff in staffList" :key="staff.id" :value="staff.id">
              {{ staff.name }}
            </option>
          </select>
          <input 
            v-model="historyMonth" 
            type="month" 
            class="month-input"
          />
        </div>

        <div class="history-list">
          <div v-for="payment in paymentHistory" :key="payment.id" class="history-card">
            <div class="history-header">
              <div>
                <h4>{{ payment.employee_name }}</h4>
                <p class="payment-date">{{ payment.date }}</p>
              </div>
              <div class="payment-amount">Rs. {{ payment.amount.toLocaleString() }}</div>
            </div>
            <div class="payment-details">
              <span class="payment-type">{{ payment.type }}</span>
              <span class="payment-method">{{ payment.method }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- LEAVE MANAGEMENT TAB -->
      <div v-if="activeTab === 'leaves'" class="tab-content">
        <button @click="openLeaveModal(null)" class="add-leave-btn">
          <span>+</span> Request Leave
        </button>

        <div class="leaves-list">
          <div v-for="leave in leavesList" :key="leave.id" class="leave-card">
            <div class="leave-header">
              <div class="employee-info">
                <h4>{{ leave.employee_name }}</h4>
                <p>{{ leave.type }} - {{ leave.duration }}</p>
              </div>
              <div class="leave-status">
                <span :class="['status-badge', leave.status.toLowerCase()]">
                  {{ leave.status }}
                </span>
              </div>
            </div>
            <div class="leave-dates">
              <p>📅 {{ leave.start_date }} to {{ leave.end_date }}</p>
              <p v-if="leave.reason" class="leave-reason">📝 {{ leave.reason }}</p>
            </div>
            <div v-if="leave.status === 'Pending'" class="leave-actions">
              <button @click="approveLeave(leave)" class="approve-btn">
                ✅ Approve
              </button>
              <button @click="rejectLeave(leave)" class="reject-btn">
                ❌ Reject
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- DOCUMENTS TAB -->
      <div v-if="activeTab === 'documents'" class="tab-content">
        <div class="documents-grid">
          <div v-for="staff in staffList" :key="staff.id" class="document-card">
            <div class="document-header">
              <h3>{{ staff.name }}</h3>
              <button @click="uploadDocument(staff)" class="upload-btn">
                📤 Upload
              </button>
            </div>
            <div class="document-list">
              <div v-if="staff.profile_image" class="document-item">
                <span class="doc-icon">👤</span>
                <span>Profile Image</span>
                <button @click="viewDocument(staff.profile_image)" class="view-btn">View</button>
              </div>
              <div v-if="staff.nic_copy" class="document-item">
                <span class="doc-icon">🆔</span>
                <span>NIC Copy</span>
                <button @click="viewDocument(staff.nic_copy)" class="view-btn">View</button>
              </div>
              <div v-if="staff.contract" class="document-item">
                <span class="doc-icon">📄</span>
                <span>Contract</span>
                <button @click="viewDocument(staff.contract)" class="view-btn">View</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Toast Notification -->
    <div v-if="toast.show" :class="['toast', toast.type]">
      {{ toast.message }}
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'

// Reactive Data
const activeTab = ref('overview')
const staffSearch = ref('')
const roleFilter = ref('')
const payrollMonth = ref(new Date().getMonth() + 1)
const payrollYear = ref(new Date().getFullYear())
const historyEmployee = ref('')
const historyMonth = ref('')
const toast = ref({ show: false, message: '', type: 'success' })

// Tab Configuration
const tabs = [
  { value: 'overview', label: 'Overview', icon: '📊' },
  { value: 'staff', label: 'Staff', icon: '👥' },
  { value: 'payroll', label: 'Payroll', icon: '💰' },
  { value: 'advances', label: 'Advances', icon: '💵' },
  { value: 'history', label: 'History', icon: '📜' },
  { value: 'leaves', label: 'Leaves', icon: '📅' },
  { value: 'documents', label: 'Documents', icon: '📁' }
]

// Mock Data (Replace with API calls)
const staffList = ref([
  {
    id: 1,
    name: 'John Doe',
    role: 'Chef',
    contact: '+94 77 123 4567',
    email: 'john@restaurant.com',
    nic: '981234567V',
    status: 'Active',
    profile_image: null,
    nic_copy: null,
    contract: null,
    joining_date: '2023-01-15',
    employment_type: 'Full-time',
    salary_type: 'Fixed',
    salary: 80000
  },
  {
    id: 2,
    name: 'Jane Smith',
    role: 'Waiter',
    contact: '+94 76 987 6543',
    email: 'jane@restaurant.com',
    nic: '998765432V',
    status: 'Active',
    profile_image: null,
    nic_copy: null,
    contract: null,
    joining_date: '2023-03-20',
    employment_type: 'Full-time',
    salary_type: 'Fixed',
    salary: 60000
  }
])

const overviewCards = ref([
  { label: 'Total Staff', value: '12', color: '#10B981', sub: '2 on leave' },
  { label: 'Active Today', value: '10', color: '#3B82F6', sub: '85% attendance' },
  { label: 'Monthly Payroll', value: 'Rs. 850K', color: '#F59E0B', sub: '3 pending' },
  { label: 'Service Charge', value: 'Rs. 125K', color: '#8B5CF6', sub: 'This month' }
])

const monthlyServiceCharge = ref(125000)
const payrollList = ref([])
const advancesList = ref([])
const paymentHistory = ref([])
const leavesList = ref([])

// Computed Properties
const filteredStaff = computed(() => {
  return staffList.value.filter(staff => {
    const matchesSearch = staff.name.toLowerCase().includes(staffSearch.value.toLowerCase()) ||
                         staff.contact.includes(staffSearch.value)
    const matchesRole = !roleFilter.value || staff.role === roleFilter.value
    return matchesSearch && matchesRole
  })
})

const months = computed(() => [
  { value: 1, label: 'January' },
  { value: 2, label: 'February' },
  { value: 3, label: 'March' },
  { value: 4, label: 'April' },
  { value: 5, label: 'May' },
  { value: 6, label: 'June' },
  { value: 7, label: 'July' },
  { value: 8, label: 'August' },
  { value: 9, label: 'September' },
  { value: 10, label: 'October' },
  { value: 11, label: 'November' },
  { value: 12, label: 'December' }
])

const years = computed(() => {
  const currentYear = new Date().getFullYear()
  return Array.from({ length: 5 }, (_, i) => currentYear - 2 + i)
})

// Methods
const openStaffModal = (staff) => {
  // Open staff add/edit modal
  showToast('Staff modal would open here', 'info')
}

const openStaffDetails = (staff) => {
  // Navigate to staff details or open modal
  showToast(`Viewing ${staff.name}'s details`, 'info')
}

const generatePayroll = async () => {
  try {
    // Generate payroll for selected month/year
    showToast('Payroll generated successfully', 'success')
    await loadPayrollList()
  } catch (error) {
    showToast('Failed to generate payroll', 'error')
  }
}

const distributeServiceCharge = async () => {
  try {
    // Distribute service charge among staff
    showToast('Service charge distributed successfully', 'success')
  } catch (error) {
    showToast('Failed to distribute service charge', 'error')
  }
}

const downloadPayslip = (payroll) => {
  // Download payslip PDF
  showToast(`Downloading payslip for ${payroll.employee_name}`, 'success')
}

const markAsPaid = async (payroll) => {
  try {
    payroll.status = 'Paid'
    showToast('Payroll marked as paid', 'success')
  } catch (error) {
    showToast('Failed to update status', 'error')
  }
}

const openAdvanceModal = (advance) => {
  // Open advance/loan modal
  showToast('Advance modal would open here', 'info')
}

const openLeaveModal = (leave) => {
  // Open leave request modal
  showToast('Leave modal would open here', 'info')
}

const approveLeave = async (leave) => {
  try {
    leave.status = 'Approved'
    showToast('Leave approved', 'success')
  } catch (error) {
    showToast('Failed to approve leave', 'error')
  }
}

const rejectLeave = async (leave) => {
  try {
    leave.status = 'Rejected'
    showToast('Leave rejected', 'success')
  } catch (error) {
    showToast('Failed to reject leave', 'error')
  }
}

const uploadDocument = (staff) => {
  // Open document upload modal
  showToast(`Upload document for ${staff.name}`, 'info')
}

const viewDocument = (document) => {
  // View document in new tab/modal
  showToast('Viewing document', 'info')
}

const showToast = (message, type = 'success') => {
  toast.value = { show: true, message, type }
  setTimeout(() => { toast.value.show = false }, 3000)
}

// Data Loading Functions
const loadStaff = async () => {
  // Load staff from API
}

const loadPayrollList = async () => {
  // Load payroll data for selected month/year
  payrollList.value = [
    {
      id: 1,
      employee_name: 'John Doe',
      role: 'Chef',
      status: 'Paid',
      basic_salary: 80000,
      overtime: 5000,
      service_charge: 12000,
      bonuses: 3000,
      deductions: 2000,
      net_salary: 98000
    }
  ]
}

const loadAdvances = async () => {
  // Load advances and loans
  advancesList.value = [
    {
      id: 1,
      employee_name: 'Jane Smith',
      type: 'Salary Advance',
      amount: 20000,
      remaining_balance: 15000,
      monthly_deduction: 5000,
      date: '2024-01-15'
    }
  ]
}

const loadPaymentHistory = async () => {
  // Load payment history
  paymentHistory.value = [
    {
      id: 1,
      employee_name: 'John Doe',
      amount: 98000,
      type: 'Monthly Salary',
      method: 'Bank Transfer',
      date: '2024-01-31'
    }
  ]
}

const loadLeaves = async () => {
  // Load leave requests
  leavesList.value = [
    {
      id: 1,
      employee_name: 'Jane Smith',
      type: 'Sick Leave',
      duration: '2 days',
      start_date: '2024-02-10',
      end_date: '2024-02-11',
      status: 'Pending',
      reason: 'Medical appointment'
    }
  ]
}

// Watchers
watch([payrollMonth, payrollYear], () => {
  loadPayrollList()
})

watch([historyEmployee, historyMonth], () => {
  loadPaymentHistory()
})

// Initialize
onMounted(async () => {
  await Promise.all([
    loadStaff(),
    loadPayrollList(),
    loadAdvances(),
    loadPaymentHistory(),
    loadLeaves()
  ])
})
</script>

<style scoped>
/* ====== CONTAINER ====== */
.staff-management-container {
  display: flex;
  flex-direction: column;
  height: 100vh;
  background: #0A0C10;
  overflow: hidden;
}

/* ====== HEADER ====== */
.staff-header {
  background: #12151C;
  border-bottom: 1px solid #252B38;
  padding: 12px 20px;
  flex-shrink: 0;
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.header-title {
  font-size: 20px;
  font-weight: 700;
  color: #F1F5F9;
  margin: 0;
}

.add-staff-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #10B981;
  color: white;
  border: none;
  border-radius: 8px;
  padding: 8px 16px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.add-staff-btn:hover {
  background: #059669;
  transform: translateY(-1px);
}

.btn-icon {
  font-size: 16px;
}

.btn-text {
  font-size: 14px;
}

/* ====== TAB NAVIGATION ====== */
.tab-navigation {
  display: flex;
  gap: 2px;
  padding: 10px 16px;
  background: #12151C;
  border-bottom: 1px solid #252B38;
  flex-shrink: 0;
  overflow-x: auto;
}

.tab-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 16px;
  border: none;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  background: transparent;
  color: #64748B;
  white-space: nowrap;
}

.tab-btn:hover {
  background: rgba(100, 116, 139, 0.1);
}

.tab-active {
  background: #F59E0B;
  color: #000;
}

.tab-icon {
  font-size: 14px;
}

.tab-label {
  font-size: 13px;
}

/* ====== MAIN CONTENT ====== */
.main-content {
  flex: 1;
  overflow-y: auto;
  padding: 16px 20px;
}

.tab-content {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

/* ====== STATS GRID ====== */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 12px;
  margin-bottom: 20px;
}

.stat-card {
  background: #1A1E28;
  border: 1px solid #252B38;
  border-radius: 12px;
  padding: 16px;
  transition: all 0.2s;
}

.stat-card:hover {
  border-color: #3B82F6;
  transform: translateY(-2px);
}

.stat-label {
  font-size: 11px;
  color: #64748B;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  margin-bottom: 6px;
}

.stat-value {
  font-size: 24px;
  font-weight: 700;
  margin-bottom: 3px;
}

.stat-sub {
  font-size: 11px;
  color: #64748B;
}

/* ====== SERVICE CHARGE CARD ====== */
.service-charge-card {
  background: #1A1E28;
  border: 1px solid #252B38;
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 20px;
}

.section-title {
  font-size: 16px;
  font-weight: 700;
  color: #F1F5F9;
  margin-bottom: 12px;
}

.charge-amount {
  font-size: 28px;
  font-weight: 700;
  color: #10B981;
  margin-bottom: 16px;
}

.distribute-btn {
  background: #3B82F6;
  color: white;
  border: none;
  border-radius: 8px;
  padding: 10px 20px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.distribute-btn:hover {
  background: #2563EB;
}

/* ====== QUICK ACTIONS ====== */
.quick-actions {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 12px;
}

.action-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  background: #1A1E28;
  border: 1px solid #252B38;
  border-radius: 12px;
  padding: 20px;
  cursor: pointer;
  transition: all 0.2s;
  text-align: center;
}

.action-card:hover {
  border-color: #F59E0B;
  transform: translateY(-2px);
}

.action-icon {
  font-size: 24px;
}

.action-title {
  font-size: 14px;
  font-weight: 600;
  color: #F1F5F9;
}

.action-subtitle {
  font-size: 12px;
  color: #64748B;
}

/* ====== FILTERS ====== */
.staff-filters {
  display: flex;
  gap: 12px;
  margin-bottom: 20px;
  flex-wrap: wrap;
}

.search-input,
.filter-select,
.month-select,
.year-select,
.employee-select,
.month-input {
  background: #1A1E28;
  border: 1px solid #252B38;
  border-radius: 8px;
  padding: 10px 12px;
  color: #F1F5F9;
  font-size: 14px;
  transition: all 0.2s;
}

.search-input:focus,
.filter-select:focus,
.month-select:focus,
.year-select:focus,
.employee-select:focus,
.month-input:focus {
  outline: none;
  border-color: #3B82F6;
}

.search-input {
  flex: 1;
  min-width: 200px;
}

/* ====== STAFF GRID ====== */
.staff-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 16px;
}

.staff-card {
  display: flex;
  align-items: center;
  gap: 12px;
  background: #1A1E28;
  border: 1px solid #252B38;
  border-radius: 12px;
  padding: 16px;
  cursor: pointer;
  transition: all 0.2s;
}

.staff-card:hover {
  border-color: #3B82F6;
  transform: translateY(-2px);
}

.staff-avatar {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  overflow: hidden;
  background: #252B38;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.staff-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.avatar-placeholder {
  font-size: 18px;
  font-weight: 600;
  color: #64748B;
}

.staff-info {
  flex: 1;
}

.staff-name {
  font-size: 16px;
  font-weight: 600;
  color: #F1F5F9;
  margin: 0 0 4px 0;
}

.staff-role {
  font-size: 13px;
  color: #10B981;
  margin: 0 0 2px 0;
}

.staff-contact {
  font-size: 12px;
  color: #64748B;
  margin: 0;
}

.status-badge {
  padding: 4px 8px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
}

.status-badge.active {
  background: rgba(16, 185, 129, 0.15);
  color: #10B981;
}

.status-badge.pending {
  background: rgba(245, 158, 11, 0.15);
  color: #F59E0B;
}

.status-badge.paid {
  background: rgba(59, 130, 246, 0.15);
  color: #3B82F6;
}

.status-badge.approved {
  background: rgba(16, 185, 129, 0.15);
  color: #10B981;
}

.status-badge.rejected {
  background: rgba(239, 68, 68, 0.15);
  color: #EF4444;
}

/* ====== PAYROLL CONTROLS ====== */
.payroll-controls {
  display: flex;
  gap: 12px;
  margin-bottom: 20px;
  flex-wrap: wrap;
}

.generate-btn {
  background: #10B981;
  color: white;
  border: none;
  border-radius: 8px;
  padding: 10px 20px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.generate-btn:hover {
  background: #059669;
}

/* ====== PAYROLL LIST ====== */
.payroll-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.payroll-card {
  background: #1A1E28;
  border: 1px solid #252B38;
  border-radius: 12px;
  padding: 20px;
}

.payroll-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 16px;
}

.employee-info h4 {
  font-size: 16px;
  font-weight: 600;
  color: #F1F5F9;
  margin: 0 0 4px 0;
}

.employee-info p {
  font-size: 13px;
  color: #64748B;
  margin: 0;
}

.payroll-breakdown {
  background: #0A0C10;
  border-radius: 8px;
  padding: 16px;
  margin-bottom: 16px;
}

.breakdown-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 0;
  border-bottom: 1px solid #252B38;
}

.breakdown-item:last-child {
  border-bottom: none;
}

.breakdown-item.negative span:last-child {
  color: #EF4444;
}

.breakdown-item.total {
  font-weight: 700;
  font-size: 16px;
  color: #10B981;
  padding-top: 12px;
  border-top: 2px solid #252B38;
}

.payroll-actions {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}

.download-btn,
.mark-paid-btn {
  background: #3B82F6;
  color: white;
  border: none;
  border-radius: 6px;
  padding: 8px 16px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.download-btn:hover,
.mark-paid-btn:hover {
  background: #2563EB;
}

.mark-paid-btn {
  background: #10B981;
}

.mark-paid-btn:hover {
  background: #059669;
}

/* ====== ADVANCES ====== */
.add-advance-btn {
  background: #10B981;
  color: white;
  border: none;
  border-radius: 8px;
  padding: 10px 20px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  margin-bottom: 20px;
}

.add-advance-btn:hover {
  background: #059669;
}

.advances-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.advance-card {
  background: #1A1E28;
  border: 1px solid #252B38;
  border-radius: 12px;
  padding: 20px;
}

.advance-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 16px;
}

.advance-amount {
  font-size: 18px;
  font-weight: 700;
  color: #10B981;
}

.advance-details {
  background: #0A0C10;
  border-radius: 8px;
  padding: 16px;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 8px;
}

.detail-row:last-child {
  margin-bottom: 12px;
}

.progress-bar {
  width: 100%;
  height: 8px;
  background: #252B38;
  border-radius: 4px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #10B981, #059669);
  transition: width 0.3s ease;
}

/* ====== HISTORY ====== */
.history-filters {
  display: flex;
  gap: 12px;
  margin-bottom: 20px;
  flex-wrap: wrap;
}

.history-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.history-card {
  background: #1A1E28;
  border: 1px solid #252B38;
  border-radius: 12px;
  padding: 20px;
}

.history-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 12px;
}

.payment-amount {
  font-size: 18px;
  font-weight: 700;
  color: #10B981;
}

.payment-date {
  font-size: 13px;
  color: #64748B;
  margin: 4px 0 0 0;
}

.payment-details {
  display: flex;
  gap: 12px;
}

.payment-type,
.payment-method {
  padding: 4px 8px;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 600;
}

.payment-type {
  background: rgba(59, 130, 246, 0.15);
  color: #3B82F6;
}

.payment-method {
  background: rgba(139, 92, 246, 0.15);
  color: #8B5CF6;
}

/* ====== LEAVES ====== */
.add-leave-btn {
  background: #10B981;
  color: white;
  border: none;
  border-radius: 8px;
  padding: 10px 20px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  margin-bottom: 20px;
}

.add-leave-btn:hover {
  background: #059669;
}

.leaves-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.leave-card {
  background: #1A1E28;
  border: 1px solid #252B38;
  border-radius: 12px;
  padding: 20px;
}

.leave-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 12px;
}

.leave-dates p {
  font-size: 13px;
  color: #64748B;
  margin: 4px 0;
}

.leave-reason {
  font-style: italic;
}

.leave-actions {
  display: flex;
  gap: 12px;
  margin-top: 16px;
}

.approve-btn {
  background: #10B981;
  color: white;
  border: none;
  border-radius: 6px;
  padding: 8px 16px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.reject-btn {
  background: #EF4444;
  color: white;
  border: none;
  border-radius: 6px;
  padding: 8px 16px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

/* ====== DOCUMENTS ====== */
.documents-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 16px;
}

.document-card {
  background: #1A1E28;
  border: 1px solid #252B38;
  border-radius: 12px;
  padding: 20px;
}

.document-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.document-header h3 {
  font-size: 16px;
  font-weight: 600;
  color: #F1F5F9;
  margin: 0;
}

.upload-btn {
  background: #3B82F6;
  color: white;
  border: none;
  border-radius: 6px;
  padding: 6px 12px;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.document-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.document-item {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 12px;
  background: #0A0C10;
  border-radius: 6px;
  font-size: 13px;
}

.doc-icon {
  font-size: 16px;
}

.view-btn {
  margin-left: auto;
  background: #10B981;
  color: white;
  border: none;
  border-radius: 4px;
  padding: 4px 8px;
  font-size: 11px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

/* ====== TOAST ====== */
.toast {
  position: fixed;
  top: 20px;
  right: 20px;
  padding: 12px 20px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  z-index: 1000;
  animation: slideIn 0.3s ease;
}

.toast.success {
  background: #10B981;
  color: white;
}

.toast.error {
  background: #EF4444;
  color: white;
}

.toast.info {
  background: #3B82F6;
  color: white;
}

@keyframes slideIn {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

/* ====== RESPONSIVE DESIGN ====== */
@media (max-width: 768px) {
  .staff-management-container {
    height: 100vh;
  }

  .header-content {
    flex-direction: column;
    gap: 12px;
    align-items: stretch;
  }

  .add-staff-btn {
    justify-content: center;
  }

  .tab-navigation {
    padding: 8px 12px;
    gap: 4px;
  }

  .tab-btn {
    padding: 6px 12px;
    font-size: 12px;
  }

  .tab-label {
    display: none;
  }

  .main-content {
    padding: 12px 16px;
  }

  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 8px;
  }

  .quick-actions {
    grid-template-columns: repeat(2, 1fr);
  }

  .staff-filters {
    flex-direction: column;
  }

  .search-input {
    min-width: auto;
  }

  .staff-grid {
    grid-template-columns: 1fr;
  }

  .staff-card {
    padding: 12px;
  }

  .payroll-controls {
    flex-direction: column;
  }

  .payroll-actions {
    flex-direction: column;
  }

  .history-filters {
    flex-direction: column;
  }

  .documents-grid {
    grid-template-columns: 1fr;
  }

  .toast {
    top: 10px;
    right: 10px;
    left: 10px;
    font-size: 13px;
  }
}

@media (max-width: 480px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }

  .quick-actions {
    grid-template-columns: 1fr;
  }

  .stat-card {
    padding: 12px;
  }

  .stat-value {
    font-size: 20px;
  }

  .action-card {
    padding: 16px;
  }

  .payroll-card,
  .advance-card,
  .history-card,
  .leave-card,
  .document-card {
    padding: 16px;
  }
}
</style>
