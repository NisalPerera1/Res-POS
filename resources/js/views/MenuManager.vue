<template>
  <div style="display:flex; flex-direction:column; height:100%; background:#0A0C10;">

    <!-- Header -->
    <div style="padding:12px 16px; border-bottom:1px solid #252B38; background:#12151C;">
      <h1 style="font-size:22px; font-weight:700; color:#F1F5F9; margin:0;">Menu Manager</h1>
    </div>

    <!-- Tab Navigation -->
    <div style="display:flex; gap:4px; padding:12px 16px; background:#1A1E28;">
      <button
        @click="activeTab = 'categories'"
        :style="{
          padding: '8px 16px',
          borderRadius: '6px',
          fontSize: '14px',
          fontWeight: '600',
          border: 'none',
          cursor: 'pointer',
          background: activeTab === 'categories' ? '#F59E0B' : 'transparent',
          color: activeTab === 'categories' ? '#000' : '#64748B',
        }"
      >
        Categories
      </button>
      <button
        @click="activeTab = 'items'"
        :style="{
          padding: '8px 16px',
          borderRadius: '6px',
          fontSize: '14px',
          fontWeight: '600',
          border: 'none',
          cursor: 'pointer',
          background: activeTab === 'items' ? '#F59E0B' : 'transparent',
          color: activeTab === 'items' ? '#000' : '#64748B',
        }"
      >
        Menu Items
      </button>
    </div>

    <!-- Content Area -->
    <div style="flex:1; overflow:hidden; display:flex;">
      
      <!-- Categories Tab -->
      <div v-if="activeTab === 'categories'" style="flex:1; display:flex; flex-direction:column; padding:16px;">
        
        <!-- Categories Header -->
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
          <h2 style="font-size:18px; font-weight:600; color:#F1F5F9;">Categories</h2>
          <button
            @click="showCategoryModal = true; editingCategory = null"
            style="padding:8px 16px; background:#10B981; color:#fff; border:none; border-radius:6px; font-size:12px; font-weight:600; cursor:pointer;"
          >
            + Add Category
          </button>
        </div>

        <!-- Categories List -->
        <div style="flex:1; overflow-y:auto;">
          <div
            v-for="category in categories"
            :key="category.id"
            style="display:flex; align-items:center; justify-content:space-between; padding:12px; background:#1A1E28; border:1px solid #252B38; border-radius:8px; margin-bottom:8px;"
          >
            <div style="display:flex; align-items:center; gap:12px;">
              <div style="width:40px; height:40px; background:#F59E0B; border-radius:6px; display:flex; align-items:center; justify-content:center; font-size:20px;">
                {{ category.icon ?? '🍽️' }}
              </div>
              <div>
                <div style="font-size:14px; font-weight:600; color:#F1F5F9;">{{ category.name }}</div>
                <div style="font-size:12px; color:#64748B;">{{ category.items_count || 0 }} items</div>
              </div>
            </div>
            <div style="display:flex; gap:8px;">
              <button
                @click="editCategory(category)"
                style="padding:6px 12px; background:#3B82F6; color:#fff; border:none; border-radius:4px; font-size:11px; cursor:pointer;"
              >
                Edit
              </button>
              <button
                @click="deleteCategory(category)"
                style="padding:6px 12px; background:#EF4444; color:#fff; border:none; border-radius:4px; font-size:11px; cursor:pointer;"
              >
                Delete
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Menu Items Tab -->
      <div v-if="activeTab === 'items'" style="flex:1; display:flex; flex-direction:column; padding:16px;">
        
        <!-- Items Header -->
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
          <h2 style="font-size:18px; font-weight:600; color:#F1F5F9;">Menu Items</h2>
          <button
            @click="showItemModal = true; editingItem = null"
            style="padding:8px 16px; background:#10B981; color:#fff; border:none; border-radius:6px; font-size:12px; font-weight:600; cursor:pointer;"
          >
            + Add Item
          </button>
        </div>

        <!-- Filter by Category -->
        <div style="margin-bottom:16px;">
          <select
            v-model="selectedCategory"
            @change="loadMenuItems"
            style="padding:8px 12px; background:#1A1E28; border:1px solid #252B38; border-radius:6px; color:#F1F5F9; font-size:14px; width:200px;"
          >
            <option value="">All Categories</option>
            <option v-for="cat in categories" :key="cat.id" :value="cat.id">
              {{ cat.name }}
            </option>
          </select>
        </div>

        <!-- Items Grid -->
        <div style="flex:1; overflow-y:auto;">
          <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(280px, 1fr)); gap:12px;">
            <div
              v-for="item in filteredItems"
              :key="item.id"
              style="background:#1A1E28; border:1px solid #252B38; border-radius:8px; padding:16px;"
            >
              <div style="display:flex; justify-content:space-between; align-items:start; margin-bottom:12px;">
                <div>
                  <div style="font-size:16px; font-weight:600; color:#F1F5F9;">{{ item.name }}</div>
                  <div style="font-size:14px; color:#F59E0B; font-weight:700;">${{ item.price }}</div>
                </div>
                <div style="font-size:24px;">{{ item.icon ?? '🍽️' }}</div>
              </div>
              
              <div style="font-size:12px; color:#64748B; margin-bottom:8px;">{{ item.description || 'No description' }}</div>
              
              <div style="display:flex; gap:4px; flex-wrap:wrap; margin-bottom:12px;">
                <span
                  :style="{
                    padding: '2px 6px',
                    borderRadius: '4px',
                    fontSize: '10px',
                    background: item.is_available ? 'rgba(16,185,129,0.1)' : 'rgba(239,68,68,0.1)',
                    color: item.is_available ? '#10B981' : '#EF4444'
                  }"
                >
                  {{ item.is_available ? 'Available' : 'Unavailable' }}
                </span>
                <span v-if="item.is_popular" style="padding:2px 6px; background:rgba(245,158,11,0.1); color:#F59E0B; border-radius:4px; font-size:10px;">
                  Popular
                </span>
              </div>
              
              <div style="display:flex; gap:8px;">
                <button
                  @click="editItem(item)"
                  style="padding:6px 12px; background:#3B82F6; color:#fff; border:none; border-radius:4px; font-size:11px; cursor:pointer; flex:1;"
                >
                  Edit
                </button>
                <button
                  @click="toggleAvailability(item)"
                  :style="{
                    padding: '6px 12px',
                    border: 'none',
                    borderRadius: '4px',
                    fontSize: '11px',
                    cursor: 'pointer',
                    background: item.is_available ? '#EF4444' : '#10B981',
                    color: '#fff',
                    flex: '1'
                  }"
                >
                  {{ item.is_available ? 'Disable' : 'Enable' }}
                </button>
                <button
                  @click="deleteItem(item)"
                  style="padding:6px 12px; background:#EF4444; color:#fff; border:none; border-radius:4px; font-size:11px; cursor:pointer; flex:1;"
                >
                  Delete
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Category Modal -->
    <Teleport to="body">
      <div v-if="showCategoryModal" style="position:fixed; inset:0; background:rgba(0,0,0,0.6); display:flex; align-items:center; justify-content:center; z-index:50;">
        <div style="background:#12151C; border:1px solid #252B38; border-radius:12px; padding:20px; width:400px; max-width:90vw;">
          <h3 style="font-size:18px; font-weight:700; color:#F1F5F9; margin:0 0 16px 0;">
            {{ editingCategory ? 'Edit Category' : 'Add Category' }}
          </h3>
          
          <div style="display:flex; flex-direction:column; gap:12px;">
            <div>
              <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Category Name</label>
              <input
                v-model="categoryForm.name"
                type="text"
                placeholder="e.g., Appetizers"
                style="width:100%; padding:8px 12px; background:#1A1E28; border:1px solid #252B38; border-radius:6px; color:#F1F5F9; font-size:14px;"
              >
            </div>
            
            <div>
              <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Icon</label>
              <input
                v-model="categoryForm.icon"
                type="text"
                placeholder="e.g., 🥗"
                style="width:100%; padding:8px 12px; background:#1A1E28; border:1px solid #252B38; border-radius:6px; color:#F1F5F9; font-size:14px;"
              >
            </div>
          </div>
          
          <div style="display:flex; gap:8px; margin-top:16px; justify-content:flex-end;">
            <button
              @click="closeCategoryModal"
              style="padding:8px 16px; background:transparent; color:#64748B; border:1px solid #252B38; border-radius:6px; cursor:pointer;"
            >
              Cancel
            </button>
            <button
              @click="saveCategory"
              style="padding:8px 16px; background:#F59E0B; color:#000; border:none; border-radius:6px; cursor:pointer; font-weight:600;"
            >
              {{ editingCategory ? 'Update' : 'Create' }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Menu Item Modal -->
    <Teleport to="body">
      <div v-if="showItemModal" style="position:fixed; inset:0; background:rgba(0,0,0,0.6); display:flex; align-items:center; justify-content:center; z-index:50;">
        <div style="background:#12151C; border:1px solid #252B38; border-radius:12px; padding:20px; width:500px; max-width:90vw; max-height:90vh; overflow-y:auto;">
          <h3 style="font-size:18px; font-weight:700; color:#F1F5F9; margin:0 0 16px 0;">
            {{ editingItem ? 'Edit Menu Item' : 'Add Menu Item' }}
          </h3>
          
          <div style="display:flex; flex-direction:column; gap:12px;">
            <div>
              <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Item Name</label>
              <input
                v-model="itemForm.name"
                type="text"
                placeholder="e.g., Burger"
                style="width:100%; padding:8px 12px; background:#1A1E28; border:1px solid #252B38; border-radius:6px; color:#F1F5F9; font-size:14px;"
              >
            </div>
            
            <div style="display:flex; gap:12px;">
              <div style="flex:1;">
                <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Price</label>
                <input
                  v-model.number="itemForm.price"
                  type="number"
                  step="0.01"
                  placeholder="9.99"
                  style="width:100%; padding:8px 12px; background:#1A1E28; border:1px solid #252B38; border-radius:6px; color:#F1F5F9; font-size:14px;"
                >
              </div>
              
              <div style="flex:1;">
                <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Category</label>
                <select
                  v-model="itemForm.category_id"
                  style="width:100%; padding:8px 12px; background:#1A1E28; border:1px solid #252B38; border-radius:6px; color:#F1F5F9; font-size:14px;"
                >
                  <option value="">Select Category</option>
                  <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                    {{ cat.name }}
                  </option>
                </select>
              </div>
            </div>
            
            <div>
              <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Description</label>
              <textarea
                v-model="itemForm.description"
                placeholder="Item description..."
                rows="3"
                style="width:100%; padding:8px 12px; background:#1A1E28; border:1px solid #252B38; border-radius:6px; color:#F1F5F9; font-size:14px; resize:vertical;"
              ></textarea>
            </div>
            
            <div style="display:flex; gap:12px;">
              <div style="flex:1;">
                <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Icon</label>
                <input
                  v-model="itemForm.icon"
                  type="text"
                  placeholder="🍔"
                  style="width:100%; padding:8px 12px; background:#1A1E28; border:1px solid #252B38; border-radius:6px; color:#F1F5F9; font-size:14px;"
                >
              </div>
              
              <div style="flex:1;">
                <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Prep Time (min)</label>
                <input
                  v-model.number="itemForm.prep_time"
                  type="number"
                  placeholder="15"
                  style="width:100%; padding:8px 12px; background:#1A1E28; border:1px solid #252B38; border-radius:6px; color:#F1F5F9; font-size:14px;"
                >
              </div>
            </div>
            
            <div style="display:flex; gap:16px;">
              <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                <input
                  type="checkbox"
                  v-model="itemForm.is_available"
                  style="width:16px; height:16px;"
                >
                <span style="font-size:14px; color:#F1F5F9;">Available</span>
              </label>
              
              <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                <input
                  type="checkbox"
                  v-model="itemForm.is_popular"
                  style="width:16px; height:16px;"
                >
                <span style="font-size:14px; color:#F1F5F9;">Popular</span>
              </label>
            </div>
          </div>
          
          <div style="display:flex; gap:8px; margin-top:16px; justify-content:flex-end;">
            <button
              @click="closeItemModal"
              style="padding:8px 16px; background:transparent; color:#64748B; border:1px solid #252B38; border-radius:6px; cursor:pointer;"
            >
              Cancel
            </button>
            <button
              @click="saveItem"
              style="padding:8px 16px; background:#F59E0B; color:#000; border:none; border-radius:6px; cursor:pointer; font-weight:600;"
            >
              {{ editingItem ? 'Update' : 'Create' }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'

const activeTab = ref('categories')
const categories = ref([])
const menuItems = ref([])
const selectedCategory = ref('')

// Modal states
const showCategoryModal = ref(false)
const showItemModal = ref(false)
const editingCategory = ref(null)
const editingItem = ref(null)

// Forms
const categoryForm = ref({
  name: '',
  icon: ''
})

const itemForm = ref({
  name: '',
  description: '',
  price: 0,
  category_id: '',
  icon: '',
  prep_time: 15,
  is_available: true,
  is_popular: false
})

// Computed
const filteredItems = computed(() => {
  if (!selectedCategory.value) return menuItems.value
  return menuItems.value.filter(item => item.category_id == selectedCategory.value)
})

// Methods
async function loadCategories() {
  try {
    const { data } = await axios.get('/menu/categories')
    categories.value = data
  } catch (e) {
    console.error('Failed to load categories:', e)
  }
}

async function loadMenuItems() {
  try {
    const { data } = await axios.get('/menu/items')
    menuItems.value = data
  } catch (e) {
    console.error('Failed to load menu items:', e)
  }
}

async function saveCategory() {
  try {
    if (editingCategory.value) {
      await axios.put(`/menu/categories/${editingCategory.value.id}`, categoryForm.value)
    } else {
      await axios.post('/menu/categories', categoryForm.value)
    }
    await loadCategories()
    closeCategoryModal()
  } catch (e) {
    console.error('Failed to save category:', e)
    alert('Failed to save category: ' + (e.response?.data?.message ?? e.message))
  }
}

async function deleteCategory(category) {
  if (!confirm(`Are you sure you want to delete "${category.name}"?`)) return
  
  try {
    await axios.delete(`/menu/categories/${category.id}`)
    await loadCategories()
  } catch (e) {
    console.error('Failed to delete category:', e)
    alert('Failed to delete category: ' + (e.response?.data?.message ?? e.message))
  }
}

function editCategory(category) {
  editingCategory.value = category
  categoryForm.value = {
    name: category.name,
    icon: category.icon || ''
  }
  showCategoryModal.value = true
}

function closeCategoryModal() {
  showCategoryModal.value = false
  editingCategory.value = null
  categoryForm.value = { name: '', icon: '' }
}

async function saveItem() {
  try {
    if (editingItem.value) {
      await axios.put(`/menu/items/${editingItem.value.id}`, itemForm.value)
    } else {
      await axios.post('/menu/items', itemForm.value)
    }
    await loadMenuItems()
    closeItemModal()
  } catch (e) {
    console.error('Failed to save menu item:', e)
    alert('Failed to save menu item: ' + (e.response?.data?.message ?? e.message))
  }
}

async function deleteItem(item) {
  if (!confirm(`Are you sure you want to delete "${item.name}"?`)) return
  
  try {
    await axios.delete(`/menu/items/${item.id}`)
    await loadMenuItems()
  } catch (e) {
    console.error('Failed to delete menu item:', e)
    alert('Failed to delete menu item: ' + (e.response?.data?.message ?? e.message))
  }
}

async function toggleAvailability(item) {
  try {
    await axios.patch(`/menu/items/${item.id}/toggle`)
    await loadMenuItems()
  } catch (e) {
    console.error('Failed to toggle availability:', e)
  }
}

function editItem(item) {
  editingItem.value = item
  itemForm.value = {
    name: item.name,
    description: item.description || '',
    price: item.price,
    category_id: item.category_id,
    icon: item.icon || '',
    prep_time: item.prep_time || 15,
    is_available: item.is_available,
    is_popular: item.is_popular
  }
  showItemModal.value = true
}

function closeItemModal() {
  showItemModal.value = false
  editingItem.value = null
  itemForm.value = {
    name: '',
    description: '',
    price: 0,
    category_id: '',
    icon: '',
    prep_time: 15,
    is_available: true,
    is_popular: false
  }
}

onMounted(async () => {
  await Promise.all([
    loadCategories(),
    loadMenuItems()
  ])
})
</script>
