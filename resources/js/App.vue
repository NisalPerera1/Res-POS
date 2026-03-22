<template>
  <div class="min-h-screen bg-pos-bg text-pos-text font-sans">
    <router-view />
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
const auth = useAuthStore()
</script>