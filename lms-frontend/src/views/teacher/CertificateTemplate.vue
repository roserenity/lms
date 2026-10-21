<template>
  <div>
    <div class="d-flex flex-wrap align-center justify-space-between mb-3">
      <v-btn dense text color="primary" class="mb-2 pa-0"
        @click="(prevRoute && prevRoute.name === 'Instructor Certificate Templates') ? $router.back() : $router.push({ name: 'Instructor Certificate Templates' , query: { search: '', page: 1, paginate: 10 }})">
          BACK
      </v-btn>
      
      <ButtonPrimary
        v-if="$route.name !== 'Instructor Certificate Template View'"
        :label="$route.params.id ? 'Update' : 'Save'"
        :loading="saveLoading"
        @click="() => {
          if(this.$route.params.id) {
            update()
          } else {
            save()
          }
        }"
      />
    </div>
    <LinearProgress v-if="loading" />
    <div class="flex flex-wrap fade" v-if="!loading">
      <v-row dense>
        <v-col  md="6" sm="12">
          <v-sheet class="custom-border border rounded pa-3 poppins f14 mb-3">
            <FormTextFieldRequired
              :label="'CERTIFICATE TEMPLATE NAME:'"
              :value.sync="form.name"
              :error="errors && errors.name?.[0]"/>
            <FormSelectObject
              :label="'CERTIFICATE CATEGORY:'"
              :items="categories"
              class="my-3 mb-2"
              :value.sync="form.certificate_category"
              :error="errors && errors.certificate_category?.[0]"
            />
            <div class="d-flex align-center flex-row mb-1" v-if="form.certificate_category">
              <v-checkbox v-model="form.default" dense hide-details />
              <label class="caption mt-2">Set as default for {{ categories.find((category) => form.certificate_category === category.id)?.name }} category.</label>
            </div>
            <label class="caption">UPLOAD LOGO</label>
            <FormUploadImage 
              :crop="true"
              @acceptImage="addLogo"
              @acceptImageSrc="addLogoSrc"
              @acceptBlob="acceptLogoBlob"
              :error="errors && errors.logo?.[0]"
              class="mb-1"/>
            <v-divider class="my-5"/>
            <label class="caption">CERTIFICATE CONTENT</label>
            <v-alert type="warning" class="f12 caption" :icon="false" text dense>
              Note: 
              <div>
              Ensure that the text fields follows the correct format, Any alterations may result in invalid formatting on the final certificate: 
                <div>
                  <ul>
                    <li v-html="'Use {{ name }} for the learner name.'"></li>
                    <li v-html="'Use {{ course_title }} for the course title.'"></li>
                    <li v-html="'Use {{ credit_training_hours }} for course credit values.'"></li>
                    <li v-html="'Use {{ date_received }} for the certificate receiving date.'"></li>
                    <li v-html="'Use {{ training_credits }} for the course training credits.'"></li>
                    <li v-html="'Use {{ course_conducted }} for the course conducted dates.'"></li>
                  </ul>
                </div>
              </div>
            </v-alert>
            <div v-for="(value, index) in form.content" :key="index">
              <v-row dense>
                <v-col cols="3">
                  <FormSelectArray
                    :items="types"
                    :value.sync="value.type"
                  />
                </v-col>
                <v-col>
                  <FormTextFieldOptional
                    :value.sync="value.text"
                    :class_="'f12'"/>
                </v-col>
                <v-col cols="1">
                  <v-btn icon text dense @click="remove(i)">
                    <v-icon size="18">
                      mdi-close
                    </v-icon>
                  </v-btn>
                </v-col>
              </v-row>
            </div>
            
            <ButtonPrimary
              :label="'Add text field'"
              :outlined="true"
              @click="() => {
                form.content.push({
                  type: 'Paragraph',
                  text: 'Text here'
                })
              }"
              class="mt-2"
            />
            <v-divider class="my-5"/>
            <label class="caption">UPLOAD SIGNATORY E-SIGNATURE *</label>
            <FormUploadImage 
              :crop="true"
              @acceptImage="addSignatorySign"
              @acceptImageSrc="addSignatorySignSrc"
              @acceptBlob="acceptSignatoryBlob"
              :error="errors && errors.signatory_img?.[0]"
              class="mb-1"
            />
            <FormTextFieldOptional
                :label="'SIGNATORY NAME'"
                :value.sync="form.signatory[0].name"
                :error="errors && errors.signatory_name"/>
            <FormTextFieldOptional
                :label="'SIGNATORY DESIGNATION'"
                :value.sync="form.signatory[0].designation"
                :error="errors && errors.signatory_designation"/>
            <FormTextFieldOptional
                :label="'OTHER INFO'"
                :value.sync="form.signatory[0].info"
                :error="errors && errors.signatory_info"/>
            <label class="caption">UPLOAD ATTESTOR E-SIGNATURE *</label>
            <FormUploadImage 
              :crop="true"
              @acceptImage="addAttestorSign"
              @acceptImageSrc="addAttestorSignSrc"
              @acceptBlob="acceptAttestorBlob"
              :error="errors && errors.attestor_img?.[0]"
              class="mb-1"
            />
            <FormTextFieldOptional
                :label="'ATTESTOR NAME'"
                :value.sync="form.attestor[0].name"
                :error="errors && errors.attestor_name"/>
            <FormTextFieldOptional
                :label="'ATTESTOR DESIGNATION'"
                :value.sync="form.attestor[0].designation"
                :error="errors && errors.attestor_designation"/>
            <FormTextFieldOptional
                :label="'OTHER INFO'"
                :value.sync="form.attestor[0].info"
                :error="errors && errors.attestor_info"/>
          </v-sheet>

        </v-col>
        <v-col cols="12" md="4" sm="12">
          <v-sheet width="700px" height="1122" class="custom-border pa-5 timesnewroman" style="position: relative; overflow: hidden;">

              <section class="f16" style="height: 100%; width: 100%; margin: 0; padding: 0; -webkit-text-size-adjust: none; text-size-adjust: none; text-align: center; display: flex; flex-direction: column; justify-content:space-between">
                <div>
                  <div class="v243_4">
                    <img width="120px" height="70" contain :src="images.logoSrc ? images.logoSrc : require('@/assets/images/default-logo.png')" />
                  </div>
                  <div style="margin-top: 60px;">
                    <template v-for="(value, key) in form.content">
                      <div v-if="value.type === 'Header'" style="font-size: 30px; font-weight: 700; margin-top: 20px;" :key="key">
                        {{ value.text }}
                      </div>

                      <div v-if="value.type === 'Paragraph'" style="margin-top: 20px;" :key="key">
                        {{ value.text }}
                      </div>

                      <div v-if="value.type === 'Subheader'" style="margin-top: 20px; font-weight: 700; " :class="value.length > 50 ? 'font-size-small' : value.length > 30 ? 'font-size-medium' : 'font-size-large'" :key="key">
                        {{ value.text }}
                      </div>

                    </template>
                  </div>
                  
                  <!-- <div class="v243_7" style="margin-top: 80px;"><span class="v243_8">This</span></div>
                  <div class="v243_9" style="margin-top: 20px;"><span style="font-size: 30px; font-weight: 700;" >{{ form.title }}</span></div>
                  <div class="v243_11" style="margin-top: 20px;"><span class="v243_12">is awarded to</span></div>
                  <div class="v243_13" style="margin-top: 20px; font-weight: 700;"><span :class="name.length > 50 ? 'font-size-small' : name.length > 30 ? 'font-size-medium' : 'font-size-large'">{{ name }}</span></div>
                  <div class="v243_15" style="margin-top: 20px;"><span class="v243_16">for having successfully completed the conduct of the</span></div>
                  

                  <div class="v243_17" style="margin-top: 20px; font-weight: 700;"><span :class="courseTitle.length > 50 ? 'font-size-small' : courseTitle.length > 30 ? 'font-size-medium' : 'font-size-large'"> {{ courseTitle }}</span></div>
                  <div class="v243_19" style="margin-top: 20px;"><span class="v243_20">
                    ( credit of eight [8] hours Leadership and management training )
                  </span></div>
                  <div class="v243_21" style="margin-top: 20px;"><span class="v243_22">conducted by Aster Academy 
                    {{ formatDateRange }}
                  </span></div>
                  <div class="v243_24" style="margin-top: 30px;"><span class="v243_25">Given this {{ $dateFormat.MMYY(issueDate) }}.</span></div> -->
                  
                </div>
                <div style="position: absolute; bottom: 0; margin-bottom: 80px; width: 665px;">
                  <div class="v243_26">
                    <div v-for="(item, i)  in form.signatory" :key="item.name">
                      <img :key="signatoryKey" v-if="item.img" class="signature" contain :src="item.img ? item.img : require('@/assets/images/default-signatory.png')" />
                      <div class="v243_34"  style="margin-top: 10px;"></div>
                      <div >{{ item.name }}</div>
                      <div class="f12">{{ item.designation }}</div>
                      <div class="f12">{{ item.info }}</div>
                    </div>
                  </div>
                  <div class="v243_33" style="margin-top: 30px;">
                    <span class="v243_88">Attested by:</span>
                    <div v-for="(item, i)  in form.attestor" :key="item.name">
                      <img :key="attestorKey" v-if="item.img" class="signature" contain :src="item.img ? item.img : require('@/assets/images/default-attestor.png')" />
                      <div class="v243_34"  style="margin-top: 10px;"></div>
                      <div >{{ item.name }}</div>
                      <div class="f12">{{ item.designation }}</div>
                      <div class="f12">{{ item.info }}</div>
                    </div>
                  </div>
                </div>
                
                <div style="font-size:1px; margin-bottom: 20px; position: absolute; bottom: 0;">
                  <img height="80" contain :src="require('@/assets/images/sample-qr-code.png')"/>
                </div>
            </section>
          </v-sheet>
        </v-col>
      </v-row>
    </div>

  </div>
</template>

<style>
  .font-size-large { font-size: 30px; }
  .font-size-medium { font-size: 27px; }
  .font-size-small { font-size: 24px; }
  body {
    font-size: 16px;
  }
  .signature {
    width: 200px;
    aspect-ratio: 3 / 1;
    object-fit: contain;
  }
</style>

<script>
import FormTextFieldRequired from '@/components/reusable/forms/FormTextFieldRequired.vue';
import errors from '@/shared/errors';
import { mapActions, mapState, mapMutations } from 'vuex';

export default {
  data: () => ({
    types: ['Paragraph', 'Header', 'Subheader'],
    prevRoute: null,
    name: 'User Account',
    courseTitle: 'Course Title',
    dateConducted: {
      start_date: "2024-12-01",
      end_date: "2024-12-10"
    },
    createdAt: "2024-11-20",
    issueDate: "2024-11-20",
    form: {
      id: null,
      title: 'CERTIFICATE OF COMPLETION',
      name: '',
      default: 0,
      certificate_category: 1,
      logo: null,
      content: [
        { type: "Paragraph", text: "This" },
        { type: "Header", text: "CERTIFICATE OF COMPLETION" },
        { type: "Paragraph", text: "is awarded to" },
        { type: "Subheader", text: "{{ name }}" },
        { type: "Paragraph", text: "for having successfully completed the training on" },
        { type: "Subheader", text: "{{ course_title }}" },
        { type: "Paragraph", text: "(credit of {{ credit_training_hours }} hour {{ training_credits }})" },
        { type: "Paragraph", text: "conducted by Aster Academy." },
        { type: "Paragraph", text: "Given this {{ date_received }}" },
      ],
      signatory: [
        {
          name: null,
          designation: null,
          info: null,
          img: null, 
          blob: null
        }
      ],
      attestor: [
        {
          name: null,
          designation: null,
          info: null,
          img: null,
          blob: null
        }
      ],
    },
    images: {
      logoSrc: null,
      logoBlob: null
    },
    saveLoading: false,
    modes: ['E-LEARNING', 'BLENDED'],
    loading: false, 
    signatoryKey: 0,
    attestorKey: 0,
    categories: []
  }),
  
  methods: {
    ...mapActions('instructor', ['addCertificateTemplateAction', 'getCertificateTemplateAction', 'updateCertificateTemplateAction', 'getCertificateTemplateCategory']),
    ...mapMutations(['alertMutation']),

    addLogo(e) {
      this.form.logo = null
      this.form.logo = e
    },

    remove(i){
        this.form.content.splice(i, 1)
    },

    addLogoSrc(e) {
      this.images.logoSrc = null
      this.images.logoSrc = e
    },

    acceptLogoBlob(e) {
      this.form.logo = null
      this.form.logo = e
    },

    addSignatorySign(e) {
      this.form.signatory[0].img = null
      this.form.signatory[0].img = e
      this.signatoryKey++
    },

    addSignatorySignSrc(e) {
      /* this.images.logoSrc = e */
    },

    acceptSignatoryBlob(e) {
      this.form.signatory[0].blob = null
      this.form.signatory[0].blob = e
    },

    addAttestorSign(e) {
      this.form.attestor[0].img = null
      this.form.attestor[0].img = e
      this.attestorKey++
    },

    addAttestorSignSrc(e) {
      this.form.attestor[0].img = null
      this.form.attestor[0].img = e
      /* this.images.logoSrc = e */
    },
    
    acceptAttestorBlob(e) {
      this.form.attestor[0].blob = null
      this.form.attestor[0].blob = e
    },

    save() {
      this.saveLoading = true
      let form = new FormData()

      form.append('name', this.form.name)
      form.append('content', JSON.stringify(this.form.content))
      form.append('default', this.form.default === true ? 1 : 0)
      form.append('certificate_category', this.form.certificate_category)

      if(this.form.logo) {
        form.append('logo', this.form.logo)
      }

      if(this.form.attestor[0].name) {
        form.append('attestor_name', this.form.attestor[0].name)
      }
      if(this.form.attestor[0].designation) {
        form.append('attestor_designation', this.form.attestor[0].designation)
      }
      if(this.form.attestor[0].blob) {
        form.append('attestor_img', this.form.attestor[0].blob)
      }
      if(this.form.attestor[0].info) {
        form.append('attestor_info', this.form.attestor[0].info)
      }

      if(this.form.signatory[0].name) {
        form.append('signatory_name', this.form.signatory[0].name)
      }
      if(this.form.signatory[0].designation) {
        form.append('signatory_designation', this.form.signatory[0].designation)
      }
      if(this.form.attestor[0].blob) {
        form.append('signatory_img', this.form.signatory[0].blob)
      }
      if(this.form.attestor[0].info) {
        form.append('signatory_info', this.form.attestor[0].info)
      }

      this.addCertificateTemplateAction(form).then(() => {
        this.saveLoading = false
        this.alertMutation({
          show: true,
          text: `Certificate Template successfully saved`,
          type: "success"
        })
        if(this.prevRoute && this.prevRoute.name === 'Instructor Certificate Templates') {
          this.$router.back()
        } else {
          this.$router.push({ name: 'Instructor Certificate Templates' , query: { search: '', page: 1, paginate: 10 }})
        }

      }).catch(() => {
        this.saveLoading = false
        this.alertMutation({
          show: true,
          text: `Something went wrong`,
          type: "error"
        })
      })
    },

    update() {
      this.saveLoading = true
      let formData = new FormData()

      formData.append('id', this.form.id)
      formData.append('name', this.form.name)
      formData.append('content', JSON.stringify(this.form.content))
      formData.append('default', this.form.default === true ? 1 : 0)
      formData.append('certificate_category', this.form.certificate_category)

      if(this.form.logo) {
        formData.append('logo', this.form.logo)
      }

      if(this.form.attestor[0].name) {
        formData.append('attestor_name', this.form.attestor[0].name)
      }
      if(this.form.attestor[0].designation) {
        formData.append('attestor_designation', this.form.attestor[0].designation)
      }
      if(this.form.attestor[0].blob) {
        formData.append('attestor_img', this.form.attestor[0].blob)
      }
      if(this.form.attestor[0].info) {
        formData.append('attestor_info', this.form.attestor[0].info)
      }

      
      if(this.form.signatory[0].name) {
        formData.append('signatory_name', this.form.signatory[0].name)
      }
      if(this.form.signatory[0].designation) {
        formData.append('signatory_designation', this.form.signatory[0].designation)
      }
      if(this.form.signatory[0].blob) {
        formData.append('signatory_img', this.form.signatory[0].blob)
      }
      if(this.form.signatory[0].info) {
        formData.append('signatory_info', this.form.signatory[0].info)
      }

      formData.append('_method', 'PUT');

      this.updateCertificateTemplateAction({ id: this.$route.params.id, data: formData}).then(() => {
        this.saveLoading = false
        this.alertMutation({
          show: true,
          text: `Certificate Template successfully updated`,
          type: "success"
        })

      }).catch(() => {
        this.saveLoading = false
        this.alertMutation({
          show: true,
          text: `Something went wrong`,
          type: "error"
        })
      })
    },

    getTemplate(){
      this.loading = true
      this.getCertificateTemplateAction(Number(this.$route.params.id)).then(res => {
        this.loading = false
        this.form.id = res.id
        this.form.name = res.name
        this.form.title = res.title
        this.form.content = JSON.parse(res.content)
        this.form.default = res.is_default === 1 ? true : false
        this.form.certificate_category = res.certificate_category_id
        this.images.logoSrc = res.logo ? res.logo.image.url : null

        if(res.attestor.length > 0) {
          res.attestor.forEach((attestor, i) => {
            this.form.attestor[i] = {
              name: attestor.name,
              designation: attestor.designation,
              info: attestor.info,
              img: attestor.image.url,
              blob: null
            }
          })
        }

        if(res.signatory.length > 0) {
          res.signatory.forEach((signatory, i) => {
            this.form.signatory[i] = {
              name: signatory.name,
              designation: signatory.designation,
              info: signatory.info,
              img: signatory.image.url,
              blob: null
            }
          })
        }

      }).catch(() => {
        this.loading = false
        this.alertMutation({
          show: true,
          text: `Something went wrong`,
          type: "error"
        })
      })
    }

  },
  watch: {
    courseTitle(val) {
      if(val.trim() === '') {
        this.courseTitle = 'Course Title'
      }
    },
    name(val) {
      if(val.trim() === '') {
        this.name = 'User Account'
      }
    }
  },
  mounted(){
    if(this.$route.params.id) {
      this.getTemplate()
    }
    this.getCertificateTemplateCategory().then((res) => {
        this.categories = res
    })
  },
  computed: {
    ...mapState({
      errors: (state) => state.errors,
    }),

    formatDateRange () {
      let result = "";

      if (this.dateConducted && this.dateConducted.start_date && this.dateConducted.end_date) {
        const startDate = new Date(this.dateConducted.start_date);
        const endDate = new Date(this.dateConducted.end_date);

        const startYear = startDate.getFullYear();
        const endYear = endDate.getFullYear();

        if (startYear === endYear) {
          if (this.dateConducted.start_date === this.dateConducted.end_date) {
            result = `on ${endDate.toLocaleDateString('en-US', {
              year: 'numeric',
              month: 'long',
              day: 'numeric'
            })}`;
          } else {
            result = `on ${startDate.toLocaleDateString('en-US', {
              month: 'long',
              day: 'numeric'
            })} - ${endDate.toLocaleDateString('en-US', {
              year: 'numeric',
              month: 'long',
              day: 'numeric'
            })}`;
          }
        } else {
          result = `on ${startDate.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
          })} - ${endDate.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
          })}`;
        }
      } else {
        const createdAtDate = new Date(createdAt);
        result = `on ${createdAtDate.toLocaleDateString('en-US', {
          year: 'numeric',
          month: 'long',
          day: 'numeric'
        })}`;
      }

      return result;
    }
  },
  
  beforeRouteEnter(to, from, next) {
    next(vm => {
        vm.prevRoute = from 
    })
  },
};
</script>
