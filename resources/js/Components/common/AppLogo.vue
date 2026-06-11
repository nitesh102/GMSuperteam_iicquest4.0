<template>
  <div class="app-logo">
    <img
      :src="logo"
      alt="CiviSense Logo"
      class="logo-image"
    />

    <span class="brand-text">
      <span class="civi">Civi</span>
      <span class="sense">Sense</span>
    </span>
  </div>
</template>

<script setup>
import logo from '@/assets/logo.png'
</script>

<style scoped>
.app-logo{
  display:flex;
  align-items:center;
  gap:12px;
}

.logo-image{
  height:48px;
  width:auto;
  object-fit:contain;
  flex-shrink:0;
}

.brand-text{
  display:flex;
  align-items:center;
  font-size:32px;
  font-weight:700;
  line-height:1;
}

.civi{
  color:#1D4ED8;
}

.sense{
  color:#EF4444;
}

@media (max-width:768px){
  .logo-image{
    height:40px;
  }

  .brand-text{
    font-size:24px;
  }
}
</style>
