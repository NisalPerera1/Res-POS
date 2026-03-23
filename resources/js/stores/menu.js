import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'

export const useMenuStore = defineStore('menu', () => {
  const categories     = ref([])
  const activeCategory = ref(null)
  const loading        = ref(false)

  async function fetchMenu() {
    if (categories.value.length > 0) return // cached
    loading.value = true
    try {
      const { data } = await axios.get('/menu')
      categories.value = data
      if (data.length && !activeCategory.value) {
        activeCategory.value = data[0].id
      }
    } catch (e) {
      console.error('fetchMenu failed:', e)
    } finally {
      loading.value = false
    }
  }

  function setActiveCategory(id) {
    activeCategory.value = id
  }

  function getItemsByCategory(catId) {
    const cat = categories.value.find(c => c.id === catId)
    return cat?.menu_items ?? []
  }

  // Force refresh (after menu changes)
  async function refreshMenu() {
    categories.value = []
    await fetchMenu()
  }

  return {
    categories, activeCategory, loading,
    fetchMenu, refreshMenu, setActiveCategory, getItemsByCategory
  }
})