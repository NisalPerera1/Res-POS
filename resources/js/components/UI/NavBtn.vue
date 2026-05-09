<template>
  <router-link
    :to="to"
    :style="wide ? {
      width: '100%', 
      height: '40px', 
      borderRadius: '8px', 
      display: 'flex',
      alignItems: 'center', 
      justifyContent: 'flex-start',
      textDecoration: 'none',
      transition: 'all 0.15s', 
      fontSize: '14px',
      padding: '0 12px',
      gap: '12px',
      position: 'relative',
      background: isActive ? 'rgba(245,158,11,0.15)' : 'transparent',
      color: isActive ? 'var(--accent-color)' : 'var(--text-secondary)',
    } : {
      width: '36px', 
      height: '36px', 
      borderRadius: '8px', 
      display: 'flex',
      alignItems: 'center', 
      justifyContent: 'center',
      textDecoration: 'none',
      transition: 'all 0.15s', 
      fontSize: '16px',
      position: 'relative',
      background: isActive ? 'rgba(245,158,11,0.15)' : 'transparent',
      color: isActive ? 'var(--accent-color)' : 'var(--text-secondary)',
    }"
    @mouseenter="e => { if(!isActive) e.currentTarget.style.background='var(--bg-tertiary)' }"
    @mouseleave="e => { if(!isActive) e.currentTarget.style.background='transparent' }"
    :title="label"
  >
    <!-- Icons as emoji fallback -->
    <span>{{ iconEmoji }}</span>
    <span v-if="wide" style="font-weight:500;">{{ label }}</span>
  </router-link>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'

const props = defineProps({
  // `to` can be a string path (legacy) or a Vue Router location object (recommended).
  to:   { type: [String, Object], required: true },
  icon: String,
  label: String,
  wide: { type: Boolean, default: false },
})

const route    = useRoute()
const isActive = computed(() => {
  const currentPath = route.path

  // Support both:
  // 1) props.to = '/direct' (string path)
  // 2) props.to = { name: 'direct-order' } (route name)
  const targetPath = typeof props.to === 'string' ? props.to : props.to?.path
  const targetName = typeof props.to === 'object' ? props.to?.name : null

  if (targetName && route.name === targetName) return true
  if (targetPath) return currentPath === targetPath || currentPath.startsWith(targetPath)

  return false
})

const iconEmoji = computed(() => ({
  dashboard: '📊',
  grid:      '🗑️',
  arrow:     '➡️',
  direct:    '➡️',
  chef:      '🍳',
  menu:      '🍴',
  chart:     '📈',
  users:     '👥',
  receipt:   '📝',
}[props.icon] ?? '🍴'))
</script>