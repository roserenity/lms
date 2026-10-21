<template>
  <v-sheet color="#FAFAFA" rounded class=" border poppins mt-2 cursor-pointer px-2" width="100%">
    <div class="d-flex align-center flex-row">
      <v-avatar size="40" class="mr-2">
        <v-img 
            :src="item.image?item.image.url:require('@/assets/default-photo-group.png')"
            v-on:error="require('@/assets/default-photo-group.png')"/>
      </v-avatar>
        
      <v-list-item-content width="75%" class="mr-2">
          <v-list-item-title class="f12 fw500" :class="!item.latest_group_message ? 'py-3' : 'py-0'">
            {{`${item.name}`}}
          </v-list-item-title>
          <v-list-item-subtitle v-if="item.latest_group_message" class="f11 secondary--text my-1">
            <span v-if="item.latest_group_message.message_body">
              {{item.latest_group_message.message_body}}
            </span>
            <span v-else-if="!item.latest_group_message.message_body && item.latest_group_message.attachments.length > 0">
              <span v-if="item.latest_group_message.sender">
                {{`${item.latest_group_message.sender.first_name ?? ''} ${item.latest_group_message.sender.last_name ?? ''} ${item.latest_group_message.sender.suffix ?? ''}`}} sent an attachment
              </span>
              <span v-else>
                Someone sent an attachment
              </span>
            </span>
          </v-list-item-subtitle>
      </v-list-item-content>
      <div class=" poppins f10 secondary--text" v-if="item.latest_group_message">
        {{$dateFormat.fromNow(item.latest_group_message.created_at)}}
      </div>
      <div class=" poppins f10 secondary--text" v-else>
        {{$dateFormat.fromNow(item.created_at)}}
      </div>
    </div>
  </v-sheet>
</template>

<script>
export default {
  props: ['item', 'user']
}
</script>