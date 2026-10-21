<template>
    <section>
      <div
        :contenteditable="edit"
        class="editable f14 fw500 roboto"
        :style="type == 'title' ? 'min-height: 0px' : ''"
        v-html="type == 'title' ? data.title : data.body"
        @focus="
          $emit('inputFocus', {
            event: $event,
            index: action == 'update' ? data : index,
            parent: parent,
            type: type,
          })
        "
        @blur="
          $emit('inputLostFocus', {
            event: $event,
            index: action == 'update' ? data : index,
            parent: parent,
            type: type,
          })
        "
      />
    </section>
  </template>
  
  <script>
  export default {
    props: ["data", "index", "parent", "type", "action", "edit"],
  };
  </script>
  
  <style scoped>
  .editable {
    width: 150px;
    min-height: 120px;
    max-height: 100px;
    overflow-y: auto;
  }
  .editable::-webkit-scrollbar {
    display: none;
  }
  div[contenteditable="true"] {
    outline: none;
    padding: 5px;
  }
  
  div[contenteditable="true"]:focus {
    border-bottom: 1px solid #bdbdbd;
  }
  
  div[contenteditable="true"]:hover {
    outline: 1px dashed #bdbdbd;
    /* font-weight: bold; */
  }
  </style>
  