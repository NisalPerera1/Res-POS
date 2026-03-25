import { defineStore } from 'pinia'
import { ref }         from 'vue'
import axios           from 'axios'

export const useOrderStore = defineStore('orders', () => {
  const currentOrder = ref(null)
  const loading      = ref(false)

  const LS_ORDER_KEY = 'pos_current_order'
  const LS_TABLE_KEY = 'pos_selected_table'

  // ── Storage ───────────────────────────────────────────
  function saveOrderToStorage(order) {
    try {
      if (order) localStorage.setItem(LS_ORDER_KEY, JSON.stringify(order))
      else       localStorage.removeItem(LS_ORDER_KEY)
    } catch(e) { console.warn('Storage save failed:', e) }
  }

  function saveTableToStorage(table) {
    try {
      if (table) localStorage.setItem(LS_TABLE_KEY, JSON.stringify(table))
      else       localStorage.removeItem(LS_TABLE_KEY)
    } catch(e) {}
  }

  function getCurrentTable() {
    try {
      const s = localStorage.getItem(LS_TABLE_KEY)
      return s ? JSON.parse(s) : null
    } catch { return null }
  }

  function clearOrder() {
    const currentOrderData = currentOrder.value
    currentOrder.value = null
    
    // Only clear from localStorage if it's a table order
    // Keep direct orders in localStorage for persistence
    if (!currentOrderData || currentOrderData.table_id) {
      localStorage.removeItem(LS_ORDER_KEY)
      localStorage.removeItem(LS_TABLE_KEY)
    }
  }

  const clearStorage = clearOrder
  function initializeFromStorage() {} // API is source of truth

  // ── Helpers ───────────────────────────────────────────
  function setOrder(data) {
    // Normalize: ensure items is always array
    if (!data) return
    if (!Array.isArray(data.items)) data.items = []
    currentOrder.value = data
    saveOrderToStorage(data)
  }

  // ── fetchOrder ────────────────────────────────────────
  async function fetchOrder(id) {
    if (!id) return null
    loading.value = true
    try {
      const { data } = await axios.get(`/orders/${id}`)
      setOrder(data)
      console.log(
        '✅ fetchOrder:', data.order_number,
        '| total items:', data.items?.length,
        '| active:', data.items?.filter(i => !i.is_void && i.is_void !== 1).length,
        '| sent:', data.items?.filter(i => i.kot_round).length,
        '| unsent:', data.items?.filter(i => !i.kot_round && !i.is_void).length,
      )
      return data
    } catch (e) {
      console.error('❌ fetchOrder failed:', e.response?.data ?? e.message)
      throw e
    } finally {
      loading.value = false
    }
  }

  // ── createOrder ───────────────────────────────────────
  async function createOrder(payload) {
    loading.value = true
    try {
      const { data } = await axios.post('/orders', payload)
      setOrder(data)
      if (payload.table_id) {
        saveTableToStorage({ id: payload.table_id })
      }
      console.log('✅ createOrder:', data.order_number)
      return data
    } catch (e) {
      console.error('❌ createOrder failed:', e.response?.data ?? e.message)
      throw e
    } finally {
      loading.value = false
    }
  }

  // ── addItem ───────────────────────────────────────────
  async function addItem(orderId, payload) {
    try {
      const { data } = await axios.post(`/orders/${orderId}/items`, payload)
      const order = data.order ?? data
      setOrder(order)
      return data
    } catch (e) {
      console.error('❌ addItem failed:', e.response?.data ?? e.message)
      throw e
    }
  }

  // ── updateItemQty ─────────────────────────────────────
  async function updateItemQty(orderId, itemId, quantity) {
    try {
      const { data } = await axios.patch(
        `/orders/${orderId}/items/${itemId}`,
        { quantity }
      )
      const order = data.order ?? data
      setOrder(order)
      return data
    } catch (e) {
      console.error('❌ updateItemQty failed:', e.response?.data ?? e.message)
      throw e
    }
  }

  // ── voidItem ──────────────────────────────────────────
  async function voidItem(orderId, itemId) {
    try {
      // Use PATCH (matches route definition)
      const { data } = await axios.patch(
        `/orders/${orderId}/items/${itemId}/void`
      )
      const order = data.order ?? data
      setOrder(order)
      return data
    } catch (e) {
      console.error('❌ voidItem failed:', e.response?.data ?? e.message)
      throw e
    }
  }

  // ── sendKOT ───────────────────────────────────────────
  async function sendKOT(orderId) {
    try {
      const { data } = await axios.post(`/orders/${orderId}/send-kot`)
      // data = { message, round, items_sent, order: {...} }
      if (data.order) {
        setOrder(data.order)
        console.log(
          '✅ sendKOT: round', data.round,
          '| sent:', data.items_sent,
          '| items after:', data.order.items?.map(i => ({
            name: i.item_name,
            kot_round: i.kot_round,
            status: i.status
          }))
        )
      } else {
        // Fallback re-fetch
        console.warn('⚠️ sendKOT: no order in response, re-fetching...')
        await fetchOrder(orderId)
      }
      return data
    } catch (e) {
      console.error('❌ sendKOT failed:', e.response?.data ?? e.message)
      throw e
    }
  }

  // ── updateStatus ──────────────────────────────────────
  async function updateStatus(orderId, status) {
    try {
      const { data } = await axios.patch(`/orders/${orderId}/status`, { status })
      setOrder(data)
      return data
    } catch (e) {
      console.error('❌ updateStatus failed:', e.response?.data ?? e.message)
      throw e
    }
  }

  // ── processPayment ────────────────────────────────────
  async function processPayment(orderId, payments) {
    try {
      const { data } = await axios.post(`/orders/${orderId}/payments`, { payments })
      const order = data.order ?? data
      if (order?.payment_status === 'paid') {
        clearOrder()
      } else {
        setOrder(order)
      }
      return data
    } catch (e) {
      console.error('❌ processPayment failed:', e.response?.data ?? e.message)
      throw e
    }
  }

  return {
    currentOrder,
    loading,
    fetchOrder,
    createOrder,
    addItem,
    updateItemQty,
    voidItem,
    sendKOT,
    updateStatus,
    processPayment,
    clearOrder,
    clearStorage,
    initializeFromStorage,
    saveOrderToStorage,
    saveTableToStorage,
    getCurrentTable,
    setOrder,
  }
})