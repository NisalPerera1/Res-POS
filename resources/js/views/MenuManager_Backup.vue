<template>
  <div style="display:flex; flex-direction:column; height:100%; background:#0A0C10; overflow:hidden;">

    <!-- Header -->
    <div style="padding:12px 20px; border-bottom:1px solid #252B38; background:#12151C;
                display:flex; align-items:center; justify-content:space-between; flex-shrink:0;">
      <h1 style="font-size:20px; font-weight:700; color:#F1F5F9; margin:0;">Menu Manager</h1>
      <div style="font-size:12px; color:#64748B;">
        {{ menuItems.length }} items · {{ categories.length }} categories
      </div>
    </div>

    <!-- Tabs -->
    <div style="display:flex; gap:2px; padding:10px 16px; background:#12151C;
                border-bottom:1px solid #252B38; flex-shrink:0;">
      <button
        v-for="tab in tabs" :key="tab.value"
        @click="activeTab = tab.value"
        style="padding:7px 16px; border-radius:7px; font-size:13px; font-weight:600;
               border:none; cursor:pointer; transition:all 0.15s;"
        :style="{
          background: activeTab === tab.value ? '#F59E0B' : 'transparent',
          color:      activeTab === tab.value ? '#000'    : '#64748B',
        }"
      >{{ tab.label }}</button>
    </div>

    <!-- Category Modal -->
    <div v-if="activeTab === 'categories'"
      style="flex:1; overflow-y:auto; padding:16px;">

      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
        <div style="font-size:14px; font-weight:600; color:#F1F5F9;">All Categories</div>
        <button @click="openCategoryModal(null)"
          style="padding:7px 14px; background:#10B981; color:#fff; border:none;
                 border-radius:7px; font-size:12px; font-weight:600; cursor:pointer;">
          + Add Category
        </button>
      </div>

      <div style="display:flex; flex-direction:column; gap:6px;">
        <div v-for="cat in categories" :key="cat.id"
          style="display:flex; align-items:center; justify-content:space-between;
                 padding:12px 14px; background:#1A1E28; border:1px solid #252B38;
                 border-radius:9px;">
          <div style="display:flex; align-items:center; gap:12px;">
            <div style="width:38px; height:38px; border-radius:8px; background:#252B38;
                        display:flex; align-items:center; justify-content:center; font-size:20px;">
              {{ cat.icon ?? '🍽️' }}
            </div>
            <div style="flex:1;">
              <div style="font-size:14px; font-weight:700; color:#F1F5F9;">{{ cat.name }}</div>
              <div style="font-size:11px; color:#64748B;">{{ cat.items?.length ?? 0 }} items</div>
            </div>
            <div style="display:flex; align-items:center; gap:8px;">
              <button @click="editCategory(cat)"
                style="padding:4px 8px; background:rgba(59,130,246,0.1); color:#3B82F6;
                       border:1px solid rgba(59,130,246,0.2); border-radius:5px;
                       font-size:11px; cursor:pointer;">✏️</button>
              <button @click="deleteCategory(cat)"
                style="padding:4px 8px; background:rgba(239,68,68,0.1); color:#EF4444;
                       border:1px solid rgba(239,68,68,0.2); border-radius:5px;
                       font-size:11px; cursor:pointer;">✕</button>
            </div>
          </div>
        </div>

        <div v-if="categories.length === 0"
             style="text-align:center; padding:40px 16px; color:#64748B; font-size:13px;">
          <div style="font-size:40px; opacity:0.3; margin-bottom:12px;">📂</div>
          <div style="font-size:14px;">No categories yet</div>
          <button @click="showItemModal = false; activeTab = 'modifiers'"
            style="color:#F59E0B; background:none; border:none; cursor:pointer; font-size:12px;">
            Create one →
          </button>
        </div>
      </div>
    </div>

    <!-- ══════════ MENU ITEMS TAB ══════════ -->
    <div v-if="activeTab === 'items'"
      style="flex:1; overflow-y:auto; padding:16px;">

      <!-- Search bar -->
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
        <div style="display:flex; align-items:center; gap:8px; flex:1;">
          <input v-model="searchQuery" placeholder="Search items..." 
                 style="flex:1; padding:8px 12px; background:#12151C; border:1px solid #252B38;
                        border-radius:8px; color:#F1F5F9; font-size:14px; outline:none;"
                 @focus="e => e.target.style.borderColor='#F59E0B'">
          <div v-if="searchQuery" @click="searchQuery = ''"
               style="padding:6px 10px; background:#252B38; color:#64748B; border-radius:6px;
               cursor:pointer; font-size:12px;">✕</div>
        </div>
        <button @click="openItemModal(null)"
          style="padding:7px 14px; background:#10B981; color:#fff; border:none;
                 border-radius:7px; font-size:12px; font-weight:600; cursor:pointer;">
          + Add Item
        </button>
      </div>

      <!-- Items grid -->
      <div v-if="filteredItems.length === 0"
           style="grid-column:1/-1; padding:60px; text-align:center; color:#64748B;">
        <div style="font-size:40px; opacity:0.3; margin-bottom:12px;">🍽️</div>
        <div style="font-size:14px;">No items found</div>
      </div>

      <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(280px, 1fr)); gap:12px;">
        <div v-for="item in filteredItems" :key="item.id"
             style="display:flex; flex-direction:column; background:#1A1E28; border:1px solid #252B38;
                    border-radius:9px; padding:12px; cursor:pointer; transition:all 0.15s;"
             @click="editItem(item)">
          <div style="display:flex; justify-content:space-between; align-items:flex-start;">
            <div style="flex:1;">
              <div style="font-size:14px; font-weight:700; color:#F1F5F9; white-space:nowrap;
                           overflow:hidden; text-overflow:ellipsis;">{{ item.name }}</div>
              <div style="font-size:15px; font-weight:700; color:#F59E0B; margin-top:2px;">
                ${{ parseFloat(item.price).toFixed(2) }}
              </div>
            </div>
            <div style="font-size:28px; margin-left:8px; flex-shrink:0;">{{ item.icon ?? '🍽️' }}</div>
            <div style="display:flex; align-items:center; gap:4px;">
              <span v-if="item.is_available" style="font-size:10px; color:#10B981;">✅ Available</span>
              <span v-if="item.modifier_groups?.length > 0"
                style="font-size:10px; font-weight:600; padding:2px 7px; border-radius:4px;
                       background:rgba(16,185,129,0.1); color:#10B981;">
                ⚙️ {{ item.modifier_groups.length }} modifier{{ item.modifier_groups.length > 1 ? 's' : '' }}
              </span>
              <span v-if="item.is_instant"
                style="font-size:10px; font-weight:600; padding:2px 7px; border-radius:4px;
                       background:rgba(16,185,129,0.1); color:#10B981;">
                ⚡ Instant
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ══════════ MODIFIER GROUPS TAB ══════════ -->
    <div v-if="activeTab === 'modifiers'"
      style="flex:1; display:flex; overflow:hidden;">

      <!-- Left: groups list -->
      <div style="width:260px; border-right:1px solid #252B38; display:flex;
                  flex-direction:column; flex-shrink:0;">
        <div style="padding:10px 14px; border-bottom:1px solid #252B38;
                    display:flex; align-items:center; justify-content:space-between;">
          <div style="font-size:13px; font-weight:600; color:#F1F5F9;">Groups</div>
          <button @click="openGroupModal(null)"
            style="padding:4px 10px; background:#10B981; color:#fff; border:none;
                   border-radius:7px; font-size:12px; font-weight:600; cursor:pointer;">
            + Add Group
          </button>
        </div>

        <div style="flex:1; overflow-y:auto; padding:8px;">
          <div v-for="group in modifierGroups" :key="group.id"
                @click="selectedGroup = group"
                style="padding:10px 12px; border-radius:8px; cursor:pointer;
                   margin-bottom:4px; transition:all 0.15s; border:1px solid;"
                :style="{
                  background:   selectedGroup?.id === group?.id ? '#1A1E28' : 'transparent',
                  borderColor: selectedGroup?.id === group?.id ? '#F59E0B' : '#252B38',
                }"
          >
            <div style="flex:1;">
              <div style="font-size:13px; font-weight:600; color:#F1F5F9;">{{ group.name }}</div>
              <div style="font-size:11px; color:#64748B; margin-top:3px;">
                {{ group.modifiers?.length ?? 0 }} options ·
                Pick {{ group.min_select }}–{{ group.max_select }}
              </div>
            </div>
            <div style="width:24px; height:24px; display:flex; align-items:center; justify-content:center;
                       font-size:12px; color:#64748B;">
              {{ selectedGroup?.id === group?.id ? '✓' : '' }}
            </div>
          </div>

          <div v-if="modifierGroups.length === 0"
                style="padding:16px; text-align:center; color:#64748B; font-size:12px;
                         background:#12151C; border-radius:8px; border:1px dashed #252B38;">
                No groups yet
                <button @click="showItemModal = false; activeTab = 'modifiers'"
                  style="color:#F59E0B; background:none; border:none; cursor:pointer; font-size:12px;">
                    Create one →
                </button>
          </div>
        </div>
      </div>

      <!-- Right: group detail + modifiers -->
      <div style="flex:1; display:flex; flex-direction:column; overflow:hidden;">

        <!-- No group selected -->
        <div v-if="!selectedGroup"
              style="display:flex; flex-direction:column; align-items:center; justify-content:center;
                     padding:40px; color:#64748B; font-size:13px;">
          <div style="font-size:48px; opacity:0.3; margin-bottom:12px;">⚙️</div>
          <div style="font-size:16px; margin-bottom:8px;">Select a group to see options</div>
          <div style="font-size:12px; opacity:0.7;">Groups help organize modifiers (e.g. "Size", "Spice Level", "Add-ons")</div>
        </div>

        <!-- Group selected -->
        <div v-else style="flex:1; display:flex; flex-direction:column;">

          <!-- Header -->
          <div style="padding:16px 20px; border-bottom:1px solid #252B38; background:#12151C;">
            <div style="display:flex; align-items:center; gap:12px;">
              <div style="font-size:18px; font-weight:700; color:#F1F5F9;">
                {{ selectedGroup.name }}
              </div>
              <div style="font-size:12px; color:#64748B;">
                {{ selectedGroup.min_select }}–{{ selectedGroup.max_select }} selection
              </div>
            </div>
          </div>

          <!-- Modifiers list -->
          <div style="flex:1; overflow-y:auto; padding:12px 16px;">

            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
              <div style="font-size:13px; font-weight:600; color:#F1F5F9;">
                Options ({{ selectedGroup.modifiers?.length ?? 0 }})
              </div>
              <button @click="openModifierModal(null)"
                style="padding:5px 12px; background:#10B981; color:#fff; border:none;
                       border-radius:6px; font-size:11px; font-weight:600; cursor:pointer;">
                + Add Option
              </button>
            </div>

            <div style="display:flex; flex-direction:column; gap:6px;">
              <div v-for="mod in selectedGroup.modifiers" :key="mod.id"
                style="display:flex; align-items:center; justify-content:space-between;
                       padding:10px 12px; background:#1A1E28; border:1px solid #252B38;
                       border-radius:8px;">
                
                <div style="display:flex; align-items:center; gap:10px; flex:1;">
                  <div>
                    <div style="font-size:13px; font-weight:500; color:#F1F5F9;">{{ mod.name }}</div>
                    <div style="font-size:11px; margin-top:1px;"
                      :style="{ color: mod.price > 0 ? '#F59E0B' : '#64748B' }">
                      {{ mod.price > 0 ? '+$' + parseFloat(mod.price).toFixed(2) : 'Included' }}
                    </div>
                  </div>
                </div>
                <div style="display:flex; align-items:center; gap:4px;">
                  <button @click="openModifierModal(mod)"
                    style="padding:4px 10px; background:rgba(59,130,246,0.1); color:#3B82F6;
                           border:1px solid rgba(59,130,246,0.2); border-radius:5px;
                           font-size:11px; cursor:pointer; font-weight:600;">Edit</button>
                  <button @click="deleteModifier(mod)"
                    style="padding:4px 8px; background:rgba(239,68,68,0.1); color:#EF4444;
                           border:1px solid rgba(239,68,68,0.2); border-radius:5px;
                           font-size:11px; cursor:pointer;">✕</button>
                </div>
              </div>
            </div>

            <div v-if="!selectedGroup.modifiers?.length"
                  style="padding:30px; text-align:center; color:#64748B; font-size:13px;">
                No options yet. Add some above.
              </div>
          </div>

          <!-- Modal footer -->
          <div style="display:flex; gap:8px; margin-top:20px; justify-content:flex-end;">
            <button @click="showGroupModal = false"
              style="padding:10px 18px; background:transparent; color:#64748B;
                     border:1px solid #252B38; border-radius:8px; cursor:pointer; font-size:13px;">
              Cancel
            </button>
            <button @click="saveGroup"
              style="padding:10px 24px; background:#F59E0B; color:#000; border:none;
                     border-radius:8px; cursor:pointer; font-weight:700; font-size:13px;">
              {{ editingGroup ? 'Update Group' : 'Create Group' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ══════════ MODIFIER PRICING TAB ══════════ -->
    <div v-if="activeTab === 'pricing'"
      style="flex:1; display:flex; overflow:hidden;">

      <!-- Left: Menu Items list -->
      <div style="width:300px; border-right:1px solid #252B38; display:flex;
                  flex-direction:column; flex-shrink:0;">
        <div style="padding:10px 14px; border-bottom:1px solid #252B38;
                    display:flex; align-items:center; justify-content:space-between;">
          <div style="font-size:13px; font-weight:600; color:#F1F5F9;">Menu Items</div>
        </div>

        <div style="flex:1; overflow-y:auto; padding:8px;">
          <div v-for="item in menuItems" :key="item.id"
                @click="selectItemForPricing(item)"
                style="display:flex; align-items:center; justify-content:space-between;
                       padding:10px 12px; border-radius:8px; cursor:pointer;
                       margin-bottom:4px; transition:all 0.15s; border:1px solid;"
                :style="{
                  background: selectedItemForPricing?.id === item.id ? '#1A1E28' : 'transparent',
                  borderColor: selectedItemForPricing?.id === item.id ? '#F59E0B' : '#252B38',
                }"
          >
            <div style="flex:1;">
              <div style="font-size:13px; font-weight:500; color:#F1F5F9;">{{ item.name }}</div>
              <div style="font-size:11px; color:#64748B;">Rs. {{ parseFloat(item.price).toFixed(2) }}</div>
            </div>
            <div style="width:24px; height:24px; display:flex; align-items:center; justify-content:center;
                       font-size:12px; color:#64748B;">
              {{ selectedItemForPricing?.id === item.id ? '✓' : '' }}
            </div>
          </div>
        </div>
      </div>

      <!-- Right: Modifier pricing configuration -->
      <div style="flex:1; display:flex; flex-direction:column; overflow:hidden;">

        <!-- No item selected -->
        <div v-if="!selectedItemForPricing"
              style="display:flex; flex-direction:column; align-items:center; justify-content:center;
                     padding:40px; color:#64748B; font-size:13px; text-align:center;">
          <div style="font-size:48px; opacity:0.3; margin-bottom:12px;">⚙️</div>
          <div style="font-size:16px; margin-bottom:8px;">Select a Menu Item</div>
          <div style="font-size:12px; opacity:0.7;">Choose an item to configure its modifier pricing</div>
        </div>

        <!-- Item selected - show pricing -->
        <div v-else style="flex:1; display:flex; flex-direction:column;">

          <!-- Header -->
          <div style="padding:16px 20px; border-bottom:1px solid #252B38; background:#12151C;">
            <div style="display:flex; align-items:center; gap:12px;">
              <div style="font-size:18px; font-weight:700; color:#F1F5F9;">
                {{ selectedItemForPricing.name }}
              </div>
              <div style="font-size:14px; color:#64748B;">
                Base Price: Rs. {{ parseFloat(selectedItemForPricing.price).toFixed(2) }}
              </div>
            </div>
          </div>

          <!-- Pricing configuration -->
          <div style="flex:1; overflow-y:auto; padding:16px 20px;">
            <div style="font-size:14px; font-weight:600; color:#F1F5F9; margin-bottom:16px;">
              Modifier Pricing Configuration
            </div>

            <div style="display:flex; flex-direction:column; gap:12px;">
              <div v-for="modifier in allModifiers" :key="modifier.id"
                    style="padding:12px; background:#1A1E28; border:1px solid #252B38; border-radius:8px;">
                
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
                  <div style="font-size:14px; font-weight:500; color:#F1F5F9;">{{ modifier.name }}</div>
                  <div style="font-size:12px; color:#64748B;">Default: Rs. {{ parseFloat(modifier.price).toFixed(2) }}</div>
                </div>

                <!-- Pricing type selector -->
                <div style="display:flex; align-items:center; gap:8px; margin-bottom:8px;">
                  <label style="font-size:11px; color:#64748B; min-width:80px;">Pricing Type:</label>
                  <select v-model="modifierPricing[modifier.id].pricing_type" 
                          style="flex:1; padding:6px 8px; background:#12151C; border:1px solid #252B38;
                                 border-radius:6px; color:#F1F5F9; font-size:12px;">
                    <option value="absolute">Absolute Price</option>
                    <option value="increment">Increment Price</option>
                  </select>
                </div>

                <!-- Price inputs based on type -->
                <div v-if="modifierPricing[modifier.id].pricing_type === 'absolute'" 
                     style="display:flex; align-items:center; gap:8px;">
                  <label style="font-size:11px; color:#64748B; min-width:80px;">Price:</label>
                  <input v-model.number="modifierPricing[modifier.id].custom_price" 
                         type="number" step="0.01" placeholder="0.00"
                         style="flex:1; padding:6px 8px; background:#12151C; border:1px solid #252B38;
                                border-radius:6px; color:#F1F5F9; font-size:12px;">
                </div>

                <div v-if="modifierPricing[modifier.id].pricing_type === 'increment'" 
                     style="display:flex; align-items:center; gap:8px;">
                  <label style="font-size:11px; color:#64748B; min-width:80px;">Increment:</label>
                  <input v-model.number="modifierPricing[modifier.id].increment_price" 
                         type="number" step="0.01" placeholder="0.00"
                         style="flex:1; padding:6px 8px; background:#12151C; border:1px solid #252B38;
                                border-radius:6px; color:#F1F5F9; font-size:12px;">
                  <div style="font-size:10px; color:#64748B;">(amount to add to base price)</div>
                </div>

                <!-- Preview -->
                <div style="margin-top:8px; padding:8px; background:#12151C; border-radius:6px;">
                  <div style="font-size:11px; color:#64748B; margin-bottom:4px;">Preview:</div>
                  <div style="font-size:12px; font-weight:600; color:#F59E0B;">
                    <span v-if="modifierPricing[modifier.id].pricing_type === 'absolute'">
                      Final Price: Rs. {{ parseFloat(modifierPricing[modifier.id].custom_price || 0).toFixed(2) }}
                    </span>
                    <span v-else>
                      Final Price: Rs. {{ (parseFloat(selectedItemForPricing.price) + parseFloat(modifierPricing[modifier.id].increment_price || 0)).toFixed(2) }}
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Save button -->
            <div style="padding:16px 0; border-top:1px solid #252B38;">
              <button @click="saveModifierPricing"
                      style="width:100%; padding:10px; background:#10B981; color:#fff; border:none;
                             border-radius:8px; font-size:13px; font-weight:600; cursor:pointer;">
                Save Modifier Pricing
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Category Modal -->
    <Teleport to="body">
      <div v-if="showCategoryModal"
        style="position:fixed; inset:0; background:rgba(0,0,0,0.65);
               display:flex; align-items:center; justify-content:center; z-index:100;"
        @click.self="showCategoryModal = false">
        <div style="background:#1A1E28; border:1px solid #252B38; border-radius:14px;
                    padding:22px; width:380px;">
          <div style="font-size:16px; font-weight:700; color:#F1F5F9; margin-bottom:16px;">
            {{ editingCategory ? 'Edit Category' : 'New Category' }}
          </div>
          <div style="display:flex; flex-direction:column; gap:12px;">
            <label style="font-size:11px; color:#64748B; display:block; margin-bottom:5px;
                           text-transform:uppercase; letter-spacing:0.05em;">Name *</label>
            <input v-model="categoryForm.name" type="text" placeholder="e.g. Beverages"
                  style="width:100%; padding:9px 12px; background:#12151C; border:1px solid #252B38;
                         border-radius:8px; color:#F1F5F9; font-size:15px; font-weight:700; outline:none;"
                  @focus="e => e.target.style.borderColor='#F59E0B'">
            </div>
            <label style="font-size:11px; color:#64748B; display:block; margin-bottom:5px;
                           text-transform:uppercase; letter-spacing:0.05em;">Icon *</label>
            <input v-model="categoryForm.icon" type="text" placeholder="🍽️"
                  style="width:100%; padding:9px 12px; background:#12151C; border:1px solid #252B38;
                         border-radius:8px; color:#F1F5F9; font-size:15px; font-weight:700; outline:none;"
                  @focus="e => e.target.style.borderColor='#F59E0B'">
            </div>
          </div>
          <div style="display:flex; gap:8px; margin-top:20px; justify-content:flex-end;">
            <button @click="showCategoryModal = false"
              style="padding:10px 18px; background:transparent; color:#64748B;
                     border:1px solid #252B38; border-radius:8px; cursor:pointer; font-size:13px;">
              Cancel
            </button>
            <button @click="saveCategory"
              style="padding:10px 24px; background:#F59E0B; color:#000; border:none;
                     border-radius:8px; cursor:pointer; font-weight:700; font-size:13px;">
              {{ editingCategory ? 'Update Category' : 'Create Category' }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Item Modal -->
    <Teleport to="body">
      <div v-if="showItemModal"
        style="position:fixed; inset:0; background:rgba(0,0,0,0.65);
               display:flex; align-items:center; justify-content:center; z-index:100;"
        @click.self="showItemModal = false">
        <div style="background:#1A1E28; border:1px solid #252B38; border-radius:14px;
                    padding:22px; width:420px;">
          <div style="font-size:16px; font-weight:700; color:#F1F5F9; margin-bottom:16px;">
            {{ editingItem ? 'Edit Item' : 'Create Item' }}
          </div>

          <div style="display:flex; flex-direction:column; gap:12px;">
            <label style="font-size:11px; color:#64748B; display:block; margin-bottom:5px;
                           text-transform:uppercase; letter-spacing:0.05em;">Name *</label>
            <input v-model="itemForm.name" type="text" placeholder="e.g. Beef Burger"
                  style="width:100%; padding:9px 12px; background:#12151C; border:1px solid #252B38;
                         border-radius:8px; color:#F1F5F9; font-size:15px; font-weight:700; outline:none;"
                  @focus="e => e.target.style.borderColor='#F59E0B'">
            </div>
            <label style="font-size:11px; color:#64748B; display:block; margin-bottom:5px;
                           text-transform:uppercase; letter-spacing:0.05em;">Description</label>
            <textarea v-model="itemForm.description" placeholder="e.g. Juicy beef patty with lettuce, tomato, and special sauce"
                      style="width:100%; padding:9px 12px; background:#12151C; border:1px solid #252B38;
                             border-radius:8px; color:#F1F5F9; font-size:13px; resize:vertical; outline:none;"
                      rows="3"
                      @focus="e => e.target.style.borderColor='#F59E0B'"></textarea>
            </div>
            <label style="font-size:11px; color:#64748B; display:block; margin-bottom:5px;
                           text-transform:uppercase; letter-spacing:0.05em;">Price *</label>
            <input v-model.number="itemForm.price" type="number" step="0.01" placeholder="0.00"
                  style="width:100%; padding:9px 12px; background:#12151C; border:1px solid #252B38;
                         border-radius:8px; color:#F59E0B; font-size:15px; font-weight:700; outline:none;"
                  @focus="e => e.target.style.borderColor='#F59E0B'">
            </div>
            <label style="font-size:11px; color:#64748B; display:block; margin-bottom:5px;
                           text-transform:uppercase; letter-spacing:0.05em;">Cost Price</label>
            <input v-model.number="itemForm.cost_price" type="number" step="0.01" placeholder="0.00"
                  style="width:100%; padding:9px 12px; background:#12151C; border:1px solid #252B38;
                         border-radius:8px; color:#F1F5F9; font-size:15px; font-weight:700; outline:none;"
                  @focus="e => e.target.style.borderColor='#F59E0B'">
            </div>
            <label style="font-size:11px; color:#64748B; display:block; margin-bottom:5px;">
                           text-transform:uppercase; letter-spacing:0.05em;">Category *</label>
            <select v-model="itemForm.category_id" 
                    style="width:100%; padding:9px 12px; background:#12151C; border:1px solid #252B38;
                           border-radius:8px; color:#F1F5F9; font-size:15px; font-weight:700; outline:none;"
                    @focus="e => e.target.style.borderColor='#F59E0B'">
              <option value="">Select category</option>
              <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                {{ cat.name }}
              </option>
            </select>
            <label style="font-size:11px; color:#64748B; display:block; margin-bottom:5px;
                           text-transform:uppercase; letter-spacing:0.05em;">Icon</label>
            <input v-model="itemForm.icon" type="text" placeholder="🍽️"
                  style="width:100%; padding:9px 12px; background:#12151C; border:1px solid #252B38;
                         border-radius:8px; color:#F1F5F9; font-size:15px; font-weight:700; outline:none;"
                  @focus="e => e.target.style.borderColor='#F59E0B'">
            </div>
            <label style="font-size:11px; color:#64748B; display:block; margin-bottom:5px;">
                           text-transform:uppercase; letter-spacing:0.05em;">Type</label>
            <select v-model="itemForm.type" 
                    style="width:100%; padding:9px 12px; background:#12151C; border:1px solid #252B38;
                           border-radius:8px; color:#F1F5F9; font-size:15px; font-weight:700; outline:none;"
                    @focus="e => e.target.style.borderColor='#F59E0B'">
              <option value="food">Food</option>
              <option value="beverage">Beverage</option>
            </select>
            <label style="font-size:11px; color:#64748B; display:block; margin-bottom:5px;">
                           text-transform:uppercase; letter-spacing:0.05em;">Prep Time</label>
            <input v-model.number="itemForm.prep_time" type="number" placeholder="15"
                  style="width:100%; padding:9px 12px; background:#12151C; border:1px solid #252B38;
                         border-radius:8px; color:#F1F5F9; font-size:15px; font-weight:700; outline:none;"
                  @focus="e => e.target.style.borderColor='#F59E0B'">
            </div>
            <label style="font-size:11px; color:#64748B; display:block; margin-bottom:5px;">
                           text-transform:uppercase; letter-spacing:0.05em;">Sort Order</label>
            <input v-model.number="itemForm.sort_order" type="number" placeholder="0"
                  style="width:100%; padding:9px 12px; background:#12151C; border:1px solid #252B38;
                         border-radius:8px; color:#F1F5F9; font-size:15px; font-weight:700; outline:none;"
                  @focus="e => e.target.style.borderColor='#F59E0B'">
            </div>
            <label style="font-size:11px; color:#64748B; display:block; margin-bottom:5px;">
                           text-transform:uppercase; letter-spacing:0.05em;">Availability</label>
            <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
              <input v-model="itemForm.is_available" type="checkbox" true
                     style="width:18px; height:18px;">
              <span style="font-size:13px; color:#64748B;">Available</span>
            </label>
            <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
              <input v-model="itemForm.is_instant" type="checkbox" true
                     style="width:18px; height:18px;">
              <span style="font-size:13px; color:#64748B;">Instant Item</span>
            </label>
            <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
              <input v-model="itemForm.is_popular" type="checkbox" true
                     style="width:18px; height:18px;">
              <span style="font-size:13px; color:#64748B;">Popular Item</span>
            </label>
          </div>

          <!-- ── MODIFIER GROUPS SECTION ── -->
          <div style="border-top:1px solid #252B38; padding-top:14px;">
              <div style="font-size:12px; font-weight:700; color:#F1F5F9; margin-bottom:10px;
                           text-transform:uppercase; letter-spacing:0.06em; display:flex;
                           align-items:center; gap:6px;">
                ⚙️ Modifier Groups
                <span style="font-size:10px; color:#64748B; font-weight:400; text-transform:none;">
                  (optional — for size, spice, add-ons etc.)
                </span>
              </div>

              <!-- Available groups to attach -->
              <div style="display:flex; flex-direction:column; gap:6px;">
                <div v-for="group in modifierGroups" :key="group.id"
                  @click="toggleGroupOnItem(group.id)"
                  style="display:flex; align-items:center; justify-content:space-between;
                         padding:10px 12px; border-radius:8px; cursor:pointer;
                         border:1.5px solid; transition:all 0.15s;"
                  :style="{
                    background:   itemForm.modifier_group_ids.includes(group.id)
                      ? 'rgba(245,158,11,0.08)' : '#12151C',
                    borderColor: itemForm.modifier_group_ids.includes(group.id)
                      ? '#F59E0B' : '#252B38',
                  }"
                >
                  <div style="flex:1;">
                    <div style="font-size:13px; font-weight:500;"
                      :style="{ color: itemForm.modifier_group_ids.includes(group.id) ? '#F1F5F9' : '#94A3B8' }">
                      {{ group.name }}
                      <span style="font-size:10px; margin-left:6px; opacity:0.7;">
                        {{ group.is_required ? '(required)' : '(optional)' }} ·
                        {{ group.modifiers?.length ?? 0 }} options
                      </span>
                    </div>
                    <!-- Show modifier names -->
                    <div style="font-size:10px; color:#64748B; margin-top:2px;">
                      {{ group.modifiers?.slice(0,4).map(m => m.name).join(', ') }}
                      {{ (group.modifiers?.length ?? 0) > 4 ? '...' : '' }}
                    </div>
                  </div>
                  <!-- Checkbox indicator -->
                  <div style="display:flex; align-items:center; justify-content:center;
                               display:flex; align-items:center; justify-content:center;
                               font-size:12px; font-weight:700; margin-left:10px; transition:all 0.15s;"
                    :style="{
                      background: itemForm.modifier_group_ids.includes(group.id) ? '#F59E0B' : '#252B38',
                      color:      itemForm.modifier_group_ids.includes(group.id) ? '#000' : '#64748B',
                    }"
                  >{{ itemForm.modifier_group_ids.includes(group.id) ? '✓' : '' }}</div>
                </div>

                <div v-if="modifierGroups.length === 0"
                  style="padding:16px; text-align:center; color:#64748B; font-size:12px;
                         background:#12151C; border-radius:8px; border:1px dashed #252B38;">
                  No modifier groups yet.
                  <button @click="showItemModal = false; activeTab = 'modifiers'"
                    style="color:#F59E0B; background:none; border:none; cursor:pointer; font-size:12px;">
                    Create one →
                  </button>
                </div>
          </div>

          <div style="display:flex; gap:8px; margin-top:20px; justify-content:flex-end;">
            <button @click="showItemModal = false"
              style="padding:10px 18px; background:transparent; color:#64748B;
                     border:1px solid #252B38; border-radius:8px; cursor:pointer; font-size:13px;">
              Cancel
            </button>
            <button @click="saveItem"
              style="padding:10px 24px; background:#F59E0B; color:#000; border:none;
                     border-radius:8px; cursor:pointer; font-weight:700; font-size:13px;">
              {{ editingItem ? 'Update Item' : 'Create Item' }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Group Modal -->
    <Teleport to="body">
      <div v-if="showGroupModal"
        style="position:fixed; inset:0; background:rgba(0,0,0,0.65);
               display:flex; align-items:center; justify-content:center; z-index:100;"
        @click.self="showGroupModal = false">
        <div style="background:#1A1E28; border:1px solid #252B38; border-radius:14px;
                    padding:22px; width:380px;">
          <div style="font-size:16px; font-weight:700; color:#F1F5F9; margin-bottom:16px;">
            {{ editingGroup ? 'Edit Group' : 'New Group' }}
          </div>
          <div style="display:flex; flex-direction:column; gap:12px;">
            <label style="font-size:11px; color:#64748B; display:block; margin-bottom:5px;
                           text-transform:uppercase; letter-spacing:0.05em;">Name *</label>
            <input v-model="groupForm.name" type="text" placeholder="e.g. Size"
                  style="width:100%; padding:9px 12px; background:#12151C; border:1px solid #252B38;
                         border-radius:8px; color:#F1F5F9; font-size:15px; font-weight:700; outline:none;"
                  @focus="e => e.target.style.borderColor='#F59E0B'">
            </div>
            <label style="font-size:11px; color:#64748B; display:block; margin-bottom:5px;
                           text-transform:uppercase; letter-spacing:0.05em;">Min Select</label>
            <input v-model.number="groupForm.min_select" type="number" placeholder="0"
                  style="width:100%; padding:9px 12px; background:#12151C; border:1px solid #252B38;
                         border-radius:8px; color:#F1F5F9; font-size:15px; font-weight:700; outline:none;"
                  @focus="e => e.target.style.borderColor='#F59E0B'">
            </div>
            <label style="font-size:11px; color:#64748B; display:block; margin-bottom:5px;
                           text-transform:uppercase; letter-spacing:0.05em;">Max Select</label>
            <input v-model.number="groupForm.max_select" type="number" placeholder="99"
                  style="width:100%; padding:9px 12px; background:#12151C; border:1px solid #252B38;
                         border-radius:8px; color:#F1F5F9; font-size:15px; font-weight:700; outline:none;"
                  @focus="e => e.target.style.borderColor='#F59E0B'">
            </div>
            <label style="font-size:11px; color:#64748B; display:block; margin-bottom:5px;">
                           text-transform:uppercase; letter-spacing:0.05em;">Required</label>
            <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
              <input v-model="groupForm.is_required" type="checkbox" true
                     style="width:18px; height:18px;">
              <span style="font-size:13px; color:#64748B;">Required</span>
            </label>
          </div>
          <div style="display:flex; gap:8px; margin-top:20px; justify-content:flex-end;">
            <button @click="showGroupModal = false"
              style="padding:10px 18px; background:transparent; color:#64748B;
                     border:1px solid #252B38; border-radius:8px; cursor:pointer; font-size:13px;">
              Cancel
            </button>
            <button @click="saveGroup"
              style="padding:10px 24px; background:#F59E0B; color:#000; border:none;
                     border-radius:8px; cursor:pointer; font-weight:700; font-size:13px;">
              {{ editingGroup ? 'Update Group' : 'Create Group' }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Modifier Modal -->
    <Teleport to="body">
      <div v-if="showModifierModal"
        style="position:fixed; inset:0; background:rgba(0,0,0,0.65);
               display:flex; align-items:center; justify-content:center; z-index:100;"
        @click.self="showModifierModal = false">
        <div style="background:#1A1E28; border:1px solid #252B38; border-radius:14px;
                    padding:22px; width:380px;">
          <div style="font-size:16px; font-weight:700; color:#F1F5F9; margin-bottom:16px;">
            {{ editingModifier ? 'Edit Option' : 'New Option' }}
          </div>
          <div style="display:flex; flex-direction:column; gap:12px;">
            <label style="font-size:11px; color:#64748B; display:block; margin-bottom:5px;
                           text-transform:uppercase; letter-spacing:0.05em;">Name *</label>
            <input v-model="modifierForm.name" type="text" placeholder="e.g. Extra Cheese"
                  style="width:100%; padding:9px 12px; background:#12151C; border:1px solid #252B38;
                         border-radius:8px; color:#F1F5F9; font-size:15px; font-weight:700; outline:none;"
                  @focus="e => e.target.style.borderColor='#F59E0B'">
            </div>
            <label style="font-size:11px; color:#64748B; display:block; margin-bottom:5px;">
                           text-transform:uppercase; letter-spacing:0.05em;">Extra Price ($) — 0 = included
              </label>
            <input v-model.number="modifierForm.price" type="number" step="0.01" min="0" placeholder="0.00"
                  style="width:100%; padding:9px 12px; background:#12151C; border:1px solid #252B38;
                       border-radius:8px; color:#F1F5F9; font-size:15px; font-weight:700; outline:none;"
                  @focus="e => e.target.style.borderColor='#F59E0B'">
            </div>
            <label style="font-size:11px; color:#64748B; display:block; margin-bottom:5px;">
                           text-transform:uppercase; letter-spacing:0.05em;">Group</label>
            <select v-model="modifierForm.group_id" 
                    style="width:100%; padding:9px 12px; background:#12151C; border:1px solid #252B38;
                           border-radius:8px; color:#F1F5F9; font-size:15px; font-weight:700; outline:none;"
                    @focus="e => e.target.style.borderColor='#F59E0B'">
              <option value="">Select group</option>
              <option v-for="group in modifierGroups" :key="group.id" :value="group.id">
                {{ group.name }}
              </option>
            </select>
            <label style="font-size:11px; color:#64748B; display:block; margin-bottom:5px;">
                           text-transform:uppercase; letter-spacing:0.05em;">Active</label>
            <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
              <input v-model="modifierForm.is_active" type="checkbox" true
                     style="width:18px; height:18px;">
              <span style="font-size:13px; color:#64748B;">Active</span>
            </label>
          </div>
          <div style="display:flex; gap:8px; margin-top:20px; justify-content:flex-end;">
            <button @click="showModifierModal = false"
              style="padding:10px 18px; background:transparent; color:#64748B;
                     border:1px solid #252B38; border-radius:8px; cursor:pointer; font-size:13px;">
              Cancel
            </button>
            <button @click="saveModifier"
              style="padding:10px 24px; background:#F59E0B; color:#000; border:none;
                     border-radius:8px; cursor:pointer; font-weight:700; font-size:13px;">
              {{ editingModifier ? 'Update Option' : 'Create Option' }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'

// ── Tabs ──────────────────────────────────────────
const tabs = [
  { value: 'categories', label: 'Categories' },
  { value: 'items',       label: 'Menu Items' },
  { value: 'modifiers',  label: 'Modifier Groups' },
  { value: 'pricing',     label: 'Modifier Pricing' },
]
const activeTab = ref('items')

// ── Data ──────────────────────────────────────────────────
const categories     = ref([])
const menuItems      = ref([])
const modifierGroups = ref([])
const selectedGroup  = ref(null)
const selectedItemForPricing = ref(null)
const allModifiers = ref([])
const modifierPricing = ref({})

// ── Filters ───────────────────────────────────────────────
const selectedCategory = ref('')
const searchQuery      = ref('')

// ── Modal states ──────────────────────────────────────────
const showCategoryModal = ref(false)
const showItemModal     = ref(false)
const showGroupModal    = ref(false)
const showModifierModal = ref(false)

// ── Forms ───────────────────────────────────────────────
const defaultCategoryForm = () => ({
  name: '',
})

const defaultItemForm = () => ({
  name:                '',
  description:         '',
  price:               0,
  cost_price:          0,
  category_id:         '',
  icon:                '',
  type:                'food',
  is_available:        true,
  is_instant:          false,
  is_popular:          false,
  sort_order:          0,
  modifier_group_ids:  [],
})

const defaultGroupForm = () => ({
  name:      '',
  is_active: true,
})

const defaultModifierForm = () => ({
  name:      '',
  price:     0,
  is_active: true,
})

// ── Reactive refs ─────────────────────────────────────────────
const editingCategory = ref(null)
const editingItem      = ref(null)
const editingGroup    = ref(null)
const editingModifier = ref(null)
const categoryForm    = ref(defaultCategoryForm())
const itemForm        = ref(defaultItemForm())
const groupForm       = ref(defaultGroupForm())
const modifierForm    = ref(defaultModifierForm())

// ── Computed ───────────────────────────────────────────────
const filteredItems = computed(() => {
  let filtered = menuItems.value

  // Category filter
  if (selectedCategory.value) {
    filtered = filtered.filter(item => item.category_id === selectedCategory.value)
  }

  // Search filter
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(item => 
      item.name.toLowerCase().includes(query) ||
      (item.description && item.description.toLowerCase().includes(query))
    )
  }

  return filtered
})

// ── Toast ───────────────────────────────────────────────────
function showToast(message, type = 'success') {
  toast.value = { show: true, message, type }
  setTimeout(() => { toast.value.show = false }, 3000)
}

const toast = ref({ show: false, message: '', type: 'success' })

// ── Categories ───────────────────────────────────────────────
async function loadCategories() {
  try {
    const { data } = await axios.get('/menu/categories')
    categories.value = data
  } catch (e) {
    showToast('Failed to load categories', 'error')
  }
}

async function saveCategory() {
  if (!categoryForm.value.name) {
    showToast('Please enter a category name', 'error'); return
  }

  try {
    if (editingCategory.value) {
      await axios.put(`/menu/categories/${editingCategory.value.id}`, categoryForm.value)
      showToast('Category updated', 'success')
    } else {
      await axios.post('/menu/categories', categoryForm.value)
      showToast('Category created', 'success')
    }
    await loadCategories()
    showCategoryModal.value = false
    categoryForm.value = defaultCategoryForm()
    editingCategory.value = null
  } catch (e) {
    showToast(e.response?.data?.message ?? 'Cannot save', 'error')
  }
}

function editCategory(cat) {
  editingCategory.value = cat
  categoryForm.value = { ...cat }
  showCategoryModal.value = true
}

function deleteCategory(cat) {
  if (!confirm(`Are you sure you want to delete "${cat.name}"?`)) return
  
  try {
    await axios.delete(`/menu/categories/${cat.id}`)
    showToast('Category deleted')
    await loadCategories()
  } catch (e) {
    showToast(e.response?.data?.message ?? 'Cannot delete', 'error')
  }
}

// ── Menu Items ───────────────────────────────────────────────
async function loadItems() {
  try {
    const { data } = await axios.get('/menu/items')
    menuItems.value = data
  } catch (e) {
    showToast('Failed to load menu items', 'error')
  }
}

async function saveItem() {
  if (!itemForm.value.category_id) {
    showToast('Please select a category', 'error'); return
  }

  if (!itemForm.value.price || itemForm.value.price <= 0) {
    showToast('Price must be greater than 0', 'error'); return
  }

  try {
    if (editingItem.value) {
      await axios.put(`/menu/items/${editingItem.value.id}`, itemForm.value)
      showToast('Item updated', 'success')
    } else {
      await axios.post('/menu/items', itemForm.value)
      showToast('Item created', 'success')
    }
    await loadItems()
    showItemModal.value = false
    itemForm.value = defaultItemForm()
    editingItem.value = null
  } catch (e) {
    showToast(e.response?.data?.message ?? 'Cannot save', 'error')
  }
}

function editItem(item) {
  editingItem.value = item
  itemForm.value = {
    name:               item.name,
    description:        item.description ?? '',
    price:              parseFloat(item.price),
    cost_price:         parseFloat(item.cost_price ?? 0),
    category_id:        item.category_id,
    icon:               item.icon ?? '',
    type:               item.type ?? 'food',
    is_available:        item.is_available !== false,
    is_instant:          item.is_instant || false,
    is_popular:          item.is_popular || false,
    sort_order:          item.sort_order || 0,
    modifier_group_ids:  item.modifier_group_ids || [],
  }
  showItemModal.value = true
}

function deleteItem(item) {
  if (!confirm(`Are you sure you want to delete "${item.name}"?`)) return
  
  try {
    await axios.delete(`/menu/items/${item.id}`)
    showToast('Item deleted')
    await loadItems()
  } catch (e) {
    showToast(e.response?.data?.message ?? 'Cannot delete', 'error')
  }
}

// ── Modifier Groups ───────────────────────────────────────
async function loadModifierGroups() {
  try {
    const { data } = await axios.get('/modifier-groups')
    modifierGroups.value = data
  } catch (e) {
    showToast('Failed to load modifier groups', 'error')
  }
}

async function saveGroup() {
  if (!groupForm.value.name) {
    showToast('Please enter a group name', 'error'); return
  }

  try {
    if (editingGroup.value) {
      await axios.put(`/modifier-groups/${editingGroup.value.id}`, groupForm.value)
      showToast('Group updated', 'success')
    } else {
      await axios.post('/modifier-groups', groupForm.value)
      showToast('Group created', 'success')
    }
    await loadModifierGroups()
    showGroupModal.value = false
    groupForm.value = defaultGroupForm()
    editingGroup.value = null
  } catch (e) {
    showToast(e.response?.data?.message ?? 'Cannot save', 'error')
  }
}

function openGroupModal(group) {
  editingGroup.value = group
  groupForm.value = group ? {
    name:      group.name,
    is_active: group.is_active !== false,
  } : defaultGroupForm()
  showGroupModal.value = true
}

function deleteModifier(mod) {
  if (!confirm(`Are you sure you want to delete "${mod.name}"?`)) return
  
  try {
    await axios.delete(`/modifiers/${mod.id}`)
    showToast('Option deleted')
    await loadModifierGroups()
  } catch (e) {
    showToast(e.response?.data?.message ?? 'Cannot delete', 'error')
  }
}

// ── Pricing Tab Functions ─────────────────────────────
async function selectItemForPricing(item) {
  selectedItemForPricing.value = item
  
  // Load all modifiers for this item
  try {
    const { data } = await axios.get(`/menu/items/${item.id}/modifiers`)
    allModifiers.value = data
    
    // Load existing pricing for this item
    const { data: pricing } = await axios.get(`/menu/items/${item.id}/modifier-pricing`)
    modifierPricing.value = pricing.reduce((acc, p) => {
      acc[p.modifier_id] = {
        pricing_type: p.pricing_type || 'absolute',
        custom_price: p.custom_price || 0,
        increment_price: p.increment_price || 0,
      }
      return acc
    }, {})
  } catch (e) {
    showToast('Failed to load modifiers', 'error')
  }
}

async function saveModifierPricing() {
  if (!selectedItemForPricing.value) return
  
  try {
    // Save pricing for each modifier
    const promises = Object.entries(modifierPricing.value).map(([modifierId, pricing]) => {
      return axios.post(`/menu/items/${selectedItemForPricing.value.id}/modifier-pricing`, {
        modifier_id: parseInt(modifierId),
        ...pricing
      })
    })
    
    await Promise.all(promises)
    showToast('Modifier pricing saved successfully', 'success')
  } catch (e) {
    showToast('Failed to save modifier pricing', 'error')
  }
}

// ── Init ──────────────────────────────────────────────────
onMounted(loadAll)

async function loadAll() {
  await Promise.all([
    loadCategories(),
    loadItems(),
    loadModifierGroups()
  ])
}
</script>
