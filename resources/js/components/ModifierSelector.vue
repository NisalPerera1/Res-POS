<template>
  <Teleport to="body">
    <div
      style="position:fixed; inset:0; background:rgba(0,0,0,0.7);
             display:flex; align-items:center; justify-content:center;
             z-index:200; padding:16px;"
      @click.self="$emit('cancel')"
    >
      <div style="background:#1A1E28; border:1px solid #252B38; border-radius:16px;
                  width:100%; max-width:440px; max-height:88vh;
                  display:flex; flex-direction:column; overflow:hidden;">

        <!-- Header -->
        <div style="padding:16px 18px; border-bottom:1px solid #252B38;
                    display:flex; align-items:center; justify-content:space-between;
                    flex-shrink:0;">
          <div>
            <div style="font-size:15px; font-weight:700; color:#F1F5F9;">
              {{ item.name }}
            </div>
            <div style="font-size:12px; color:#64748B; margin-top:2px;">
              Base price: ${{ parseFloat(item.price).toFixed(2) }}
            </div>
          </div>
          <button @click="$emit('cancel')"
            style="width:32px; height:32px; border-radius:8px; background:#252B38;
                   border:none; color:#64748B; font-size:18px; cursor:pointer;
                   display:flex; align-items:center; justify-content:center;">×</button>
        </div>

        <!-- Modifier Groups -->
        <div style="flex:1; overflow-y:auto; padding:14px 18px;">
          <div
            v-for="group in item.modifier_groups"
            :key="group.id"
            style="margin-bottom:18px;"
          >
            <!-- Group header -->
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
              <div style="display:flex; align-items:center; gap:8px;">
                <div style="font-size:13px; font-weight:700; color:#F1F5F9;">
                  {{ group.name }}
                </div>
                <div v-if="group.is_required"
                  style="font-size:9px; font-weight:700; padding:2px 6px; border-radius:4px;
                         background:rgba(239,68,68,0.12); color:#EF4444;
                         text-transform:uppercase; letter-spacing:0.05em;">
                  Required
                </div>
                <div v-else
                  style="font-size:9px; color:#64748B;">
                  Optional
                </div>
              </div>
              <div style="font-size:10px; color:#64748B;">
                {{ group.max_select > 1 ? `Pick up to ${group.max_select}` : 'Pick 1' }}
              </div>
            </div>

            <!-- Validation error -->
            <div v-if="groupErrors[group.id]"
              style="font-size:11px; color:#EF4444; margin-bottom:6px; padding:4px 8px;
                     background:rgba(239,68,68,0.08); border-radius:5px;">
              ⚠️ {{ groupErrors[group.id] }}
            </div>

            <!-- Options grid -->
            <div style="display:grid; grid-template-columns:repeat(2,1fr); gap:6px;">
              <button
                v-for="mod in group.modifiers"
                :key="mod.id"
                @click="toggleModifier(group, mod)"
                style="padding:10px 12px; border-radius:9px; text-align:left;
                       transition:all 0.12s; cursor:pointer; border:1.5px solid;
                       display:flex; align-items:center; justify-content:space-between;"
                :style="isSelected(mod.id) ? {
                  background:   'rgba(245,158,11,0.1)',
                  borderColor:  '#F59E0B',
                } : {
                  background:   '#12151C',
                  borderColor:  '#252B38',
                }"
              >
                <div>
                  <div style="font-size:12px; font-weight:500;"
                    :style="{ color: isSelected(mod.id) ? '#F1F5F9' : '#94A3B8' }">
                    {{ mod.name }}
                  </div>
                  <div v-if="getModifierPrice(mod) > 0"
                    style="font-size:11px; margin-top:1px;"
                    :style="{ color: isSelected(mod.id) ? '#F59E0B' : '#64748B' }">
                    +${{ getModifierPrice(mod).toFixed(2) }}
                  </div>
                  <div v-else style="font-size:11px; color:#64748B; margin-top:1px;">
                    Included
                  </div>
                </div>

                <!-- Check indicator -->
                <div
                  style="width:20px; height:20px; border-radius:50%; flex-shrink:0;
                         display:flex; align-items:center; justify-content:center;
                         font-size:11px; font-weight:700; transition:all 0.12s;"
                  :style="isSelected(mod.id) ? {
                    background: '#F59E0B',
                    color:      '#000',
                  } : {
                    background: '#252B38',
                    color:      '#64748B',
                  }"
                >
                  {{ isSelected(mod.id) ? '✓' : '' }}
                </div>
              </button>
            </div>
          </div>

          <!-- Notes -->
          <div style="margin-top:4px;">
            <div style="font-size:12px; font-weight:600; color:#64748B; margin-bottom:6px;
                        text-transform:uppercase; letter-spacing:0.06em;">
              Special Instructions
            </div>
            <textarea
              v-model="notes"
              placeholder="e.g. No onions, extra crispy, allergy info..."
              rows="2"
              style="width:100%; background:#12151C; border:1px solid #252B38;
                     border-radius:8px; padding:10px 12px; color:#F1F5F9;
                     font-size:13px; resize:none; font-family:inherit; outline:none;
                     transition:border 0.15s;"
              @focus="e => e.target.style.borderColor='#F59E0B'"
              @blur="e => e.target.style.borderColor='#252B38'"
            ></textarea>
          </div>
        </div>

        <!-- Footer -->
        <div style="padding:14px 18px; border-top:1px solid #252B38; flex-shrink:0;">

          <!-- Price preview -->
          <div style="display:flex; align-items:center; justify-content:space-between;
                      margin-bottom:10px; padding:10px 12px;
                      background:#12151C; border-radius:8px; border:1px solid #252B38;">
            <div>
              <div style="font-size:12px; color:#64748B;">Total per item</div>
              <div v-if="selectedModifierObjects.length > 0"
                style="font-size:10px; color:#64748B; margin-top:2px;">
                Base ${{ parseFloat(item.price).toFixed(2) }}
                + {{ selectedModifierObjects.map(m => m.name).join(', ') }}
              </div>
            </div>
            <div style="font-size:20px; font-weight:700; color:#F59E0B;">
              ${{ totalPrice }}
            </div>
          </div>

          <!-- Qty + Add button -->
          <div style="display:flex; gap:8px; align-items:center;">
            <!-- Quantity -->
            <div style="display:flex; align-items:center; gap:6px;
                        background:#12151C; border:1px solid #252B38;
                        border-radius:8px; padding:4px 8px;">
              <button @click="qty = Math.max(1, qty - 1)"
                style="width:28px; height:28px; border-radius:6px; background:#252B38;
                       border:none; color:#F1F5F9; font-size:16px; cursor:pointer;
                       display:flex; align-items:center; justify-content:center;"
                @mouseenter="e => { e.currentTarget.style.background='#F59E0B'; e.currentTarget.style.color='#000' }"
                @mouseleave="e => { e.currentTarget.style.background='#252B38'; e.currentTarget.style.color='#F1F5F9' }"
              >−</button>
              <span style="font-size:15px; font-weight:700; min-width:24px;
                           text-align:center; color:#F1F5F9;">{{ qty }}</span>
              <button @click="qty++"
                style="width:28px; height:28px; border-radius:6px; background:#252B38;
                       border:none; color:#F1F5F9; font-size:16px; cursor:pointer;
                       display:flex; align-items:center; justify-content:center;"
                @mouseenter="e => { e.currentTarget.style.background='#F59E0B'; e.currentTarget.style.color='#000' }"
                @mouseleave="e => { e.currentTarget.style.background='#252B38'; e.currentTarget.style.color='#F1F5F9' }"
              >+</button>
            </div>

            <!-- Add to cart button -->
            <button @click="confirmAdd"
              style="flex:1; padding:12px; border-radius:9px; background:#F59E0B;
                     color:#000; border:none; font-size:14px; font-weight:700;
                     cursor:pointer; transition:all 0.15s;"
              @mouseenter="e => e.currentTarget.style.filter='brightness(1.1)'"
              @mouseleave="e => e.currentTarget.style.filter='brightness(1)'"
            >
              Add {{ qty }} to Cart · ${{ (parseFloat(totalPrice) * qty).toFixed(2) }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'

const props = defineProps({
  item: {
    type: Object,
    required: true,
  },
})

const emit = defineEmits(['confirm', 'cancel'])

const qty          = ref(1)
const notes        = ref('')
const selected     = ref({})   // { groupId: [modifierId, ...] }
const groupErrors  = ref({})
const itemModifierPricing = ref({}) // Store item-specific pricing

// Load item-specific modifier pricing on mount
onMounted(async () => {
  try {
    const { data } = await axios.get(`/menu/items/${props.item.id}/modifier-pricing`)
    itemModifierPricing.value = data.reduce((acc, p) => {
      acc[p.modifier_id] = {
        pricing_type: p.pricing_type,
        custom_price: p.custom_price,
        increment_price: p.increment_price,
      }
      return acc
    }, {})
  } catch (e) {
    console.error('Failed to load modifier pricing:', e)
  }
})

// Pre-select first option for required single-select groups
props.item.modifier_groups?.forEach(group => {
  if (group.is_required && group.max_select === 1 && group.modifiers?.length > 0) {
    selected.value[group.id] = [group.modifiers[0].id]
  } else {
    selected.value[group.id] = []
  }
})

// ── Computed ──────────────────────────────────────────
const allSelectedIds = computed(() =>
  Object.values(selected.value).flat()
)

const allModifiers = computed(() => {
  const mods = []
  props.item.modifier_groups?.forEach(g => {
    g.modifiers?.forEach(m => mods.push({ ...m, group_id: g.id }))
  })
  return mods
})

const selectedModifierObjects = computed(() =>
  allModifiers.value.filter(m => allSelectedIds.value.includes(m.id))
)

// Calculate the correct price for a modifier based on the menu item
const getModifierPrice = (modifier) => {
  // First check if we have item-specific pricing loaded
  const itemPricing = itemModifierPricing.value[modifier.id]
  if (itemPricing) {
    if (itemPricing.pricing_type === 'absolute') {
      return parseFloat(itemPricing.custom_price || 0)
    } else if (itemPricing.pricing_type === 'increment') {
      return parseFloat(itemPricing.increment_price || 0)
    }
  }
  // Fallback to default modifier price
  return parseFloat(modifier.price || 0)
}

const modifierExtra = computed(() =>
  selectedModifierObjects.value.reduce((s, m) => s + getModifierPrice(m), 0)
)

const totalPrice = computed(() =>
  (parseFloat(props.item.price) + modifierExtra.value).toFixed(2)
)

// ── Methods ───────────────────────────────────────────
function isSelected(modId) {
  return allSelectedIds.value.includes(modId)
}

function toggleModifier(group, mod) {
  const current = selected.value[group.id] ?? []
  const isOn    = current.includes(mod.id)

  if (group.max_select === 1) {
    // Single select (radio behavior)
    selected.value[group.id] = isOn ? [] : [mod.id]
  } else {
    // Multi select (checkbox behavior)
    if (isOn) {
      selected.value[group.id] = current.filter(id => id !== mod.id)
    } else {
      if (current.length >= group.max_select) {
        groupErrors.value[group.id] = `Maximum ${group.max_select} selections allowed`
        setTimeout(() => { delete groupErrors.value[group.id] }, 2000)
        return
      }
      selected.value[group.id] = [...current, mod.id]
    }
  }
  // Clear error on valid selection
  delete groupErrors.value[group.id]
}

function validate() {
  let valid = true
  groupErrors.value = {}

  props.item.modifier_groups?.forEach(group => {
    const count = (selected.value[group.id] ?? []).length

    if (group.is_required && count < group.min_select) {
      groupErrors.value[group.id] = `Please select at least ${group.min_select} option`
      valid = false
    }
  })

  return valid
}

function confirmAdd() {
  if (!validate()) return

  emit('confirm', {
    menu_item_id:       props.item.id,
    quantity:           qty.value,
    selected_modifiers: allSelectedIds.value,
    notes:              notes.value || null,
    is_instant:         props.item.is_instant,
    // For display
    _modifierObjects:   selectedModifierObjects.value,
    _totalPrice:        totalPrice.value,
  })
}
</script>