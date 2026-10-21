<template>
  <v-sheet color="#FAFAFA" rounded class=" border poppins mt-2 cursor-pointer px-2">
    <div class="d-flex align-center flex-row">
      <v-avatar size="40" class="mr-2">
        <v-img 
          v-if="user.id == item.sender.id"
          :src="item.receiver.image?item.receiver.image.url:require('@/assets/default-photo.png')"
          v-on:error="item.receiver.image = null"
        />
        <v-img 
          v-else
          :src="item.sender.image?item.sender.image.url:require('@/assets/default-photo.png')"
          v-on:error="item.sender.image = null"
        />
      </v-avatar>
        
      <v-list-item-content width="75%" class="mr-2">
          <v-list-item-title v-if="user.id == item.sender.id" class="f12 fw500">
            {{ item.receiver.full_name }}
          </v-list-item-title>
          <v-list-item-title v-else class="f12 fw500">
            {{ item.sender.full_name }} 
          </v-list-item-title>
          <v-list-item-subtitle v-if="item.latest_private_message" class="f11 secondary--text my-1">
            <span v-if="item.latest_private_message.message_body">
              {{item.latest_private_message.message_body}}
            </span>
            <span v-else-if="!item.latest_private_message.message_body && item.latest_private_message.attachments.length > 0">
              <span v-if="item.latest_private_message.sender">
                <span v-if="item.latest_private_message.sender.id === user.id">
                  You sent an attachment
                </span>
                <span v-else>
                  {{ item.latest_private_message.sender.full_name }} sent an attachment
                </span>
              </span>
              <span v-else>
                Someone sent an attachment
              </span>
            </span>
          </v-list-item-subtitle>
      </v-list-item-content>
      <div class=" poppins f10 secondary--text" v-if="item.latest_private_message">
        {{$dateFormat.fromNow(item.latest_private_message.created_at)}}
      </div>
    </div>
  </v-sheet>
</template>

<script>
export default {
  props: ['item', 'user'],
}
</script>