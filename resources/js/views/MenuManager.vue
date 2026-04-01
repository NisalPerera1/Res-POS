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

    <!-- ══════════ CATEGORIES TAB ══════════ -->
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
            <div>
              <div style="font-size:14px; font-weight:600; color:#F1F5F9;">{{ cat.name }}</div>
              <div style="font-size:11px; color:#64748B; margin-top:1px;">
                {{ cat.menu_items_count ?? 0 }} items
              </div>
            </div>
          </div>
          <div style="display:flex; gap:6px;">
            <button @click="openCategoryModal(cat)"
              style="padding:5px 12px; background:rgba(59,130,246,0.12); color:#3B82F6;
                     border:1px solid rgba(59,130,246,0.3); border-radius:6px;
                     font-size:11px; cursor:pointer; font-weight:600;">Edit</button>
            <button @click="deleteCategory(cat)"
              style="padding:5px 12px; background:rgba(239,68,68,0.1); color:#EF4444;
                     border:1px solid rgba(239,68,68,0.2); border-radius:6px;
                     font-size:11px; cursor:pointer; font-weight:600;">Delete</button>
          </div>
        </div>

        <div v-if="categories.length === 0"
          style="padding:60px; text-align:center; color:#64748B;">
          <div style="font-size:40px; opacity:0.3; margin-bottom:12px;">📂</div>
          <div style="font-size:14px;">No categories yet</div>
        </div>
      </div>
    </div>

    <!-- ══════════ MENU ITEMS TAB ══════════ -->
    <div v-if="activeTab === 'items'"
      style="flex:1; display:flex; flex-direction:column; overflow:hidden;">

      <!-- Filter bar -->
      <div style="padding:10px 16px; border-bottom:1px solid #252B38;
                  display:flex; gap:8px; align-items:center; flex-shrink:0;">
        <select v-model="selectedCategory"
          style="padding:6px 10px; background:#1A1E28; border:1px solid #252B38;
                 border-radius:7px; color:#F1F5F9; font-size:13px; outline:none;">
          <option value="">All Categories</option>
          <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
        </select>
        <input v-model="searchQuery" placeholder="Search items..."
          style="flex:1; padding:6px 10px; background:#1A1E28; border:1px solid #252B38;
                 border-radius:7px; color:#F1F5F9; font-size:13px; outline:none; max-width:220px;"
          @focus="e => e.target.style.borderColor='#F59E0B'"
          @blur="e => e.target.style.borderColor='#252B38'"
        />
        <button @click="openItemModal(null)"
          style="padding:7px 14px; background:#10B981; color:#fff; border:none;
                 border-radius:7px; font-size:12px; font-weight:600; cursor:pointer; margin-left:auto;">
          + Add Item
        </button>
      </div>

      <!-- Items grid -->
      <div style="flex:1; overflow-y:auto; padding:14px;">
        <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(290px,1fr)); gap:10px;">
          <div v-for="item in filteredItems" :key="item.id"
            style="background:#1A1E28; border:1px solid #252B38; border-radius:10px; padding:14px;">

            <div style="display:flex; justify-content:space-between; align-items:start; margin-bottom:10px;">
              <div style="flex:1; min-width:0;">
                <div v-if="item.image" style="width:110px; height:90px; border-radius:12px; margin-bottom:10px; background-size:cover; background-position:center; background-repeat:no-repeat; background-color:#1A1E28; align-self:center;" :style="{ backgroundImage: 'url(/storage/menu_items/' + item.image + ')' }"></div>
                <div v-else style="width:110px; height:90px; border-radius:12px; margin-bottom:10px; background-color:#2D3748; display:flex; align-items:center; justify-content:center; font-size:36px; color:#64748B;">🍽️</div>
                <div style="font-size:14px; font-weight:700; color:#F1F5F9; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ item.name }}</div>
                <div style="font-size:15px; font-weight:700; color:#F59E0B; margin-top:2px;">Rs.{{ parseFloat(item.price).toFixed(2) }}</div>
              </div>
            </div>

            <div style="font-size:11px; color:#64748B; margin-bottom:10px; line-height:1.4;
                        overflow:hidden; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical;">
              {{ item.description || 'No description' }}
            </div>

            <div style="display:flex; flex-wrap:wrap; gap:4px; margin-bottom:10px;">
              <span style="font-size:10px; font-weight:600; padding:2px 7px; border-radius:4px;"
                :style="{
                  background: item.is_available ? 'rgba(16,185,129,0.1)' : 'rgba(239,68,68,0.1)',
                  color:      item.is_available ? '#10B981' : '#EF4444',
                }">
                {{ item.is_available ? '● Available' : '● Unavailable' }}
              </span>
              <span v-if="item.is_popular"
                style="font-size:10px; font-weight:600; padding:2px 7px; border-radius:4px;
                       background:rgba(245,158,11,0.1); color:#F59E0B;">★ Popular</span>
              <span v-if="item.is_instant"
                style="font-size:10px; font-weight:600; padding:2px 7px; border-radius:4px;
                       background:rgba(16,185,129,0.1); color:#10B981;">⚡ Instant</span>
              <span v-if="item.modifier_groups?.length > 0"
                style="font-size:10px; font-weight:600; padding:2px 7px; border-radius:4px;
                       background:rgba(139,92,246,0.1); color:#8B5CF6;">
                ⚙️ {{ item.modifier_groups.length }} modifier{{ item.modifier_groups.length > 1 ? 's' : '' }}
              </span>
            </div>

            <div style="display:flex; flex-wrap:wrap; gap:3px; margin-bottom:10px;"
              v-if="item.modifier_groups?.length > 0">
              <span v-for="g in item.modifier_groups" :key="g.id"
                style="font-size:9px; padding:2px 6px; border-radius:4px; background:#252B38; color:#64748B;">
                {{ g.name }}{{ g.is_required ? ' *' : '' }}
              </span>
            </div>

            <div style="display:flex; gap:6px;">
              <button @click="openItemModal(item)"
                style="flex:2; padding:7px; background:rgba(59,130,246,0.12); color:#3B82F6;
                       border:1px solid rgba(59,130,246,0.3); border-radius:6px;
                       font-size:11px; font-weight:600; cursor:pointer;">✏️ Edit</button>
              <button @click="toggleAvailability(item)"
                style="flex:1; padding:7px; border-radius:6px; font-size:11px; font-weight:600;
                       cursor:pointer; border:1px solid;"
                :style="{
                  background:  item.is_available ? 'rgba(239,68,68,0.1)' : 'rgba(16,185,129,0.1)',
                  color:       item.is_available ? '#EF4444' : '#10B981',
                  borderColor: item.is_available ? 'rgba(239,68,68,0.3)' : 'rgba(16,185,129,0.3)',
                }">{{ item.is_available ? 'Off' : 'On' }}</button>
              <button @click="deleteItem(item)"
                style="flex:1; padding:7px; background:rgba(239,68,68,0.1); color:#EF4444;
                       border:1px solid rgba(239,68,68,0.2); border-radius:6px;
                       font-size:11px; font-weight:600; cursor:pointer;">🗑️</button>
            </div>
          </div>

          <div v-if="filteredItems.length === 0"
            style="grid-column:1/-1; padding:60px; text-align:center; color:#64748B;">
            <div style="font-size:40px; opacity:0.3; margin-bottom:12px;">🍽️</div>
            <div style="font-size:14px;">No items found</div>
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
                   border-radius:6px; font-size:11px; font-weight:600; cursor:pointer;">+ Add</button>
        </div>

        <div style="flex:1; overflow-y:auto; padding:8px;">
          <div v-for="group in modifierGroups" :key="group.id"
            @click="selectedGroup = group"
            style="padding:10px 12px; border-radius:8px; cursor:pointer;
                   margin-bottom:4px; transition:all 0.15s; border:1px solid;"
            :style="{
              background:  selectedGroup?.id === group.id ? 'rgba(245,158,11,0.1)' : '#1A1E28',
              borderColor: selectedGroup?.id === group.id ? '#F59E0B' : '#252B38',
            }"
          >
            <div style="display:flex; justify-content:space-between; align-items:center;">
              <div style="font-size:13px; font-weight:600; color:#F1F5F9;">{{ group.name }}</div>
              <div style="font-size:10px; padding:1px 6px; border-radius:4px;"
                :style="{
                  background: group.is_required ? 'rgba(239,68,68,0.1)' : 'rgba(100,116,139,0.1)',
                  color:      group.is_required ? '#EF4444' : '#64748B',
                }">{{ group.is_required ? 'Required' : 'Optional' }}</div>
            </div>
            <div style="font-size:11px; color:#64748B; margin-top:3px;">
              {{ group.modifiers?.length ?? 0 }} options · Pick {{ group.min_select }}–{{ group.max_select }}
            </div>
          </div>

          <div v-if="modifierGroups.length === 0"
            style="text-align:center; padding:40px 16px; color:#64748B; font-size:13px;">No groups yet</div>
        </div>
      </div>

      <!-- Right: group detail -->
      <div style="flex:1; display:flex; flex-direction:column; overflow:hidden;">
        <div v-if="!selectedGroup"
          style="flex:1; display:flex; flex-direction:column; align-items:center;
                 justify-content:center; color:#64748B; gap:8px;">
          <div style="font-size:40px; opacity:0.2;">⚙️</div>
          <div style="font-size:13px;">Select a group to edit its options</div>
        </div>

        <template v-else>
          <div style="padding:12px 16px; border-bottom:1px solid #252B38; flex-shrink:0;">
            <div style="display:flex; align-items:center; justify-content:space-between;">
              <div>
                <div style="font-size:16px; font-weight:700; color:#F1F5F9;">{{ selectedGroup.name }}</div>
                <div style="font-size:11px; color:#64748B; margin-top:2px;">
                  {{ selectedGroup.is_required ? 'Required' : 'Optional' }} ·
                  Pick {{ selectedGroup.min_select }}–{{ selectedGroup.max_select }}
                </div>
              </div>
              <div style="display:flex; gap:6px;">
                <button @click="openGroupModal(selectedGroup)"
                  style="padding:6px 12px; background:rgba(59,130,246,0.12); color:#3B82F6;
                         border:1px solid rgba(59,130,246,0.3); border-radius:6px;
                         font-size:11px; font-weight:600; cursor:pointer;">Edit Group</button>
                <button @click="deleteGroup(selectedGroup)"
                  style="padding:6px 12px; background:rgba(239,68,68,0.1); color:#EF4444;
                         border:1px solid rgba(239,68,68,0.2); border-radius:6px;
                         font-size:11px; font-weight:600; cursor:pointer;">Delete Group</button>
              </div>
            </div>
          </div>

          <div style="flex:1; overflow-y:auto; padding:12px 16px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
              <div style="font-size:13px; font-weight:600; color:#F1F5F9;">
                Options ({{ selectedGroup.modifiers?.length ?? 0 }})
              </div>
              <button @click="openModifierModal(null)"
                style="padding:5px 12px; background:#10B981; color:#fff; border:none;
                       border-radius:6px; font-size:11px; font-weight:600; cursor:pointer;">+ Add Option</button>
            </div>

            <div style="display:flex; flex-direction:column; gap:6px;">
              <div v-for="mod in selectedGroup.modifiers" :key="mod.id"
                style="display:flex; align-items:center; justify-content:space-between;
                       padding:10px 12px; background:#1A1E28; border:1px solid #252B38; border-radius:8px;">
                <div>
                  <div style="font-size:13px; font-weight:500; color:#F1F5F9;">{{ mod.name }}</div>
                  <div style="font-size:11px; margin-top:1px;"
                    :style="{ color: mod.price > 0 ? '#F59E0B' : '#64748B' }">
                    {{ mod.price > 0 ? '+Rs.' + parseFloat(mod.price).toFixed(2) : 'Included' }}
                  </div>
                </div>
                <div style="display:flex; gap:5px; align-items:center;">
                  <div style="font-size:10px; padding:2px 6px; border-radius:4px;"
                    :style="{
                      background: mod.is_active ? 'rgba(16,185,129,0.1)' : 'rgba(239,68,68,0.1)',
                      color:      mod.is_active ? '#10B981' : '#EF4444',
                    }">{{ mod.is_active ? 'Active' : 'Inactive' }}</div>
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

              <div v-if="!selectedGroup.modifiers?.length"
                style="padding:30px; text-align:center; color:#64748B; font-size:13px;">
                No options yet. Add some above.
              </div>
            </div>
          </div>
        </template>
      </div>
    </div>

    <!-- ══════════ MODIFIER PRICING TAB ══════════ -->
    <div v-if="activeTab === 'pricing'"
      style="flex:1; display:flex; overflow:hidden;">

      <!-- Left: item selector -->
      <div style="width:280px; border-right:1px solid #252B38; display:flex;
                  flex-direction:column; flex-shrink:0;">
        <div style="padding:10px 12px; border-bottom:1px solid #252B38;">
          <input v-model="pricingItemSearch" placeholder="Search items..."
            style="width:100%; padding:7px 10px; background:#12151C; border:1px solid #252B38;
                   border-radius:7px; color:#F1F5F9; font-size:13px; outline:none;"
            @focus="e => e.target.style.borderColor='#F59E0B'"
            @blur="e => e.target.style.borderColor='#252B38'" />
        </div>

        <div style="flex:1; overflow-y:auto; padding:8px;">
          <div style="font-size:10px; color:#64748B; text-transform:uppercase;
                      letter-spacing:0.06em; padding:4px 6px 8px;">Items with modifiers</div>

          <div v-for="item in pricingEligibleItems" :key="item.id"
            @click="selectItemForPricing(item)"
            style="padding:10px 12px; border-radius:8px; cursor:pointer;
                   margin-bottom:4px; border:1px solid; transition:all 0.15s;"
            :style="{
              background:  selectedItemForPricing?.id === item.id ? 'rgba(245,158,11,0.1)' : '#1A1E28',
              borderColor: selectedItemForPricing?.id === item.id ? '#F59E0B' : '#252B38',
            }"
          >
            <div style="display:flex; align-items:center; gap:8px;">
              <span style="font-size:20px;">{{ item.icon ?? '🍽️' }}</span>
              <div style="flex:1; min-width:0;">
                <div style="font-size:13px; font-weight:600; color:#F1F5F9;
                            white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ item.name }}</div>
                <div style="font-size:11px; color:#64748B; margin-top:1px;">
                  Base: Rs.{{ parseFloat(item.price).toFixed(2) }} ·
                  {{ item.modifier_groups?.length ?? 0 }} group{{ item.modifier_groups?.length === 1 ? '' : 's' }}
                </div>
              </div>
            </div>
          </div>

          <div v-if="pricingEligibleItems.length === 0"
            style="padding:30px 16px; text-align:center; color:#64748B; font-size:12px;">
            No items with modifier groups.<br>
            <span style="opacity:0.6;">Attach modifier groups to items first.</span>
          </div>
        </div>
      </div>

      <!-- Right: pricing matrix -->
      <div style="flex:1; display:flex; flex-direction:column; overflow:hidden;">

        <div v-if="!selectedItemForPricing"
          style="flex:1; display:flex; flex-direction:column; align-items:center;
                 justify-content:center; color:#64748B; gap:10px;">
          <div style="font-size:48px; opacity:0.15;">💰</div>
          <div style="font-size:14px; font-weight:600;">Select an item to configure modifier pricing</div>
          <div style="font-size:12px; opacity:0.6;">Set per-item increment or absolute prices for each modifier option</div>
        </div>

        <template v-else>
          <!-- Header -->
          <div style="padding:14px 18px; border-bottom:1px solid #252B38; flex-shrink:0;
                      display:flex; align-items:center; justify-content:space-between;">
            <div style="display:flex; align-items:center; gap:10px;">
              <span style="font-size:24px;">{{ selectedItemForPricing.icon ?? '🍽️' }}</span>
              <div>
                <div style="font-size:16px; font-weight:700; color:#F1F5F9;">{{ selectedItemForPricing.name }}</div>
                <div style="font-size:12px; color:#64748B; margin-top:1px;">
                  Base price: <span style="color:#F59E0B; font-weight:700;">
                    Rs.{{ parseFloat(selectedItemForPricing.price).toFixed(2) }}
                  </span>
                </div>
              </div>
            </div>
            <button @click="saveModifierPricing" :disabled="pricingSaving"
              style="padding:9px 20px; background:#F59E0B; color:#000; border:none;
                     border-radius:8px; cursor:pointer; font-weight:700; font-size:13px;
                     opacity:1; transition:opacity 0.15s;"
              :style="{ opacity: pricingSaving ? 0.6 : 1 }">
              {{ pricingSaving ? '⏳ Saving…' : '💾 Save Pricing' }}
            </button>
          </div>

          <!-- Legend -->
          <div style="padding:8px 18px; background:rgba(245,158,11,0.05);
                      border-bottom:1px solid #252B38; flex-shrink:0;
                      display:flex; gap:20px; align-items:center;">
            <div style="display:flex; align-items:center; gap:6px;">
              <div style="width:10px; height:10px; border-radius:2px; background:#8B5CF6;"></div>
              <span style="font-size:11px; color:#94A3B8;">
                <b style="color:#F1F5F9;">Increment</b> — adds to base price
              </span>
            </div>
            <div style="display:flex; align-items:center; gap:6px;">
              <div style="width:10px; height:10px; border-radius:2px; background:#3B82F6;"></div>
              <span style="font-size:11px; color:#94A3B8;">
                <b style="color:#F1F5F9;">Absolute</b> — fixed add-on regardless of base
              </span>
            </div>
          </div>

          <!-- Pricing rows -->
          <div style="flex:1; overflow-y:auto; padding:14px 18px;">
            <div v-if="pricingLoading"
              style="padding:40px; text-align:center; color:#64748B; font-size:13px;">
              Loading modifier pricing…
            </div>

            <template v-else>
              <div v-for="group in selectedItemForPricing.modifier_groups" :key="group.id"
                style="margin-bottom:20px;">

                <!-- Group label -->
                <div style="display:flex; align-items:center; gap:8px; margin-bottom:8px;">
                  <div style="font-size:12px; font-weight:700; color:#F1F5F9;
                              text-transform:uppercase; letter-spacing:0.06em;">{{ group.name }}</div>
                  <div style="font-size:10px; padding:2px 7px; border-radius:4px;"
                    :style="{
                      background: group.is_required ? 'rgba(239,68,68,0.1)' : 'rgba(100,116,139,0.1)',
                      color:      group.is_required ? '#EF4444' : '#64748B',
                    }">{{ group.is_required ? 'Required' : 'Optional' }}</div>
                  <div style="flex:1; height:1px; background:#252B38;"></div>
                </div>

                <!-- Modifier rows -->
                <div style="display:flex; flex-direction:column; gap:6px;">
                  <div v-for="mod in group.modifiers" :key="mod.id"
                    style="display:grid; grid-template-columns:1fr 160px 160px 120px;
                           gap:8px; align-items:center;
                           padding:10px 12px; background:#1A1E28; border:1px solid #252B38;
                           border-radius:8px;">

                    <!-- Name + default -->
                    <div>
                      <div style="font-size:13px; font-weight:500; color:#F1F5F9;">{{ mod.name }}</div>
                      <div style="font-size:11px; color:#64748B; margin-top:1px;">
                        Default: Rs.{{ parseFloat(mod.price ?? 0).toFixed(2) }}
                      </div>
                    </div>

                    <!-- Type toggle -->
                    <div style="display:flex; border-radius:7px; overflow:hidden; border:1px solid #252B38;">
                      <button @click="setPricingType(mod.id, 'increment')"
                        style="flex:1; padding:6px 4px; font-size:10px; font-weight:700;
                               border:none; cursor:pointer; transition:all 0.15s;"
                        :style="{
                          background: getModifierPricing(mod.id).pricing_type === 'increment' ? '#8B5CF6' : '#12151C',
                          color:      getModifierPricing(mod.id).pricing_type === 'increment' ? '#fff' : '#64748B',
                        }">INC</button>
                      <button @click="setPricingType(mod.id, 'absolute')"
                        style="flex:1; padding:6px 4px; font-size:10px; font-weight:700;
                               border:none; cursor:pointer; transition:all 0.15s;"
                        :style="{
                          background: getModifierPricing(mod.id).pricing_type === 'absolute' ? '#3B82F6' : '#12151C',
                          color:      getModifierPricing(mod.id).pricing_type === 'absolute' ? '#fff' : '#64748B',
                        }">ABS</button>
                    </div>

                    <!-- Price input -->
                    <div style="position:relative;">
                      <span style="position:absolute; left:9px; top:50%; transform:translateY(-50%);
                                   font-size:10px; color:#64748B; pointer-events:none;">Rs.</span>
                      <input
                        :value="getPricingValue(mod.id)"
                        @input="e => setPricingValue(mod.id, e.target.value)"
                        type="number" min="0" step="1" placeholder="0"
                        style="width:100%; padding:7px 8px 7px 30px; background:#12151C;
                               border:1px solid #252B38; border-radius:7px; color:#F59E0B;
                               font-size:14px; font-weight:700; outline:none;"
                        @focus="e => e.target.style.borderColor='#F59E0B'"
                        @blur="e => e.target.style.borderColor='#252B38'"
                      />
                    </div>

                    <!-- Final price preview -->
                    <div style="text-align:right;">
                      <div style="font-size:10px; color:#64748B; margin-bottom:2px;">Final price</div>
                      <div style="font-size:14px; font-weight:700; color:#10B981;">
                        Rs.{{ computePreviewPrice(mod.id) }}
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div v-if="!selectedItemForPricing.modifier_groups?.length"
                style="padding:40px; text-align:center; color:#64748B; font-size:13px;">
                This item has no modifier groups attached.
              </div>
            </template>
          </div>
        </template>
      </div>
    </div>

    <!-- ════════════════ MODALS ════════════════ -->

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
            <div>
              <label style="font-size:11px; color:#64748B; display:block; margin-bottom:5px;
                             text-transform:uppercase; letter-spacing:0.05em;">Name *</label>
              <input v-model="categoryForm.name" placeholder="e.g. Appetizers"
                style="width:100%; padding:9px 12px; background:#12151C; border:1px solid #252B38;
                       border-radius:8px; color:#F1F5F9; font-size:14px; outline:none;"
                @focus="e => e.target.style.borderColor='#F59E0B'"
                @blur="e => e.target.style.borderColor='#252B38'" />
            </div>
            <div>
              <label style="font-size:11px; color:#64748B; display:block; margin-bottom:5px;
                             text-transform:uppercase; letter-spacing:0.05em;">Icon (emoji)</label>
              <input v-model="categoryForm.icon" placeholder="🍔"
                style="width:100%; padding:9px 12px; background:#12151C; border:1px solid #252B38;
                       border-radius:8px; color:#F1F5F9; font-size:20px; outline:none;"
                @focus="e => e.target.style.borderColor='#F59E0B'"
                @blur="e => e.target.style.borderColor='#252B38'" />
            </div>
          </div>
          <div style="display:flex; gap:8px; margin-top:18px; justify-content:flex-end;">
            <button @click="showCategoryModal = false"
              style="padding:9px 16px; background:transparent; color:#64748B;
                     border:1px solid #252B38; border-radius:8px; cursor:pointer; font-size:13px;">Cancel</button>
            <button @click="saveCategory"
              style="padding:9px 20px; background:#F59E0B; color:#000; border:none;
                     border-radius:8px; cursor:pointer; font-weight:700; font-size:13px;">
              {{ editingCategory ? 'Update' : 'Create' }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Menu Item Modal -->
    <Teleport to="body">
      <div v-if="showItemModal"
        style="position:fixed; inset:0; background:rgba(0,0,0,0.65);
               display:flex; align-items:center; justify-content:center; z-index:100; padding:16px;"
        @click.self="showItemModal = false">
        <div style="background:#1A1E28; border:1px solid #252B38; border-radius:14px;
                    padding:22px; width:520px; max-height:90vh; overflow-y:auto;">
          <div style="font-size:16px; font-weight:700; color:#F1F5F9; margin-bottom:18px;">
            {{ editingItem ? 'Edit Menu Item' : 'New Menu Item' }}
          </div>

          <div style="display:flex; flex-direction:column; gap:14px;">

            <!-- Name + Image -->
            <div style="display:flex; gap:12px;">
              <div style="flex:1;">
                <label style="font-size:11px; color:#64748B; display:block; margin-bottom:5px;
                               text-transform:uppercase; letter-spacing:0.05em;">Name *</label>
                <input v-model="itemForm.name" placeholder="Beef Burger"
                  style="width:100%; padding:9px 12px; background:#12151C; border:1px solid #252B38;
                         border-radius:8px; color:#F1F5F9; font-size:14px; outline:none;"
                  @focus="e => e.target.style.borderColor='#F59E0B'"
                  @blur="e => e.target.style.borderColor='#252B38'" />
              </div>
              <div style="width:120px;">
                <label style="font-size:11px; color:#64748B; display:block; margin-bottom:5px;
                               text-transform:uppercase; letter-spacing:0.05em;">Image</label>
                <input type="file" @change="handleImageUpload" accept="image/*"
                  style="width:100%; padding:6px 9px; background:#12151C; border:1px solid #252B38;
                         border-radius:8px; color:#F1F5F9; font-size:12px; outline:none;"
                  @focus="e => e.target.style.borderColor='#F59E0B'"
                  @blur="e => e.target.style.borderColor='#252B38'" />
                <div v-if="itemForm.image" style="margin-top:5px; text-align:center;">
                  <img :src="'/storage/menu_items/' + itemForm.image" 
                       style="width:110px; height:90px; border-radius:12px; object-fit:cover;" />
                </div>
              </div>
            </div>

            <!-- Price + Category -->
            <div style="display:flex; gap:12px;">
              <div style="flex:1;">
                <label style="font-size:11px; color:#64748B; display:block; margin-bottom:5px;
                               text-transform:uppercase; letter-spacing:0.05em;">Price (Rs.) *</label>
                <input v-model.number="itemForm.price" type="number" step="0.01" placeholder="0.00"
                  style="width:100%; padding:9px 12px; background:#12151C; border:1px solid #252B38;
                         border-radius:8px; color:#F59E0B; font-size:15px; font-weight:700; outline:none;"
                  @focus="e => e.target.style.borderColor='#F59E0B'"
                  @blur="e => e.target.style.borderColor='#252B38'" />
              </div>
              <div style="flex:1;">
                <label style="font-size:11px; color:#64748B; display:block; margin-bottom:5px;
                               text-transform:uppercase; letter-spacing:0.05em;">Category *</label>
                <select v-model="itemForm.category_id"
                  style="width:100%; padding:9px 12px; background:#12151C; border:1px solid #252B38;
                         border-radius:8px; color:#F1F5F9; font-size:14px; outline:none;">
                  <option value="">Select category</option>
                  <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                </select>
              </div>
            </div>

            <!-- Description -->
            <div>
              <label style="font-size:11px; color:#64748B; display:block; margin-bottom:5px;
                             text-transform:uppercase; letter-spacing:0.05em;">Description</label>
              <textarea v-model="itemForm.description" placeholder="Item description..." rows="2"
                style="width:100%; padding:9px 12px; background:#12151C; border:1px solid #252B38;
                       border-radius:8px; color:#F1F5F9; font-size:14px; resize:none;
                       font-family:inherit; outline:none;"
                @focus="e => e.target.style.borderColor='#F59E0B'"
                @blur="e => e.target.style.borderColor='#252B38'"></textarea>
            </div>

            <!-- Prep time + Type -->
            <div style="display:flex; gap:12px;">
              <div style="flex:1;">
                <label style="font-size:11px; color:#64748B; display:block; margin-bottom:5px;
                               text-transform:uppercase; letter-spacing:0.05em;">Prep Time (min)</label>
                <input v-model.number="itemForm.prep_time" type="number" placeholder="15"
                  style="width:100%; padding:9px 12px; background:#12151C; border:1px solid #252B38;
                         border-radius:8px; color:#F1F5F9; font-size:14px; outline:none;"
                  @focus="e => e.target.style.borderColor='#F59E0B'"
                  @blur="e => e.target.style.borderColor='#252B38'" />
              </div>
              <div style="flex:1;">
                <label style="font-size:11px; color:#64748B; display:block; margin-bottom:5px;
                               text-transform:uppercase; letter-spacing:0.05em;">Type</label>
                <select v-model="itemForm.type"
                  style="width:100%; padding:9px 12px; background:#12151C; border:1px solid #252B38;
                         border-radius:8px; color:#F1F5F9; font-size:14px; outline:none;">
                  <option value="food">Food</option>
                  <option value="beverage">Beverage</option>
                  <option value="dessert">Dessert</option>
                  <option value="other">Other</option>
                </select>
              </div>
            </div>

            <!-- Toggles -->
            <div style="display:flex; flex-wrap:wrap; gap:16px; padding:10px 0;">
              <label v-for="toggle in toggleFields" :key="toggle.key"
                style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                <div @click="itemForm[toggle.key] = !itemForm[toggle.key]"
                  style="width:40px; height:22px; border-radius:11px; cursor:pointer;
                         transition:background 0.2s; position:relative; flex-shrink:0;"
                  :style="{ background: itemForm[toggle.key] ? '#F59E0B' : '#252B38' }">
                  <div style="width:18px; height:18px; border-radius:50%; background:#fff;
                               position:absolute; top:2px; transition:left 0.2s;"
                    :style="{ left: itemForm[toggle.key] ? '20px' : '2px' }"></div>
                </div>
                <span style="font-size:13px; color:#F1F5F9;">{{ toggle.label }}</span>
              </label>
            </div>

            <!-- Modifier groups section -->
            <div style="border-top:1px solid #252B38; padding-top:14px;">
              <div style="font-size:12px; font-weight:700; color:#F1F5F9; margin-bottom:10px;
                           text-transform:uppercase; letter-spacing:0.06em; display:flex;
                           align-items:center; gap:6px;">
                ⚙️ Modifier Groups
                <span style="font-size:10px; color:#64748B; font-weight:400; text-transform:none;">
                  (optional — for size, spice, add-ons etc.)
                </span>
              </div>

              <div style="display:flex; flex-direction:column; gap:6px;">
                <div v-for="group in modifierGroups" :key="group.id"
                  @click="toggleGroupOnItem(group.id)"
                  style="display:flex; align-items:center; justify-content:space-between;
                         padding:10px 12px; border-radius:8px; cursor:pointer;
                         border:1.5px solid; transition:all 0.15s;"
                  :style="{
                    background:  itemForm.modifier_group_ids.includes(group.id) ? 'rgba(245,158,11,0.08)' : '#12151C',
                    borderColor: itemForm.modifier_group_ids.includes(group.id) ? '#F59E0B' : '#252B38',
                  }">
                  <div style="flex:1;">
                    <div style="font-size:13px; font-weight:500;"
                      :style="{ color: itemForm.modifier_group_ids.includes(group.id) ? '#F1F5F9' : '#94A3B8' }">
                      {{ group.name }}
                      <span style="font-size:10px; margin-left:6px; opacity:0.7;">
                        {{ group.is_required ? '(required)' : '(optional)' }} · {{ group.modifiers?.length ?? 0 }} options
                      </span>
                    </div>
                    <div style="font-size:10px; color:#64748B; margin-top:2px;">
                      {{ group.modifiers?.slice(0,4).map(m => m.name).join(', ') }}{{ (group.modifiers?.length ?? 0) > 4 ? '...' : '' }}
                    </div>
                  </div>
                  <div style="width:22px; height:22px; border-radius:6px; flex-shrink:0;
                               display:flex; align-items:center; justify-content:center;
                               font-size:12px; font-weight:700; margin-left:10px;"
                    :style="{
                      background: itemForm.modifier_group_ids.includes(group.id) ? '#F59E0B' : '#252B38',
                      color:      itemForm.modifier_group_ids.includes(group.id) ? '#000' : '#64748B',
                    }">{{ itemForm.modifier_group_ids.includes(group.id) ? '✓' : '' }}</div>
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
            </div>
          </div>

          <div style="display:flex; gap:8px; margin-top:20px; justify-content:flex-end;">
            <button @click="showItemModal = false"
              style="padding:10px 18px; background:transparent; color:#64748B;
                     border:1px solid #252B38; border-radius:8px; cursor:pointer; font-size:13px;">Cancel</button>
            <button @click="saveItem"
              style="padding:10px 24px; background:#F59E0B; color:#000; border:none;
                     border-radius:8px; cursor:pointer; font-weight:700; font-size:13px;">
              {{ editingItem ? 'Update Item' : 'Create Item' }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Modifier Group Modal -->
    <Teleport to="body">
      <div v-if="showGroupModal"
        style="position:fixed; inset:0; background:rgba(0,0,0,0.65);
               display:flex; align-items:center; justify-content:center; z-index:110;"
        @click.self="showGroupModal = false">
        <div style="background:#1A1E28; border:1px solid #252B38; border-radius:14px;
                    padding:22px; width:420px;">
          <div style="font-size:16px; font-weight:700; color:#F1F5F9; margin-bottom:18px;">
            {{ editingGroup ? 'Edit Modifier Group' : 'New Modifier Group' }}
          </div>
          <div style="display:flex; flex-direction:column; gap:12px;">
            <div>
              <label style="font-size:11px; color:#64748B; display:block; margin-bottom:5px;
                             text-transform:uppercase; letter-spacing:0.05em;">Group Name *</label>
              <input v-model="groupForm.name" placeholder="e.g. Size, Spice Level, Add-ons"
                style="width:100%; padding:9px 12px; background:#12151C; border:1px solid #252B38;
                       border-radius:8px; color:#F1F5F9; font-size:14px; outline:none;"
                @focus="e => e.target.style.borderColor='#F59E0B'"
                @blur="e => e.target.style.borderColor='#252B38'" />
            </div>
            <div style="display:flex; gap:12px;">
              <div style="flex:1;">
                <label style="font-size:11px; color:#64748B; display:block; margin-bottom:5px;
                               text-transform:uppercase; letter-spacing:0.05em;">Min Select</label>
                <input v-model.number="groupForm.min_select" type="number" min="0"
                  style="width:100%; padding:9px 12px; background:#12151C; border:1px solid #252B38;
                         border-radius:8px; color:#F1F5F9; font-size:14px; outline:none;"
                  @focus="e => e.target.style.borderColor='#F59E0B'"
                  @blur="e => e.target.style.borderColor='#252B38'" />
              </div>
              <div style="flex:1;">
                <label style="font-size:11px; color:#64748B; display:block; margin-bottom:5px;
                               text-transform:uppercase; letter-spacing:0.05em;">Max Select</label>
                <input v-model.number="groupForm.max_select" type="number" min="1"
                  style="width:100%; padding:9px 12px; background:#12151C; border:1px solid #252B38;
                         border-radius:8px; color:#F1F5F9; font-size:14px; outline:none;"
                  @focus="e => e.target.style.borderColor='#F59E0B'"
                  @blur="e => e.target.style.borderColor='#252B38'" />
              </div>
            </div>
            <label style="display:flex; align-items:center; gap:10px; cursor:pointer; padding:4px 0;">
              <div @click="groupForm.is_required = !groupForm.is_required"
                style="width:40px; height:22px; border-radius:11px; cursor:pointer;
                       transition:background 0.2s; position:relative; flex-shrink:0;"
                :style="{ background: groupForm.is_required ? '#EF4444' : '#252B38' }">
                <div style="width:18px; height:18px; border-radius:50%; background:#fff;
                             position:absolute; top:2px; transition:left 0.2s;"
                  :style="{ left: groupForm.is_required ? '20px' : '2px' }"></div>
              </div>
              <div>
                <div style="font-size:13px; color:#F1F5F9; font-weight:500;">Required</div>
                <div style="font-size:11px; color:#64748B;">Customer must pick at least one option</div>
              </div>
            </label>
          </div>
          <div style="display:flex; gap:8px; margin-top:18px; justify-content:flex-end;">
            <button @click="showGroupModal = false"
              style="padding:9px 16px; background:transparent; color:#64748B;
                     border:1px solid #252B38; border-radius:8px; cursor:pointer; font-size:13px;">Cancel</button>
            <button @click="saveGroup"
              style="padding:9px 20px; background:#F59E0B; color:#000; border:none;
                     border-radius:8px; cursor:pointer; font-weight:700; font-size:13px;">
              {{ editingGroup ? 'Update' : 'Create' }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Modifier Option Modal -->
    <Teleport to="body">
      <div v-if="showModifierModal"
        style="position:fixed; inset:0; background:rgba(0,0,0,0.65);
               display:flex; align-items:center; justify-content:center; z-index:110;"
        @click.self="showModifierModal = false">
        <div style="background:#1A1E28; border:1px solid #252B38; border-radius:14px;
                    padding:22px; width:380px;">
          <div style="font-size:16px; font-weight:700; color:#F1F5F9; margin-bottom:18px;">
            {{ editingModifier ? 'Edit Option' : 'New Option' }}
            <span style="font-size:12px; color:#64748B; font-weight:400; margin-left:6px;">
              for {{ selectedGroup?.name }}
            </span>
          </div>
          <div style="display:flex; flex-direction:column; gap:12px;">
            <div>
              <label style="font-size:11px; color:#64748B; display:block; margin-bottom:5px;
                             text-transform:uppercase; letter-spacing:0.05em;">Option Name *</label>
              <input v-model="modifierForm.name" placeholder="e.g. Large, Hot, Extra Cheese"
                style="width:100%; padding:9px 12px; background:#12151C; border:1px solid #252B38;
                       border-radius:8px; color:#F1F5F9; font-size:14px; outline:none;"
                @focus="e => e.target.style.borderColor='#F59E0B'"
                @blur="e => e.target.style.borderColor='#252B38'" />
            </div>
            <div>
              <label style="font-size:11px; color:#64748B; display:block; margin-bottom:5px;
                             text-transform:uppercase; letter-spacing:0.05em;">
                Default Price (Rs.) — 0 = included
              </label>
              <input v-model.number="modifierForm.price" type="number" step="0.01" min="0" placeholder="0.00"
                style="width:100%; padding:9px 12px; background:#12151C; border:1px solid #252B38;
                       border-radius:8px; color:#F59E0B; font-size:15px; font-weight:700; outline:none;"
                @focus="e => e.target.style.borderColor='#F59E0B'"
                @blur="e => e.target.style.borderColor='#252B38'" />
            </div>
            <label style="display:flex; align-items:center; gap:10px; cursor:pointer;">
              <div @click="modifierForm.is_active = !modifierForm.is_active"
                style="width:40px; height:22px; border-radius:11px; cursor:pointer;
                       transition:background 0.2s; position:relative; flex-shrink:0;"
                :style="{ background: modifierForm.is_active ? '#10B981' : '#252B38' }">
                <div style="width:18px; height:18px; border-radius:50%; background:#fff;
                             position:absolute; top:2px; transition:left 0.2s;"
                  :style="{ left: modifierForm.is_active ? '20px' : '2px' }"></div>
              </div>
              <span style="font-size:13px; color:#F1F5F9;">Active (visible to customers)</span>
            </label>
          </div>
          <div style="display:flex; gap:8px; margin-top:18px; justify-content:flex-end;">
            <button @click="showModifierModal = false"
              style="padding:9px 16px; background:transparent; color:#64748B;
                     border:1px solid #252B38; border-radius:8px; cursor:pointer; font-size:13px;">Cancel</button>
            <button @click="saveModifier"
              style="padding:9px 20px; background:#F59E0B; color:#000; border:none;
                     border-radius:8px; cursor:pointer; font-weight:700; font-size:13px;">
              {{ editingModifier ? 'Update' : 'Add Option' }}
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
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'

// ── Tabs ──────────────────────────────────────────────────
const tabs = [
  { value: 'categories', label: '📁 Categories'      },
  { value: 'items',      label: '🍽️ Menu Items'      },
  { value: 'modifiers',  label: '⚙️ Modifier Groups' },
  { value: 'pricing',    label: '💰 Modifier Pricing' },
]
const activeTab = ref('items')

// ── Core data ─────────────────────────────────────────────
const categories     = ref([])
const menuItems      = ref([])
const modifierGroups = ref([])
const selectedGroup  = ref(null)

// ── Filters (items tab) ───────────────────────────────────
const selectedCategory = ref('')
const searchQuery      = ref('')

// ── Pricing tab state ─────────────────────────────────────
const pricingItemSearch      = ref('')
const selectedItemForPricing = ref(null)
const modifierPricing        = ref({})   // { [modifierId]: { pricing_type, custom_price, increment_price } }
const pricingLoading         = ref(false)
const pricingSaving          = ref(false)
const allModifiers           = ref([])   // FIX: was missing — caused ReferenceError

// ── Modal visibility ──────────────────────────────────────
const showCategoryModal = ref(false)
const showItemModal     = ref(false)
const showGroupModal    = ref(false)
const showModifierModal = ref(false)

// ── Editing targets ───────────────────────────────────────
const editingCategory = ref(null)
const editingItem     = ref(null)
const editingGroup    = ref(null)
const editingModifier = ref(null)

// ── Form defaults ─────────────────────────────────────────
const defaultItemForm = () => ({
  name: '', description: '', price: 0, cost_price: 0,
  category_id: '', image: '', type: 'food', prep_time: 15,
  is_available: true, is_popular: false, is_instant: false,
  modifier_group_ids: [],
})
const defaultGroupForm    = () => ({ name: '', is_required: false, min_select: 0, max_select: 1, sort_order: 0 })
const defaultModifierForm = () => ({ name: '', price: 0, is_active: true })

const categoryForm = ref({ name: '', icon: '' })
const itemForm     = ref(defaultItemForm())
const groupForm    = ref(defaultGroupForm())
const modifierForm = ref(defaultModifierForm())

// ── Toggle fields config ──────────────────────────────────
const toggleFields = [
  { key: 'is_available', label: 'Available'  },
  { key: 'is_popular',   label: 'Popular'    },
  { key: 'is_instant',   label: '⚡ Instant' },
]

// ── Toast ─────────────────────────────────────────────────
const toast = ref({ show: false, message: '', type: 'success' })
function showToast(message, type = 'success') {
  toast.value = { show: true, message, type }
  setTimeout(() => { toast.value.show = false }, 3000)
}

// ── Computed ──────────────────────────────────────────────
const filteredItems = computed(() => {
  let items = menuItems.value
  if (selectedCategory.value) items = items.filter(i => i.category_id == selectedCategory.value)
  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase()
    items = items.filter(i => i.name.toLowerCase().includes(q))
  }
  return items
})

const pricingEligibleItems = computed(() => {
  const q = pricingItemSearch.value.toLowerCase()
  return menuItems.value.filter(item => {
    const hasGroups = (item.modifier_groups?.length ?? 0) > 0
    return hasGroups && (!q || item.name.toLowerCase().includes(q))
  })
})

// ── Data loaders ──────────────────────────────────────────
async function loadAll() {
  await Promise.all([loadCategories(), loadMenuItems(), loadModifierGroups()])
}

async function loadCategories() {
  const { data } = await axios.get('/menu/categories')
  categories.value = data
}

async function loadMenuItems() {
  const { data } = await axios.get('/menu/items')
  menuItems.value = data
}

async function loadModifierGroups() {
  const { data } = await axios.get('/modifier-groups')
  modifierGroups.value = data
  if (selectedGroup.value) {
    selectedGroup.value = data.find(g => g.id === selectedGroup.value.id) ?? null
  }
}

// ── Categories ────────────────────────────────────────────
function openCategoryModal(cat) {
  editingCategory.value = cat
  categoryForm.value = cat ? { name: cat.name, icon: cat.icon ?? '' } : { name: '', icon: '' }
  showCategoryModal.value = true
}

async function saveCategory() {
  if (!categoryForm.value.name.trim()) { showToast('Category name is required', 'error'); return }
  try {
    if (editingCategory.value) {
      await axios.put(`/menu/categories/${editingCategory.value.id}`, categoryForm.value)
      showToast('Category updated')
    } else {
      await axios.post('/menu/categories', categoryForm.value)
      showToast('Category created')
    }
    showCategoryModal.value = false
    await loadCategories()
  } catch (e) { showToast(e.response?.data?.message ?? 'Failed to save', 'error') }
}

async function deleteCategory(cat) {
  if (!confirm(`Delete "${cat.name}"? Items in this category will be affected.`)) return
  try {
    await axios.delete(`/menu/categories/${cat.id}`)
    showToast('Category deleted')
    await loadCategories()
  } catch (e) { showToast(e.response?.data?.message ?? 'Cannot delete', 'error') }
}

// ── Menu Items ────────────────────────────────────────────
function openItemModal(item) {
  editingItem.value = item
  itemForm.value = item ? {
    name: item.name, description: item.description ?? '',
    price: parseFloat(item.price), cost_price: parseFloat(item.cost_price ?? 0),
    category_id: item.category_id, image: item.image ?? '',
    type: item.type ?? 'food', prep_time: item.prep_time ?? 15,
    is_available: !!item.is_available, is_popular: !!item.is_popular, is_instant: !!item.is_instant,
    modifier_group_ids: (item.modifier_groups ?? []).map(g => g.id),
  } : defaultItemForm()
  showItemModal.value = true
}

function toggleGroupOnItem(groupId) {
  const ids = itemForm.value.modifier_group_ids
  const idx = ids.indexOf(groupId)
  idx >= 0 ? ids.splice(idx, 1) : ids.push(groupId)
}

function handleImageUpload(event) {
  const file = event.target.files[0]
  if (!file) return
  
  const formData = new FormData()
  formData.append('image', file)
  
  // Upload to server
  axios.post('/menu/items/upload-image', formData, {
    headers: {
      'Content-Type': 'multipart/form-data'
    }
  }).then(response => {
    if (response.data.success) {
      itemForm.value.image = response.data.filename
      showToast('Image uploaded ✓')
    } else {
      showToast(response.data.message || 'Upload failed', 'error')
    }
  }).catch(error => {
    showToast('Upload failed: ' + (error.response?.data?.message || error.message), 'error')
  })
}

async function saveItem() {
  if (!itemForm.value.name.trim())    { showToast('Item name is required', 'error'); return }
  if (!itemForm.value.category_id)    { showToast('Please select a category', 'error'); return }
  if (!(itemForm.value.price > 0))    { showToast('Price must be greater than 0', 'error'); return }
  try {
    if (editingItem.value) {
      await axios.put(`/menu/items/${editingItem.value.id}`, itemForm.value)
      showToast('Item updated ✓')
    } else {
      await axios.post('/menu/items', itemForm.value)
      showToast('Item created ✓')
    }
    showItemModal.value = false
    await loadMenuItems()
  } catch (e) { showToast(e.response?.data?.message ?? 'Failed to save', 'error') }
}

async function toggleAvailability(item) {
  try {
    await axios.patch(`/menu/items/${item.id}/toggle`)
    await loadMenuItems()
    showToast(item.is_available ? 'Item disabled' : 'Item enabled')
  } catch (e) { showToast('Failed to toggle', 'error') }
}

async function deleteItem(item) {
  if (!confirm(`Delete "${item.name}"?`)) return
  try {
    await axios.delete(`/menu/items/${item.id}`)
    showToast('Item deleted')
    await loadMenuItems()
  } catch (e) { showToast(e.response?.data?.message ?? 'Cannot delete', 'error') }
}

// ── Modifier Groups ───────────────────────────────────────
function openGroupModal(group) {
  editingGroup.value = group
  groupForm.value = group ? {
    name: group.name, is_required: group.is_required,
    min_select: group.min_select, max_select: group.max_select, sort_order: group.sort_order ?? 0,
  } : defaultGroupForm()
  showGroupModal.value = true
}

async function saveGroup() {
  if (!groupForm.value.name.trim()) { showToast('Group name is required', 'error'); return }
  try {
    if (editingGroup.value) {
      await axios.put(`/modifier-groups/${editingGroup.value.id}`, groupForm.value)
      showToast('Group updated ✓')
    } else {
      await axios.post('/modifier-groups', groupForm.value)
      showToast('Group created ✓')
    }
    showGroupModal.value = false
    await loadModifierGroups()
  } catch (e) { showToast(e.response?.data?.message ?? 'Failed to save', 'error') }
}

async function deleteGroup(group) {
  if (!confirm(`Delete group "${group.name}" and all its options?`)) return
  try {
    await axios.delete(`/modifier-groups/${group.id}`)
    selectedGroup.value = null
    showToast('Group deleted')
    await loadModifierGroups()
  } catch (e) { showToast(e.response?.data?.message ?? 'Cannot delete', 'error') }
}

// ── Modifier Options ──────────────────────────────────────
function openModifierModal(mod) {
  editingModifier.value = mod
  modifierForm.value = mod ? {
    name: mod.name, price: parseFloat(mod.price ?? 0), is_active: mod.is_active !== false,
  } : defaultModifierForm()
  showModifierModal.value = true
}

async function saveModifier() {
  if (!modifierForm.value.name.trim()) { showToast('Option name is required', 'error'); return }
  try {
    if (editingModifier.value) {
      await axios.put(`/modifiers/${editingModifier.value.id}`, modifierForm.value)
      showToast('Option updated ✓')
    } else {
      await axios.post('/modifiers', { modifier_group_id: selectedGroup.value.id, ...modifierForm.value })
      showToast('Option added ✓')
    }
    showModifierModal.value = false
    await loadModifierGroups()
  } catch (e) { showToast(e.response?.data?.message ?? 'Failed to save', 'error') }
}

async function deleteModifier(mod) {
  if (!confirm(`Delete option "${mod.name}"?`)) return
  try {
    await axios.delete(`/modifiers/${mod.id}`)
    showToast('Option deleted')
    await loadModifierGroups()
  } catch (e) { showToast(e.response?.data?.message ?? 'Cannot delete', 'error') }
}

// ── Pricing Tab ───────────────────────────────────────────

/**
 * FIX 1: Reset modifierPricing before loading new item's data.
 * FIX 2: Added missing allModifiers ref.
 * FIX 3: pricingLoading now always resets in finally block.
 * FIX 4: Use Promise.all instead of sequential awaits (race condition).
 */
async function selectItemForPricing(item) {
  selectedItemForPricing.value = item
  modifierPricing.value = {}      // FIX: clear stale data from previously selected item
  pricingLoading.value  = true

  try {
    const [modRes, pricingRes] = await Promise.all([
      axios.get(`/menu/items/${item.id}/modifiers`),
      axios.get(`/menu/items/${item.id}/modifier-pricing`),
    ])

    allModifiers.value = modRes.data

    // Populate saved pricing from DB (item-specific)
    pricingRes.data.forEach(p => {
      modifierPricing.value[p.modifier_id] = {
        pricing_type:    p.pricing_type    || 'absolute',
        custom_price:    parseFloat(p.custom_price    ?? 0),
        increment_price: parseFloat(p.increment_price ?? 0),
      }
    })

    // Initialise defaults for modifiers that have no saved pricing yet
    allModifiers.value.forEach(modifier => {
      if (!modifierPricing.value[modifier.id]) {
        modifierPricing.value[modifier.id] = {
          pricing_type:    'absolute',
          custom_price:    parseFloat(modifier.price) || 0,
          increment_price: 0,
        }
      }
    })
  } catch (e) {
    showToast('Failed to load modifier pricing', 'error')
  } finally {
    pricingLoading.value = false   // FIX: always stop spinner, even on error
  }
}

function getModifierPricing(modifierId) {
  return modifierPricing.value[modifierId] ?? { pricing_type: 'absolute', custom_price: 0, increment_price: 0 }
}

function getPricingValue(modifierId) {
  const p = getModifierPricing(modifierId)
  return p.pricing_type === 'increment' ? p.increment_price : p.custom_price
}

function setPricingType(modifierId, type) {
  modifierPricing.value[modifierId] = { ...getModifierPricing(modifierId), pricing_type: type }
}

function setPricingValue(modifierId, rawValue) {
  const val = parseFloat(rawValue) || 0
  const cur = getModifierPricing(modifierId)
  modifierPricing.value[modifierId] = cur.pricing_type === 'increment'
    ? { ...cur, increment_price: val }
    : { ...cur, custom_price: val }
}

function computePreviewPrice(modifierId) {
  if (!selectedItemForPricing.value) return '—'
  const base = parseFloat(selectedItemForPricing.value.price)
  const p    = getModifierPricing(modifierId)
  const addon = p.pricing_type === 'increment' ? (p.increment_price ?? 0) : (p.custom_price ?? 0)
  return (base + addon).toFixed(2)
}

async function saveModifierPricing() {
  if (!selectedItemForPricing.value || pricingSaving.value) return
  pricingSaving.value = true
  try {
    const payload = Object.entries(modifierPricing.value).map(([modifierId, p]) => ({
      modifier_id:     parseInt(modifierId),
      pricing_type:    p.pricing_type,
      custom_price:    p.pricing_type === 'absolute'  ? p.custom_price    : null,
      increment_price: p.pricing_type === 'increment' ? p.increment_price : null,
    }))
    await axios.patch(`/menu/items/${selectedItemForPricing.value.id}/modifier-pricing`, { pricing: payload })
    showToast('Modifier pricing saved ✓')
  } catch (e) {
    showToast(e.response?.data?.message ?? 'Failed to save pricing', 'error')
  } finally {
    pricingSaving.value = false
  }
}

// ── Init ──────────────────────────────────────────────────
onMounted(loadAll)
</script>

<style scoped>
/* ── Mobile Responsive ── */
@media (max-width: 768px) {
  .menu-manager-layout {
    flex-direction: column;
  }
  
  .sidebar {
    width: 100%;
    max-width: 100%;
    border-right: none;
    border-bottom: 1px solid #252B38;
    max-height: 200px;
    overflow-y: auto;
  }
  
  .sidebar ul {
    flex-direction: row;
    flex-wrap: wrap;
    gap: 4px;
    padding: 8px;
  }
  
  .sidebar li {
    width: auto;
    margin: 0;
  }
  
  .sidebar button {
    width: auto;
    padding: 8px 12px;
    font-size: 12px;
    white-space: nowrap;
  }
  
  .main-content {
    width: 100%;
    max-width: 100%;
  }
  
  .content-header {
    flex-direction: column;
    gap: 12px;
    align-items: stretch;
  }
  
  .header-actions {
    flex-direction: column;
    gap: 8px;
  }
  
  .header-actions button {
    width: 100%;
    justify-content: center;
  }
  
  .search-bar {
    width: 100%;
  }
  
  .items-grid {
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 12px;
    padding: 12px;
  }
  
  .item-card {
    padding: 12px;
    border-radius: 12px;
  }
  
  .item-image {
    width: 80px;
    height: 66px;
    font-size: 24px;
  }
  
  .item-name {
    font-size: 12px;
  }
  
  .item-price {
    font-size: 11px;
  }
  
  .modal-content {
    width: 95%;
    max-width: 95%;
    margin: 2.5% auto;
    max-height: 95vh;
  }
  
  .modal-body {
    padding: 16px;
  }
  
  .form-grid {
    grid-template-columns: 1fr;
    gap: 12px;
  }
  
  .form-group {
    margin-bottom: 12px;
  }
  
  .form-group label {
    font-size: 12px;
    margin-bottom: 4px;
  }
  
  .form-group input,
  .form-group select,
  .form-group textarea {
    font-size: 14px;
    padding: 8px 12px;
  }
  
  .form-actions {
    flex-direction: column;
    gap: 8px;
  }
  
  .form-actions button {
    width: 100%;
  }
}

@media (max-width: 480px) {
  .sidebar {
    max-height: 150px;
  }
  
  .sidebar ul {
    padding: 6px;
    gap: 2px;
  }
  
  .sidebar button {
    padding: 6px 10px;
    font-size: 11px;
  }
  
  .items-grid {
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    gap: 8px;
    padding: 8px;
  }
  
  .item-card {
    padding: 8px;
    border-radius: 8px;
  }
  
  .item-image {
    width: 70px;
    height: 58px;
    font-size: 20px;
  }
  
  .item-name {
    font-size: 11px;
  }
  
  .item-price {
    font-size: 10px;
  }
  
  .modal-content {
    width: 98%;
    max-width: 98%;
    margin: 1% auto;
    max-height: 98vh;
  }
  
  .modal-body {
    padding: 12px;
  }
  
  .form-group label {
    font-size: 11px;
  }
  
  .form-group input,
  .form-group select,
  .form-group textarea {
    font-size: 13px;
    padding: 6px 10px;
  }
}
</style>