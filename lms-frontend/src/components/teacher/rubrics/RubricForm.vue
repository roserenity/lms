<template>
  <section>
    <v-row dense>
      <v-col cols="12" xl="10">
        <v-btn text color="primary" @click="$emit('onSave', 0)" v-if="action == 'create'">
            <v-icon small>
                mdi-arrow-left
            </v-icon>
            BACK
        </v-btn>

        <v-form ref="form" v-if="action==='update'">
          <v-row class="col-8" dense>
            <v-col cols="12" lg="6">
              <label class="caption">TITLE *</label>
              <v-text-field
                dense
                hide-details
                v-model="rubric_.title"
                v-on:change="update"
                class="general-custom-field mb-1"
                :readonly="action == 'view'"
              >
              </v-text-field>
            </v-col>
            <v-col cols="12" lg="6">
              <label class="caption">DESCRIPTION *</label>
              <v-text-field
                dense
                hide-details
                v-model="rubric_.description"
                v-on:change="update"
                class="general-custom-field mb-1"
                :readonly="action == 'view'"
              >
              </v-text-field>
            </v-col>
          </v-row>
        </v-form>
        <v-form ref="form"  v-else>
          <v-row class="col-8" dense>
            <v-col cols="12" lg="6">
              <FormTextFieldRequired 
                :label="'TITLE *'"
                :value.sync="rubric.title"
                :readonly="action == 'view'"
              />
            </v-col>
            <v-col cols="12" lg="6">
              <FormTextFieldRequired 
                :label="'DESCRIPTION *'"
                :value.sync="rubric.description"
                :readonly="action == 'view'"
              />
              <section class="d-flex justify-end">
                <v-btn
                  dense
                  v-if="action == 'update'"
                  :loading="loading"
                  color="primary"
                  class="poppins text-capitalize mt-3"
                  @click="update()"
                  >Update</v-btn
                >
              </section>
            </v-col>
          </v-row>
        </v-form>

        <v-sheet class="rounded-lg fade pa-3">
          <section class="overflow-x-auto ">
            <table class="border fixed rubric">
              <tr>
                <th rowspan="2" width="200" class="rubric-inner">
                  <div class="d-flex align-center justify-center">
                    <div class="mr-2">Criteria</div>
                    <v-btn
                      icon
                      small
                      color="primary"
                      :loading="addingCriteria"
                      @click="addCriteria()"
                      v-show="action != 'view'"
                    >
                      <v-icon>mdi-plus-box</v-icon>
                    </v-btn>
                  </div>
                </th>
                <th class="rubric-inner">
                  <div class="d-flex align-center justify-center">
                    <div class="mr-2">Rating</div>
                    <v-btn
                      :loading="addingRating"
                      icon
                      small
                      color="primary"
                      @click="addRating()"
                      v-show="action != 'view'"
                    >
                      <v-icon>mdi-plus-box</v-icon>
                    </v-btn>
                  </div>
                </th>
                <th rowspan="2" width="200">Score</th>
              </tr>
              <tr>
                <div class="wrapper">
                  <td  v-if="rubric_" v-for="(item, n) in rubric_.ratings" :key="n" class="rubric-inner">
                    <v-sheet width="150" class="d-flex align-center justify-center">
                      <label v-show="action == 'view'"> {{ item.rate }}</label>
                      <input
                        v-model="item.rate"
                        type="number"
                        style="width: 50px"
                        class="input-sm text-center"
                        v-on:input="inputRate($event, item)"
                        v-show="action != 'view'"
                      />
                      <v-btn 
                        icon 
                        small 
                        v-if="rubric.ratings.length > 1"
                        @click="removeItem(n, item, 'Rating')"
                        v-show="action != 'view'">
                        <v-icon>mdi-delete-outline</v-icon>
                      </v-btn>
                    </v-sheet>
                  </td>
                </div>
              </tr>
              <tr
                v-for="(item, c) in rubric_.criterias"
                :key="c"
                class="roboto f14 secondary-3--text"
              >
                <td class="rubric-inner">
                  <div class="text-center d-flex align-center flex-column">
                    <div class="align-self-end">
                      <v-btn
                        icon
                        small
                        style="margin-top: -20px"
                        v-if="rubric_.criterias.length > 1"
                        @click.stop="removeItem(c, item, 'Criteria')"
                        v-show="action != 'view'"
                      >
                        <v-icon>mdi-delete-outline</v-icon>
                      </v-btn>
                    </div>
                    <Editable
                      :data="item"
                      :index="c"
                      :parent="0"
                      :action="action"
                      :edit="action != 'view'"
                      type="title"
                      @inputFocus="inputFocus($event)"
                      @inputLostFocus="inputLostFocus($event)"
                      class="mx-auto"
                    />
                  </div>
                </td>
                <div class="wrapper" v-if="action == 'create'">
                  <td
                    v-for="(indicator, n) in item.indicators"
                    :key="n"
                    class="rubric-inner"
                  >
                    <Editable
                      :data="indicator"
                      :index="n"
                      :parent="c"
                      :action="action"
                      :edit="true"
                      type="desc"
                      @inputFocus="inputFocus($event)"
                      @inputLostFocus="inputLostFocus($event)"
                    />
                  </td>
                </div>
                <div class="wrapper" v-else>
                  <td
                    v-if="rubric_"
                    v-for="rating in rubric_.ratings"
                    :key="rating.id"
                    class="rubric-inner"
                  >
                    <div v-for="indicator in rating.indicators" :key="indicator.id">
                      <Editable
                        v-if="indicator.rubric_criteria_id == item.id"
                        :data="indicator"
                        :action="action"
                        :edit="action != 'view'"
                        type="desc"
                        @inputFocus="inputFocus($event)"
                        @inputLostFocus="inputLostFocus($event)"
                      />
                    </div>
                  </td>
                </div>

                <td class="rubric-inner">
                  <div class="d-flex justify-center">
                    <span class="unit-input mx-auto" v-show="action != 'view'">
                      <input
                        class="unit-input__input f13 roboto fw500"
                        type="number"
                        v-model="item.score"
                        v-on:blur="inputScore($event, item)"
                      />
                      <span class="unit-input__append">pts</span>
                    </span>
                    <label class="roboto unit-input__append" v-show="action == 'view'"> {{ item.score }} pts</label>
                  </div>
                </td>
              </tr>
              <tr>
                <th colspan="100%" class="rubric-inner">
                  <div class="d-flex align-center justify-end roboto mx-7">
                    <div class="mr-16">Total Score</div>
                    <div class="f14 secondary-2--text ml-16">
                      {{ scoreLoading ? "Processing..." : `${total} pts` }}
                    </div>
                  </div>
                </th>
              </tr>
            </table>
          </section>

          <ButtonPrimary 
            v-if="action == 'create'"
            @click="save()"
            :label="'Save Rubric'"
            :loading="loading"
            class="mt-5"
          />
        </v-sheet>
      </v-col>
    </v-row>
    <RubricDelete
      :dialog="dialog"
      :deleting="deleting"
      :type="toDelete"
      @delete="emitDelete"
      @close="dialog = false"
    />
  </section>
</template>

<script>
import { mapActions, mapMutations } from "vuex";
import RubricDelete from "./RubricDelete.vue";
import Editable from "./Editable.vue";
export default {
  components: {
    RubricDelete,
    Editable,
  },
  props: ["type", "rubric", "action"],
  data: () => ({
    loading: false,
    onInput: false,
    dialog: false,
    toDelete: null,
    dataToDelete: null,
    indexToDelete: null,
    scoreLoading: false,
    addingRating: false,
    addingCriteria: false,
    deleting: false,
    rubric_: null
    // total: 0
  }),
  mounted(){
    this.rubric_ = this.rubric
  },
  computed: {
    total: {
      get() {
        if(this.rubric.criterias.length === 0) {
          return 0
        }
        let score = this.rubric.criterias.reduce(function (s, a) {
          return parseInt(s) + parseInt(a.score);
        }, 0);

        return score;
      },
      set(val) {
        return val
      }
    },
  },
  methods: {
    ...mapActions("instructor", [
      "addRubricAction",
      "showRubricAction",
      "getRubricsAction",
      "addRubricCriteriaAction",
      "updateRubricAction",
      "addRubricRatingAction",
      "updateIndicatorAction",
      "updateCriteriaAction",
      "updateRatingAction",
      "deleteCriteriaAction",
      "deleteRatingAction",
    ]),
    ...mapMutations(["alertMutation"]),

    getRubric(){
      this.showRubricAction(this.rubric.id).then(res => {
        this.rubric_ = res
        this.loading = false
      }).catch(() => {
        this.loading = false
      })
    },

    save() {
      if(this.$refs.form.validate()) {
        this.loading = true;
        this.addRubricAction({
          title: this.rubric.title,
          description: this.rubric.description,
          type_of_rubric: this.type,
          criterias: this.rubric.criterias,
          ratings: this.rubric.ratings,
        }).then(() => {
          this.alertMutation({
            text: `${this.rubric.title} successfully saved!`,
            show: true,
            type: "success"
          })
          this.getRubricsAction().then(() => {
            this.$emit("onSave", 1);
          })

          this.loading = false;
        }).catch(res => {
          this.alertMutation({
            text: 'Something went wrong',
            show: true,
            type: "error"
          });
          this.loading = false
        })
      }
    },

    removeItem(index, data, type) {
      this.dialog = true;
      this.toDelete = type;
      this.dataToDelete = data;
      this.indexToDelete = index;
    },

    emitDelete() {
      if (this.action == "create") {
        if (this.toDelete == "Rating") {
          this.rubric.ratings.splice(this.indexToDelete, 1);
          this.rubric.criterias.forEach((el) => {
            el.indicators.splice(this.indexToDelete, 1);
          });
        } else {
          this.rubric.criterias.splice(this.indexToDelete, 1);
        }
        if(this.rubric.ratings.length === 0) {
          this.addRating()
        }
        if(this.rubric.criterias.length === 0) {
          this.addCriteria()
        }
        this.dialog = false;
        this.alertMutation({
          text: `${this.toDelete} successfully deleted!`,
          show: true,
          type: "success"
        });
        this.getRubric()
      } else {
        this.deleting = true;
        return this[`delete${this.toDelete}Action`]({
          id: this.dataToDelete.id,
          rubric_id: this.rubric.id,
        }).then(() => {
          this.deleting = false;
          this.dialog = false;
          this.alertMutation({
            text: `${this.toDelete} successfully deleted!`,
            show: true,
            type: "success"
          });
          
          if(this.rubric.ratings.length === 0) {
            this.addRating()
          }
          if(this.rubric.criterias.length === 0) {
            this.addCriteria()
          }

          this.getRubric()
        }).catch(res => {
          this.alertMutation({
            text: 'Something went wrong',
            show: true,
            type: "error"
          });
          this.deleting = false
        })
      }
    },

    addCriteria() {
      // add directly to the backend
      if (this.action == "update") {
        this.addingCriteria = true;
        this.addRubricCriteriaAction(this.rubric.id).then(() => {
          this.addingCriteria = false;
          
          this.alertMutation({
            text: `${this.rubric.title} successfully saved!`,
            show: true,
            type: "success"
          })
          this.getRubric()
        }).catch(res => {
          this.alertMutation({
            text: 'Something went wrong',
            show: true,
            type: "error"
          });
          this.addingCriteria = false
        })
        return;
      }

      let indicators = [];
      this.rubric.ratings.forEach((el) => {
        indicators.push({
          body: "Indicator for criterion",
        });
      });
      this.rubric.criterias.push({
        title: "New Criterion",
        score: 0,
        indicators: indicators,
      });
    },

    addRating() {
      // add directly to the backend
      if (this.action == "update") {
        this.addingRating = true;
        this.addRubricRatingAction(this.$route.params.id).then(() => {
          this.addingRating = false;
          
          this.alertMutation({
            text: `${this.rubric.title} successfully saved!`,
            show: true,
            type: "success"
          })

          this.getRubric()
        }).catch(res => {
          this.alertMutation({
            text: 'Something went wrong',
            show: true,
            type: "error"
          });
          this.addingRating = false
        })
        return;
      }
      let count = this.rubric.ratings.length + 1;
      this.rubric.ratings.unshift({ rate: count });
      this.rubric.criterias.forEach((el) => {
        el.indicators.unshift({ body: "New indicator for criterion" });
      });
    },

    inputFocus(e) {
      let text = e.event.target.innerText;

      if (e.type == "title") {
        if (text == "New Criterion") return (this.rubric.criterias[index].title = "");
      } else {
        if (this.action == "update") return;
        if (text == "Indicator for criterion" || text == "New indicator for criterion")
          return (this.rubric.criterias[e.parent].indicators[e.index].body = "");
      }
    },

    inputLostFocus(e) {
      let text = e.event.target.innerText;
      // @index = data
      if (e.type == "title") {
        if (this.action == "update") {
          this.updateCriteriaAction({
            id: e.index.id,
            title: text,
            score: e.index.score,
          });
          return;
        }
        this.rubric.criterias[e.index].title = text == "" ? "New Criterion" : text;
      } else {
        if (this.action == "update") {
          this.updateIndicatorAction({ id: e.index.id, body: text });
          return;
        }
        this.rubric.criterias[e.parent].indicators[e.index].body =
          text.length == 0 ? "Indicator for criterion" : text;
      }
    },

    inputScore(e, data) {
      if (this.action == "create") {
        if(this.rubric.criterias.length === 0) {
          this.total = 0
        } else {
          this.total = this.rubric.criterias.reduce(function (s, a) {
            return parseInt(s) + parseInt(a.score);
          }, 0);
        }
        return;
      };
      this.scoreLoading = true;
      this.updateCriteriaAction({
        id: data.id,
        title: data.title,
        score: parseInt(e.target.value),
      }).then(() => {
        this.showRubricAction(data.rubric_id).then(res => {
          this.rubric_ = res
          this.scoreLoading = false;
        
          this.alertMutation({
            text: `${this.rubric.title} successfully saved!`,
            show: true,
            type: "success"
          })
        }).catch(() => {
          this.scoreLoading = false;
        });
      }).catch(res => {
          this.alertMutation({
            text: 'Something went wrong',
            show: true,
            type: "error"
          });
          this.scoreLoading = false
        })
    },

    inputRate(e, data) {
      if (this.action == "create") {
        
        return;
      }
      this.updateRatingAction({
        id: data.id,
        rate: parseInt(e.target.value),
      }).then(() => {
        
        this.alertMutation({
          text: `${this.rubric.title} successfully saved!`,
          show: true,
          type: "success"
        })

        this.getRubric()
      }).catch(res => {
        this.alertMutation({
          text: 'Something went wrong',
          show: true,
          type: "error"
        });
      })
    },

    update(){
      this.loading = true
      this.updateRubricAction({ id: this.rubric.id, title: this.rubric.title, description: this.rubric.description }).then(() => {
        this.alertMutation({
          text: `${this.rubric.title} successfully saved!`,
          show: true,
          type: "success"
        })
        this.loading = false
        this.getRubric()
      }).catch(res => {
        this.alertMutation({
          text: 'Something went wrong',
          show: true,
          type: "error"
        });
        this.loading = false
      })
    }
  },

  watch: {
    type(e) {
      this.rubric_ = this.rubric
    }
  }
};
</script>

<style scoped></style>