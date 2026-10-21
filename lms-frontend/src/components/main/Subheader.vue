<template>
  <div class="">
    <div style="font-size: 12px;" class="nunito mx-1 secondary-2--text">
      {{ $route.name.replace(role, '').toUpperCase() }}
    </div>
    <h1 style="font-size: 20px;" class="poppins black--text">
      Good Day, Maria!
    </h1>
  </div>
  
</template>

<script>
export default {
  props: ['role']
}
</script>
