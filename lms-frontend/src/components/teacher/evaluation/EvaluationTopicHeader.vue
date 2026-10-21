<template>
    <!-- <v-sheet v-if="!loading" class="pa-5 mt-2 custom-border border"> -->
        <section v-if="!loading" class="d-flex flex-wrap ">
            <div class="col-xl-6 col-lg-8">
                <label class="poppins fw500 f12 secondary--text mb-2 align-start">TOPIC *</label>
                <editor
                    v-if="showMce1"
                    v-on:input="updateEvaluation"
                    class="tiny"
                    :api-key="mce_key"
                    :init="{
                        height: 200,
                        menubar: false,
                        plugins: [
                            'advlist autolink lists link image charmap print preview anchor table',
                            'searchreplace visualblocks code fullscreen',
                            'insertdatetime media table paste code help wordcount',
                        ],
                        toolbar:
                            'formatselect | bold italic backcolor | \
                            alignleft aligncenter alignright alignjustify | \
                            table tabledelete | tableprops tablerowprops tablecellprops | tableinsertrowbefore tableinsertrowafter tabledeleterow | tableinsertcolbefore tableinsertcolafter tabledeletecol | \
                            bullist numlist outdent indent | removeformat | help',
                        content_style:
                        'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }',
                    }"
                    />
                <circular v-else />
            </div>
            <div class="col-xl-6 col-lg-4">
                <label class="poppins fw500 f12 secondary-2--text mb-2">DESCRIPTION *</label>
                <editor
                    v-if="showMce2"
                    v-on:input="updateEvaluation"
                    class="tiny"
                    :api-key="mce_key"
                    :init="{
                        height: 200,
                        menubar: false,
                        plugins: [
                        'advlist autolink lists link image charmap print preview anchor',
                        'searchreplace visualblocks code fullscreen',
                        'insertdatetime media table paste code help wordcount',
                        ],
                        toolbar:
                        'formatselect | bold italic backcolor | \
                        alignleft aligncenter alignright alignjustify | \
                        bullist numlist outdent indent | removeformat | help',
                        content_style:
                        'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }',
                    }"
                    />
                <circular v-else />
            </div>
            <v-divider />
            <div class="col-xl-6 col-lg-4">
                <v-btn
                    dense
                    text class="f12 text-capitalize primary-font"
                    color="primary">
                    <v-icon class="mr-1" color="primary"> mdi-image </v-icon>
                    Insert Image
                </v-btn>
            </div>
        </section>
    <circular v-else />
</template>

<script>
import Editor from "@tinymce/tinymce-vue";

export default {
    components: {
        editor: Editor,
    },
    mounted() {
      this.$nextTick(() => {
        setTimeout(() => {
          this.showMce1 = true;
          this.showMce2 = true;
        }, 2000);
      });
    },
    data: () => ({
      showMce1: false,
      showMce2: false,
      loading: false,
      mce_key: `${process.env.VUE_APP_TINYMCE_KEY}`
    }),
}
</script>