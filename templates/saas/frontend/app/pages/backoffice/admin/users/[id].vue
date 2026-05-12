<script setup>
const route = useRoute()
await navigateTo(`/backoffice/admin/users/${route.params.id}/`, { replace: true })
</script>

<template>
  <div />
</template>
