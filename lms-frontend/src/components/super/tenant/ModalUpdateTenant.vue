<template>
  <v-dialog v-model="dialog" persistent max-width="550">
    <v-card v-if="dialog">
      <div class="poppins f18 fw600 d-flex px-2 py-3">
        <div class=" mx-auto primary--text">
          {{form.tenant_name}}
        </div>
        <v-btn icon small @click="cancel()">
          <v-icon size="18">mdi-close</v-icon>
        </v-btn>
      </div>
      <v-form ref="tenantForm">
        <v-expansion-panels accordion dense elevation="0">
          <v-expansion-panel>
            <v-expansion-panel-header class="primary--text poppins fw600">GENERAL INFO</v-expansion-panel-header>
            <v-expansion-panel-content>
              <v-row no-gutters >
                <v-col cols="6" class="px-2">
                  <label class="secondary-2--text f12 poppins">TENANT NAME *</label>
                  <v-text-field v-model="form.tenant_name" 
                    outlined 
                    dense 
                    class=" f14"
                    :class="form_errors.tenant_name ? '' : 'general-custom-field'" 
                    :error-messages="form_errors.tenant_name"
                    :readonly="type === 'view'"
                  />
                </v-col>
                <v-col cols="6" class="px-2">
                  <label class="secondary-2--text f12 poppins">TYPE OF ORGANIZATION *</label>
                  <v-select
                    v-model="form.info.organization"
                    :items="types"
                    item-value="value"
                    dense
                    outlined
                    :menu-props="{ top: true, offsetY: true }"
                    small-chips
                    :class="form_errors.organization ? '' : 'general-custom-field'" 
                    :error-messages="form_errors.organization"
                    class="f14"
                    :readonly="type === 'view'">
                    <template #selection="{ item }">
                      <v-chip small label color="primary">{{item.text}}</v-chip>
                    </template>
                  </v-select>
                </v-col>
                <v-col cols="6" class="px-2">
                  <label class="secondary-2--text f12 poppins">DOMAIN NAME *</label>
                  
                  <v-menu offset-y eager :close-on-content-click="false" content-class="elevation-2">
                    <template v-slot:activator="{ on, attrs }">
                      <v-text-field v-bind="attrs" v-on="on" outlined dense readonly 
                      v-model="domain_holder"
                      :hide-details="domain_err == '' ? true : false"
                      :class="domain_err ? '' : 'general-custom-field'"
                      class="f14"
                      :error-messages="domain_err"
                      append-icon="mdi-menu-down"/>
                      
                    </template>
                    <v-card class="pa-2" flat>
                      <label class="secondary-2--text f12 poppins">FRONTEND DOMAIN NAME*</label>
                      <v-text-field 
                        v-model="form.domain_name" 
                        outlined 
                        dense  
                        class="f14"
                        :class="form_errors.domain_name ? '' : 'general-custom-field'" 
                        :error-change="form_errors.domain_name"
                        v-on:change="domain_change($event, 0)"
                        :readonly="type === 'view'"
                      />
                      <label class="secondary-2--text f12 poppins">API ENDPOINT *</label>
                      <v-text-field 
                        v-model="form.api_endpoint" 
                        outlined 
                        dense  
                        class="f14"
                        :class="form_errors.api_endpoint ? '' : 'general-custom-field'" 
                        :error-messages="form_errors.api_endpoint" 
                        v-on:change="domain_change($event, 1)"  
                        :readonly="type === 'view'"
                      />
                    </v-card>
                  </v-menu>
                </v-col>
                <v-col cols="6" class="px-2">
                  <label class="secondary-2--text f12 poppins">STATUS *</label>
                  <v-select
                    v-model="form.status"
                    :items="status"
                    item-value="value"
                    dense
                    outlined
                    :menu-props="{ top: true, offsetY: true }"
                    small-chips
                    class="f14"
                    :class="form_errors.status ? '' : 'general-custom-field'" 
                    :error-messages="form_errors.status"
                    :readonly="type === 'view'">
                    <template #selection="{ item }">
                      <v-chip small label color="primary">{{item.text}}</v-chip>
                    </template>
                  </v-select>
                </v-col>
                <v-col cols="12" class="px-2">
                  <label class="secondary-2--text f12 poppins">PROJECT DESCRIPTION</label>
                  <v-textarea v-model="form.tenant_description" 
                    outlined 
                    dense 
                    class="f14" 
                    rows="3"
                    :class="form_errors.tenant_description ? '' : 'general-custom-field'" 
                    :error-messages="form_errors.tenant_description"
                    :readonly="type === 'view'"
                  />
                </v-col>
                </v-row>
            </v-expansion-panel-content>
          </v-expansion-panel>

          <v-expansion-panel>
            <v-expansion-panel-header class="primary--text poppins fw600">TENANT CUSTOMIZATION</v-expansion-panel-header>
            <v-expansion-panel-content>
              <v-row no-gutters>
                <v-col cols="12" class="px-2 mb-2">
                  <section class="d-flex align-center justify-space-between ">
                    <label class="secondary-2--text f12 poppins">TENANT FAVICON</label>
                    <v-btn v-if="images_arr.favicon" dense color="danger" class="f12" text @click="deletePhoto('favicon')">
                      <v-icon small>mdi-close</v-icon>
                      REMOVE
                    </v-btn>
                  </section>
                  <div
                      class="bulk-border mt-2 d-flex flex-column align-center justify-center"
                      @dragover="dragover"
                      @dragleave="dragleave"
                      @drop="dropImg($event, 'favicon', 5)"
                    >
                    <v-img
                      contain
                      max-height="100"
                      v-if="images_arr.favicon"
                      :src="images_arr.favicon ? images_arr.favicon : require('../../../assets/default/logo.ico')"
                      class="d-flex align-center text-center mt-3"
                    />
                    <div class="d-flex align-center pa-3">
                      <v-icon color="primary-1" size="22"
                        >mdi-cloud-upload-outline</v-icon
                      >
                      <div class="secondary--text f12 ml-2 poppins">
                        <a
                          href="#"
                          @click="$refs.favicon[0].click()"
                          class="primary--text text-decoration-none"
                        >
                          Browse
                        </a>
                        or drag file here (ico)
                      </div>
                    </div>
                  </div>
                  <alert 
                    v-if="msg[5].show"
                    :show = "msg[5].show"
                    :text = "msg[5].text"
                    :type = "msg[5].type"
                  />
                </v-col>
                <v-col cols="12" class="px-2 mb-2">
                  <section class="d-flex align-center justify-space-between ">
                    <label class="secondary-2--text f12 poppins">TENANT LOGO ( VERTICAL )</label>
                    <v-btn v-if="images_arr.vertical_logo" dense color="danger" class="f12" text @click="deletePhoto('vertical_logo')">
                      <v-icon small>mdi-close</v-icon>
                      REMOVE
                    </v-btn>
                  </section>
                  <section v-if="type==='view'" class="d-flex align-center">
                    <v-img
                      contain
                      max-height="100"
                      :src="(this.form.images.length > 0 && this.form.images.findIndex((item) => item.image_key === 'vertical_logo') > -1) ? getImage(0) : `${asset_path}/favicon.png`"
                      class="my-3"
                    />
                  </section>
                  <div
                      class="bulk-border mt-2 d-flex flex-column align-center justify-center"
                      @dragover="dragover"
                      @dragleave="dragleave"
                      @drop="dropImg($event, 'vertical_logo', 0)"
                      v-else
                    >
                    <v-img
                      contain
                      max-height="100"
                      v-if="images_arr.vertical_logo"
                      :src="images_arr.vertical_logo ? images_arr.vertical_logo : `${asset_path}/favicon.png`"
                      class="d-flex align-center text-center mt-3"
                    />
                    <div class="d-flex align-center pa-3">
                      <v-icon color="primary-1" size="22"
                        >mdi-cloud-upload-outline</v-icon
                      >
                      <div class="secondary--text f12 ml-2 poppins">
                        <a
                          href="#"
                          @click="$refs.vertical_logo[0].click()"
                          class="primary--text text-decoration-none"
                        >
                          Browse
                        </a>
                        or drag file here (png, jpg)
                      </div>
                    </div>
                  </div>
                  <alert 
                    v-if="msg[0].show"
                    :show = "msg[0].show"
                    :text = "msg[0].text"
                    :type = "msg[0].type"
                  />
                </v-col>
                <v-col cols="12" class="px-2 mb-2">
                  <section class="d-flex align-center justify-space-between ">
                    <label class="secondary-2--text f12 poppins">TENANT LOGO ( HORIZONTAL )</label>
                    <v-btn v-if="images_arr.horizontal_logo" dense color="danger" class="f12" text @click="deletePhoto('horizontal_logo')">
                      <v-icon small>mdi-close</v-icon>
                      REMOVE
                    </v-btn>
                  </section>
                  <section class="d-flex align-center justify-center" v-if="type==='view'">
                    <v-img
                      max-width="200"
                      :src="(this.form.images.length > 0 && this.form.images.findIndex((item) => item.image_key === 'horizontal_logo') > -1) ? getImage(1) : `${asset_path}/logo.png`"
                      class="my-3"
                    />
                  </section>
                  <div
                      class="bulk-border mt-2 d-flex flex-column align-center justify-center"
                      @dragover="dragover"
                      @dragleave="dragleave"
                      @drop="dropImg($event, 'horizontal_logo', 1)"
                      v-else
                    >
                    <v-img
                      contain
                      max-width="200"
                      v-if="images_arr.horizontal_logo"
                      :src="images_arr.horizontal_logo ? images_arr.horizontal_logo : `${asset_path}/logo.png`"
                      class="d-flex align-center text-center mt-3"
                    />
                    <div class="d-flex align-center pa-3">
                      <v-icon color="primary-1" size="22"
                        >mdi-cloud-upload-outline</v-icon
                      >
                      <div class="secondary--text f12 ml-2 poppins">
                        <a
                          href="#"
                          @click="$refs.horizontal_logo[0].click()"
                          class="primary--text text-decoration-none"
                        >
                          Browse
                        </a>
                        or drag file here (png, jpg)
                      </div>
                    </div>
                  </div>
                  <alert 
                    v-if="msg[1].show"
                    :show = "msg[1].show"
                    :text = "msg[1].text"
                    :type = "msg[1].type"
                  />
                </v-col>
                <v-col cols="12" class="px-2 mb-2">
                  <section class="d-flex align-center justify-space-between ">
                    <label class="secondary-2--text f12 poppins">TENANT LOGIN LOGO</label>
                    <v-btn v-if="images_arr.login_logo" dense color="danger" text class="f12" @click="deletePhoto('login_logo')">
                      <v-icon small>mdi-close</v-icon>
                      REMOVE
                    </v-btn>
                  </section>
                  <section class="d-flex align-center justify-center" v-if="type==='view'">
                    <v-img
                      max-width="200"
                      :src="(this.form.images.length > 0 && this.form.images.findIndex((item) => item.image_key === 'login_logo') > -1) ? getImage(3) : `${asset_path}/logo.png`"
                      class="my-3"
                    />
                  </section>
                  <div
                      class="bulk-border mt-2 d-flex flex-column align-center justify-center"
                      @dragover="dragover"
                      @dragleave="dragleave"
                      @drop="dropImg($event, 'login_logo', 3)"
                      v-else
                    >
                    <v-img
                      contain
                      max-width="200"
                      v-if="images_arr.login_logo"
                      :src="images_arr.login_logo ? images_arr.login_logo : `${asset_path}/logo.png`"
                      class="d-flex align-center text-center mt-3"
                    />
                    <div class="d-flex align-center pa-3">
                      <v-icon color="primary-1" size="22"
                        >mdi-cloud-upload-outline</v-icon
                      >
                      <div class="secondary--text f12 ml-2 poppins">
                        <a
                          href="#"
                          @click="$refs.login_logo[0].click()"
                          class="primary--text text-decoration-none"
                        >
                          Browse
                        </a>
                        or drag file here (png, jpg)
                      </div>
                    </div>
                  </div>
                  <alert 
                      v-if="msg[3].show"
                      :show = "msg[3].show"
                      :text = "msg[3].text"
                      :type = "msg[3].type"
                  />
                </v-col>
                <v-col cols="12" class="px-2 mt-5">
                  <v-row no-gutters>
                    <v-col cols="6">
                      <v-sheet class="custom-border d-flex justify-center py-5" :color="form.navigation_banner_color">
                        <v-avatar tile  width="115">
                          <v-img :src="images_arr.horizontal_logo ? images_arr.horizontal_logo : `${asset_path}/logo.png`"/>
                        </v-avatar>
                      </v-sheet>
                    </v-col>
                    <v-col cols="6" class="px-3">
                      <label class="secondary-2--text f12 poppins">NAVIGATION BANNER COLOR</label>
                        <v-text-field
                          v-model="form.navigation_banner_color"
                          outlined
                          dense
                          class="f14 general-custom-field"
                          :readonly="type === 'view'"
                        >
                          <template v-slot:append-outer>
                            <v-menu right offset-x :close-on-content-click="colorpicker1">
                              <template v-slot:activator="{ on, attrs }">
                                <v-btn
                                v-bind="attrs"
                                v-on="on"
                                icon
                                :disabled="type === 'view'"
                              >
                                <v-icon>
                                  mdi-palette
                                </v-icon>
                              </v-btn>
                              </template>
                                <v-sheet>
                                  <v-color-picker
                                    dot-size="22"
                                    hide-mode-switch
                                    mode="hexa"
                                    swatches-max-height="100"
                                    v-model="form.navigation_banner_color"
                                  ></v-color-picker>
                                </v-sheet>
                            </v-menu>
                          </template>
                        </v-text-field>
                    </v-col>
                  </v-row>
                </v-col>
                <v-col cols="12" class="px-2 mt-5">
                  <v-row no-gutters>
                    <v-col cols="6">
                      <v-sheet class="custom-border">
                        <v-list dense :color="form.navigation_background_color">
                          <v-list-item-group v-model="selected">
                            <v-list-item disabled>
                              <v-list-item-icon>
                                <v-icon :style="{ color: form.navigation_link_inactive_color }">mdi-view-dashboard</v-icon>
                              </v-list-item-icon>
                              <v-list-item-content>
                                <v-list-item-title :style="{ color: form.navigation_link_inactive_color }" > Dashboard </v-list-item-title>
                              </v-list-item-content>
                            </v-list-item>
                            <v-list-item  
                              :style="{ background: form.navigation_link_color.concat('33'), color: form.navigation_link_color, borderRight: '5px solid'}">
                              <v-list-item-icon>
                                <v-icon :color="form.navigation_text_icon_color">mdi-book-open-outline</v-icon>
                              </v-list-item-icon>
                              <v-list-item-title
                                :style="{ color: form.navigation_text_icon_color }">Courses</v-list-item-title>
                            </v-list-item>
                          </v-list-item-group>
                        </v-list>
                      </v-sheet>
                    </v-col>
                    <v-col cols="6" class="px-3 mt-1">
                      <label class="secondary-2--text f12 poppins">NAVIGATION BACKGROUND COLOR</label>
                      <v-text-field
                        v-model="form.navigation_background_color"
                        outlined
                        dense
                        class="f14 general-custom-field"
                        :readonly="type === 'view'"
                      >
                        <template v-slot:append-outer>
                          <v-menu right offset-x :close-on-content-click="colorpicker2">
                            <template v-slot:activator="{ on, attrs }">
                              <v-btn
                              v-bind="attrs"
                              v-on="on"
                              icon
                              :disabled="type === 'view'"
                            >
                              <v-icon>
                                mdi-palette
                              </v-icon>
                            </v-btn>
                            </template>
                              <v-sheet>
                                <v-color-picker
                                  dot-size="22"
                                  hide-mode-switch
                                  mode="hexa"
                                  swatches-max-height="100"
                                  v-model="form.navigation_background_color"
                                ></v-color-picker>
                              </v-sheet>
                          </v-menu>
                        </template>
                      </v-text-field>
                      <label class="secondary-2--text f12 poppins">NAVIGATION LINK COLOR</label>
                      <v-text-field
                        v-model="form.navigation_link_color"
                        outlined
                        dense
                        class="f14 general-custom-field"
                        :readonly="type === 'view'"
                      >
                        <template v-slot:append-outer>
                          <v-menu right offset-x :close-on-content-click="colorpicker3">
                            <template v-slot:activator="{ on, attrs }">
                              <v-btn
                              v-bind="attrs"
                              v-on="on"
                              icon
                              :disabled="type === 'view'"
                            >
                              <v-icon>
                                mdi-palette
                              </v-icon>
                            </v-btn>
                            </template>
                              <v-sheet>
                                <v-color-picker
                                  dot-size="22"
                                  hide-mode-switch
                                  mode="hexa"
                                  swatches-max-height="100"
                                  v-model="form.navigation_link_color"
                                ></v-color-picker>
                              </v-sheet>
                          </v-menu>
                        </template>
                      </v-text-field>
                      <label class="secondary-2--text f12 poppins">NAVIGATION TEXT & ICON COLOR</label>
                      <v-text-field
                        v-model="form.navigation_text_icon_color"
                        outlined
                        dense
                        class="f14 general-custom-field"
                        :readonly="type === 'view'"
                      >
                        <template v-slot:append-outer>
                          <v-menu right offset-x :close-on-content-click="colorpicker4">
                            <template v-slot:activator="{ on, attrs }">
                              <v-btn
                              v-bind="attrs"
                              v-on="on"
                              icon
                              :disabled="type === 'view'"
                            >
                              <v-icon>
                                mdi-palette
                              </v-icon>
                            </v-btn>
                            </template>
                              <v-sheet>
                                <v-color-picker
                                  dot-size="22"
                                  hide-mode-switch
                                  mode="hexa"
                                  swatches-max-height="100"
                                  v-model="form.navigation_text_icon_color"
                                ></v-color-picker>
                              </v-sheet>
                          </v-menu>
                        </template>
                      </v-text-field>
                      <label class="secondary-2--text f12 poppins">NAVIGATION LINK INACTIVE COLOR</label>
                      <v-text-field
                        v-model="form.navigation_link_inactive_color"
                        outlined
                        dense
                        class="f14 general-custom-field"
                        :readonly="type === 'view'"
                      >
                        <template v-slot:append-outer>
                          <v-menu right offset-x :close-on-content-click="colorpicker11">
                            <template v-slot:activator="{ on, attrs }">
                              <v-btn
                              v-bind="attrs"
                              v-on="on"
                              icon
                              :disabled="type === 'view'"
                            >
                              <v-icon>
                                mdi-palette
                              </v-icon>
                            </v-btn>
                            </template>
                              <v-sheet>
                                <v-color-picker
                                  dot-size="22"
                                  hide-mode-switch
                                  mode="hexa"
                                  swatches-max-height="100"
                                  v-model="form.navigation_link_inactive_color"
                                ></v-color-picker>
                              </v-sheet>
                          </v-menu>
                        </template>
                      </v-text-field>
                    </v-col>
                  </v-row>
                </v-col>
                <v-col cols="12" class="px-2 mt-5">
                  <v-row>
                    <v-col cols="6">
                      <v-sheet class="custom-border" >
                        <v-sheet class="custom-border" width="60">
                          <v-list dense :color="form.mini_navigation_background_color">
                            <v-list-item-group v-model="selected_mini">
                              <v-list-item disabled>
                                <v-list-item-icon>
                                  <v-icon :color="form.mini_navigation_inactive_link_color">mdi-view-dashboard</v-icon>
                                </v-list-item-icon>
                              </v-list-item>
                              <v-list-item  
                                :style="{ background: form.mini_navigation_link_color.concat('33'), color: form.mini_navigation_link_color}">
                                <v-list-item-icon>
                                  <v-icon :color="form.mini_navigation_icon_color">mdi-book-open-outline</v-icon>
                                </v-list-item-icon>
                              </v-list-item>
                            </v-list-item-group>
                          </v-list>
                        </v-sheet>
                      </v-sheet>
                    </v-col>
                    <v-col cols="6">
                      <label class="secondary-2--text f12 poppins">MINI NAVIGATION BACKGROUND COLOR</label>
                        <v-text-field
                          v-model="form.mini_navigation_background_color"
                          outlined
                          dense
                          class="f14 general-custom-field"
                          :readonly="type === 'view'"
                        >
                          <template v-slot:append-outer>
                            <v-menu right offset-x :close-on-content-click="colorpicker5">
                              <template v-slot:activator="{ on, attrs }">
                                <v-btn
                                v-bind="attrs"
                                v-on="on"
                                icon
                                :disabled="type === 'view'"
                              >
                                <v-icon>
                                  mdi-palette
                                </v-icon>
                              </v-btn>
                              </template>
                                <v-sheet>
                                  <v-color-picker
                                    dot-size="22"
                                    hide-mode-switch
                                    mode="hexa"
                                    swatches-max-height="100"
                                    v-model="form.mini_navigation_background_color"
                                  ></v-color-picker>
                                </v-sheet>
                            </v-menu>
                          </template>
                        </v-text-field>
                        <label class="secondary-2--text f12 poppins">MINI NAVIGATION LINK COLOR</label>
                        <v-text-field
                          v-model="form.mini_navigation_link_color"
                          outlined
                          dense
                          class="f14 general-custom-field"
                          :readonly="type === 'view'"
                        >
                          <template v-slot:append-outer>
                            <v-menu right offset-x :close-on-content-click="colorpicker6">
                              <template v-slot:activator="{ on, attrs }">
                                <v-btn
                                v-bind="attrs"
                                v-on="on"
                                icon
                                :disabled="type === 'view'"
                              >
                                <v-icon>
                                  mdi-palette
                                </v-icon>
                              </v-btn>
                              </template>
                                <v-sheet>
                                  <v-color-picker
                                    dot-size="22"
                                    hide-mode-switch
                                    mode="hexa"
                                    swatches-max-height="100"
                                    v-model="form.mini_navigation_link_color"
                                  ></v-color-picker>
                                </v-sheet>
                            </v-menu>
                          </template>
                        </v-text-field>
                        <label class="secondary-2--text f12 poppins">MINI NAVIGATION ICON COLOR</label>
                        <v-text-field
                          v-model="form.mini_navigation_icon_color"
                          outlined
                          dense
                          class="f14 general-custom-field"
                          :readonly="type === 'view'"
                        >
                          <template v-slot:append-outer>
                            <v-menu right offset-x :close-on-content-click="colorpicker7">
                              <template v-slot:activator="{ on, attrs }">
                                <v-btn
                                v-bind="attrs"
                                v-on="on"
                                icon
                                :disabled="type === 'view'"
                              >
                                <v-icon>
                                  mdi-palette
                                </v-icon>
                              </v-btn>
                              </template>
                                <v-sheet>
                                  <v-color-picker
                                    dot-size="22"
                                    hide-mode-switch
                                    mode="hexa"
                                    swatches-max-height="100"
                                    v-model="form.mini_navigation_icon_color"
                                  ></v-color-picker>
                                </v-sheet>
                            </v-menu>
                          </template>
                        </v-text-field>
                        <label class="secondary-2--text f12 poppins">MINI NAVIGATION INACTIVE LINK COLOR</label>
                        <v-text-field
                          v-model="form.mini_navigation_inactive_link_color"
                          outlined
                          dense
                          class="f14 general-custom-field"
                          :readonly="type === 'view'"
                        >
                          <template v-slot:append-outer>
                            <v-menu right offset-x :close-on-content-click="colorpicker12">
                              <template v-slot:activator="{ on, attrs }">
                                <v-btn
                                v-bind="attrs"
                                v-on="on"
                                icon
                                :disabled="type === 'view'"
                              >
                                <v-icon>
                                  mdi-palette
                                </v-icon>
                              </v-btn>
                              </template>
                                <v-sheet>
                                  <v-color-picker
                                    dot-size="22"
                                    hide-mode-switch
                                    mode="hexa"
                                    swatches-max-height="100"
                                    v-model="form.mini_navigation_inactive_link_color"
                                  ></v-color-picker>
                                </v-sheet>
                            </v-menu>
                          </template>
                        </v-text-field>
                      </v-col>
                  </v-row>
                </v-col>
                <v-col cols="12" class="px-2 mt-5">
                  <v-row no-gutters>
                    <v-col cols="6">
                      <v-sheet class="custom-border" color="pink">
                        <v-row no-gutters>
                          <v-col cols="6">
                            <v-skeleton-loader
                              boilerplate
                              type="article"
                              tile
                              height="130" 
                            ></v-skeleton-loader>
                          </v-col>
                          <v-col cols="6">
                            <v-sheet height="130" :color="form.sidebar_color">
                              <v-carousel hide-delimiters cycle :show-arrows="false" height="130">
                                <v-carousel-item>
                                  <v-sheet
                                    color="transparent"
                                    height="130"
                                    class="d-flex align-center justify-center"
                                  >
                                  <v-img
                                      contain
                                      max-height="30"
                                      :src="images_arr.sidebar_logo ? images_arr.sidebar_logo : `${asset_path}/favicon-white.png`"
                                    />
                                  </v-sheet>
                                </v-carousel-item>
                                <v-carousel-item>
                                  <v-sheet
                                    color="transparent"
                                    height="130"
                                    class="d-flex align-center justify-center"
                                  >
                                      <v-icon color="#fff">mdi-account-tie-outline</v-icon>
                                  </v-sheet>
                                </v-carousel-item>
                                <v-carousel-item>
                                  <v-sheet
                                    color="transparent"
                                    height="130"
                                    class="d-flex align-center justify-center"
                                  >
                                      <v-icon color="#fff">mdi-shield-key-outline</v-icon>
                                  </v-sheet>
                                </v-carousel-item>
                              </v-carousel>
                            </v-sheet>
                          </v-col>
                        </v-row>
                      </v-sheet>
                    </v-col>
                    <v-col cols="6" class="px-3">
                      <label class="secondary-2--text f12 poppins">SIDEBAR COLOR</label>
                      <v-text-field
                        v-model="form.sidebar_color"
                        outlined
                        dense
                        class="f14 general-custom-field"
                        :readonly="type === 'view'"
                      >
                        <template v-slot:append-outer>
                          <v-menu right offset-x :close-on-content-click="colorpicker5">
                            <template v-slot:activator="{ on, attrs }">
                              <v-btn
                              v-bind="attrs"
                              v-on="on"
                              icon
                              :disabled="type === 'view'"
                            >
                              <v-icon>
                                mdi-palette
                              </v-icon>
                            </v-btn>
                            </template>
                              <v-sheet>
                                <v-color-picker
                                  dot-size="22"
                                  hide-mode-switch
                                  mode="hexa"
                                  swatches-max-height="100"
                                  v-model="form.sidebar_color"
                                ></v-color-picker>
                              </v-sheet>
                          </v-menu>
                        </template>
                      </v-text-field>
                      <label class="secondary-2--text f12 poppins">SIDEBAR LOGO</label>
                      <section class="d-flex align-center justify-center" v-if="type==='view'">
                        <v-img
                          max-width="200"
                          :src="(this.form.images.length > 0 && this.form.images.findIndex((item) => item.image_key === 'sidebar_logo') > -1) ? getImage(2) : `${asset_path}/favicon-white.png`"
                          class="my-3"
                        />
                      </section>
                      <div
                          v-else
                          class="bulk-border mt-2 d-flex flex-column align-center justify-center"
                          @dragover="dragover"
                          @dragleave="dragleave"
                          @drop="dropImg($event, 'sidebar_logo', 2)"
                        >
                        <v-img
                          contain
                          max-height="100"
                          :src="images_arr.sidebar_logo ? images_arr.sidebar_logo : `${asset_path}/favicon-white.png`"
                          class="d-flex align-center text-center mt-3"
                        />
                        <div class="d-flex align-center pa-3">
                          <v-icon color="primary-1" size="22"
                            >mdi-cloud-upload-outline</v-icon
                          >
                          <div class="secondary--text f12 ml-2 poppins">
                            <a
                              href="#"
                              @click="$refs.sidebar_logo[0].click()"
                              class="primary--text text-decoration-none"
                            >
                              Browse
                            </a>
                            or drag file here (png, jpg)
                          </div>
                        </div>
                      </div>
                      <alert 
                          v-if="msg[2].show"
                          :show = "msg[2].show"
                          :text = "msg[2].text"
                          :type = "msg[2].type"
                      />
                      <!-- <label class="secondary-2--text f12 poppins">SIDEBAR PHOTO</label>
                      <section class="d-flex align-center justify-center" v-if="type==='view'">
                        <v-img
                          max-width="200"
                          :src="(this.form.images.length > 0 && this.form.images.findIndex((item) => item.image_key === 'sidebar_photo') > -1) && getImage(11)"
                          class="my-3"
                        />
                      </section>
                      <div
                          v-else
                          class="bulk-border mt-2 d-flex flex-column align-center justify-center"
                          @dragover="dragover"
                          @dragleave="dragleave"
                          @drop="dropImg($event, 'sidebar_photo', 4)"
                        >
                        <v-img
                          contain
                          max-height="100"
                          :src="images_arr.sidebar_photo && images_arr.sidebar_photo"
                          class="d-flex align-center text-center mt-3"
                        />
                        <div class="d-flex align-center pa-3">
                          <v-icon color="primary-1" size="22"
                            >mdi-cloud-upload-outline</v-icon
                          >
                          <div class="secondary--text f12 ml-2 poppins">
                            <a
                              href="#"
                              @click="$refs.sidebar_photo[0].click()"
                              class="primary--text text-decoration-none"
                            >
                              Browse
                            </a>
                            or drag file here (png, jpg)
                          </div>
                        </div>
                      </div>
                      <alert 
                          v-if="msg[4].show"
                          :show = "msg[4].show"
                          :text = "msg[4].text"
                          :type = "msg[4].type"
                      /> -->
                    </v-col>
                  </v-row>
                </v-col>
                <v-col cols="12" class="px-2 mt-5">
                  <label class="secondary-2--text f12 poppins">CAROUSEL PHOTO</label>
                  <div width="400" class=" pa-2 d-flex overflow-y-auto scroller mb-1">
                    <section v-for="(item, index) in images_arr" :key="index" v-if="index.includes('side_carousel') && images_arr[index]" class="d-flex flex-column ma-1" style="width: 110px;">
                      <v-btn @click="deletePhoto(index, 4)" fab dense x-small color="secondary-2" class="ml-auto" style="z-index: 2; position: relative; top: 15px;">
                        <v-icon small color="white"> mdi-close</v-icon>
                      </v-btn>
                      <v-img 
                        :src="images_arr[index]"
                        height="100"
                        width="100"
                        contain
                        class="rounded secondary-5 "/>
                    </section>
                  </div>
                  <div
                      class="bulk-border mt-2 d-flex flex-column align-center justify-center"
                      @dragover="dragover"
                      @dragleave="dragleave"
                      @drop="dropImg($event, 'side_carousel', 4)"
                    >
                    <div class="d-flex align-center pa-3">
                      <v-icon color="primary-1" size="22"
                        >mdi-cloud-upload-outline</v-icon
                      >
                      <div class="secondary--text f12 ml-2 poppins">
                        <a
                          href="#"
                          @click="$refs.side_carousel[0].click()"
                          class="primary--text text-decoration-none"
                        >
                          Browse
                        </a>
                        or drag file here (png, jpg)
                      </div>
                    </div>
                  </div>
                  <alert 
                    v-if="msg[4].show"
                    :show = "msg[4].show"
                    :text = "msg[4].text"
                    :type = "msg[4].type"
                  />
                </v-col>
                <v-col cols="12" class="px-2 mt-5">
                  <v-row >
                    <v-col cols="6" class="d-flex flex-row justify-space-between align-center">
                      <v-btn :color="form.primary_buttons_color" class="poppins">
                        BLOCK
                      </v-btn>
                      <v-btn outlined :color="form.primary_buttons_color" class="poppins">
                        OUTLINED
                      </v-btn>
                    </v-col>
                    <v-col cols="6">
                      <label class="secondary-2--text f12 poppins">PRIMARY BUTTONS COLOR</label>
                      <v-text-field
                        v-model="form.primary_buttons_color"
                        outlined
                        dense
                        class="f14 general-custom-field"
                        :readonly="type === 'view'"
                      >
                        <template v-slot:append-outer>
                          <v-menu right offset-x :close-on-content-click="colorpicker9">
                            <template v-slot:activator="{ on, attrs }">
                              <v-btn
                              v-bind="attrs"
                              v-on="on"
                              icon
                              :disabled="type === 'view'"
                            >
                              <v-icon>
                                mdi-palette
                              </v-icon>
                            </v-btn>
                            </template>
                              <v-sheet>
                                <v-color-picker
                                  dot-size="22"
                                  hide-mode-switch
                                  mode="hexa"
                                  swatches-max-height="100"
                                  v-model="form.primary_buttons_color"
                                ></v-color-picker>
                              </v-sheet>
                          </v-menu>
                        </template>
                      </v-text-field>
                    </v-col>
                  </v-row>
                </v-col>
              </v-row>
            </v-expansion-panel-content>
          </v-expansion-panel>

          <v-expansion-panel>
            <v-expansion-panel-header class="primary--text poppins fw600">CONTACT DETAILS</v-expansion-panel-header>
            <v-expansion-panel-content>
              <v-row no-gutters >
                <v-col cols="6" class="px-2">
                  <label class="secondary-2--text f12 poppins">CONTACT PERSON *</label>
                  <v-text-field v-model="form.info.contact_person" 
                    outlined 
                    dense  
                    class="f14"
                    :class="form_errors.contact_person ? '' : 'general-custom-field'" 
                    :error-messages="form_errors.contact_person"
                    :readonly="type === 'view'"
                  />
                </v-col>
                <v-col cols="6" class="px-2">
                  <label class="secondary-2--text f12 poppins">EMAIL *</label>
                  <v-text-field v-model="form.info.email" 
                  type="email"
                    outlined 
                    dense  
                    class="f14"
                    :class="form_errors.email ? '' : 'general-custom-field'" 
                    :error-messages="form_errors.email"
                    :readonly="type === 'view'"
                  />
                </v-col>
              <v-col cols="6" class="px-2">
                  <label class="secondary-2--text f12 poppins">MOBILE NUMBER *</label>
                  <v-text-field v-model="form.info.mobile_no" 
                    outlined 
                    dense  
                    class="f14"
                    :class="form_errors.mobile_no ? '' : 'general-custom-field'" 
                    :error-messages="form_errors.mobile_no"
                    :readonly="type === 'view'"
                  />
                </v-col>
                <v-col cols="6" class="px-2">
                  <label class="secondary-2--text f12 poppins">TEL. NUMBER</label>
                  <v-text-field v-model="form.info.tel_no" 
                    outlined 
                    dense  
                    class="general-custom-field f14"
                    :readonly="type === 'view'"
                  />
                </v-col>
              </v-row>
            </v-expansion-panel-content>
          </v-expansion-panel>
        </v-expansion-panels>
        <section v-for="(name, i) in image_keys">
          <input
            type="file"
            :ref="`${name}`"
            class="d-none"
            :multiple="name === 'side_carousel'"
            accept=".jpeg,.png,.jpg"
            v-on:change="acceptInput(i)"
            v-if="name !== 'favicon'"
          />
          <input
            type="file"
            :ref="`${name}`"
            class="d-none"
            accept=".ico"
            v-on:change="acceptInput(i)"
            v-if="name === 'favicon'"
          />
        </section>
      </v-form>
      <v-card-actions class="d-flex justify-end mx-6 pb-5 mt-" v-if="type==='edit'">
        <!-- <v-btn text color="secondary-2" class="text-capitalize poppins f14" @click="cancel">Cancel</v-btn>  -->
        <v-btn color="primary" width="100" class="text-capitalize poppins f14" @click="updateTenant">
          <v-progress-circular
            size="16"
            width="2"
            indeterminate
            color="#fff"
            v-if="saving"
          />
          <div v-else>
            Save
          </div>
          
        </v-btn> 
      </v-card-actions>
    </v-card>
    
  </v-dialog>
</template>

<script>
import { mapActions, mapState, mapMutations } from 'vuex';
export default {
  props: ['dialog', 'form', 'type'],
  data: () => ({
    status: [
      { text: 'ACTIVE', value: 'active' },
      { text: 'INACTIVE', value: 'inactive' },
    ],
    types: [
      { text: 'CORPORATE',value: 'corporate' },
      { text: 'GENERAL EDUCATION',value: 'general_education' },
      { text: 'NGO',value: 'ngo' }
    ],
    radioGroup: 1,
    form_errors: [],
    domain_err: '',
    domain_arr: [[],[]],
    domain_holder: '',
    saving: false,
    images_arr: {
      favicon: null,
      vertical_logo: null,
      horizontal_logo: null,
      sidebar_logo: null,
      login_logo: null,
    },
    colors: {
      navigation_background_color: '#ffffff',
      navigation_banner_color: '#ffffff',
      navigation_link_color: '#3966D8',
      sidebar_color: '#3966D8',
      navigation_text_icon_color: '#3966D8',
      mini_navigation_background_color: '#3966D8',
      mini_navigation_link_color: '#3966D8',
      mini_navigation_icon_color: '#ffffff',
      primary_buttons_color: '#3966D8',
      navigation_link_inactive_color: '#828282',
      mini_navigation_inactive_link_color: '#F2F2F2'
    },
    colorpicker1: false,
    colorpicker2: false,
    colorpicker3: false,
    colorpicker4: false,
    colorpicker5: false,
    colorpicker6: false,
    colorpicker7: false,
    colorpicker8: false,
    colorpicker9: false,
    colorpicker10: false,
    colorpicker11: false,
    colorpicker12: false,
    selected: 1,
    selected_mini: 1,
    msg: [],
    asset_path: `${process.env.VUE_APP_ASSET_PATH}`,
    image_keys: ['vertical_logo','horizontal_logo','sidebar_logo','login_logo','side_carousel', 'favicon']
  }),
  computed: {
    ...mapState({
      errors: (state) => state.errors
    }),
  },
  mounted(){
    this.msg = new Array(6).fill({
      show: false,
      type: 'success',
      text: ''
    });
  },
  methods: {
    ...mapActions('superad',['updateTenantAction', 'removeTenantPhotoAction']),
    ...mapMutations(['alertMutation']),

    deletePhoto(key, index){
      this.$set(this.msg, index, {
        show: false,
        type: '',
        text: ''
      });

      this.removeTenantPhotoAction({id: this.form.id, image_key: key}).then(()=>{
        this.images_arr[key] = null
        this.$set(this.msg, index, {
            show: true,
            type: 'success',
            text: 'Photo successfully removed'
          })
        setTimeout(()=>{
          this.$set(this.msg, index, {
            show: false,
            type: '',
            text: ''
          })
        }, 3000)
      }).catch(() => {
        this.$set(this.msg, index, {
          show: true,
          type: 'error',
          text: 'Something went wrong.'
        });
      })
    },

    updateTenant() {
      this.saving= true
      let _form = new FormData();

      _form.append("_method", 'PUT');
      _form.append("tenant_name", this.form.tenant_name);
      _form.append("organization", this.form.info.organization);
      _form.append("domain_name", this.form.domain_name);
      _form.append("sub_domain", this.form.api_endpoint);
      _form.append("status", this.form.status);
      _form.append("tenant_description", this.form.tenant_description);
      _form.append("contact_person", this.form.info.contact_person);
      _form.append("email", this.form.info.email);
      _form.append("mobile_no", this.form.info.mobile_no);
      _form.append("tel_no", this.form.info.tel_no);

      if(this.form.images.length > 0) {
        this.form.images.forEach((_item, i) => {
          if(_item.image) {
            _form.append(`image_id[${i}]`, _item.id ? _item.id : -1)
            _form.append(`image_key[${i}]`, _item.image_key)
            _form.append(`images[${i}]`, _item.image)
          }
        })
      }

      Object.keys(this.colors).forEach(_color => {
        _form.append(_color, this.form[_color])
      })

      this.updateTenantAction({id: this.form.id, formm: _form}).then(() => {
        this.saving= false
        this.alertMutation({
          show: true,
          text: 'You have successfully updated a tenant',
          type: "success"
        })
        this.$emit('close')
      }).catch(e => {
        this.saving= false
        this.form_errors = e
      })
    
    },

    resetForm(){
      this.$refs.favicon[0].value = '';
      this.$refs.vertical_logo[0].value = '';
      this.$refs.horizontal_logo[0].value = '';
      this.$refs.sidebar_logo[0].value = '';
      this.$refs.login_logo[0].value = '';
      this.$refs.side_carousel[0].value = '';
      this.images_arr = {
        favicon: null,
        vertical_logo: null,
        horizontal_logo: null,
        sidebar_logo: null,
        login_logo: null,
      }
      this.form.images = []
      this.form_errors = [];
      this.domain_err = [];
      this.saving = false;
      this.$refs.tenantForm.reset()
    },

    domain_change(val, index) {
      this.domain_arr[index] = val
      this.domain_holder = this.domain_arr.toString()
    },

    cancel() {
      this.$emit('close')
    },

    dragover(e) {
      e.preventDefault();
      e.currentTarget.classList.add("bulk-border-drag");
    },

    dragleave(e) {
      e.preventDefault();
      e.currentTarget.classList.remove("bulk-border-drag");
    },

    dropImg(e, name, index) {
      e.preventDefault();
      this.$refs[name][0].files = e.dataTransfer.files;
      this.acceptInput(index);
      e.currentTarget.classList.remove("bulk-border-drag");
    },

    getImage(index){
      return this.form.images.find(_item => _item.image_key === this.image_keys[index]).url
    },

    acceptInput(index) {
      if(this.$refs[this.image_keys[index]][0].files[0]) {
        let _index = this.form.images.findIndex((item) => item.image_key === this.image_keys[index])

        if(this.image_keys[index] === 'favicon' && !['image/x-icon', 'image/vnd.microsoft.icon'].includes(this.$refs[this.image_keys[index]][0].files[0].type)){
          this.$set(this.msg, index, {
            show: true,
            type: 'error',
            text: 'File not supported'
          });

          this.$refs[this.image_keys[index]].value = ''

        } else if(this.image_keys[index] !== 'favicon' && !['image/png', 'image/jpeg'].includes(this.$refs[this.image_keys[index]][0].files[0].type)) {

          this.$set(this.msg, index, {
            show: true,
            type: 'error',
            text: 'File not supported'
          });

          this.$refs[this.image_keys[index]].value = ''

        } else {

          this.$set(this.msg, index, {
            show: false,
            type: 'success',
            text: ''
          });

          if(_index > -1) {
            this.form.images[_index].image = this.$refs[this.image_keys[index]][0].files[0]
          } else {
            if(index === 4) {
              let _arr = this.form.images.filter((item) => item.image_key.includes('side_carousel'))
              let _index = _arr.length === 0 ? 0 : parseInt(_arr[_arr.length - 1]['image_key'].replace('side_carousel_', ''))
              this.$refs[this.image_keys[index]][0].files.forEach(file => {
              const reader = new FileReader();
              const promise = new Promise((resolve, reject) => {
                reader.onload = (e) => {
                  resolve(e.target.result);
                };
                reader.onerror = (e) => {
                  reject(e);
                };
              });

              promise
                .then((result) => {
                  let _arr = { ...this.images_arr, [`side_carousel_${++_index}`]: result}
                  this.images_arr = _arr;
                  this.form.images.push({
                    id: `side_carousel_${_index}`,
                    image_key: `side_carousel_${_index}`,
                    image: file
                  });
                })
                .catch((error) => {
                  console.error('Error reading file:', error);
                });

              reader.readAsDataURL(file);
            });

              this.$refs[this.image_keys[index]].value = ''
            } else {
              this.form.images.push({
                image_key: this.image_keys[index],
                image: this.$refs[this.image_keys[index]][0].files[0]
              })

              const reader = new FileReader()
              reader.onload = (e) => {
                this.images_arr[this.image_keys[index]] = e.target.result
                this.$refs[this.image_keys[index]].value = ''
              }
              reader.readAsDataURL(this.$refs[this.image_keys[index]][0].files[0])
            } 
          }
        }
      }
    }
  },
  watch: {
    errors(val) {
      this.domain_err= ''
      if(val) {
        if(this.form_errors.domain_name || this.form_errors.sub_domain) this.domain_err='The domain field is required'
        this.saving= false
      } 
    },
    dialog(val) {
      if(val) {
        this.domain_arr[0] = this.form.domain_name
        this.domain_arr[1] = this.form.end_point
        this.domain_holder = this.domain_arr.toString()
        this.image_keys.forEach(_item => {
          if(_item === 'side_carousel') {
            let _arr = {}
            this.form.images.filter(_img => _img.image_key.includes('side_carousel')).forEach(_img => {
              _arr[_img.image_key] = _img.url
            })

            this.images_arr = {...this.images_arr, ..._arr}
          } else {
            this.images_arr[_item] = this.form.images.find(_img => _img.image_key === _item) ? this.form.images.find(_img => _img.image_key === _item).url : null
          }
        })

        Object.keys(this.colors).forEach(_color => {
          if(!this.form.hasOwnProperty(_color)) {
            this.form[_color] = this.colors[_color]
          }
        })
      }
    }
  },
}
</script>
