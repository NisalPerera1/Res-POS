import { defineStore } from 'pinia'
import { ref } from 'vue'
import axios from 'axios'

export const useMenuStore = defineStore('menu', () => {
  const categories     = ref([])
  const activeCategory = ref(null)
  const loading        = ref(false)

  async function fetchMenu() {
    loading.value = true
    try {
      console.log('Fetching menu categories and items...')
      
      // Fetch categories and items separately
      const [categoriesRes, itemsRes] = await Promise.all([
        axios.get('/menu/categories'),
        axios.get('/menu/items')
      ])
      
      console.log('Categories:', categoriesRes.data)
      console.log('Items:', itemsRes.data)
      
      // Handle different response formats
      const categoriesData = Array.isArray(categoriesRes.data) ? categoriesRes.data : []
      const itemsData = Array.isArray(itemsRes.data) ? itemsRes.data : []
      
      // Combine categories with their items
      categories.value = categoriesData.map(category => ({
        ...category,
        menu_items: itemsData.filter(item => item.category_id === category.id)
      }))
      
      console.log('Combined menu data:', categories.value)
      
      if (categories.value.length) {
        activeCategory.value = categories.value[0].id
      }
    } catch (error) {
      console.error('Error fetching menu:', error)
      console.error('Error response:', error.response?.data)
      
      // Set empty state on error
      categories.value = []
      activeCategory.value = null
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

  return { categories, activeCategory, loading, fetchMenu, setActiveCategory, getItemsByCategory }
})