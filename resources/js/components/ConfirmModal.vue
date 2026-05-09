<template>
  <div style="position:fixed; inset:0; background:rgba(0,0,0,0.65);
               display:flex; align-items:center; justify-content:center; z-index:60;">
    <div style="background:#1A1E28; border:1px solid #252B38; border-radius:16px;
               width:400px; max-width:94vw; overflow:hidden;
               display:flex; flex-direction:column;
               box-shadow:0 25px 50px -12px rgba(0,0,0,0.5);">
      
      <!-- Header -->
      <div style="padding:20px; border-bottom:1px solid #252B38;">
        <div style="font-size:18px; font-weight:700; color:#F1F5F9;">{{ title }}</div>
      </div>
      
      <!-- Content -->
      <div style="padding:20px; flex:1;">
        <div style="font-size:14px; color:#94A3B8; line-height:1.6;">{{ message }}</div>
      </div>
      
      <!-- Actions -->
      <div style="padding:20px; border-top:1px solid #252B38; display:flex; gap:12px;">
        <button @click="$emit('cancel')"
          style="flex:1; padding:12px; border-radius:8px; font-size:14px; font-weight:600;
                 background:transparent; color:#64748B; border:1px solid #252B38; cursor:pointer;
                 transition:all 0.2s;"
          @mouseenter="e => e.currentTarget.style.background='#252B38'"
          @mouseleave="e => e.currentTarget.style.background='transparent'">
          {{ cancelText }}
        </button>
        <button @click="$emit('confirm')"
          style="flex:1; padding:12px; border-radius:8px; font-size:14px; font-weight:600;
                 background:#10B981; color:#fff; border:none; cursor:pointer;
                 transition:all 0.2s;"
          @mouseenter="e => e.currentTarget.style.background='#059669'"
          @mouseleave="e => e.currentTarget.style.background='#10B981'">
          {{ confirmText }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  title: {
    type: String,
    default: 'Confirm Action'
  },
  message: {
    type: String,
    default: 'Are you sure you want to proceed?'
  },
  cancelText: {
    type: String,
    default: 'Cancel'
  },
  confirmText: {
    type: String,
    default: 'Confirm'
  }
})

const emit = defineEmits(['confirm', 'cancel'])
</script>
