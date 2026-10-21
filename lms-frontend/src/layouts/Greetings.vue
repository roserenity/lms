<template>
  <section id="greetings">
    <div class="mb-5">
      <div class="poppins mx-1 grey--text" style="font-size: 14px">
        {{ $route.name.replace(role, '').toUpperCase() }}
      </div>
      <h1 class="nunito">
        Good Day, {{name}}!
      </h1>
    </div>
  </section>
</template>

<script>

export default {
  props: ['role', 'name']
}
</script>