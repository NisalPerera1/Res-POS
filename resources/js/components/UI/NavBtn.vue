<template>
  <router-link
    :to="to"
    style="width:36px; height:36px; border-radius:8px; display:flex;
           align-items:center; justify-content:center; text-decoration:none;
           transition:all 0.15s; font-size:16px; position:relative;"
    :style="{
      background: isActive ? 'rgba(245,158,11,0.15)' : 'transparent',
      color:      isActive ? '#F59E0B' : '#64748B',
    }"
    @mouseenter="e => { if(!isActive) e.currentTarget.style.background='#1A1E28' }"
    @mouseleave="e => { if(!isActive) e.currentTarget.style.background='transparent' }"
    :title="label"
  >
    <!-- Icons as emoji fallback -->
    <span>{{ iconEmoji }}</span>
  </router-link>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'

const props = defineProps({
  to:   String,
  icon: String,
  label: String,
})

const route    = useRoute()
const isActive = computed(() => {
  const currentPath = route.path
  const targetPath = props.to
  
  console.log('NavBtn isActive check:', {
    currentPath,
    targetPath,
    isActive: currentPath === targetPath || currentPath.startsWith(targetPath)
  })
  
  return currentPath === targetPath || currentPath.startsWith(targetPath)
})

const iconEmoji = computed(() => ({
  grid:  '⊞',
  chef:  '👨‍🍳',
  menu:  '📋',
  chart: '📊',
}[props.icon] ?? '●'))
</script>