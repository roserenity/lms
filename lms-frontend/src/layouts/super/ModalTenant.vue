<template>
  <v-dialog v-model="dialog" persistent max-width="550">
    <v-card>
      <div class="poppins f18 fw600 d-flex px-2 py-3">
        <div class="mx-auto primary--text">Add Tenant</div>
        <v-btn icon small @click="cancel">
          <v-icon size="18">mdi-close</v-icon>
        </v-btn>
      </div>
      <v-form ref="tenantForm">
        <v-card-text>
          <div class="primary--text poppins fw600 ma-2">GENERAL INFO</div>
          <v-row no-gutters>
            <v-col cols="12" md="6" class="px-2">
              <label class="secondary-2--text f12 poppins">TENANT NAME *</label>
              <v-text-field
                v-model="form.tenant_name"
                outlined
                dense
                class="f14"
                :class="errs.tenant_name ? '' : 'general-custom-field'"
                :error-messages="errs.tenant_name"
              />
            </v-col>
            <v-col cols="12" md="6" class="px-2">
              <label class="secondary-2--text f12 poppins">TYPE OF ORGANIZATION *</label>
              <v-select
                v-model="form.organization"
                :items="types"
                item-value="value"
                dense
                outlined
                :menu-props="{ top: true, offsetY: true }"
                small-chips
                :class="errs.organization ? '' : 'general-custom-field'"
                :error-messages="errs.organization"
                class="f14"
              >
                <template #selection="{ item }">
                  <v-chip small label color="primary">{{ item.text }}</v-chip>
                </template>
              </v-select>
            </v-col>
            <v-col cols="12" md="6" class="px-2">
              <label class="secondary-2--text f12 poppins">DOMAIN NAME *</label>

              <v-menu
                offset-y
                eager
                :close-on-content-click="false"
                content-class="elevation-2"
              >
                <template v-slot:activator="{ on, attrs }">
                  <v-text-field
                    v-bind="attrs"
                    v-on="on"
                    outlined
                    dense
                    readonly
                    v-model="domain_holder"
                    :class="domain_err ? '' : 'general-custom-field'"
                    class="f14"
                    :error-messages="domain_err"
                    append-icon="mdi-menu-down"
                  />
                </template>
                <v-card class="pa-2" flat>
                  <label class="secondary-2--text f12 poppins"
                    >FRONTEND DOMAIN NAME*</label
                  >
                  <v-text-field
                    v-model="form.domain_name"
                    outlined
                    dense
                    class="f14"
                    placeholder="https://sample.com"
                    :class="errs.domain_name ? '' : 'general-custom-field'"
                    :error-messages="errs.domain_name"
                    v-on:change="domain_change($event, 0)"
                    @blur="leave($event)"
                  />
                  <label class="secondary-2--text f12 poppins">API ENDPOINT *</label>
                  <v-text-field
                    v-model="sub"
                    readonly
                    outlined
                    dense
                    class="f14"
                    :class="errs.sub_domain ? '' : 'general-custom-field'"
                    :error-messages="errs.sub_domain"
                    v-on:change="domain_change($event, 1)"
                  />
                </v-card>
              </v-menu>
            </v-col>
            <v-col cols="12" md="6" class="px-2">
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
                :class="errs.status ? '' : 'general-custom-field'"
                :error-messages="errs.status"
              >
                <template #selection="{ item }">
                  <v-chip small label color="primary">{{ item.text }}</v-chip>
                </template>
              </v-select>
            </v-col>
            <v-col cols="12" class="px-2">
              <label class="secondary-2--text f12 poppins">PROJECT DESCRIPTION *</label>
              <v-textarea
                v-model="form.tenant_description"
                outlined
                dense
                class="f14"
                rows="3"
                :class="errs.tenant_description ? '' : 'general-custom-field'"
                :error-messages="errs.tenant_description"
              />
            </v-col>
          </v-row>
          </v-card-text>
          <v-divider />
          <v-card-text>
            <div class="primary--text poppins fw600 ma-2">TENANT CUSTOMIZATION</div>
            <v-row no-gutters>
              <v-col cols="12" class="px-2">
                <label class="secondary-2--text f12 poppins">TENANT FAVICON</label>
                <div
                  class="bulk-border mb-2 d-flex flex-column align-center justify-center"
                  @dragover="dragover"
                  @dragleave="dragleave"
                  @drop="dropFavicon"
                >
                  <v-img
                    contain
                    max-height="100"
                    :src="imgFavicon ? imgFavicon : require('../../assets/default/logo.ico')"
                    class="d-flex align-center text-center my-3"
                  />
                  <div class="d-flex align-center mb-3">
                    <v-icon color="rgba(63, 93, 208, 0.12)" size="22"
                      >mdi-cloud-upload-outline</v-icon
                    >
                    <div class="secondary--text f12 ml-2 poppins">
                      <a
                        href="#"
                        @click="$refs.favicon.click()"
                        class="primary--text text-decoration-none"
                      >
                        Browse
                      </a>
                      or drag file here
                    </div>
                  </div>
                </div>
              </v-col>
              <v-col cols="12" class="px-2">
                <label class="secondary-2--text f12 poppins">TENANT LOGO ( VERTICAL )</label>
                <div
                  class="bulk-border mb-2 d-flex flex-column align-center justify-center"
                  @dragover="dragover"
                  @dragleave="dragleave"
                  @drop="dropPortrait"
                >
                  <v-img
                    contain
                    max-height="100"
                    :src="imgVertical ? imgVertical : `${asset_path}/favicon.png`"
                    class="d-flex align-center text-center my-3"
                  />
                  <div class="d-flex align-center mb-3">
                    <v-icon color="rgba(63, 93, 208, 0.12)" size="22"
                      >mdi-cloud-upload-outline</v-icon
                    >
                    <div class="secondary--text f12 ml-2 poppins">
                      <a
                        href="#"
                        @click="$refs.portrait.click()"
                        class="primary--text text-decoration-none"
                      >
                        Browse
                      </a>
                      or drag file here
                    </div>
                  </div>
                </div>
            </v-col>
            <v-col cols="12" class="px-2">
              <label class="secondary-2--text f12 poppins">TENANT LOGO ( HORIZONTAL )</label>
              <div
                  class="bulk-border mb-2 d-flex flex-column align-center justify-center"
                  @dragover="dragover"
                  @dragleave="dragleave"
                  @drop="dropLandscape"
                >
                  <v-img
                    contain
                    max-width="200"
                    :src="imgHorizontal ? imgHorizontal : `${asset_path}/logo.png`"
                    class="d-flex align-center text-center my-3"
                  />
                  <div class="d-flex align-center mb-3">
                    <v-icon color="rgba(63, 93, 208, 0.12)" size="22"
                      >mdi-cloud-upload-outline</v-icon
                    >
                    <div class="secondary--text f12 ml-2 poppins">
                      <a
                        href="#"
                        @click="$refs.landscape.click()"
                        class="primary--text text-decoration-none"
                      >
                        Browse
                      </a>
                      or drag file here
                    </div>
                  </div>
                </div>
            </v-col>
            <v-col cols="12" class="px-2">
              <label class="secondary-2--text f12 poppins">TENANT LOGIN LOGO</label>
              <div
                  class="bulk-border mb-2 d-flex flex-column align-center justify-center"
                  @dragover="dragover"
                  @dragleave="dragleave"
                  @drop="dropLoginlogo"
                >
                  <v-img
                    contain
                    max-width="200"
                    :src="imgLoginLogo ? imgLoginLogo : `${asset_path}/logo.png`"
                    class="d-flex align-center text-center my-3"
                  />
                  <div class="d-flex align-center mb-3">
                    <v-icon color="rgba(63, 93, 208, 0.12)" size="22"
                      >mdi-cloud-upload-outline</v-icon
                    >
                    <div class="secondary--text f12 ml-2 poppins">
                      <a
                        href="#"
                        @click="$refs.login_logo.click()"
                        class="primary--text text-decoration-none"
                      >
                        Browse
                      </a>
                      or drag file here
                    </div>
                  </div>
                </div>
            </v-col>
            <v-col cols="12" class="px-2 mt-5">
              <v-row no-gutters>
                <v-col cols="12" md="6">
                  <v-sheet class="custom-border d-flex justify-center py-5" :color="form.navigation_banner_color">
                    <v-avatar tile  width="115">
                      <v-img :src="imgHorizontal ? imgHorizontal : `${asset_path}/logo.png`"/>
                    </v-avatar>
                  </v-sheet>
                </v-col>
                <v-col cols="12" md="6" class="px-3">
                  <label class="secondary-2--text f12 poppins">NAVIGATION BANNER COLOR</label>
                    <v-text-field
                      v-model="form.navigation_banner_color"
                      outlined
                      dense
                      class="f14 general-custom-field"
                    >
                      <template v-slot:append-outer>
                        <v-menu right offset-x :close-on-content-click="colorpicker1">
                          <template v-slot:activator="{ on, attrs }">
                            <v-btn
                            v-bind="attrs"
                            v-on="on"
                            icon
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
                <v-col cols="12" md="6">
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
                <v-col cols="12" md="6" class="px-3 mt-1">
                  <label class="secondary-2--text f12 poppins">NAVIGATION BACKGROUND COLOR</label>
                  <v-text-field
                    v-model="form.navigation_background_color"
                    outlined
                    dense
                    class="f14 general-custom-field"
                  >
                    <template v-slot:append-outer>
                      <v-menu right offset-x :close-on-content-click="colorpicker2">
                        <template v-slot:activator="{ on, attrs }">
                          <v-btn
                          v-bind="attrs"
                          v-on="on"
                          icon
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
                  >
                    <template v-slot:append-outer>
                      <v-menu right offset-x :close-on-content-click="colorpicker3">
                        <template v-slot:activator="{ on, attrs }">
                          <v-btn
                          v-bind="attrs"
                          v-on="on"
                          icon
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
                  >
                    <template v-slot:append-outer>
                      <v-menu right offset-x :close-on-content-click="colorpicker4">
                        <template v-slot:activator="{ on, attrs }">
                          <v-btn
                          v-bind="attrs"
                          v-on="on"
                          icon
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
                  >
                    <template v-slot:append-outer>
                      <v-menu right offset-x :close-on-content-click="colorpicker11">
                        <template v-slot:activator="{ on, attrs }">
                          <v-btn
                          v-bind="attrs"
                          v-on="on"
                          icon
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
                <v-col cols="12" md="6">
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
                <v-col cols="12" md="6">
                  <label class="secondary-2--text f12 poppins">MINI NAVIGATION BACKGROUND COLOR</label>
                    <v-text-field
                      v-model="form.mini_navigation_background_color"
                      outlined
                      dense
                      class="f14 general-custom-field"
                    >
                      <template v-slot:append-outer>
                        <v-menu right offset-x :close-on-content-click="colorpicker5">
                          <template v-slot:activator="{ on, attrs }">
                            <v-btn
                            v-bind="attrs"
                            v-on="on"
                            icon
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
                    >
                      <template v-slot:append-outer>
                        <v-menu right offset-x :close-on-content-click="colorpicker6">
                          <template v-slot:activator="{ on, attrs }">
                            <v-btn
                            v-bind="attrs"
                            v-on="on"
                            icon
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
                    >
                      <template v-slot:append-outer>
                        <v-menu right offset-x :close-on-content-click="colorpicker7">
                          <template v-slot:activator="{ on, attrs }">
                            <v-btn
                            v-bind="attrs"
                            v-on="on"
                            icon
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
                    <label class="secondary-2--text f12 poppins">MINI NAVIGATION INACTIVE ICON COLOR</label>
                    <v-text-field
                      v-model="form.mini_navigation_inactive_link_color"
                      outlined
                      dense
                      class="f14 general-custom-field"
                    >
                      <template v-slot:append-outer>
                        <v-menu right offset-x :close-on-content-click="colorpicker12">
                          <template v-slot:activator="{ on, attrs }">
                            <v-btn
                            v-bind="attrs"
                            v-on="on"
                            icon
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
                <v-col cols="12" md="6">
                  <v-sheet class="custom-border" color="pink">
                    <v-row no-gutters>
                      <v-col cols="12" md="6">
                        <v-skeleton-loader
                          boilerplate
                          type="article"
                          tile
                          height="140" 
                        ></v-skeleton-loader>
                      </v-col>
                      <v-col cols="12" md="6">
                        <v-sheet height="140" :color="form.sidebar_color">
                          <v-carousel height="140" hide-delimiters cycle :show-arrows="false">
                            <v-carousel-item>
                              <v-sheet
                                color="transparent"
                                height="140"
                                class="d-flex align-center justify-center"
                              >
                              <v-img
                                  contain
                                  max-height="30"
                                  :src="imgSidebarLogo ? imgSidebarLogo : `${asset_path}/favicon-white.png`"
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
                <v-col cols="12" md="6" class="px-2">
                  <label class="secondary-2--text f12 poppins">SIDEBAR COLOR</label>
                  <v-text-field
                    v-model="form.sidebar_color"
                    outlined
                    dense
                    class="f14 general-custom-field"
                  >
                    <template v-slot:append-outer>
                      <v-menu right offset-x :close-on-content-click="colorpicker8">
                        <template v-slot:activator="{ on, attrs }">
                          <v-btn
                          v-bind="attrs"
                          v-on="on"
                          icon
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
                  <div
                      class="bulk-border mb-2 d-flex flex-column align-center justify-center"
                      @dragover="dragover"
                      @dragleave="dragleave"
                      @drop="dropSidebarLogo"
                    >
                      <v-img
                        contain
                        max-height="100"
                        :src="imgSidebarLogo ? imgSidebarLogo : `${asset_path}/favicon-white.png`"
                        class="d-flex align-center text-center my-3"
                      />
                      <div class="d-flex align-center mb-3">
                        <v-icon color="rgba(63, 93, 208, 0.12)" size="22"
                          >mdi-cloud-upload-outline</v-icon
                        >
                        <div class="secondary--text f12 ml-2 poppins">
                          <a
                            href="#"
                            @click="$refs.sidebar_logo.click()"
                            class="primary--text text-decoration-none"
                          >
                            Browse
                          </a>
                          or drag file here
                        </div>
                      </div>
                    </div>
                  </v-col>
                </v-row>
              </v-col>
            </v-row>
            <v-row>
              <v-col cols="12" class="px-2 d-flex flex-column">
                <label class="secondary-2--text f12 poppins">CAROUSEL PHOTOS</label>
                <div width="400" v-if="imgCarouselPhotos.length > 0 " class=" pa-2 d-flex overflow-y-auto scroller mb-1">
                    <section v-for="(item, index) in imgCarouselPhotos" :key="index" class="d-flex flex-column ma-1" style="width: 110px;">
                      <v-btn @click="remove(index)" fab dense x-small color="secondary-2" class="ml-auto" style="z-index: 2; position: relative; top: 15px;">
                        <v-icon small color="white"> mdi-close</v-icon>
                      </v-btn>
                      <v-img 
                        :src="item"
                        height="100"
                        width="100"
                        contain
                        class="rounded secondary-5 "/>
                    </section>
                  </div>
                <div
                  class="bulk-border mb-2 d-flex flex-column align-center justify-center"
                  @dragover="dragover"
                  @dragleave="dragleave"
                  @drop="dropCarouselPhoto"
                >
                  <!-- <v-img
                    contain
                    max-height="100"
                    :src="imgSidebarPhoto && imgSidebarPhoto"
                    class="d-flex align-center text-center my-3"
                  /> -->
                  <div class="d-flex flex-column my-3">
                    <div class="secondary--text f12 poppins">
                      <v-icon color="rgba(63, 93, 208, 0.12)" size="22" class=" mr-1"
                        >mdi-cloud-upload-outline</v-icon>
                      <a
                        href="#"
                        @click="$refs.side_carousel.click()"
                        class="primary--text text-decoration-none"
                      >
                        Browse
                      </a>
                      or drag file here
                    </div>
                  </div>
                </div>
              </v-col>
            </v-row>
          <v-row>
            <v-col cols="12" md="6">
              <label class="secondary-2--text f12 poppins">PRIMARY BUTTONS COLOR</label>
              <v-text-field
                v-model="form.primary_buttons_color"
                outlined
                dense
                class="f14 general-custom-field"
              >
                <template v-slot:append-outer>
                  <v-menu right offset-x :close-on-content-click="colorpicker9">
                    <template v-slot:activator="{ on, attrs }">
                      <v-btn
                      v-bind="attrs"
                      v-on="on"
                      icon
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
          <!-- <v-row no-gutters>
            <v-col cols="8">
              <label class="secondary-2--text f12 poppins">ICONS COLOR</label>
                <v-text-field
                  v-model="form.icons_color"
                  outlined
                  dense
                  class="f14 general-custom-field"
                >
                  <template v-slot:append-outer>
                    <v-menu right offset-x :close-on-content-click="colorpicker10">
                      <template v-slot:activator="{ on, attrs }">
                        <v-btn
                        v-bind="attrs"
                        v-on="on"
                        icon
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
                            v-model="form.icons_color"
                          ></v-color-picker>
                        </v-sheet>
                    </v-menu>
                  </template>
                </v-text-field>
            </v-col>
          </v-row> -->
        </v-card-text>
        <v-divider/>
        <v-card-text>
          <div class="primary--text poppins fw600 mx-2 pt-5 pb-3">CONTACT DETAILS</div>
          <v-row no-gutters>
            <v-col cols="12" md="6" class="px-2">
              <label class="secondary-2--text f12 poppins">CONTACT PERSON *</label>
              <v-text-field
                v-model="form.contact_person"
                outlined
                dense
                class="f14"
                :class="errs.contact_person ? '' : 'general-custom-field'"
                :error-messages="errs.contact_person"
              />
            </v-col>
            <v-col cols="12" md="6" class="px-2">
              <label class="secondary-2--text f12 poppins">EMAIL *</label>
              <v-text-field
                v-model="form.email"
                type="email"
                outlined
                dense
                class="f14"
                :class="errs.email ? '' : 'general-custom-field'"
                :error-messages="errs.email"
              />
            </v-col>
            <v-col cols="12" md="6" class="px-2">
              <label class="secondary-2--text f12 poppins">MOBILE NUMBER *</label>
              <v-text-field
                v-model="form.mobile_no"
                outlined
                dense
                class="f14"
                :class="errs.mobile_no ? '' : 'general-custom-field'"
                :error-messages="errs.mobile_no"
              />
            </v-col>
            <v-col cols="12" md="6" class="px-2">
              <label class="secondary-2--text f12 poppins">TEL. NUMBER *</label>
              <v-text-field
                v-model="form.tel_no"
                outlined
                dense
                class="general-custom-field f14"
                :error-messages="errs.tel_no"
              />
            </v-col>
          </v-row>
        </v-card-text>
        <input
          type="file"
          ref="favicon"
          class="d-none"
          accept=".ico"
          v-on:change="acceptFavicon"
        />
        <input
          type="file"
          ref="portrait"
          class="d-none"
          accept=".jpeg,.png,.jpg"
          v-on:change="acceptPortrait"
        />
        <input
          type="file"
          ref="landscape"
          class="d-none"
          accept=".jpeg,.png,.jpg"
          v-on:change="acceptLandscape"
        />
        <input
          type="file"
          ref="sidebar_logo"
          class="d-none"
          accept=".jpeg,.png,.jpg"
          v-on:change="acceptSidebarLogo"
        />
        <input
          type="file"
          ref="login_logo"
          class="d-none"
          accept=".jpeg,.png,.jpg"
          v-on:change="acceptLoginLogo"
        />
        <input
          type="file"
          ref="side_carousel"
          class="d-none"
          multiple
          accept=".jpeg,.png,.jpg"
          v-on:change="acceptCarouselPhoto"
        />
      </v-form>
      <v-card-actions class="d-flex justify-end mx-6 pb-5">
        <v-btn
          text
          color="secondary-2"
          class="text-capitalize poppins f14"
          @click="cancel"
          >Cancel</v-btn
        >
        <v-btn
          color="primary"
          width="100"
          class="text-capitalize poppins f14"
          @click="saveTenant"
          :loading="saving"
        >
          Save
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
import { mapActions, mapState, mapMutations } from "vuex";
export default {
  props: ["dialog"],
  data: () => ({
    asset_path: `${process.env.VUE_APP_ASSET_PATH}`,
    status: [
      { text: "ACTIVE", value: "active" },
      { text: "INACTIVE", value: "inactive" },
    ],
    types: [
      { text: "CORPORATE", value: "corporate" },
      { text: "GENERAL EDUCATION", value: "general_education" },
      { text: "NGO", value: "ngo" },
    ],
    radioGroup: 1,
    form: {
      tenant_name: "",
      organization: "",
      domain_name: "",
      sub_domain: "",
      status: "",
      tenant_description: "",
      contact_person: "",
      email: "",
      mobile_no: "",
      tel_no: "",
      images: [],
      navigation_background_color: '#ffffff',
      navigation_banner_color: '#ffffff',
      navigation_link_color: '#3966D8',
      navigation_text_icon_color: '#3966D8',
      navigation_link_inactive_color: '#828282',
      sidebar_color: '#3966D8',
      mini_navigation_background_color: '#3966D8',
      mini_navigation_link_color: '#3966D8',
      mini_navigation_icon_color: '#ffffff',
      mini_navigation_inactive_link_color: '#F2f2f2',
      primary_buttons_color: '#3966D8',
      // secondary_buttons_color: '#3966D8',
      icons_color: '#757575',
    },
    errs: [],
    domain_err: "",
    domain_arr: [[], []],
    domain_holder: "",
    saving: true,
    sub: '',
    imgFavicon: null,
    imgVertical: null,
    imgHorizontal: null,
    imgSidebarLogo: null,
    imgLoginLogo: null,
    imgCarouselPhotos: [],
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
    selected_mini: 1
  }),
  computed: {
    ...mapState({
      errors: (state) => state.errors,
    }),
  },
  methods: {
    ...mapActions("superad", ["addTenantAction"]),
    ...mapMutations(['alertMutation']),

    remove(index){
      this.imgCarouselPhotos.splice(index, 1)
      this.form.images.splice(index, 1)
    },

    saveTenant() {
      let form = new FormData();

      form.append("tenant_name", this.form.tenant_name);
      form.append("organization", this.form.organization);
      form.append("domain_name", this.form.domain_name);
      form.append("sub_domain", this.form.sub_domain);
      form.append("status", this.form.status);
      form.append("tenant_description", this.form.tenant_description);
      form.append("contact_person", this.form.contact_person);
      form.append("email", this.form.email);
      form.append("mobile_no", this.form.mobile_no);
      form.append("tel_no", this.form.tel_no);
      if(this.form.images.length > 0) {
        this.form.images.forEach((_item, i) => {
          form.append(`image_key[${i}]`, _item.image_key)
          form.append(`images[${i}]`, _item.image)
        })
      }
      form.append('navigation_background_color', this.form.navigation_background_color)
      form.append('navigation_banner_color', this.form.navigation_banner_color)
      form.append('navigation_link_color', this.form.navigation_link_color)
      form.append('navigation_text_icon_color', this.form.navigation_text_icon_color)
      form.append('navigation_link_inactive_color', this.form.navigation_link_inactive_color)
      form.append('sidebar_color', this.form.sidebar_color)
      form.append('mini_navigation_background_color', this.form.mini_navigation_background_color)
      form.append('mini_navigation_link_color', this.form.mini_navigation_link_color)
      form.append('mini_navigation_icon_color', this.form.mini_navigation_icon_color)
      form.append('mini_navigation_inactive_link_color', this.form.mini_navigation_inactive_link_color)
      form.append('primary_buttons_color', this.form.primary_buttons_color)
      // form.append('secondary_buttons_color', this.form.secondary_buttons_color)

      this.saving = true;
      this.addTenantAction(form).then(() => {
        this.saving = false
        this.$refs.tenantForm.reset();
        this.resetForm()
        this.alertMutation({
          show: true,
          text: 'You have successfully added a tenant',
          type: "success"
        })
        this.$emit("close");
      }).catch(e => {
        this.saving = false
        this.errs = e
      })
    },
    domain_change(val, index) {
      this.domain_arr[index] = val;
      this.domain_holder = this.domain_arr.toString();
      // console.log(this.domain_holder);
    },
    cancel() {
      this.$refs.tenantForm.reset();
      this.resetForm();
      this.$emit("close");
    },

    resetForm(){
      this.$refs.favicon.value = '';
      this.$refs.portrait.value = '';
      this.$refs.landscape.value = '';
      this.$refs.sidebar_logo = '';
      this.$refs.login_logo = '',
      this.$refs.side_carousel = ''
      this.imgFavicon = null
      this.imgVertical = null
      this.imgHorizontal = null
      this.imgSidebarLogo = null
      this.imgLoginLogo = null
      this.imgCarouselPhotos = []
      this.form.images = []
      this.errs = [];
      this.domain_err = [];
      this.saving = false;
      this.form.navigation_background_color = '#ffffff',
      this.form.navigation_banner_color = '#ffffff',
      this.form.navigation_link_color = '#3966D8',
      this.form.navigation_text_icon_color = '#3966D8',
      this.form.navigation_link_inactive_color = '#828282'
      this.form.sidebar_color = '#3966D8'
      this.form.mini_navigation_background_color = '#3966D8',
      this.form.mini_navigation_link_color = '#3966D8',
      this.form.mini_navigation_icon_color = '#ffffff',
      this.form.mini_navigation_inactive_link_color = '#f2f2f2',
      this.form.primary_buttons_color = '#3966D8',
      // this.form.secondary_buttons_color = '#3966D8',
      this.form.icons_color = '#757575'
    },

    leave(e) {
      this.sub = new URL(e.target.value)
      const origin = this.sub.hostname.split('.')[0]

      this.form.sub_domain = origin //subdomain will be saved to the backend
      let central = new URL(process.env.VUE_APP_API);
      let base = `${central.protocol}//${origin}.${central.host}${central.pathname}`

      return this.sub = base
    },
    
    dragover(e) {
      e.preventDefault();
      e.currentTarget.classList.add("bulk-border-drag");
    },
    dragleave(e) {
      e.preventDefault();
      e.currentTarget.classList.remove("bulk-border-drag");
    },

    dropFavicon(e) {
      e.preventDefault();
      this.$refs.favicon.files = e.dataTransfer.files;
      this.acceptFavicon();
      e.currentTarget.classList.remove("bulk-border-drag");
    },

    dropPortrait(e) {
      e.preventDefault();
      this.$refs.portrait.files = e.dataTransfer.files;
      this.acceptPortrait();
      e.currentTarget.classList.remove("bulk-border-drag");
    },

    dropLandscape(e) {
      e.preventDefault();
      this.$refs.landscape.files = e.dataTransfer.files;
      this.acceptLandscape();
      e.currentTarget.classList.remove("bulk-border-drag");
    },

    dropSidebarLogo(e) {
      e.preventDefault();
      this.$refs.sidebar_logo.files = e.dataTransfer.files;
      this.acceptLandscape();
      e.currentTarget.classList.remove("bulk-border-drag");
    },

    dropLoginlogo(e) {
      e.preventDefault();
      this.$refs.login_logo.files = e.dataTransfer.files;
      this.acceptLoginLogo();
      e.currentTarget.classList.remove("bulk-border-drag");
    },
    dropCarouselPhoto(e) {
      e.preventDefault();
      this.$refs.side_carousel.files = e.dataTransfer.files;
      this.acceptCarouselPhoto();
      e.currentTarget.classList.remove("bulk-border-drag");
    },
    acceptFavicon(){
      if(this.$refs.favicon.files[0]) {
        let _index = this.form.images.findIndex((item) => item.image_key === 'favicon')

        if(_index > -1) {
          this.form.images.splice(_index, 1)
        }

        this.form.images.push(
          {
            id: 'favicon',
            image_key: 'favicon',
            image: this.$refs.favicon.files[0]
          }
        )

        const reader = new FileReader()

        reader.onload = (e) => {
          this.imgFavicon = e.target.result
        }
      
        reader.readAsDataURL(this.$refs.favicon.files[0])
      }
    },
    acceptPortrait() {
      if(this.$refs.portrait.files[0]) {
        let _index = this.form.images.findIndex((item) => item.image_key === 'vertical_logo')

        if(_index > -1) {
          this.form.images.splice(_index, 1)
        }

        this.form.images.push(
          {
            id: 'vertical_logo',
            image_key: 'vertical_logo',
            image: this.$refs.portrait.files[0]
          }
        )

        const reader = new FileReader()

        reader.onload = (e) => {
          this.imgVertical = e.target.result
        }
      
        reader.readAsDataURL(this.$refs.portrait.files[0])
      }
    },
    acceptLandscape() {
      if(this.$refs.landscape.files[0]) {
        let _index = this.form.images.findIndex((item) => item.image_key === 'horizontal_logo')

        if(_index > -1) {
          this.form.images.splice(_index, 1)
        }

        this.form.images.push(
          {
            id: 'horizontal_logo',
            image_key: 'horizontal_logo',
            image: this.$refs.landscape.files[0]
          }
        )

        const reader = new FileReader()

        reader.onload = (e) => {
          this.imgHorizontal = e.target.result
        }
      
        reader.readAsDataURL(this.$refs.landscape.files[0])
      }
    },
    acceptSidebarLogo() {
      if(this.$refs.sidebar_logo.files[0]) {
        let _index = this.form.images.findIndex((item) => item.image_key === 'sidebar_logo')
        if(_index > -1) {
          this.form.images.splice(_index, 1)
        }
        this.form.images.push(
          {
            id: 'sidebar_logo',
            image_key: 'sidebar_logo',
            image: this.$refs.sidebar_logo.files[0]
          }
        )
        const reader = new FileReader()
        reader.onload = (e) => {
          this.imgSidebarLogo = e.target.result
        }
        reader.readAsDataURL(this.$refs.sidebar_logo.files[0])
      }
    },
    acceptLoginLogo() {
      if(this.$refs.login_logo.files[0]) {
        let _index = this.form.images.findIndex((item) => item.image_key === 'login_logo')
        if(_index > -1) {
          this.form.images.splice(_index, 1)
        }
        this.form.images.push(
          {
            id: 'login_logo',
            image_key: 'login_logo',
            image: this.$refs.login_logo.files[0]
          }
        )
        const reader = new FileReader()
        reader.onload = (e) => {
          this.imgLoginLogo = e.target.result
        }
        reader.readAsDataURL(this.$refs.login_logo.files[0])
      }
    },
    
    acceptCarouselPhoto() {
      if(this.$refs.side_carousel.files[0]) {
        let _arr = this.form.images.filter((item) => item.image_key.includes('side_carousel'))
        let _index = _arr.length === 0 ? 0 : parseInt(_arr[_arr.length - 1]['id'].replace('side_carousel_', ''))

        this.$refs.side_carousel.files.forEach(file => {
          this.form.images.push(
            {
              id: `side_carousel_${++_index}`,
              image_key: `side_carousel_${_index}`,
              image: file
            }
          )
          const reader = new FileReader()
          reader.onload = (e) => {
            this.imgCarouselPhotos.push(e.target.result)
          }
          reader.readAsDataURL(file)
        } )
      }
    },
  },
  watch: {
    errors(val) {
      this.domain_err = "";
      this.errs = val;
      if (val) {
        if (this.errs.domain_name || this.errs.sub_domain)
          this.domain_err = "The domain field is required";
        this.saving = false;
      }
    },
  },
};
</script>
