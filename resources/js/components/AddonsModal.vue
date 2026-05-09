<template>
  <Teleport to="body">
    <div v-if="show" class="modal-overlay" @click.self="close">
      <div class="modal-content addons-modal">
        <div class="modal-header">
          <h3 class="modal-title">🧄 Add Extras to {{ item?.item_name }}</h3>
          <button class="modal-close" @click="close">×</button>
        </div>

        <div class="modal-body">
          <div class="addon-form">
            <!-- Item Name -->
            <div class="form-group">
              <label class="form-label">Extra Item Name</label>
              <input
                v-model="form.addon_name"
                type="text"
                class="form-input"
                placeholder="e.g., Extra Cheese, Special Sauce"
                @focus="onFocus"
                @blur="onBlur"
              />
            </div>

            <!-- Quantity and Unit Row -->
            <div class="form-row">
              <div class="form-group flex-1">
                <label class="form-label">Quantity</label>
                <input
                  v-model.number="form.quantity"
                  type="number"
                  step="0.01"
                  min="0.01"
                  class="form-input"
                  placeholder="Amount"
                  @focus="onFocus"
                  @blur="onBlur"
                  @input="calculateUnitPrice"
                />
              </div>

              <div class="form-group flex-1">
                <label class="form-label">Unit</label>
                <select
                  v-model="form.unit"
                  class="form-select"
                  @focus="onFocus"
                  @blur="onBlur"
                >
                  <option value="grams">Grams (g)</option>
                  <option value="kg">Kilograms (kg)</option>
                  <option value="pieces">Pieces (pcs)</option>
                  <option value="units">Units</option>
                </select>
              </div>
            </div>

            <!-- Total Amount -->
            <div class="form-group">
              <label class="form-label">Total Amount (Rs.)</label>
              <input
                v-model.number="form.total_price"
                type="number"
                step="0.01"
                min="0"
                class="form-input"
                placeholder="0.00"
                @focus="onFocus"
                @blur="onBlur"
                @input="calculateUnitPrice"
              />
            </div>

            <!-- Unit Price (auto-calculated) -->
            <div class="form-group">
              <label class="form-label">Unit Price (Rs. per {{ formattedUnit }})</label>
              <input
                :value="unitPriceDisplay"
                type="text"
                class="form-input"
                readonly
                style="background: var(--bg-tertiary); color: var(--text-muted);"
              />
            </div>

            <!-- Notes -->
            <div class="form-group">
              <label class="form-label">Notes (Optional)</label>
              <textarea
                v-model="form.notes"
                class="form-textarea"
                rows="3"
                placeholder="e.g., Add on side, Extra spicy, No onions..."
                @focus="onFocus"
                @blur="onBlur"
              />
            </div>

            <!-- Custom Price Checkbox -->
            <div class="form-group">
              <label class="checkbox-label">
                <input
                  v-model="form.is_custom_price"
                  type="checkbox"
                  class="checkbox-input"
                />
                <span class="checkbox-text">Market price (price may vary)</span>
              </label>
            </div>
          </div>

          <!-- Existing Addons -->
          <div v-if="existingAddons.length > 0" class="existing-addons">
            <h4 class="section-title">Current Extras</h4>
            <div class="addons-list">
              <div
                v-for="addon in existingAddons"
                :key="addon.id"
                class="addon-item"
              >
                <div class="addon-info">
                  <div class="addon-name">{{ addon.addon_name }}</div>
                  <div class="addon-details">
                    {{ addon.quantity }}{{ addon.formatted_unit }} · Rs.{{ parseFloat(addon.total_price).toFixed(2) }}
                  </div>
                  <div v-if="addon.notes" class="addon-notes">📝 {{ addon.notes }}</div>
                </div>
                <button class="addon-delete" @click="deleteAddon(addon.id)">
                  ✕
                </button>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button class="btn btn-secondary" @click="close">Cancel</button>
          <button
            class="btn btn-primary"
            @click="addAddon"
            :disabled="!isFormValid || loading"
          >
            <span v-if="loading">Adding...</span>
            <span v-else>Add Extra</span>
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import axios from 'axios'

const props = defineProps({
  show: Boolean,
  item: Object, // OrderItem object
  orderType: String // 'table' or 'direct'
})

const emit = defineEmits(['close', 'added', 'deleted'])

// Form data
const form = ref({
  addon_name: '',
  quantity: '',
  unit: 'grams',
  total_price: '',
  notes: '',
  is_custom_price: false
})

const loading = ref(false)
const existingAddons = ref([])

// Computed properties
const isFormValid = computed(() => {
  return form.value.addon_name.trim() &&
         form.value.quantity > 0 &&
         form.value.total_price >= 0
})

const formattedUnit = computed(() => {
  const unitMap = {
    'grams': 'g',
    'kg': 'kg',
    'pieces': 'pcs',
    'units': 'units'
  }
  return unitMap[form.value.unit] || form.value.unit
})

const unitPriceDisplay = computed(() => {
  if (form.value.quantity && form.value.total_price) {
    const unitPrice = form.value.total_price / form.value.quantity
    return unitPrice.toFixed(2)
  }
  return '0.00'
})

// Watch for item changes to load existing addons
watch(() => props.item, (newItem) => {
  if (newItem) {
    loadExistingAddons()
    resetForm()
  }
}, { immediate: true })

// Watch for show changes
watch(() => props.show, (newShow) => {
  if (newShow) {
    loadExistingAddons()
  }
})

// Methods
function calculateUnitPrice() {
  // Unit price is calculated automatically when quantity or total changes
}

function resetForm() {
  form.value = {
    addon_name: '',
    quantity: '',
    unit: 'grams',
    total_price: '',
    notes: '',
    is_custom_price: false
  }
}

async function loadExistingAddons() {
  if (!props.item) return
  
  try {
    const response = await axios.get(`/${props.orderType}-orders/${props.item.order_id}/items/${props.item.id}/addons`)
    existingAddons.value = response.data.data || response.data
  } catch (error) {
    console.error('Failed to load addons:', error)
    existingAddons.value = []
  }
}

async function addAddon() {
  if (!isFormValid.value || !props.item) return
  
  loading.value = true
  
  try {
    const endpoint = props.orderType === 'table' 
      ? `/orders/${props.item.order_id}/items/${props.item.id}/addons`
      : `/direct-orders/${props.item.order_id}/items/${props.item.id}/addons`
    
    const response = await axios.post(endpoint, form.value)
    
    emit('added', response.data)
    resetForm()
    await loadExistingAddons()
    
    // Show success message
    showToast('Extra added successfully!', 'success')
  } catch (error) {
    console.error('Failed to add addon:', error)
    showToast(error.response?.data?.message || 'Failed to add extra', 'error')
  } finally {
    loading.value = false
  }
}

async function deleteAddon(addonId) {
  if (!confirm('Remove this extra?')) return
  
  try {
    const endpoint = props.orderType === 'table'
      ? `/orders/${props.item.order_id}/items/${props.item.id}/addons/${addonId}`
      : `/direct-orders/${props.item.order_id}/items/${props.item.id}/addons/${addonId}`
    
    await axios.delete(endpoint)
    
    emit('deleted', addonId)
    await loadExistingAddons()
    
    showToast('Extra removed', 'success')
  } catch (error) {
    console.error('Failed to delete addon:', error)
    showToast('Failed to remove extra', 'error')
  }
}

function close() {
  resetForm()
  emit('close')
}

function onFocus(event) {
  event.target.style.borderColor = '#F59E0B'
}

function onBlur(event) {
  event.target.style.borderColor = '#252B38'
}

function showToast(message, type = 'success') {
  // Create toast notification
  const toast = document.createElement('div')
  toast.className = `toast toast--${type}`
  toast.textContent = `${type === 'success' ? '✅' : '⚠️'} ${message}`
  toast.style.cssText = `
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 12px 20px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    z-index: 9999;
    background: ${type === 'success' ? '#10B981' : '#EF4444'};
    color: white;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    transition: opacity 0.3s;
  `
  
  document.body.appendChild(toast)
  
  setTimeout(() => {
    toast.style.opacity = '0'
    setTimeout(() => document.body.removeChild(toast), 300)
  }, 3000)
}
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 20px;
}

.modal-content {
  background: var(--bg-primary);
  border-radius: 12px;
  width: 100%;
  max-width: 500px;
  max-height: 90vh;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 20px;
  border-bottom: 1px solid var(--border-color);
}

.modal-title {
  font-size: 18px;
  font-weight: 700;
  color: var(--text-primary);
  margin: 0;
}

.modal-close {
  background: none;
  border: none;
  font-size: 24px;
  color: var(--text-secondary);
  cursor: pointer;
  padding: 4px;
  border-radius: 4px;
  transition: background 0.2s;
}

.modal-close:hover {
  background: var(--bg-tertiary);
}

.modal-body {
  padding: 20px;
  overflow-y: auto;
  flex: 1;
}

.form-group {
  margin-bottom: 16px;
}

.form-row {
  display: flex;
  gap: 12px;
}

.form-row .form-group {
  flex: 1;
}

.form-label {
  display: block;
  font-size: 12px;
  font-weight: 600;
  color: var(--text-secondary);
  margin-bottom: 6px;
  text-transform: uppercase;
  letter-spacing: 0.06em;
}

.form-input, .form-select, .form-textarea {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  background: var(--bg-secondary);
  color: var(--text-primary);
  font-size: 14px;
  transition: border-color 0.2s;
  box-sizing: border-box;
}

.form-input:focus, .form-select:focus, .form-textarea:focus {
  outline: none;
  border-color: #F59E0B;
}

.form-textarea {
  resize: vertical;
  min-height: 80px;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
}

.checkbox-input {
  width: 16px;
  height: 16px;
  accent-color: #F59E0B;
}

.checkbox-text {
  font-size: 14px;
  color: var(--text-primary);
}

.existing-addons {
  margin-top: 24px;
  padding-top: 20px;
  border-top: 1px solid var(--border-color);
}

.section-title {
  font-size: 14px;
  font-weight: 600;
  color: var(--text-primary);
  margin: 0 0 12px 0;
}

.addons-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.addon-item {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  padding: 12px;
  background: var(--bg-tertiary);
  border: 1px solid var(--border-color);
  border-radius: 8px;
  gap: 12px;
}

.addon-info {
  flex: 1;
}

.addon-name {
  font-size: 14px;
  font-weight: 600;
  color: var(--text-primary);
  margin-bottom: 4px;
}

.addon-details {
  font-size: 12px;
  color: var(--text-secondary);
  margin-bottom: 4px;
}

.addon-notes {
  font-size: 11px;
  color: var(--text-muted);
  font-style: italic;
}

.addon-delete {
  background: none;
  border: none;
  color: #EF4444;
  font-size: 16px;
  cursor: pointer;
  padding: 4px;
  border-radius: 4px;
  transition: background 0.2s;
  flex-shrink: 0;
}

.addon-delete:hover {
  background: rgba(239, 68, 68, 0.1);
}

.modal-footer {
  display: flex;
  gap: 12px;
  padding: 20px;
  border-top: 1px solid var(--border-color);
}

.btn {
  padding: 10px 20px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  border: none;
  transition: all 0.2s;
  flex: 1;
}

.btn-secondary {
  background: var(--bg-tertiary);
  color: var(--text-secondary);
  border: 1px solid var(--border-color);
}

.btn-secondary:hover {
  background: var(--bg-secondary);
}

.btn-primary {
  background: linear-gradient(135deg, #F59E0B, #D97706);
  color: #000;
}

.btn-primary:hover:not(:disabled) {
  background: linear-gradient(135deg, #D97706, #B45309);
}

.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Mobile responsiveness */
@media (max-width: 640px) {
  .modal-overlay {
    padding: 10px;
  }
  
  .modal-content {
    max-width: 100%;
  }
  
  .form-row {
    flex-direction: column;
    gap: 8px;
  }
  
  .modal-footer {
    flex-direction: column;
  }
}
</style>
